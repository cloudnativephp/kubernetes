# Multi-Resource Applications

Complete examples for deploying complex applications with multiple interdependent Kubernetes resources.

## Full Stack Web Application

### Complete LAMP Stack Deployment
```php
use Kubernetes\API\Core\V1\{ConfigMap, Secret, Service, PersistentVolumeClaim};
use Kubernetes\API\Apps\V1\{Deployment, StatefulSet};
use Kubernetes\API\Networking\V1\Ingress;

class LAMPStackDeployer
{
    private ClientInterface $client;
    private string $namespace;
    private string $appName;
    
    public function __construct(ClientInterface $client, string $namespace, string $appName)
    {
        $this->client = $client;
        $this->namespace = $namespace;
        $this->appName = $appName;
    }
    
    public function deploy(): array
    {
        $resources = [];
        
        // 1. Create ConfigMaps and Secrets first
        $resources[] = $this->createDatabaseConfig();
        $resources[] = $this->createDatabaseSecrets();
        $resources[] = $this->createAppConfig();
        
        // 2. Create PersistentVolumeClaims
        $resources[] = $this->createDatabasePVC();
        
        // 3. Deploy MySQL StatefulSet
        $resources[] = $this->createMySQLStatefulSet();
        $resources[] = $this->createMySQLService();
        
        // 4. Deploy PHP Application
        $resources[] = $this->createPHPDeployment();
        $resources[] = $this->createPHPService();
        
        // 5. Create Ingress for external access
        $resources[] = $this->createIngress();
        
        // Deploy all resources
        $deployed = [];
        foreach ($resources as $resource) {
            $deployed[] = $this->client->create($resource);
        }
        
        return $deployed;
    }
    
    private function createDatabaseConfig(): ConfigMap
    {
        $configMap = new ConfigMap();
        return $configMap->setName($this->appName . '-db-config')
                        ->setNamespace($this->namespace)
                        ->setData([
                            'mysql.conf' => $this->getMySQLConfig(),
                            'init.sql' => $this->getDatabaseInitScript()
                        ]);
    }
    
    private function createDatabaseSecrets(): Secret
    {
        $secret = new Secret();
        return $secret->setName($this->appName . '-db-secrets')
                     ->setNamespace($this->namespace)
                     ->setStringData([
                         'mysql-root-password' => $this->generatePassword(),
                         'mysql-user' => 'appuser',
                         'mysql-password' => $this->generatePassword(),
                         'mysql-database' => $this->appName . '_db'
                     ]);
    }
    
    private function createAppConfig(): ConfigMap
    {
        $configMap = new ConfigMap();
        return $configMap->setName($this->appName . '-app-config')
                        ->setNamespace($this->namespace)
                        ->setData([
                            'php.ini' => $this->getPHPConfig(),
                            'apache.conf' => $this->getApacheConfig(),
                            'app.env' => $this->getAppEnvironment()
                        ]);
    }
    
    private function createDatabasePVC(): PersistentVolumeClaim
    {
        $pvc = new PersistentVolumeClaim();
        return $pvc->setName($this->appName . '-db-storage')
                  ->setNamespace($this->namespace)
                  ->setAccessModes(['ReadWriteOnce'])
                  ->setStorageClass('ssd')
                  ->setStorage('10Gi');
    }
    
    private function createMySQLStatefulSet(): StatefulSet
    {
        $statefulSet = new StatefulSet();
        return $statefulSet->setName($this->appName . '-mysql')
                          ->setNamespace($this->namespace)
                          ->setReplicas(1)
                          ->setServiceName($this->appName . '-mysql-headless')
                          ->setSelectorMatchLabels(['app' => $this->appName, 'component' => 'mysql'])
                          ->addContainer('mysql', 'mysql:8.0', [3306])
                          ->setContainerEnvFromSecret('mysql', $this->appName . '-db-secrets')
                          ->setContainerResources('mysql', [
                              'requests' => ['cpu' => '500m', 'memory' => '1Gi'],
                              'limits' => ['cpu' => '1', 'memory' => '2Gi']
                          ])
                          ->addVolumeMount('mysql', 'data', '/var/lib/mysql')
                          ->addVolumeMount('mysql', 'config', '/etc/mysql/conf.d')
                          ->addPvcTemplate('data', 'ssd', '10Gi')
                          ->addConfigMapVolume('config', $this->appName . '-db-config')
                          ->setReadinessProbe('mysql', [
                              'exec' => ['command' => ['mysqladmin', 'ping', '-h', 'localhost']],
                              'initialDelaySeconds' => 30,
                              'periodSeconds' => 10
                          ]);
    }
    
    private function createMySQLService(): Service
    {
        $service = new Service();
        return $service->setName($this->appName . '-mysql')
                      ->setNamespace($this->namespace)
                      ->setType('ClusterIP')
                      ->setSelectorMatchLabels(['app' => $this->appName, 'component' => 'mysql'])
                      ->addPort('mysql', 3306, 3306);
    }
    
    private function createPHPDeployment(): Deployment
    {
        $deployment = new Deployment();
        return $deployment->setName($this->appName . '-php')
                         ->setNamespace($this->namespace)
                         ->setReplicas(3)
                         ->setSelectorMatchLabels(['app' => $this->appName, 'component' => 'php'])
                         ->addContainer('php', 'php:8.1-apache', [80])
                         ->setContainerEnv('php', [
                             'DB_HOST' => $this->appName . '-mysql',
                             'DB_PORT' => '3306'
                         ])
                         ->setContainerEnvFromSecret('php', $this->appName . '-db-secrets')
                         ->setContainerResources('php', [
                             'requests' => ['cpu' => '100m', 'memory' => '256Mi'],
                             'limits' => ['cpu' => '500m', 'memory' => '512Mi']
                         ])
                         ->addConfigMapVolume('app-config', $this->appName . '-app-config')
                         ->addVolumeMount('php', 'app-config', '/usr/local/etc/php/conf.d')
                         ->setReadinessProbe('php', [
                             'httpGet' => ['path' => '/health.php', 'port' => 80],
                             'initialDelaySeconds' => 10,
                             'periodSeconds' => 5
                         ]);
    }
    
    private function createPHPService(): Service
    {
        $service = new Service();
        return $service->setName($this->appName . '-php')
                      ->setNamespace($this->namespace)
                      ->setType('ClusterIP')
                      ->setSelectorMatchLabels(['app' => $this->appName, 'component' => 'php'])
                      ->addPort('http', 80, 80);
    }
    
    private function createIngress(): Ingress
    {
        $ingress = new Ingress();
        return $ingress->setName($this->appName . '-ingress')
                      ->setNamespace($this->namespace)
                      ->setIngressClassName('nginx')
                      ->addTLSHost($this->appName . '.example.com', $this->appName . '-tls')
                      ->addRule($this->appName . '.example.com', [
                          'path' => '/',
                          'pathType' => 'Prefix',
                          'service' => $this->appName . '-php',
                          'port' => 80
                      ]);
    }
}
```

