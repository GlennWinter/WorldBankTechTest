<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\WorldBankService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorldBankController extends Controller
{
    /**
     * @var WorldBankService
     */
    protected WorldBankService $worldBankService;

    /**
     * Constructor for WorkBankController
     *
     * @param WorldBankService $worldBankService
     */
    public function __construct(WorldBankService $worldBankService)
    {
        $this->worldBankService = $worldBankService;
    }

    /**
     * Action used to search for a country by it's ISO's code.
     *
     * @param Request $request
     * @return Factory|View|Application|RedirectResponse
     */
    public function search(Request $request):  Factory|View|Application|RedirectResponse
    {
        try {
            // Validate the ISO in the request.
            $request->validate([
                'isoCode' => 'required|alpha|size:2,3',
            ]);
            // Makes the ISO uppercase as it will error when it gets to API.
            $isoCode = strtoupper($request->input('isoCode'));

            // Make service method call to get country by ISO code.
            $response = $this->worldBankService->getCountryByIso($isoCode);
            return view('worldBank', ['countryData' => $response]);
        } catch (Exception $exception) {
           // Logs the error and returns message to frontend.
            Log::create([
                'action' => 'ISO Search',
                'error_message' => $exception->getMessage()
            ]);
            return redirect()->route('home')->withErrors([$exception->getMessage()]);
        }
    }

    /**
     * Gets all the countries from World Bank.
     *
     * @return Factory|View|Application|RedirectResponse
     */
    public function getAllCountries(): Factory|View|Application|RedirectResponse
    {
        try {
            // Make service method call to get all countries.
            $response = $this->worldBankService->getCountries();

            return view('worldBank', ['countryData' => $response]);
        } catch (Exception $exception) {
            // Logs the error and returns message to frontend.
            Log::create([
                'action' => 'Get All Countries',
                'error_message' => $exception->getMessage()
            ]);
            return redirect()->route('home')->withErrors([$exception->getMessage()]);
        }
    }
}
