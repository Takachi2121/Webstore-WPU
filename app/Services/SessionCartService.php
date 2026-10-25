<?php

declare(strict_types=1);

namespace App\Services;

use App\Contract\CartServiceInterface;
use App\Data\CartData;
use App\Data\CartItemData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelData\DataCollection;

class SessionCartService implements CartServiceInterface{

    private string $session_key = 'cart';

    protected function load() : DataCollection{
        $raw = Session::get($this->session_key, []);

        return new DataCollection(CartItemData::class, $raw);
    }

    protected function save(Collection $items){
        Session::put($this->session_key, $items->values()->all());
    }

    public function addOrUpdate(CartItemData $item): void
    {
        $collection = $this->load()->toCollection();
        $updated = false;

        $cart = $collection->map(function(CartItemData $i) use ($item, &$updated){
            if ($i->sku === $item->sku){
                $updated = true;
                return $item;
            }

            return $i;
        })->values()->collect();

        if(!$updated){
            $cart->push($item);
        }

        $this->save($cart);
    }

    public function remove(string $sku): void
    {
        $cart = $this->load()->toCollection()
                ->reject(fn(CartItemData $i) => $i->sku === $sku)
                ->values()
                ->collect();

        $this->save($cart);
    }

    public function getItemBySku(string $sku): ?CartItemData
    {
        return $this->load()->toCollection()
                ->first(fn(CartItemData $i) => $i->sku === $sku);
    }

    public function all(): CartData
    {
        return new CartData($this->load());
    }

    public function clear(): void
    {
        Session::forget($this->session_key);
    }
}