## Microservices Architecture

### Complete E-commerce Platform
```php
class EcommercePlatformDeployer
{
    private ClientInterface $client;
    private string $namespace;
    
    public function deployPlatform(): array
    {
        $services = [
            'user-service' => $this->createUserService(),
            'product-service' => $this->createProductService(),
            'order-service' => $this->createOrderService(),
            'payment-service' => $this->createPaymentService(),
            'notification-service' => $this->createNotificationService(),
            'api-gateway' => $this->createAPIGateway(),
        ];
        
        $infrastructure = [
            'redis' => $this->createRedisCluster(),
            'postgres' => $this->createPostgreSQLCluster(),
            'kafka' => $this->createKafkaCluster(),
        ];
        
        $monitoring = [
            'prometheus' => $this->createPrometheusStack(),
            'grafana' => $this->createGrafanaInstance(),
            'jaeger' => $this->createJaegerTracing(),
        ];
        
        // Deploy in order: infrastructure, services, monitoring
        $deployed = [];
        
        foreach ($infrastructure as $name => $resources) {
            foreach ($resources as $resource) {
                $deployed[] = $this->client->create($resource);
            }
            $this->waitForServiceReady($name);
        }
        
        foreach ($services as $name => $resources) {
            foreach ($resources as $resource) {
                $deployed[] = $this->client->create($resource);
            }
        }
        
        foreach ($monitoring as $name => $resources) {
            foreach ($resources as $resource) {
                $deployed[] = $this->client->create($resource);
            }
        }
        
        return $deployed;
    }
    
    private function createUserService(): array
    {
        $deployment = new Deployment();
        $deployment->setName('user-service')
                  ->setNamespace($this->namespace)
                  ->setReplicas(3)
                  ->setSelectorMatchLabels(['app' => 'user-service'])
                  ->addContainer('app', 'ecommerce/user-service:v1.2.0', [8080])
                  ->setContainerEnv('app', [
                      'DATABASE_URL' => 'postgresql://postgres:5432/users',
                      'REDIS_URL' => 'redis://redis:6379',
                      'JWT_SECRET_KEY' => '/etc/secrets/jwt-key',
                      'SERVICE_NAME' => 'user-service'
                  ])
                  ->setContainerResources('app', [
                      'requests' => ['cpu' => '100m', 'memory' => '256Mi'],
                      'limits' => ['cpu' => '500m', 'memory' => '512Mi']
                  ]);
        
        $service = new Service();
        $service->setName('user-service')
               ->setNamespace($this->namespace)
               ->setType('ClusterIP')
               ->setSelectorMatchLabels(['app' => 'user-service'])
               ->addPort('http', 8080, 8080)
               ->addPort('metrics', 9090, 9090);
        
        // Add service mesh annotations
        $deployment->addAnnotation('sidecar.istio.io/inject', 'true');
        $service->addAnnotation('prometheus.io/scrape', 'true')
               ->addAnnotation('prometheus.io/port', '9090');
        
        return [$deployment, $service];
    }
    
    private function createAPIGateway(): array
    {
        $configMap = new ConfigMap();
        $configMap->setName('api-gateway-config')
                 ->setNamespace($this->namespace)
                 ->setData([
                     'nginx.conf' => $this->getAPIGatewayConfig(),
                     'rate-limit.conf' => $this->getRateLimitConfig()
                 ]);
        
        $deployment = new Deployment();
        $deployment->setName('api-gateway')
                  ->setNamespace($this->namespace)
                  ->setReplicas(2)
                  ->setSelectorMatchLabels(['app' => 'api-gateway'])
                  ->addContainer('nginx', 'nginx:1.21', [80, 443])
                  ->addContainer('rate-limiter', 'redis/rate-limiter:latest')
                  ->setContainerResources('nginx', [
                      'requests' => ['cpu' => '200m', 'memory' => '256Mi'],
                      'limits' => ['cpu' => '1', 'memory' => '512Mi']
                  ])
                  ->addConfigMapVolume('config', 'api-gateway-config')
                  ->addVolumeMount('nginx', 'config', '/etc/nginx/conf.d');
        
        $service = new Service();
        $service->setName('api-gateway')
               ->setNamespace($this->namespace)
               ->setType('LoadBalancer')
               ->setSelectorMatchLabels(['app' => 'api-gateway'])
               ->addPort('http', 80, 80)
               ->addPort('https', 443, 443);
        
        $ingress = new Ingress();
        $ingress->setName('api-gateway-ingress')
               ->setNamespace($this->namespace)
               ->setIngressClassName('nginx')
               ->addTLSHost('api.ecommerce.com', 'api-tls-cert')
               ->addRule('api.ecommerce.com', [
                   'path' => '/',
                   'pathType' => 'Prefix',
                   'service' => 'api-gateway',
                   'port' => 80
               ]);
        
        return [$configMap, $deployment, $service, $ingress];
    }
}
```

