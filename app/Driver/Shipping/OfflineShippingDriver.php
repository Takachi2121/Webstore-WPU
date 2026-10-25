<?php
declare(strict_types= 1);

namespace App\Driver\Shipping;

use App\Contract\ShippingDriverInterface;
use App\Data\CartData;
use App\Data\RegionData;
use App\Data\ShippingData;
use App\Data\ShippingServiceData;
use Spatie\LaravelData\DataCollection;

class OfflineShippingDriver implements ShippingDriverInterface
{
    public readonly string $driver;

    public function __construct()
    {
        $this->driver = 'offline';
    }
    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getServices() : DataCollection
    {
        return ShippingServiceData::collect([
            [
                "driver" => $this->driver,
                'code' => 'offline-flat-15',
                'courier' => 'Internal Courier',
                'service' => 'Instant'
            ],
            [
                "driver" => $this->driver,
                'code' => 'offline-flat-5',
                'courier' => 'Internal Courier',
                'service' => 'SameDay'
            ]
        ], DataCollection::class);
    }
    public function getRate(
        RegionData $origin,
        RegionData $destination,
        CartData $cart,
        ShippingServiceData $shipping_service
    ) : ?ShippingData
    {
        $data = null;

        switch($shipping_service->code){
            case 'offline-flat-15':
                $data = ShippingData::from([
                    'driver' => $this->driver,
                    'courier' => $shipping_service->courier,
                    'service' => $shipping_service->service,
                    'estimated_delivery' => '1-2 Hours',
                    'cost' => 150000,
                    'weight' => $cart->total_weight,
                    'origin' => $origin,
                    'destination' => $destination
                ]);
                break;
            case 'offline-flat-5':
                $data = ShippingData::from([
                    'driver' => $this->driver,
                    'courier' => $shipping_service->courier,
                    'service' => $shipping_service->service,
                    'estimated_delivery' => '1 Day',
                    'cost' => 150000,
                    'weight' => $cart->total_weight,
                    'origin' => $origin,
                    'destination' => $destination
                ]);
                break;
        }

        return $data;
    }
}
?>
