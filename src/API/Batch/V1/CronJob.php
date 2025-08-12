<?php

declare(strict_types=1);

namespace Kubernetes\API\Batch\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes CronJob resource.
 *
 * A CronJob creates Jobs on a repeating schedule. CronJob is meant for performing
 * regular scheduled actions such as backups, report generation, and so on.
 *
 * @see https://kubernetes.io/docs/concepts/workloads/controllers/cron-jobs/
 */
class CronJob extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (CronJob)
     */
    public function getKind(): string
    {
        return 'CronJob';
    }

    /**
     * Get the cron schedule.
     *
     * @return string|null The cron schedule
     */
    public function getSchedule(): ?string
    {
        return $this->spec['schedule'] ?? null;
    }

    /**
     * Get the starting deadline in seconds.
     *
     * @return int|null The starting deadline in seconds
     */
    public function getStartingDeadlineSeconds(): ?int
    {
        return $this->spec['startingDeadlineSeconds'] ?? null;
    }

    /**
     * Set the starting deadline in seconds.
     *
     * @param int $seconds The starting deadline in seconds
     *
     * @return self
     */
    public function setStartingDeadlineSeconds(int $seconds): self
    {
        $this->spec['startingDeadlineSeconds'] = $seconds;

        return $this;
    }

    /**
     * Get the concurrency policy.
     *
     * @return string|null The concurrency policy (Allow, Forbid, Replace)
     */
    public function getConcurrencyPolicy(): ?string
    {
        return $this->spec['concurrencyPolicy'] ?? null;
    }

    /**
     * Get the suspend flag.
     *
     * @return bool Whether the CronJob is suspended
     */
    public function isSuspended(): bool
    {
        return $this->spec['suspend'] ?? false;
    }

    /**
     * Get the job template.
     *
     * @return array<string, mixed>|null The job template
     */
    public function getJobTemplate(): ?array
    {
        return $this->spec['jobTemplate'] ?? null;
    }

    /**
     * Get the successful jobs history limit.
     *
     * @return int|null The successful jobs history limit
     */
    public function getSuccessfulJobsHistoryLimit(): ?int
    {
        return $this->spec['successfulJobsHistoryLimit'] ?? null;
    }

    /**
     * Get the failed jobs history limit.
     *
     * @return int|null The failed jobs history limit
     */
    public function getFailedJobsHistoryLimit(): ?int
    {
        return $this->spec['failedJobsHistoryLimit'] ?? null;
    }

    /**
     * Get the timezone for the schedule.
     *
     * @return string|null The timezone
     */
    public function getTimeZone(): ?string
    {
        return $this->spec['timeZone'] ?? null;
    }

    /**
     * Set the timezone for the schedule.
     *
     * @param string $timeZone The timezone (e.g., "America/New_York")
     *
     * @return self
     */
    public function setTimeZone(string $timeZone): self
    {
        $this->spec['timeZone'] = $timeZone;

        return $this;
    }

    /**
     * Set a daily schedule.
     *
     * @param int $hour   The hour (0-23)
     * @param int $minute The minute (0-59)
     *
     * @return self
     */
    public function setDailySchedule(int $hour = 0, int $minute = 0): self
    {
        return $this->setSchedule("{$minute} {$hour} * * *");
    }

    /**
     * Set the cron schedule.
     *
     * @param string $schedule The cron schedule (e.g., "0 1 * * *")
     *
     * @return self
     */
    public function setSchedule(string $schedule): self
    {
        $this->spec['schedule'] = $schedule;

        return $this;
    }

    /**
     * Set a weekly schedule.
     *
     * @param int $dayOfWeek The day of week (0-6, Sunday=0)
     * @param int $hour      The hour (0-23)
     * @param int $minute    The minute (0-59)
     *
     * @return self
     */
    public function setWeeklySchedule(int $dayOfWeek = 0, int $hour = 0, int $minute = 0): self
    {
        return $this->setSchedule("{$minute} {$hour} * * {$dayOfWeek}");
    }

    /**
     * Set a monthly schedule.
     *
     * @param int $dayOfMonth The day of month (1-31)
     * @param int $hour       The hour (0-23)
     * @param int $minute     The minute (0-59)
     *
     * @return self
     */
    public function setMonthlySchedule(int $dayOfMonth = 1, int $hour = 0, int $minute = 0): self
    {
        return $this->setSchedule("{$minute} {$hour} {$dayOfMonth} * *");
    }

    /**
     * Set an hourly schedule.
     *
     * @param int $minute The minute (0-59)
     *
     * @return self
     */
    public function setHourlySchedule(int $minute = 0): self
    {
        return $this->setSchedule("{$minute} * * * *");
    }

    /**
     * Create a simple job template with container specification.
     *
     * @param string             $image         The container image
     * @param array<string>      $command       The command to run
     * @param array<string>|null $args          Optional command arguments
     * @param string             $restartPolicy The restart policy (Never, OnFailure)
     *
     * @return self
     */
    public function createSimpleJobTemplate(
        string $image,
        array $command,
        ?array $args = null,
        string $restartPolicy = 'OnFailure'
    ): self {
        $container = [
            'name'    => 'job-container',
            'image'   => $image,
            'command' => $command,
        ];

        if ($args !== null) {
            $container['args'] = $args;
        }

        $jobSpec = [
            'template' => [
                'spec' => [
                    'containers'    => [$container],
                    'restartPolicy' => $restartPolicy,
                ],
            ],
        ];

        return $this->setJobTemplateSpec($jobSpec);
    }

    /**
     * Set job template with basic configuration.
     *
     * @param array<string, mixed>       $jobSpec The job specification
     * @param array<string, string>|null $labels  Optional labels for the job
     *
     * @return self
     */
    public function setJobTemplateSpec(array $jobSpec, ?array $labels = null): self
    {
        $template = [
            'spec' => $jobSpec,
        ];

        if ($labels !== null) {
            $template['metadata'] = [
                'labels' => $labels,
            ];
        }

        return $this->setJobTemplate($template);
    }

    /**
     * Set the job template.
     *
     * @param array<string, mixed> $template The job template
     *
     * @return self
     */
    public function setJobTemplate(array $template): self
    {
        $this->spec['jobTemplate'] = $template;

        return $this;
    }

    /**
     * Allow concurrent job execution.
     *
     * @return self
     */
    public function allowConcurrency(): self
    {
        return $this->setConcurrencyPolicy('Allow');
    }

    /**
     * Set the concurrency policy.
     *
     * @param string $policy The concurrency policy (Allow, Forbid, Replace)
     *
     * @return self
     */
    public function setConcurrencyPolicy(string $policy): self
    {
        $this->spec['concurrencyPolicy'] = $policy;

        return $this;
    }

    /**
     * Forbid concurrent job execution.
     *
     * @return self
     */
    public function forbidConcurrency(): self
    {
        return $this->setConcurrencyPolicy('Forbid');
    }

    /**
     * Replace running jobs with new ones.
     *
     * @return self
     */
    public function replaceConcurrentJobs(): self
    {
        return $this->setConcurrencyPolicy('Replace');
    }

    /**
     * Suspend the CronJob.
     *
     * @return self
     */
    public function suspend(): self
    {
        return $this->setSuspend(true);
    }

    /**
     * Set the suspend flag.
     *
     * @param bool $suspend Whether to suspend the CronJob
     *
     * @return self
     */
    public function setSuspend(bool $suspend): self
    {
        $this->spec['suspend'] = $suspend;

        return $this;
    }

    /**
     * Resume the CronJob.
     *
     * @return self
     */
    public function resume(): self
    {
        return $this->setSuspend(false);
    }

    /**
     * Set default history limits.
     *
     * @param int $successful Number of successful jobs to keep
     * @param int $failed     Number of failed jobs to keep
     *
     * @return self
     */
    public function setHistoryLimits(int $successful = 3, int $failed = 1): self
    {
        return $this
            ->setSuccessfulJobsHistoryLimit($successful)
            ->setFailedJobsHistoryLimit($failed);
    }

    /**
     * Set the failed jobs history limit.
     *
     * @param int $limit The failed jobs history limit
     *
     * @return self
     */
    public function setFailedJobsHistoryLimit(int $limit): self
    {
        $this->spec['failedJobsHistoryLimit'] = $limit;

        return $this;
    }

    /**
     * Set the successful jobs history limit.
     *
     * @param int $limit The successful jobs history limit
     *
     * @return self
     */
    public function setSuccessfulJobsHistoryLimit(int $limit): self
    {
        $this->spec['successfulJobsHistoryLimit'] = $limit;

        return $this;
    }

    /**
     * Get the current status of the CronJob.
     *
     * @return array<string, mixed> The status
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Get the last schedule time.
     *
     * @return string|null The last schedule time
     */
    public function getLastScheduleTime(): ?string
    {
        return $this->status['lastScheduleTime'] ?? null;
    }

    /**
     * Get the last successful time.
     *
     * @return string|null The last successful time
     */
    public function getLastSuccessfulTime(): ?string
    {
        return $this->status['lastSuccessfulTime'] ?? null;
    }

    /**
     * Check if the CronJob has active jobs.
     *
     * @return bool True if there are active jobs
     */
    public function hasActiveJobs(): bool
    {
        return count($this->getActiveJobs()) > 0;
    }

    /**
     * Get the active jobs.
     *
     * @return array<int, array<string, mixed>> The active jobs
     */
    public function getActiveJobs(): array
    {
        return $this->status['active'] ?? [];
    }
}
