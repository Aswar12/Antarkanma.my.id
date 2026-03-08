<?php

namespace App\Filament\Resources\CourierReviewResource\Pages;

use App\Filament\Resources\CourierReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourierReviews extends ListRecords
{
    protected static string $resource = CourierReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
