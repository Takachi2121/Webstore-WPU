<?php
declare(strict_types= 1);

namespace App\Driver\Payment;

use App\Contract\PaymentDriverInterface;
use App\Data\PaymentData;
use App\Data\SalesOrderData;
use Spatie\LaravelData\DataCollection;

class OfflinePaymentDriver implements PaymentDriverInterface
{
    public readonly string $driver;

    public function __construct()
    {
        $this->driver = 'offline';
    }

    public function getDriver() : string{
        return $this->driver;
    }

    public function getMethods(): DataCollection
    {
        return PaymentData::collect([
            PaymentData::from([
                'driver' => $this->driver,
                'method' => 'bca-bank-transfer',
                'label' => 'Bank Transfer BCA',
                'payload' => [
                    'account_number' => '121231233',
                    'account_holder_name' => 'Andre Mustofa',
                ]
            ]),
        ], DataCollection::class);
    }

    public function process(SalesOrderData $sales_order)
    {
        throw new \Exception('Not implemented');
    }

    public function shouldShowPayNowButton(SalesOrderData $sales_order) : bool{
        throw new \Exception('Not implemented');
    }

    public function getRedirectURL(SalesOrderData $sales_order) : ?string{
        throw new \Exception('Not implemented');
    }
}
?>
