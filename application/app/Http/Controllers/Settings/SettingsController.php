<?php

namespace App\Http\Controllers\Settings;

use Throwable;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UpdateSettings;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class SettingsController extends Controller
{
    /**
     * @var SettingService $settingService
     */
    private $settingService;

    /**
     * SettingsController constructor.
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $allSettings = $this->settingService->allSettings();

        return view('settings.index', compact('allSettings'));
    }

    /**
     * @param UpdateSettings $request
     * @return RedirectResponse
     */
    public function update(UpdateSettings $request): RedirectResponse
    {
        try {

            // Update settings
            DB::transaction(function() use($request) {
                $this->settingService->update($request->validated());
            });

            // Handle success
            return redirect()
                ->back()
                ->with('success', 'Settings successfully updated.');

        } catch (Throwable $exception) {

            // Handle error
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update settings - ' . $exception->getMessage());

        }
    }
}