## Data Pipeline Application

### Complete ETL Pipeline with Message Queues
```php
class DataPipelineDeployer
{
    public function deployETLPipeline(): array
    {
        return [
            // Data ingestion layer
            ...$this->createKafkaCluster(),
            ...$this->createSchemaRegistry(),
            
            // Processing layer
            ...$this->createDataIngestionService(),
            ...$this->createDataTransformationService(),
            ...$this->createDataValidationService(),
            
            // Storage layer
            ...$this->createElasticsearchCluster(),
            ...$this->createPostgreSQLAnalytics(),
            
            // Monitoring and alerting
            ...$this->createPrometheusStack(),
            ...$this->createKibanaInstance(),
            ...$this->createAlertManager(),
        ];
    }
    
    private function createDataIngestionService(): array
    {
        // CronJob for scheduled data ingestion
        $cronJob = new CronJob();
        $cronJob->setName('data-ingestion-job')
               ->setNamespace('data-pipeline')
               ->setSchedule('0 * * * *') // Every hour
               ->setJobTemplate([
                   'spec' => [
                       'template' => [
                           'spec' => [
                               'containers' => [[
                                   'name' => 'ingestion',
                                   'image' => 'data-pipeline/ingestion:v2.0.0',
                                   'env' => [
                                       ['name' => 'KAFKA_BROKERS', 'value' => 'kafka:9092'],
                                       ['name' => 'SOURCE_API_URL', 'valueFrom' => [
                                           'secretKeyRef' => ['name' => 'api-secrets', 'key' => 'url']
                                       ]],
                                       ['name' => 'BATCH_SIZE', 'value' => '1000']
                                   ],
                                   'resources' => [
                                       'requests' => ['cpu' => '500m', 'memory' => '1Gi'],
                                       'limits' => ['cpu' => '2', 'memory' => '4Gi']
                                   ]
                               ]],
                               'restartPolicy' => 'OnFailure'
                           ]
                       ]
                   ]
               ])
               ->setHistoryLimits(successfulJobsHistoryLimit: 3, failedJobsHistoryLimit: 1);
        
        // Real-time ingestion deployment
        $deployment = new Deployment();
        $deployment->setName('realtime-ingestion')
                  ->setNamespace('data-pipeline')
                  ->setReplicas(3)
                  ->setSelectorMatchLabels(['app' => 'realtime-ingestion'])
                  ->addContainer('ingestion', 'data-pipeline/realtime-ingestion:v2.0.0', [8080])
                  ->setContainerEnv('ingestion', [
                      'KAFKA_BROKERS' => 'kafka:9092',
                      'KAFKA_TOPIC' => 'raw-events',
                      'PROCESSING_THREADS' => '4'
                  ])
                  ->setContainerResources('ingestion', [
                      'requests' => ['cpu' => '200m', 'memory' => '512Mi'],
                      'limits' => ['cpu' => '1', 'memory' => '2Gi']
                  ]);
        
        $service = new Service();
        $service->setName('realtime-ingestion')
               ->setNamespace('data-pipeline')
               ->setType('ClusterIP')
               ->setSelectorMatchLabels(['app' => 'realtime-ingestion'])
               ->addPort('http', 8080, 8080);
        
        return [$cronJob, $deployment, $service];
    }
    
    private function createDataTransformationService(): array
    {
        // StatefulSet for stateful stream processing
        $statefulSet = new StatefulSet();
        $statefulSet->setName('stream-processor')
                   ->setNamespace('data-pipeline')
                   ->setReplicas(3)
                   ->setServiceName('stream-processor-headless')
                   ->setSelectorMatchLabels(['app' => 'stream-processor'])
                   ->addContainer('processor', 'data-pipeline/stream-processor:v2.0.0', [8080])
                   ->setContainerEnv('processor', [
                       'KAFKA_INPUT_TOPIC' => 'raw-events',
                       'KAFKA_OUTPUT_TOPIC' => 'processed-events',
                       'STATE_STORE_PATH' => '/var/lib/processor/state',
                       'CHECKPOINT_INTERVAL' => '30000'
                   ])
                   ->addPvcTemplate('state-storage', 'ssd', '10Gi')
                   ->addVolumeMount('processor', 'state-storage', '/var/lib/processor/state')
                   ->setContainerResources('processor', [
                       'requests' => ['cpu' => '1', 'memory' => '2Gi'],
                       'limits' => ['cpu' => '4', 'memory' => '8Gi']
                   ]);
        
        $headlessService = new Service();
        $headlessService->setName('stream-processor-headless')
                       ->setNamespace('data-pipeline')
                       ->setType('ClusterIP')
                       ->setClusterIP('None') // Headless service
                       ->setSelectorMatchLabels(['app' => 'stream-processor'])
                       ->addPort('http', 8080, 8080);
        
        return [$statefulSet, $headlessService];
    }
}
```

