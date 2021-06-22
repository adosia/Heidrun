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
     */
    public function addLog(string $message): void
    {
        $this->update([
            'logs' => $this->logs . '[ ' . Carbon::now()->toDateTimeString() . ' ] : ' . $message . PHP_EOL,
        ]);
    }
}
