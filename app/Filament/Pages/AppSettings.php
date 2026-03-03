<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.app-settings';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'App Settings';

    protected static ?string $title = 'App Settings';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationGroup = 'Configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Payment Settings')
                ->description('Configure payment methods and QRIS')
                ->schema([
                    Forms\Components\FileUpload::make('qris_image')
                        ->label('QRIS Code')
                        ->helperText('Upload QRIS code image for payment. Supported formats: JPG, PNG. Max size: 2MB.')
                        ->image()
                        ->disk('public')
                        ->directory('qris')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                        ->imagePreviewHeight('200')
                        ->openable()
                        ->downloadable()
                        ->getUploadedFileNameForStorageUsing(function (UploadedFile $file): string {
                            return 'qris-antarkanma.' . $file->getClientOriginalExtension();
                        })
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->helperText('Bank name for manual transfer')
                        ->maxLength(100)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_account_number')
                        ->label('Account Number')
                        ->helperText('Bank account number for manual transfer')
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_account_name')
                        ->label('Account Name')
                        ->helperText('Bank account holder name')
                        ->maxLength(200)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Branding Settings')
                ->description('Configure application branding')
                ->schema([
                    Forms\Components\TextInput::make('app_name')
                        ->label('Application Name')
                        ->maxLength(100)
                        ->columnSpan(1),

                    Forms\Components\FileUpload::make('app_logo')
                        ->label('Application Logo')
                        ->helperText('Upload application logo. Supported formats: SVG, PNG. Max size: 1MB.')
                        ->image()
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->maxSize(1024)
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg'])
                        ->columnSpan(1),
                ])
                ->columns(2),

            Forms\Components\Section::make('Contact Settings')
                ->description('Configure customer contact information')
                ->schema([
                    Forms\Components\TextInput::make('customer_service_phone')
                        ->label('Customer Service Phone')
                        ->tel()
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('customer_service_email')
                        ->label('Customer Service Email')
                        ->email()
                        ->maxLength(200)
                        ->columnSpan(1),
                ])
                ->columns(2),
        ];
    }

    protected function getFormData(): array
    {
        $settings = AppSetting::all()->pluck('value', 'key')->toArray();
        
        return [
            'qris_image' => $settings['qris_image'] ?? null,
            'bank_name' => $settings['bank_name'] ?? 'BCA',
            'bank_account_number' => $settings['bank_account_number'] ?? '1234567890',
            'bank_account_name' => $settings['bank_account_name'] ?? 'PT Antarkanma Indonesia',
            'app_name' => $settings['app_name'] ?? 'AntarkanMa',
            'app_logo' => $settings['app_logo'] ?? null,
            'customer_service_phone' => $settings['customer_service_phone'] ?? null,
            'customer_service_email' => $settings['customer_service_email'] ?? 'support@antarkanma.com',
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Handle app logo
        if (!empty($data['app_logo'])) {
            $data['app_logo'] = is_array($data['app_logo']) ? $data['app_logo'][0] : $data['app_logo'];
        }

        $settingsToSave = [
            'qris_image' => ['value' => $data['qris_image'] ?? null, 'type' => 'image', 'group' => 'payment'],
            'bank_name' => ['value' => $data['bank_name'] ?? 'BCA', 'type' => 'string', 'group' => 'payment'],
            'bank_account_number' => ['value' => $data['bank_account_number'] ?? '', 'type' => 'string', 'group' => 'payment'],
            'bank_account_name' => ['value' => $data['bank_account_name'] ?? '', 'type' => 'string', 'group' => 'payment'],
            'app_name' => ['value' => $data['app_name'] ?? 'AntarkanMa', 'type' => 'string', 'group' => 'branding'],
            'app_logo' => ['value' => $data['app_logo'] ?? null, 'type' => 'image', 'group' => 'branding'],
            'customer_service_phone' => ['value' => $data['customer_service_phone'] ?? null, 'type' => 'string', 'group' => 'contact'],
            'customer_service_email' => ['value' => $data['customer_service_email'] ?? '', 'type' => 'string', 'group' => 'contact'],
        ];

        foreach ($settingsToSave as $key => $config) {
            AppSetting::set($key, $config['value'], $config['type']);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
