<?php

namespace App\Data;

use App\Models\SalesOrderItem;
use Illuminate\Support\Number;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class SalesOrderItemData extends Data
{
    #[Computed]
    public string $price_formatted;

    #[Computed]
    public string $total_formatted;
    public function __construct(
        public string $name,
        public string $short_description,
        public string $sku,
        public string $slug,
        public string|null $description,
        public string $cover_url,
        public int $quantity,
        public float $price,
        public float $total,
        public int $weight,
    ) {
        $this->price_formatted = Number::currency($this->price, 'IDR', locale: 'id_ID');
        $this->total_formatted = Number::currency($this->total, 'IDR', locale: 'id_ID');
    }
}
