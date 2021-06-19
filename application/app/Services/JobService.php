<?php

namespace App\Services;

use App\Models\Job;

/**
 * Class JobService
 * @package App\Services
 */
class JobService
{
    /**
     * @param string $jobType
     * @param array $jobPayload
     * @return Job
     */
    public function createJob(string $jobType, array $jobPayload): Job
    {
        $job = new Job;
        $job->fill([
            'type' => $jobType,
            'payload' => json_encode($jobPayload),
        ]);
        $job->save();
        return $job;
    }
}
