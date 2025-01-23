<?php

namespace Joaopaulolndev\FilamentGeneralSettings\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Joaopaulolndev\FilamentGeneralSettings\Forms\AnalyticsFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\ApplicationFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\EmailFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\SeoFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Forms\SocialNetworkFieldsForm;
use Joaopaulolndev\FilamentGeneralSettings\Helpers\EmailDataHelper;
use Joaopaulolndev\FilamentGeneralSettings\Mail\TestMail;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Joaopaulolndev\FilamentGeneralSettings\Services\MailSettingsService;

class GeneralSettingsPage extends Page
{
    use InteractsWithForms;

    protected static string $view = 'filament-general-settings::filament.pages.general-settings-page';

    /**
     * @throws \Exception
     */
    public static function getNavigationGroup(): ?string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getNavigationGroup();
    }

    /**
     * @throws \Exception
     */
    public static function getNavigationIcon(): ?string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getIcon();
    }

    public static function getNavigationSort(): ?int
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getSort();
    }

    public static function canAccess(): bool
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getCanAccess();
    }

    public function getTitle(): string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getTitle() ?? __('filament-general-settings::default.title');
    }

    public static function getNavigationLabel(): string
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin('filament-general-settings');

        return $plugin->getNavigationLabel() ?? __('filament-general-settings::default.title');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $data = GeneralSetting::first()?->toArray() ?: [];

        $data['seo_description'] = $data['seo_description'] ?? '';
        $data['seo_preview'] = $data['seo_preview'] ?? '';
        $data['theme_color'] = $data['theme_color'] ?? '';
        $data['seo_metadata'] = $data['seo_metadata'] ?? [];
        $data = EmailDataHelper::getEmailConfigFromDatabase($data);

        if (isset($data['site_logo']) && is_string($data['site_logo'])) {
            $data['site_logo'] = [
                'name' => $data['site_logo'],
            ];
        }

        if (isset($data['site_favicon']) && is_string($data['site_favicon'])) {
            $data['site_favicon'] = [
                'name' => $data['site_favicon'],
            ];
        }

        foreach ($data as $key => $value) {
            $data[$key] = $value ?? '';
        }

        Log::info($data);

        $this->data = $data;

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        $arrTabs = [];

        if (config('filament-general-settings.show_application_tab')) {
            $arrTabs[] = Tabs\Tab::make('Application Tab')
                ->label(__('filament-general-settings::default.application'))
                ->icon('heroicon-o-tv')
                ->schema([
                    TextInput::make('site_name')
                        ->label(__('filament-general-settings::default.site_name'))
                        ->autofocus()
                        ->columnSpanFull(),
                    Textarea::make('site_description')
                        ->label(__('filament-general-settings::default.site_description'))
                        ->columnSpanFull(),
                    Grid::make()->schema([
                        FileUpload::make('site_logo')
                            ->label(fn () => __('filament-general-settings::default.site_logo'))
                            ->image()
                            ->directory('assets')
                            ->visibility('public')
                            ->moveFiles()
                            ->imageEditor()
                            ->getUploadedFileNameForStorageUsing(fn () => 'site_logo.png')
                            ->columnSpan(2),
                        FileUpload::make('site_favicon')
                            ->label(fn () => __('filament-general-settings::default.site_favicon'))
                            ->image()
                            ->directory('assets')
                            ->visibility('public')
                            ->moveFiles()
                            ->getUploadedFileNameForStorageUsing(fn () => 'site_favicon.ico')
                            ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon'])
                            ->columnSpan(2),
                    ])
//                        ->columns(4)
//                        ->visible(fn () => config('filament-general-settings.show_logo_and_favicon')),
//                    TextInput::make('support_email')
//                        ->label(__('filament-general-settings::default.support_email'))
//                        ->prefixIcon('heroicon-o-envelope'),
//                    TextInput::make('support_phone')
//                        ->prefixIcon('heroicon-o-phone')
//                        ->label(__('filament-general-settings::default.support_phone')),
//                    ColorPicker::make('theme_color')
//                        ->label(__('filament-general-settings::default.theme_color'))
//                        ->prefixIcon('heroicon-o-swatch')
//                        ->formatStateUsing(fn (?string $state): string => $state ?? config('filament.theme.colors.primary'))
//                        ->helperText(__('filament-general-settings::default.theme_color_helper_text')),
                ])
                ->columns(3);
        }

        if (config('filament-general-settings.show_analytics_tab')) {
            $arrTabs[] = Tabs\Tab::make('Analytics Tab')
                ->label(__('filament-general-settings::default.analytics'))
                ->icon('heroicon-o-globe-alt')
                ->schema(AnalyticsFieldsForm::get());
        }

        if (config('filament-general-settings.show_seo_tab')) {
            $arrTabs[] = Tabs\Tab::make('Seo Tab')
                ->label(__('filament-general-settings::default.seo'))
                ->icon('heroicon-o-window')
                ->schema(SeoFieldsForm::get($this->data))
                ->columns(1);
        }

        if (config('filament-general-settings.show_email_tab')) {
            $arrTabs[] = Tabs\Tab::make('Email Tab')
                ->label(__('filament-general-settings::default.email'))
                ->icon('heroicon-o-envelope')
                ->schema(EmailFieldsForm::get())
                ->columns(3);
        }

        if (config('filament-general-settings.show_social_networks_tab')) {
            $arrTabs[] = Tabs\Tab::make('Social Network Tab')
                ->label(__('filament-general-settings::default.social_networks'))
                ->icon('heroicon-o-heart')
                ->schema(SocialNetworkFieldsForm::get())
                ->columns(2)
                ->statePath('social_network');
        }

        if (config('filament-general-settings.show_custom_tabs')) {
            foreach (config('filament-general-settings.custom_tabs') as $key => $customTab) {
                $arrTabs[] = Tabs\Tab::make($customTab['label'])
                    ->label(__($customTab['label']))
                    ->icon($customTab['icon'])
                    ->schema($customTab['fields'])
                    ->columns($customTab['columns'])
                    ->statePath('more_configs');
            }
        }

        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs($arrTabs),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('Save')
                ->label(__('filament-general-settings::default.save'))
                ->color('primary')
                ->submit('Update'),
        ];
    }

    public function update(): void
    {
        $data = $this->form->getState();
        if (config('filament-general-settings.show_email_tab')) {
            $data = EmailDataHelper::setEmailConfigToDatabase($data);
        }
        $data = $this->clearVariables($data);

        GeneralSetting::updateOrCreate([], $data);
        Cache::forget('general_settings');

        $this->successNotification(__('filament-general-settings::default.settings_saved'));
        redirect(request()?->header('Referer'));
    }

    private function clearVariables(array $data): array
    {
        unset(
            $data['seo_preview'],
            $data['seo_description'],
            $data['default_email_provider'],
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_encryption'],
            $data['smtp_timeout'],
            $data['smtp_username'],
            $data['smtp_password'],
            $data['mailgun_domain'],
            $data['mailgun_secret'],
            $data['mailgun_endpoint'],
            $data['postmark_token'],
            $data['amazon_ses_key'],
            $data['amazon_ses_secret'],
            $data['amazon_ses_region'],
            $data['mail_to'],
        );

        return $data;
    }

    public function sendTestMail(MailSettingsService $mailSettingsService): void
    {
        $data = $this->form->getState();
        $email = $data['mail_to'];

        $settings = $mailSettingsService->loadToConfig($data);

        try {
            Mail::mailer($settings['default_email_provider'])
                ->to($email)
                ->send(new TestMail([
                    'subject' => 'This is a test email to verify SMTP settings',
                    'body' => 'This is for testing email using smtp.',
                ]));
        } catch (\Exception $e) {
            $this->errorNotification(__('filament-general-settings::default.test_email_error'), $e->getMessage());

            return;
        }

        $this->successNotification(__('filament-general-settings::default.test_email_success') . $email);
    }

    private function successNotification(string $title): void
    {
        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    private function errorNotification(string $title, string $body): void
    {
        Log::error('[EMAIL] ' . $body);

        Notification::make()
            ->title($title)
            ->danger()
            ->body($body)
            ->send();
    }
}
