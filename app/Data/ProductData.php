<?php
declare(strict_types=1);

namespace App\Data;

use App\Models\Product;
use Illuminate\Support\Number;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ProductData extends Data
{
    #[Computed]
    public string $price_formatted;
    public function __construct(
        public string $name,
        public string $short_desc,
        public string $sku,
        public string|Optional|null $description,
        public string $slug,
        public int $stock,
        public float $price,
        public int $weight,
        public string $cover_url,
        public array|Optional $gallery = new Optional(),
    ) {
        $this->price_formatted = Number::currency($this->price, 'IDR');
    }

    public static function fromModel(Product $product, bool $with_gallery = false): self
    {
        return new self(
            $product->name,
            $product->tags()->where('type', 'Collection')->pluck('name')->implode(', '),
            $product->sku,
            $product->description,
            $product->slug,
            $product->stock,
            floatval($product->price),
            $product->weight,
            $product->getFirstMediaUrl('cover'),
            gallery: $with_gallery ? $product->getMedia('gallery')->map(function ($media){
                return $media->getUrl();
            })->toArray() : new Optional(),
        );
    }
}
