<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Collection;

/**
 * Class WalletService
 * @package App\Services
 */
class WalletService
{
    /**
     * @param string $walletId
     * @param string $walletType
     * @return Wallet|null
     */
    public function findById(string $walletId, string $walletType): ?Wallet
    {
        return Wallet::where('id', $walletId)
            ->where('type', $walletType)
            ->with('createdByUser')
            ->first();
    }

    /**
     * @param string $walletName
     * @return Wallet|null
     */
    public function findByName(string $walletName): ?Wallet
    {
        return Wallet::where('name', $walletName)->with('createdByUser')->first();
    }

    /**
     * @param string $walletType
     * @param string $walletName
     * @param string $walletAddress
     */
    public function createWallet(string $walletType, string $walletName, string $walletAddress): void
    {
        $wallet = new Wallet;
        $wallet->fill([
            'type' => $walletType,
            'network' => env('CARDANO_NETWORK'),
            'name' => $walletName,
            'address' => $walletAddress,
            'created_by_user_id' => auth()->id(),
        ]);
        $wallet->save();
    }

    /**
     * @param string $walletType
     * @return Collection
     */
    public function getAllWallets(string $walletType): Collection
    {
        return Wallet::where('type', $walletType)->with('createdByUser')->get();
    }
}
