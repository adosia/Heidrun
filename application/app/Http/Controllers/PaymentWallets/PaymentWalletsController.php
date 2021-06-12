<?php

namespace App\Http\Controllers\PaymentWallets;

use Exception;
use Throwable;
use App\Services\WalletService;
use App\Services\CardanoCliService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\CreatePaymentWallet;
use Illuminate\Contracts\Foundation\Application;

class PaymentWalletsController extends Controller
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
     * PaymentWalletsController constructor.
     * @param CardanoCliService $cardanoCliService
     * @param WalletService $walletService
     */
    public function __construct(
        CardanoCliService $cardanoCliService,
        WalletService $walletService
    )
    {
        $this->cardanoCliService = $cardanoCliService;
        $this->walletService = $walletService;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $walletList = $this->walletService->getAllWallets(WALLET_TYPE_PAYMENT);

        return view('payment-wallets.index', compact('walletList'));
    }

    /**
     * @return Application|Factory|View
     */
    public function createForm()
    {
        return view('payment-wallets.create');
    }

    /**
     * @param CreatePaymentWallet $request
     * @return RedirectResponse
     */
    public function createWallet(CreatePaymentWallet $request): RedirectResponse
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
                WALLET_TYPE_PAYMENT,
                $walletName,
                $walletAddress
            );

            // Success
            return redirect()
                ->route('payment-wallets.index')
                ->with('success', sprintf(
                    'New wallet "%s" successfully created',
                    $walletName
                ));

        } catch (Throwable $exception) {

            // Handle error
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create payment wallet - ' . $exception->getMessage());

        }
    }
}
