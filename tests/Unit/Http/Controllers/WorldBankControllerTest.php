<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\WorldBankController;
use App\Services\WorldBankService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class WorldBankControllerTest extends TestCase
{
    private MockObject $mockWorldBankService;

    /**
     * Setup ran before each test .
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        // Creates mock of the worldBankService.
        $this->mockWorldBankService = $this->createMock(WorldBankService::class);
        $this->app->instance(WorldBankService::class, $this->mockWorldBankService);
    }

    /**
     * Constructor test
     *
     * @return void
     */
    public function testConstructor(): void
    {
        $worldBankController = new WorldBankController($this->mockWorldBankService);
        $this->assertInstanceOf(WorldBankController::class, $worldBankController);
    }

    /**
     * Tests the search controller action returns the correct formatted data and http code.
     *
     * @return void
     */
    public function testSearchSuccessful(): void
    {
        $isoCode = 'GB';

        $testCountryData = [
            'name' => 'United Kingdom',
            'region' => 'Europe & Central Asia',
            'capitalCity' => 'London',
            'longitude' => '-0.126236',
            'latitude' => '51.5002'
        ];

        $this->mockWorldBankService
            ->expects($this->once())
            ->method('getCountryByIso')
            ->with($isoCode)
            ->willReturn([$testCountryData]);

        $response = $this->post('/search', ['isoCode' => $isoCode]);

        $response->assertStatus(200);
        $response->assertViewIs('worldBank');
        // Assert the view has these values assigned to them
        $response->assertViewHas('countryData', function ($viewCountryData) use ($testCountryData) {
            return $viewCountryData[0]['name'] === $testCountryData['name'] &&
                $viewCountryData[0]['region'] === $testCountryData['region'] &&
                $viewCountryData[0]['capitalCity'] === $testCountryData['capitalCity'] &&
                $viewCountryData[0]['longitude'] === $testCountryData['longitude'] &&
                $viewCountryData[0]['latitude'] === $testCountryData['latitude'];
        });
    }

    /**
     * Test when an invalid ISO code is passed to the controller action.
     *
     * @return void
     */
    public function testSearchFailsTheValidation(): void
    {
        $isoCode = 'INVALID234234324';
        $this->mockWorldBankService
            ->expects($this->never())
            ->method('getCountryByIso');

        // Assert that an appropriate error message is shown.
        $response = $this->post('/search', ['isoCode' => $isoCode]);
        $response->assertSessionHasErrors(['isoCode']);
    }

    /**
     * Tests if a service exception is thrown by the WorldBankService
     *
     * @return void
     */
    public function testSearchHandlesServiceException(): void
    {
        $isoCode = 'GB';

        $this->mockWorldBankService
            ->expects($this->once())
            ->method('getCountryByIso')
            ->with(strtoupper($isoCode))
            ->will($this->throwException(new Exception('Service error happened.')));

        $response = $this->post('/search', ['isoCode' => $isoCode]);
        // Assert the redirect is to home and there's an error message.
        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['message' => 'An error occurred while searching for the country.']);
    }

    /**
     * Tests the getAllCountries controller action and asserts it has correct data and format.
     *
     * @return void
     */
    public function testGetAllCountriesSuccessful(): void
    {
        $testCountriesData = [
            [
                'name' => 'United Kingdom',
                'region' => 'Europe & Central Asia',
                'capitalCity' => 'London',
                'longitude' => '-0.126236',
                'latitude' => '51.5002'
            ],
            [
                'name' => 'United States',
                'region' => 'North America',
                'capitalCity' => 'Washington D.C.',
                'longitude' => '-77.032',
                'latitude' => '38.8895'
            ],
            [
                'name' => 'Canada',
                'region' => 'North America',
                'capitalCity' => 'Ottawa',
                'longitude' => '-75.6919',
                'latitude' => '45.4215'
            ],
        ];

        $this->mockWorldBankService
            ->expects($this->once())
            ->method('getCountries')
            ->willReturn($testCountriesData);

        $response = $this->post('/get-all-countries');

        // Assert status code is successful and view is correct
        $response->assertStatus(200);
        $response->assertViewIs('worldBank');

        // Assert the view contains these values
        $response->assertViewHas('countryData', function ($viewCountryData) use ($testCountriesData) {
            // Loop the data
            foreach ($testCountriesData as $index => $country) {
                // Checks if index exits with correct data.
                return (
                    isset($viewCountryData[$index]) &&
                    $viewCountryData[$index]['name'] === $country['name'] &&
                    $viewCountryData[$index]['region'] === $country['region'] &&
                    $viewCountryData[$index]['capitalCity'] === $country['capitalCity'] &&
                    $viewCountryData[$index]['longitude'] === $country['longitude'] &&
                    $viewCountryData[$index]['latitude'] === $country['latitude']
                );
            }
            return false;
        });
    }

    /**
     * Tests if a service exception occurs during the getAllCountries action.
     *
     * @return void
     */
    public function testGetAllCountriesServiceException(): void
    {
        $this->mockWorldBankService
            ->expects($this->once())
            ->method('getCountries')
            ->will($this->throwException(new Exception('Service error happened.')));

        $response = $this->post('/get-all-countries');

        // Check a redirect to home happened and an appropriate error message
        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['message' => 'An error occurred while retrieving countries.']);
    }
}
