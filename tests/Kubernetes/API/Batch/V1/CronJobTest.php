<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Batch\V1;

use Kubernetes\API\Batch\V1\CronJob;

it('can create a cron job', function (): void {
    $cronJob = new CronJob();
    expect($cronJob->getApiVersion())->toBe('batch/v1');
    expect($cronJob->getKind())->toBe('CronJob');
});

it('can set and get namespace', function (): void {
    $cronJob = new CronJob();
    $cronJob->setNamespace('default');
    expect($cronJob->getNamespace())->toBe('default');
});

it('can set and get schedule', function (): void {
    $cronJob = new CronJob();
    $cronJob->setSchedule('0 2 * * *');
    expect($cronJob->getSchedule())->toBe('0 2 * * *');
});

it('can set daily schedule', function (): void {
    $cronJob = new CronJob();
    $cronJob->setDailySchedule(14, 30);
    expect($cronJob->getSchedule())->toBe('30 14 * * *');
});

it('can set weekly schedule', function (): void {
    $cronJob = new CronJob();
    $cronJob->setWeeklySchedule(1, 9, 0); // Monday at 9:00 AM
    expect($cronJob->getSchedule())->toBe('0 9 * * 1');
});

it('can set monthly schedule', function (): void {
    $cronJob = new CronJob();
    $cronJob->setMonthlySchedule(15, 12, 0); // 15th of month at noon
    expect($cronJob->getSchedule())->toBe('0 12 15 * *');
});

it('can set hourly schedule', function (): void {
    $cronJob = new CronJob();
    $cronJob->setHourlySchedule(15); // 15 minutes past every hour
    expect($cronJob->getSchedule())->toBe('15 * * * *');
});

it('can set and get starting deadline seconds', function (): void {
    $cronJob = new CronJob();
    $cronJob->setStartingDeadlineSeconds(100);
    expect($cronJob->getStartingDeadlineSeconds())->toBe(100);
});

it('can set and get concurrency policy', function (): void {
    $cronJob = new CronJob();
    $cronJob->setConcurrencyPolicy('Forbid');
    expect($cronJob->getConcurrencyPolicy())->toBe('Forbid');
});

it('can allow concurrency', function (): void {
    $cronJob = new CronJob();
    $cronJob->allowConcurrency();
    expect($cronJob->getConcurrencyPolicy())->toBe('Allow');
});

it('can forbid concurrency', function (): void {
    $cronJob = new CronJob();
    $cronJob->forbidConcurrency();
    expect($cronJob->getConcurrencyPolicy())->toBe('Forbid');
});

it('can replace concurrent jobs', function (): void {
    $cronJob = new CronJob();
    $cronJob->replaceConcurrentJobs();
    expect($cronJob->getConcurrencyPolicy())->toBe('Replace');
});

it('can set and get suspend flag', function (): void {
    $cronJob = new CronJob();
    $cronJob->setSuspend(true);
    expect($cronJob->isSuspended())->toBeTrue();
});

it('can suspend and resume', function (): void {
    $cronJob = new CronJob();
    $cronJob->suspend();
    expect($cronJob->isSuspended())->toBeTrue();

    $cronJob->resume();
    expect($cronJob->isSuspended())->toBeFalse();
});

it('defaults to not suspended', function (): void {
    $cronJob = new CronJob();
    expect($cronJob->isSuspended())->toBeFalse();
});

it('can set and get job template', function (): void {
    $cronJob = new CronJob();
    $template = [
        'spec' => [
            'template' => [
                'spec' => [
                    'containers' => [
                        [
                            'name'  => 'backup',
                            'image' => 'backup-tool:latest',
                        ],
                    ],
                    'restartPolicy' => 'OnFailure',
                ],
            ],
        ],
    ];

    $cronJob->setJobTemplate($template);
    expect($cronJob->getJobTemplate())->toBe($template);
});

it('can set job template spec', function (): void {
    $cronJob = new CronJob();
    $jobSpec = [
        'template' => [
            'spec' => [
                'containers'    => [['name' => 'test', 'image' => 'test:latest']],
                'restartPolicy' => 'Never',
            ],
        ],
    ];
    $labels = ['app' => 'backup'];

    $cronJob->setJobTemplateSpec($jobSpec, $labels);

    $template = $cronJob->getJobTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec'])->toBe($jobSpec);
        expect($template['metadata']['labels'])->toBe($labels);
    }
});

it('can create simple job template', function (): void {
    $cronJob = new CronJob();
    $cronJob->createSimpleJobTemplate(
        'alpine:latest',
        ['sh', '-c'],
        ['echo "Hello World"'],
        'Never'
    );

    $template = $cronJob->getJobTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        $container = $template['spec']['template']['spec']['containers'][0];
        expect($container['name'])->toBe('job-container');
        expect($container['image'])->toBe('alpine:latest');
        expect($container['command'])->toBe(['sh', '-c']);
        expect($container['args'])->toBe(['echo "Hello World"']);
        expect($template['spec']['template']['spec']['restartPolicy'])->toBe('Never');
    }
});

it('can set and get history limits', function (): void {
    $cronJob = new CronJob();
    $cronJob->setSuccessfulJobsHistoryLimit(5);
    $cronJob->setFailedJobsHistoryLimit(2);

    expect($cronJob->getSuccessfulJobsHistoryLimit())->toBe(5);
    expect($cronJob->getFailedJobsHistoryLimit())->toBe(2);
});

it('can set history limits with helper method', function (): void {
    $cronJob = new CronJob();
    $cronJob->setHistoryLimits(10, 3);

    expect($cronJob->getSuccessfulJobsHistoryLimit())->toBe(10);
    expect($cronJob->getFailedJobsHistoryLimit())->toBe(3);
});

it('can set and get timezone', function (): void {
    $cronJob = new CronJob();
    $cronJob->setTimeZone('America/New_York');
    expect($cronJob->getTimeZone())->toBe('America/New_York');
});

it('can get status fields', function (): void {
    $cronJob = new CronJob();
    expect($cronJob->getActiveJobs())->toBe([]);
    expect($cronJob->getLastScheduleTime())->toBeNull();
    expect($cronJob->getLastSuccessfulTime())->toBeNull();
    expect($cronJob->hasActiveJobs())->toBeFalse();
});

it('returns empty arrays when no configuration is set', function (): void {
    $cronJob = new CronJob();
    expect($cronJob->getActiveJobs())->toBe([]);
    expect($cronJob->getStatus())->toBe([]);
});

it('returns null when no configuration is set', function (): void {
    $cronJob = new CronJob();
    expect($cronJob->getSchedule())->toBeNull();
    expect($cronJob->getStartingDeadlineSeconds())->toBeNull();
    expect($cronJob->getConcurrencyPolicy())->toBeNull();
    expect($cronJob->getJobTemplate())->toBeNull();
    expect($cronJob->getSuccessfulJobsHistoryLimit())->toBeNull();
    expect($cronJob->getFailedJobsHistoryLimit())->toBeNull();
    expect($cronJob->getTimeZone())->toBeNull();
});

it('can chain setter methods', function (): void {
    $cronJob = new CronJob();
    $result = $cronJob
        ->setName('backup-job')
        ->setNamespace('default')
        ->setDailySchedule(2, 0)
        ->forbidConcurrency()
        ->setHistoryLimits(3, 1);

    expect($result)->toBe($cronJob);
    expect($cronJob->getName())->toBe('backup-job');
    expect($cronJob->getNamespace())->toBe('default');
    expect($cronJob->getSchedule())->toBe('0 2 * * *');
    expect($cronJob->getConcurrencyPolicy())->toBe('Forbid');
});
