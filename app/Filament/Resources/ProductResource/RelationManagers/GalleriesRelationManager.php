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
                    ->disk('public'),
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
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file): string =>
                                    'products/' . $this->getOwnerRecord()->id . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension()
                            ),
                    ])
                    ->using(function (array $data): void {
                        \Log::info('Uploading images:', $data);
                        $files = is_array($data['url']) ? $data['url'] : [$data['url']];

                        foreach ($files as $file) {
                            // Create gallery with local storage URL first
                            $gallery = $this->getOwnerRecord()->galleries()->create([
                                'url' => Storage::disk('public')->url($file)
                            ]);

                            // Upload to S3 in background
                            dispatch(function () use ($file, $gallery) {
                                try {
                                    // Get file contents from public storage
                                    $path = str_replace('public/', '', $file);
                                    if (!Storage::disk('public')->exists($path)) {
                                        throw new \Exception("File not found in public storage: {$path}");
                                    }

                                    $contents = Storage::disk('public')->get($path);

                                    // Upload to S3
                                    $filename = basename($path);
                                    $s3Path = "products/galleries/{$filename}";
                                    $uploaded = Storage::disk('s3')->put($s3Path, $contents);
                                    if (!$uploaded) {
                                        throw new \Exception("Failed to upload file to S3");
                                    }

                                    $s3Url = "https://is3.cloudhost.id/antarkanma/" . $s3Path;

                                    // Update gallery URL to S3
                                    $gallery->update([
                                        'url' => $s3Url
                                    ]);

                                    // Delete local file only after successful S3 upload
                                    Storage::disk('public')->delete($path);
                                } catch (\Exception $e) {
                                    \Log::error('Failed to upload product gallery to S3: ' . $e->getMessage(), [
                                        'gallery_id' => $gallery->id,
                                        'file' => $file,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->url) {
                            $path = str_replace('/storage/', '', parse_url($record->url, PHP_URL_PATH));
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->url) {
                                    $path = str_replace('/storage/', '', parse_url($record->url, PHP_URL_PATH));
                                    if (Storage::disk('public')->exists($path)) {
                                        Storage::disk('public')->delete($path);
                                    }
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable(false);
    }
}
