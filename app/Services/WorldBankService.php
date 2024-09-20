<?php

namespace App\Services;

use App\Models\Log;
use App\Services\ApiClients\WorldBankClient;
use Exception;

class WorldBankService
{
    /**
     * @var WorldBankClient
     */
    protected WorldBankClient $worldBankClient;

    /**
     * Constructor for WorldBankService
     *
     * @param WorldBankClient $worldBankClient
     */
    public function __construct(WorldBankClient $worldBankClient)
    {
        $this->worldBankClient = $worldBankClient;
    }

    /**
     * Get a Country by the ISO code.
     *
     * @param string $iso The country's ISO code.
     * @return array|Exception
     */
    public function getCountryByIso(string $iso): array|Exception
    {
        try {
            // Calls World Bank Client to make API request.
            $response = $this->worldBankClient->getCountryByIsoCode($iso);

            // Checks if response contains valid data to loop through.
            if (!empty($response[1][0])) {
                $data = $response[1][0];

                // Format data into format required by frontend.
                $countryData[] = [
                    'name' => $data['name'] ?? 'N/A',
                    'region' => $data['region']['value'] ?? 'Unknown',
                    'capitalCity' => !empty($data['capitalCity']) ? $data['capitalCity'] : 'Unknown',
                    'longitude' => $data['longitude'] ?? null,
                    'latitude' => $data['latitude'] ?? null
                ];

                return $countryData;
            } else {
                // If no data to loop, throw exception.
                throw new Exception('Unexpected error occurred while retrieving Country Data.', 500);
            }
        } catch (Exception $exception) {
            // Log the error
            Log::create([
                'action' => 'Get Country Service Method',
                'error_message' => $exception->getMessage()
            ]);
            return $exception;
        }
    }

    /**
     * Gets all countries from the World Bank API.
     *
     * @return array|Exception
     */
    public function getCountries(): array|Exception
    {
        try {
            // Calls World Bank Client to make API request to get all countries.
            $response = $this->worldBankClient->getAllCountries();

            // Checks if response contains valid data to loop through.
            if (!empty($response)) {
                $counties = $response;

                // Loop through all countries and format them.
                $countryData = [];
                foreach ($counties as $country) {
                    $countryData[] = [
                        'name' => $country['name'] ?? 'N/A',
                        'region' => $country['region']['value'] ?? 'Unknown',
                        'capitalCity' => !empty($data['capitalCity']) ? $data['capitalCity'] : 'Unknown',
                        'longitude' => !empty($data['longitude']) ? $data['longitude'] : 'Unknown',
                        'latitude' => !empty($data['latitude']) ? $data['latitude'] : 'Unknown',
                    ];
                }

                return $countryData;
            } else {
                // If no data to loop, throw exception.
                throw new Exception('Unexpected error occurred while retrieving Country Data.', 500);
            }
        } catch (Exception $exception) {
            // Log the error
            Log::create([
                'action' => 'Get All Countries Service Method',
                'error_message' => $exception->getMessage()
            ]);
            return $exception;
        }
    }
}