## Monitoring and Observability Stack

### Complete Observability Platform
```php
class ObservabilityStackDeployer
{
    public function deployObservabilityStack(): array
    {
        return [
            // Metrics collection
            ...$this->createPrometheusOperator(),
            ...$this->createPrometheusInstance(),
            ...$this->createNodeExporter(),
            ...$this->createKubeStateMetrics(),
            
            // Log aggregation
            ...$this->createElasticsearchCluster(),
            ...$this->createLogstashDeployment(),
            ...$this->createFluentdDaemonSet(),
            ...$this->createKibanaInstance(),
            
            // Distributed tracing
            ...$this->createJaegerOperator(),
            ...$this->createJaegerInstance(),
            
            // Alerting
            ...$this->createAlertManagerCluster(),
            ...$this->createGrafanaInstance(),
            
            // Service mesh observability
            ...$this->createIstioTelemetryV2(),
            ...$this->createKialiInstance(),
        ];
    }
    
    private function createPrometheusInstance(): array
    {
        // ConfigMap for Prometheus configuration
        $configMap = new ConfigMap();
        $configMap->setName('prometheus-config')
                 ->setNamespace('monitoring')
                 ->setData([
                     'prometheus.yml' => $this->getPrometheusConfig(),
                     'alert.rules.yml' => $this->getAlertRules()
                 ]);
        
        // StatefulSet for Prometheus server
        $statefulSet = new StatefulSet();
        $statefulSet->setName('prometheus')
                   ->setNamespace('monitoring')
                   ->setReplicas(2) // HA setup
                   ->setServiceName('prometheus-headless')
                   ->setSelectorMatchLabels(['app' => 'prometheus'])
                   ->addContainer('prometheus', 'prom/prometheus:v2.40.0', [9090])
                   ->setContainerArgs('prometheus', [
                       '--config.file=/etc/prometheus/prometheus.yml',
                       '--storage.tsdb.path=/prometheus',
                       '--web.console.libraries=/etc/prometheus/console_libraries',
                       '--web.console.templates=/etc/prometheus/consoles',
                       '--storage.tsdb.retention.time=15d',
                       '--web.enable-lifecycle',
                       '--web.enable-admin-api'
                   ])
                   ->addPvcTemplate('storage', 'ssd', '100Gi')
                   ->addVolumeMount('prometheus', 'storage', '/prometheus')
                   ->addConfigMapVolume('config', 'prometheus-config')
                   ->addVolumeMount('prometheus', 'config', '/etc/prometheus')
                   ->setContainerResources('prometheus', [
                       'requests' => ['cpu' => '500m', 'memory' => '2Gi'],
                       'limits' => ['cpu' => '2', 'memory' => '8Gi']
                   ]);
        
        return [$configMap, $statefulSet];
    }
}
```

These comprehensive examples demonstrate how to deploy complex, production-ready applications using the Kubernetes PHP client library, covering everything from simple web applications to sophisticated microservices architectures and observability platforms.
