<?php

namespace App\Filament\Resources\MerchantReviewResource\Pages;

use App\Filament\Resources\MerchantReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMerchantReview extends EditRecord
{
    protected static string $resource = MerchantReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
