<?php
namespace App\Services\ApiClients;

use Exception;
use Illuminate\Support\Facades\Http;
class WorldBankClient
{
    protected static string $baseApiUrl = 'https://api.worldbank.org/v2/';

    /**
     * Function used to make basic HTTP requests
     *
     * @param string $endpoint The API endpoint to make a request to.
     * @param array $params Parameters required in the request (Optional)
     * @param string $method The HTTP method (Optional)
     *
     * @return array|mixed
     * @throws Exception
     */
    public function basicRequest(string $endpoint, array $params = [], string $method = 'GET'): mixed
    {
        // Construct the API URL.
        $url = self::$baseApiUrl . $endpoint . '?format=json';

        try {
            // Send request using Laravel's HTTP client
            if ($method == 'GET') {
                $response = Http::timeout(30)->get($url);
            } else if ($method == 'POST') {
                $response = Http::timeout(30)->post($url, $params);
            } else if ($method == 'PUT') {
                $response = Http::timeout(30)->put($url, $params);
            } else if ($method == 'DELETE') {
                $response = Http::timeout(30)->delete($url, $params);
            } else {
                // If invalid request type, throw error
                throw new Exception('Invalid HTTP method');
            }

            // Checks if request was successul and returns json.
            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Request failed.');
            }
        } catch (Exception $exception) {
            // Handle exceptions appropriately.
            throw new Exception('Request failed: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * Function used to make complicated HTTP request where pagination is required.
     *
     * @param string $endpoint The API endpoint to make a request to.
     * @param array $params Parameters required in the request (Optional)
     * @param string $method The HTTP method (Optional)
     *
     * @return array
     * @throws Exception
     */
    public function paginatedRequest(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        // Set up initial variables.
        $url = self::$baseApiUrl . $endpoint;
        $params['format'] = $params['format'] ?? 'json';
        $allResults = [];
        $page = 1;
        $perPage = $params['per_page'] ?? 100;

        try {
            // While the current page is less than the total pages retrieved from the API, loop through and make
            // a request for next page of data.
            do {
                $params['page'] = $page;
                $params['per_page'] = $perPage;

                $response = Http::timeout(30)->$method($url, $params);

                // If request was successful, add the country data to the main array.
                if ($response->successful()) {
                    $data = $response->json();
                    $metadata = $data[0];
                    $results = $data[1];
                    $allResults = array_merge($allResults, $results);
                    $page++;
                    $totalPages = $metadata['pages'];
                } else {
                    throw new Exception('Request failed.');
                }
            } while ($page <= $totalPages);

            return $allResults;

        } catch (Exception $exception) {
            throw new Exception('Request failed: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * Client method for getting a country by an ISO code from the World Bank API.
     *
     * @param string $isoCode The Country's ISO code to retrieve.
     *
     * @return mixed
     * @throws Exception
     */
    public function getCountryByIsoCode(string $isoCode): mixed
    {
        // Basic check for if the ISO code supplied is 2 or 3 characters log and only capital letters.
        if (!preg_match('/^[A-Z]{2,3}$/', $isoCode)) {
            throw new Exception('Invalid ISO code format', 500);
        }

         return $this->basicRequest('country/' . $isoCode);
    }

    /**
     * Method for getting all countries from the WorldBank API.
     *
     * @return array
     * @throws Exception
     */
    public function getAllCountries(): array
    {
       return $this->paginatedRequest('countries');
    }
}
