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
                    ->disk('s3')
                    ->visibility('public'),
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
                            )
                            ->storeFileNamesIn('original_filename')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert single file to array for consistent handling
                        if (!is_array($data['url'])) {
                            $data['url'] = [$data['url']];
                        }
                        return $data;
                    })
                    ->using(function (array $data, string $model): void {
                        foreach ($data['url'] as $url) {
                            $model::create([
                                'product_id' => $this->getOwnerRecord()->id,
                                'url' => $url,
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable(false);
    }
}
