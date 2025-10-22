<?php

namespace App\Services;

class ProductCatalogService
{
    public function getAll(): array
    {
        return (array) config('shop.products', []);
    }
}

