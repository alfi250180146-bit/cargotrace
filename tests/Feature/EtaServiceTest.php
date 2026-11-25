<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\EtaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class EtaServiceTest extends TestCase
{
    use RefreshDatabase;
    use BuildsFixtures;

    /**
     * Buat shipment yang ETA-nya terlambat (atau lebih awal) sekian jam dari expected arrival.
     */
    private function shipmentWithDelay(int $hours)
    {
        $expected = now()->addDays(10)->startOfHour();

        return $this->makeShipment([
            'expected_arrival' => $expected,
            'estimated_arrival' => $expected->copy()->addHours($hours),
        ])->fresh();
    }

    private function analyze(int $hours): array
    {
        return app(EtaService::class)->analyze($this->shipmentWithDelay($hours));
    }

    public function test_shipment_not_later_than_expected_is_not_delayed(): void
    {
        foreach ([0, -2] as $hours) {
            $result = $this->analyze($hours);

            $this->assertFalse($result['is_delayed'], "delay {$hours}h");
            $this->assertNull($result['severity'], "delay {$hours}h");
        }
    }

    public function test_severity_follows_the_default_boundaries(): void
    {
        // Default: LOW sampai 6 jam, MEDIUM sampai 24 jam, di atasnya HIGH
        $expected = [4 => 'LOW', 6 => 'LOW', 10 => 'MEDIUM', 24 => 'MEDIUM', 30 => 'HIGH'];

        foreach ($expected as $hours => $severity) {
            $result = $this->analyze($hours);

            $this->assertTrue($result['is_delayed'], "delay {$hours}h");
            $this->assertSame($severity, $result['severity'], "delay {$hours}h");
        }
    }

    public function test_delay_hours_and_reason_are_reported(): void
    {
        $result = $this->analyze(4);

        $this->assertEquals(4.0, $result['delay_hours']);
        $this->assertStringContainsString('4.0 h later', $result['reason']);
    }

    public function test_default_threshold_is_12_hours(): void
    {
        $this->assertFalse($this->analyze(10)['over_threshold']);
        $this->assertTrue($this->analyze(13)['over_threshold']);
    }

    public function test_carrier_threshold_overrides_the_default(): void
    {
        $shipment = $this->shipmentWithDelay(4);
        $shipment->carrier->update(['delay_threshold_hours' => 3]);

        $result = app(EtaService::class)->analyze($shipment->fresh());

        $this->assertEquals(3.0, $result['threshold_hours']);
        $this->assertTrue($result['over_threshold']);
    }

    public function test_changing_the_setting_changes_the_severity(): void
    {
        Setting::setValue('severity_low_max_hours', 3);

        $this->assertSame('MEDIUM', $this->analyze(4)['severity']);
    }
}