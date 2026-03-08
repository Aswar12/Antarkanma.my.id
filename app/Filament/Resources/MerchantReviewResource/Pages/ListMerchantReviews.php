<?php

namespace App\Filament\Resources\MerchantReviewResource\Pages;

use App\Filament\Resources\MerchantReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMerchantReviews extends ListRecords
{
    protected static string $resource = MerchantReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
