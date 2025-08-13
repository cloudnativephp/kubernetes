<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\CertificatesK8sIo\V1;

use Kubernetes\API\CertificatesK8sIo\V1\CertificateSigningRequest;

it('can create a CertificateSigningRequest', function (): void {
    $csr = new CertificateSigningRequest();
    expect($csr->getApiVersion())->toBe('certificates.k8s.io/v1');
    expect($csr->getKind())->toBe('CertificateSigningRequest');
});

it('can set and get request', function (): void {
    $csr = new CertificateSigningRequest();
    $request = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0=';
    $csr->setRequest($request);
    expect($csr->getRequest())->toBe($request);
});

it('can set and get signer name', function (): void {
    $csr = new CertificateSigningRequest();
    $csr->setSignerName('kubernetes.io/kube-apiserver-client');
    expect($csr->getSignerName())->toBe('kubernetes.io/kube-apiserver-client');
});

it('can set and get expiration seconds', function (): void {
    $csr = new CertificateSigningRequest();
    $csr->setExpirationSeconds(3600);
    expect($csr->getExpirationSeconds())->toBe(3600);
});

it('can set and get usages', function (): void {
    $csr = new CertificateSigningRequest();
    $usages = ['digital signature', 'key encipherment', 'client auth'];
    $csr->setUsages($usages);
    expect($csr->getUsages())->toBe($usages);
});

it('can add usage', function (): void {
    $csr = new CertificateSigningRequest();
    $csr->addUsage('digital signature');
    $csr->addUsage('key encipherment');
    expect($csr->getUsages())->toBe(['digital signature', 'key encipherment']);
});

it('can set and get username', function (): void {
    $csr = new CertificateSigningRequest();
    $csr->setUsername('system:node:worker-1');
    expect($csr->getUsername())->toBe('system:node:worker-1');
});

it('can set and get UID', function (): void {
    $csr = new CertificateSigningRequest();
    $csr->setUid('uid-789');
    expect($csr->getUid())->toBe('uid-789');
});

it('can set and get groups', function (): void {
    $csr = new CertificateSigningRequest();
    $groups = ['system:nodes', 'system:authenticated'];
    $csr->setGroups($groups);
    expect($csr->getGroups())->toBe($groups);
});

it('can set and get extra attributes', function (): void {
    $csr = new CertificateSigningRequest();
    $extra = ['node-type' => ['worker'], 'region' => ['us-west-2']];
    $csr->setExtra($extra);
    expect($csr->getExtra())->toBe($extra);
});

it('can check CSR status', function (): void {
    $csr = new CertificateSigningRequest();
    expect($csr->getCertificate())->toBeNull();
    expect($csr->getConditions())->toBe([]);
    expect($csr->isApproved())->toBe(false);
    expect($csr->isDenied())->toBe(false);
    expect($csr->isPending())->toBe(true);
});

it('can create client certificate request', function (): void {
    $csr = new CertificateSigningRequest();
    $request = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0=';

    $result = $csr->createClientCertificateRequest($request, 'test-user', ['developers'], 7200);

    expect($result)->toBe($csr);
    expect($csr->getRequest())->toBe($request);
    expect($csr->getSignerName())->toBe('kubernetes.io/kube-apiserver-client');
    expect($csr->getUsername())->toBe('test-user');
    expect($csr->getGroups())->toBe(['developers']);
    expect($csr->getExpirationSeconds())->toBe(7200);
    expect($csr->getUsages())->toBe(['client auth']);
});

it('can create server certificate request', function (): void {
    $csr = new CertificateSigningRequest();
    $request = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0=';

    $result = $csr->createServerCertificateRequest($request, 'kubelet', 3600);

    expect($result)->toBe($csr);
    expect($csr->getRequest())->toBe($request);
    expect($csr->getSignerName())->toBe('kubernetes.io/kubelet-serving');
    expect($csr->getUsername())->toBe('kubelet');
    expect($csr->getExpirationSeconds())->toBe(3600);
    expect($csr->getUsages())->toBe(['digital signature', 'key encipherment', 'server auth']);
});

it('can create node certificate request', function (): void {
    $csr = new CertificateSigningRequest();
    $request = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0=';

    $result = $csr->createNodeCertificateRequest($request, 'worker-1', 86400);

    expect($result)->toBe($csr);
    expect($csr->getRequest())->toBe($request);
    expect($csr->getSignerName())->toBe('kubernetes.io/kube-apiserver-client-kubelet');
    expect($csr->getUsername())->toBe('system:node:worker-1');
    expect($csr->getGroups())->toBe(['system:nodes']);
    expect($csr->getExpirationSeconds())->toBe(86400);
    expect($csr->getUsages())->toBe(['digital signature', 'key encipherment', 'client auth']);
});

it('can set common usage patterns', function (): void {
    $csr = new CertificateSigningRequest();

    $csr->setClientAuthUsages();
    expect($csr->getUsages())->toBe(['digital signature', 'key encipherment', 'client auth']);

    $csr->setServerAuthUsages();
    expect($csr->getUsages())->toBe(['digital signature', 'key encipherment', 'server auth']);
});

it('can chain setter methods', function (): void {
    $csr = new CertificateSigningRequest();
    $result = $csr
        ->setName('test-csr')
        ->setRequest('LS0tLS1CRUdJTi==')
        ->setSignerName('kubernetes.io/kube-apiserver-client')
        ->setUsername('test-user')
        ->setExpirationSeconds(3600);

    expect($result)->toBe($csr);
});
