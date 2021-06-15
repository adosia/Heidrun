<?php

namespace App\Http\Controllers\DropWallets;

use Exception;
use Throwable;
use App\Services\WalletService;
use App\Services\CardanoCliService;
use App\Services\BlockFrostService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\CreateDropWallet;
use Illuminate\Contracts\Foundation\Application;

class DropWalletsController extends Controller
{
    /**
     * @var CardanoCliService $cardanoCliService
     */
    private $cardanoCliService;

    /**
     * @var WalletService $walletService
     */
    private $walletService;

    /**
     * @var BlockFrostService $blockFrostService
     */
    private $blockFrostService;

    /**
     * PaymentWalletsController constructor.
     * @param CardanoCliService $cardanoCliService
     * @param WalletService $walletService
     * @param BlockFrostService $blockFrostService
     */
    public function __construct(
        CardanoCliService $cardanoCliService,
        WalletService $walletService,
        BlockFrostService $blockFrostService
    )
    {
        $this->cardanoCliService = $cardanoCliService;
        $this->walletService = $walletService;
        $this->blockFrostService = $blockFrostService;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $walletList = $this->walletService->getAllWallets(WALLET_TYPE_DROP);

        return view('drop-wallets.index', compact('walletList'));
    }

    /**
     * @return Application|Factory|View
     */
    public function createForm()
    {
        return view('drop-wallets.create');
    }

    /**
     * @param CreateDropWallet $request
     * @return RedirectResponse
     */
    public function createWallet(CreateDropWallet $request): RedirectResponse
    {
        try {

            // Parse input
            $walletName = $request->name;

            // Check if wallet already exists in the database
            if (!is_null($this->walletService->findByName($walletName))) {
                throw new Exception(sprintf(
                    'Wallet "%s" already exist, choose another name',
                    $walletName
                ));
            }

            // Create wallet
            $walletAddress = $this->cardanoCliService->createWallet($walletName);

            // Save wallet in database
            $this->walletService->createWallet(
                WALLET_TYPE_DROP,
                $walletName,
                $walletAddress
            );

            // Success
            return redirect()
                ->route('drop-wallets.index')
                ->with('success', sprintf(
                    'New wallet "%s" successfully created',
                    $walletName
                ));

        } catch (Throwable $exception) {

            // Handle error
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create drop wallet - ' . $exception->getMessage());

        }
    }

    /**
     * @param int $walletId
     * @return Application|Factory|View|RedirectResponse
     */
    public function show(int $walletId)
    {
        try {

            // Load database wallet
            $wallet = $this->walletService->findById($walletId, WALLET_TYPE_DROP);

            // Check if wallet exists
            if (!$wallet) {
                throw new Exception(sprintf(
                    'Drop Wallet with id "%d" does not exist',
                    $walletId
                ));
            }

            // Load blockchain address
            $addressUTXOs = [];
            try {
                $addressUTXOs = $this->blockFrostService->get("addresses/{$wallet->address}/utxos");
            } catch (Throwable $exception) {
                logError("Failed to load wallet #{$walletId}", $exception);
            }

            // Render info
            return view(
                'drop-wallets.show',
                compact('wallet', 'addressUTXOs')
            );

        } catch (Throwable $exception) {

            // Handle error
            return redirect()
                ->route('drop-wallets.index')
                ->with('error', sprintf(
                    'Failed to load payment wallet #%d - %s',
                    $walletId,
                    $exception->getMessage()
                ));

        }
    }
}
