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
use Illuminate\Validation\ValidationException;

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
     * @return RedirectResponse|View|Application|Factory
     */
    public function search(Request $request): RedirectResponse|View|Application|Factory
    {
        try {
            // Validate the ISO in the request.
            $request->validate([
                'isoCode' => 'required|alpha|regex:/^[A-Za-z]{2,3}$/',
            ]);
        } catch (ValidationException $exception) {
            // Redirect to home page with validation error.
            return redirect()->route('home')->withErrors($exception->validator)->withInput();
        }

        // Makes the ISO uppercase as it will error when it gets to API.
        $isoCode = strtoupper($request->input('isoCode'));

        try {
            // Make service method call to get country by ISO code.
            $countryData = $this->worldBankService->getCountryByIso($isoCode);
            return view('worldBank', ['countryData' => $countryData]);
        } catch (Exception $exception) {
           // Logs the error and returns message to frontend.
            Log::create([
                'action' => 'ISO Search',
                'error_message' => $exception->getMessage()
            ]);
            return redirect()->route('home')->withErrors(['message' => 'An error occurred while searching for the country.']);
        }
    }

    /**
     * Gets all the countries from the World Bank service.
     *
     * @return Factory|View|Application|RedirectResponse
     */
    public function getAllCountries(): Factory|View|Application|RedirectResponse
    {
        try {
            // Make service method call to get all countries.
            $countries = $this->worldBankService->getCountries();

            return view('worldBank', ['countryData' => $countries]);
        } catch (Exception $exception) {
            // Logs the error and returns message to frontend.
            Log::create([
                'action' => 'Get All Countries',
                'error_message' => $exception->getMessage()
            ]);
            return redirect()->route('home')->withErrors(['message' => 'An error occurred while retrieving countries.']);
        }
    }
}
