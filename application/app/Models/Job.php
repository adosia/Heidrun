<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'type',
        'payload',
        'status',
        'attempts',
        'logs',
    ];

    /**
     * @param string $message
     * @param string|null $newStatus
     */
    public function addLog(string $message, string $newStatus = null): void
    {
        $payload = [
            'logs' => $this->logs . '[ ' . Carbon::now()->toDateTimeString() . ' ] : ' . $message . PHP_EOL,
        ];

        if (!empty($newStatus)) {
            $payload['status'] = $newStatus;
        }

        $this->update($payload);
    }
}
