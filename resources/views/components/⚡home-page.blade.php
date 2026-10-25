<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Data\ProductData;
use App\Models\Product;
use Livewire\Attributes\Computed;

new #[Title('Home Page')] class extends Component
{

    #[Computed]
    public function getFeatureProducts()
    {
        return ProductData::collect(
            Product::query()->inRandomOrder()->limit(3)->get()
        );
    }

    #[Computed]
    public function getLatestProducts()
    {
        return ProductData::collect(
            Product::query()->latest()->limit(3)->get()
        );
    }
};
?>

<div>
    <div class="container mx-auto max-w-[85rem] w-full">
        <div class="mt-10">
            <x-product-sections title="Feature Product" :url="route('product-catalog')" :products="$this->getFeatureProducts" />
            <x-featured-icon />
            <x-product-sections title="Latest Products" :url="route('product-catalog')" :products="$this->getLatestProducts" />
        </div>
    </div>
</div>
