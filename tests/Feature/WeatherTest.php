<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    private function fakeSuccess(): void
    {
        Http::fake([
            '*' => Http::response([
                'current' => [
                    'time' => '2026-11-25T10:00',
                    'temperature_2m' => 28.4,
                    'wind_speed_10m' => 12.1,
                    'weather_code' => 3,
                ],
            ]),
        ]);
    }

    public function test_successful_response_is_parsed(): void
    {
        $this->fakeSuccess();

        $result = app(WeatherService::class)->current(1.26, 103.82);

        $this->assertTrue($result['ok']);
        $this->assertSame(28.4, $result['temperature']);
        $this->assertSame('Overcast', $result['summary']);
    }

    public function test_server_error_is_handled(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $result = app(WeatherService::class)->current(1.26, 103.82);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('500', $result['message']);
    }

    public function test_empty_data_is_handled(): void
    {
        Http::fake(['*' => Http::response(['current' => []])]);

        $result = app(WeatherService::class)->current(1.26, 103.82);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('No weather data', $result['message']);
    }

    public function test_timeout_is_handled(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $result = app(WeatherService::class)->current(1.26, 103.82);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('unreachable', $result['message']);
    }

    public function test_successful_result_is_cached(): void
    {
        $this->fakeSuccess();

        $service = app(WeatherService::class);
        $service->current(1.26, 103.82);
        $service->current(1.26, 103.82);

        Http::assertSentCount(1);
    }

    public function test_owner_can_load_weather_for_their_shipment(): void
    {
        $this->fakeSuccess();
        $shipment = $this->makeShipment();

        $this->actingAs(User::find($shipment->customer_id))
            ->getJson(route('shipments.weather', $shipment))
            ->assertOk()
            ->assertJson(['ok' => true, 'summary' => 'Overcast']);
    }

    public function test_other_customer_cannot_load_the_weather(): void
    {
        $this->fakeSuccess();
        $shipment = $this->makeShipment();

        $this->actingAs($this->userWithRole(User::ROLE_CUSTOMER))
            ->getJson(route('shipments.weather', $shipment))
            ->assertForbidden();
    }

    public function test_page_still_works_when_the_weather_service_is_down(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $shipment = $this->makeShipment();

        $this->actingAs($this->userWithRole(User::ROLE_OPERATOR))
            ->getJson(route('shipments.weather', $shipment))
            ->assertOk()
            ->assertJson(['ok' => false]);
    }
}