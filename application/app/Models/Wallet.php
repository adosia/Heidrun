<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasFactory;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'type',
        'network',
        'name',
        'address',
        'created_by_user_id',
    ];

    /**
     * @return BelongsTo
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    /**
     * @return string
     */
    public function getNetworkBadgeAttribute(): string
    {
        $bgColor = 'danger';
        if ($this->network == NETWORK_MAINNET) {
            $bgColor = 'primary';
        }
        return sprintf('<span class="badge badge-%s">%s</span>', $bgColor, $this->network);
    }
}
