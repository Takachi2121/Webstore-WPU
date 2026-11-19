<?php

declare(strict_types=1);

namespace App\States\SalesOrder\Transition;

use App\Models\SalesOrder;
use App\States\SalesOrder\Cancel;
use Spatie\ModelStates\Transition;

class PendingtoCancel extends Transition
{
    public function __construct(private SalesOrder $sales_order)
    {

    }

    public function handle()
    {
        $this->sales_order->update([
            'status' => Cancel::class
        ]);

        // Event
        // Kirim Email
        // Aksi Lainnya

        return $this->sales_order;
    }
}
