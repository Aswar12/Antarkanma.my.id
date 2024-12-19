<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
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
                    ->disk('public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload')
                    ->label('Upload Images')
                    ->form([
                        Forms\Components\FileUpload::make('images')
                            ->image()
                            ->multiple()
                            ->required()
                            ->directory('product-galleries')
                            ->disk('public')
                            ->visibility('public')
                            ->storeFileNamesIn('original_filename')
                            ->getUploadedFileNameForStorageUsing(
                                function (\Illuminate\Http\UploadedFile $file): string {
                                    return Str::random(40) . '.' . $file->getClientOriginalExtension();
                                }
                            )
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif']),
                    ])
                    ->action(function (array $data): void {
                        $images = $data['images'];
                        if (!is_array($images)) {
                            $images = [$images];
                        }

                        DB::transaction(function () use ($images) {
                            foreach ($images as $image) {
                                $this->getOwnerRecord()->galleries()->create([
                                    'url' => $image,
                                ]);
                            }
                        });
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
