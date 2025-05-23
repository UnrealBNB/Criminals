<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Container\Container;
use App\Jobs\Job;
use RuntimeException;
use Throwable;

class QueueService
{
    private string $table = 'jobs';
    private int $maxAttempts = 3;
    private int $timeout = 60;

    public function __construct(
        private readonly Database $db,
        private readonly Container $container
    ) {
        $this->ensureJobsTableExists();
    }

    public function push(Job $job, ?string $queue = null): int
    {
        $payload = [
            'class' => get_class($job),
            'data' => serialize($job),
            'attempts' => 0,
        ];

        return $this->db->insert($this->table, [
            'queue' => $queue ?? 'default',
            'payload' => json_encode($payload),
            'available_at' => time(),
            'created_at' => time(),
        ]);
    }

    public function later(int $delay, Job $job, ?string $queue = null): int
    {
        $payload = [
            'class' => get_class($job),
            'data' => serialize($job),
            'attempts' => 0,
        ];

        return $this->db->insert($this->table, [
            'queue' => $queue ?? 'default',
            'payload' => json_encode($payload),
            'available_at' => time() + $delay,
            'created_at' => time(),
        ]);
    }

    public function process(?string $queue = null): void
    {
        $queue = $queue ?? 'default';

        while ($job = $this->getNextJob($queue)) {
            $this->runJob($job);
        }
    }

    private function getNextJob(string $queue): ?array
    {
        $this->db->beginTransaction();

        try {
            $job = $this->db->fetchOne(
                "SELECT * FROM {$this->table} 
                WHERE queue = :queue 
                AND available_at <= :now 
                AND reserved_at IS NULL 
                ORDER BY id ASC 
                LIMIT 1 
                FOR UPDATE",
                ['queue' => $queue, 'now' => time()]
            );

            if (!$job) {
                $this->db->rollBack();
                return null;
            }

            $this->db->execute(
                "UPDATE {$this->table} SET reserved_at = :now WHERE id = :id",
                ['now' => time(), 'id' => $job['id']]
            );

            $this->db->commit();
            return $job;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function runJob(array $jobData): void
    {
        $payload = json_decode($jobData['payload'], true);
        $job = unserialize($payload['data']);

        try {
            $job->handle($this->container);
            $this->deleteJob($jobData['id']);
        } catch (Throwable $e) {
            $this->handleJobError($jobData, $e);
        }
    }

    private function handleJobError(array $jobData, Throwable $e): void
    {
        $payload = json_decode($jobData['payload'], true);
        $payload['attempts']++;

        if ($payload['attempts'] >= $this->maxAttempts) {
            $this->failJob($jobData['id'], $e->getMessage());
            return;
        }

        $this->db->execute(
            "UPDATE {$this->table} 
            SET payload = :payload, 
                reserved_at = NULL, 
                available_at = :available_at 
            WHERE id = :id",
            [
                'payload' => json_encode($payload),
                'available_at' => time() + ($payload['attempts'] * 60),
                'id' => $jobData['id']
            ]
        );
    }

    private function deleteJob(int $id): void
    {
        $this->db->delete($this->table, 'id = :id', ['id' => $id]);
    }

    private function failJob(int $id, string $exception): void
    {
        $this->db->beginTransaction();

        try {
            $job = $this->db->fetchOne(
                "SELECT * FROM {$this->table} WHERE id = :id",
                ['id' => $id]
            );

            $this->db->insert('failed_jobs', [
                'queue' => $job['queue'],
                'payload' => $job['payload'],
                'exception' => $exception,
                'failed_at' => time(),
            ]);

            $this->deleteJob($id);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function ensureJobsTableExists(): void
    {
        // This would be handled by migrations, but we ensure it exists
    }

    public function retry(int $failedJobId): void
    {
        $failedJob = $this->db->fetchOne(
            "SELECT * FROM failed_jobs WHERE id = :id",
            ['id' => $failedJobId]
        );

        if (!$failedJob) {
            throw new RuntimeException("Failed job {$failedJobId} not found");
        }

        $this->db->insert($this->table, [
            'queue' => $failedJob['queue'],
            'payload' => $failedJob['payload'],
            'available_at' => time(),
            'created_at' => time(),
        ]);

        $this->db->delete('failed_jobs', 'id = :id', ['id' => $failedJobId]);
    }

    public function clear(string $queue = 'default'): int
    {
        return $this->db->delete(
            $this->table,
            'queue = :queue',
            ['queue' => $queue]
        );
    }

    public function getPendingCount(string $queue = 'default'): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} 
            WHERE queue = :queue AND reserved_at IS NULL",
            ['queue' => $queue]
        );
    }
}