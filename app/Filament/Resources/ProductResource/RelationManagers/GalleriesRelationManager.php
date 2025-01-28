<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleriesRelationManager extends RelationManager
{
    protected static string $relationship = 'galleries';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->square()
                    ->disk('s3'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload Images')
                    ->form([
                        Forms\Components\FileUpload::make('url')
                            ->label('Images')
                            ->multiple()
                            ->image()
                            ->required()
                            ->disk('s3')
                            ->directory('products/images')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file): string => 
                                    $this->getOwnerRecord()->id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension()
                            ),
                    ])
                    ->using(function (array $data): void {
                        \Log::info('Uploading images:', $data);
                        $files = is_array($data['url']) ? $data['url'] : [$data['url']];
                        
                        foreach ($files as $file) {
                            $this->getOwnerRecord()->galleries()->create([
                                'url' => $file
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->url) {
                            Storage::disk('s3')->delete($record->url);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->url) {
                                    Storage::disk('s3')->delete($record->url);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable(false);
    }
}
