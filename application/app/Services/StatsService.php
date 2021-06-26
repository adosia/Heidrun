<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Wallet;

/**
 * Class StatsService
 * @package App\Services
 */
class StatsService
{
    /**
     * @return array
     */
    public function summary(): array
    {
        return [
            'pendingJobs' => $this->countJobsByStatus(JOB_STATUS_PENDING),
            'processingJobs' => $this->countJobsByStatus(JOB_STATUS_PROCESSING),
            'successJobs' => $this->countJobsByStatus(JOB_STATUS_SUCCESS),
            'errorJobs' => $this->countJobsByStatus(JOB_STATUS_ERROR),
            'cpu' => $this->cpuInfo(),
            'ram' => $this->memoryInfo(),
            'disk' => $this->diskInfo(),
        ];
    }

    /**
     * @return int
     */
    private function countAllWallets(): int
    {
        return Wallet::count();
    }

    /**
     * @param string $status
     * @return int
     */
    private function countJobsByStatus(string $status): int
    {
        return Job::where('status', $status)->count();
    }

    /**
     * @return array
     */
    private function cpuInfo(): array
    {
        $prevVal = shell_exec("cat /proc/stat");
        $prevArr = explode(' ',trim($prevVal));
        $prevTotal = $prevArr[2] + $prevArr[3] + $prevArr[4] + $prevArr[5];
        $prevIdle = $prevArr[5];
        usleep(0.15 * 1000000);
        $val = shell_exec("cat /proc/stat");
        $arr = explode(' ', trim($val));
        $total = $arr[2] + $arr[3] + $arr[4] + $arr[5];
        $idle = $arr[5];
        $intervalTotal = intval($total - $prevTotal);
        $cpuPercent = intval(100 * (($intervalTotal - ($idle - $prevIdle)) / $intervalTotal));
        $cpuStats = shell_exec("cat /proc/cpuinfo | grep model\ name");
        $cpuModel = strstr($cpuStats, "\n", true);
        [,$cpuModelName] = explode(':', $cpuModel);

        return [
            'name' => trim($cpuModelName),
            'percent' => $cpuPercent,
        ];
    }

    /**
     * @return array
     */
    private function memoryInfo(): array
    {
        $freeMemoryStats = shell_exec("cat /proc/meminfo | grep MemFree");
        $memoryFree = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $freeMemoryStats) / 1024 / 1024, 2);
        $totalMemoryStats = shell_exec("cat /proc/meminfo | grep MemTotal");
        $memoryTotal = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $totalMemoryStats) / 1024 / 1024, 2);

        return [
            'free' => $memoryFree,
            'used' => $memoryTotal - $memoryFree,
            'total' => $memoryTotal,
        ];
    }

    /**
     * @return array
     */
    private function diskInfo(): array
    {
        $diskFree = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
        $diskTotal = round(disk_total_space("/") / 1024 / 1024/ 1024, 2);
        $diskUsed = $diskTotal - $diskFree;

        return [
            'free' => $diskFree,
            'used' => $diskUsed,
            'total' => $diskTotal,
        ];
    }
}
