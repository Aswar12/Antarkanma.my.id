<?php

namespace App\Filament\Resources\CourierReviewResource\Pages;

use App\Filament\Resources\CourierReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourierReview extends EditRecord
{
    protected static string $resource = CourierReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
