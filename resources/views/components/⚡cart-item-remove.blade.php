<?php

use Livewire\Component;
use App\Data\ProductData;
use App\Contract\CartServiceInterface;

new class extends Component
{
    public string $sku;

    public function mount(ProductData $product)
    {
        $this->sku = $product->sku;
    }

    public function remove(CartServiceInterface $cart)
    {
        $cart->remove($this->sku);

        session()->flash('success', "Product {{ $this->sku }} removed from cart.");

        $this->dispatch('cart-update');

        return redirect()->route('cart');
    }
};
?>


<div>
    <button wire:click="remove" class="text-red-500 bg-red-50 hover:bg-red-100 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center hover:cursor-pointer">
        Remove
        <div wire:loading class="animate-spin inline-block size-4 border-3 border-current border-t-transparent rounded-[999px] text-muted-foreground-2" role="status" aria-label="loading">
            <span class="sr-only">Loading...</span>
        </div>
    </button>
</div>
