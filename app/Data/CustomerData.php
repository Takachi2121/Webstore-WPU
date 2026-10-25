<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public string $fullname,
        public string $email,
        public string $phone,
    ) {}
}
