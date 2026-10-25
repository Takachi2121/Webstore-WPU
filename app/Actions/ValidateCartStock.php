<?php

namespace App\Actions;

use App\Contract\CartServiceInterface;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ValidateCartStock
{
    use AsAction;

    public function __construct(public CartServiceInterface $cart)
    {

    }

    public function handle()
    {
        $insufficient = [];

        foreach ($this->cart->all()->items as $item) {
            $product = $item->product();

            if ($item->quantity > $product->stock) {
                $insufficient[] = [
                    'sku' => $item->sku,
                    'name' => $product->name ?? 'Unknown Product',
                    'requested' => $item->quantity,
                    'available' => $product->stock ?? 0
                ];
            }
        }

        if($insufficient){
            throw ValidationException::withMessages([
                'cart' => 'Some items in your cart have insufficient stock.',
                'details' => $insufficient
            ]);
        }

        return $insufficient;
    }
}
