<?php
declare(strict_types= 1);

namespace App\Contract;

use App\Data\SalesOrderData;
use Spatie\LaravelData\DataCollection;

interface PaymentDriverInterface
{
    public function getDriver() : string;

    public function getMethods(): DataCollection;

    public function process(SalesOrderData $sales_order);

    public function shouldShowPayNowButton(SalesOrderData $sales_order) : bool;

    public function getRedirectURL(SalesOrderData $sales_order) : ?string;
}
?>
