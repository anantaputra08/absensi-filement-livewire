<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use App\Models\AttendanceSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker;

class AttendanceSettingPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.attendance-setting-page';

    public $check_in_time;
    public $check_out_time;
    public $check_in_max_time;
    public $check_out_min_time;

    protected function getFormSchema(): array
    {
        return [
            TimePicker::make('check_in_time')->required(),
            TimePicker::make('check_out_time')->required(),
            TimePicker::make('check_in_max_time')->required(),
            TimePicker::make('check_out_min_time')->required(),
        ];
    }

    public function mount()
    {
        $settings = AttendanceSetting::first();
        if ($settings) {
            $this->form->fill([
                'check_in_time' => $settings->check_in_time,
                'check_out_time' => $settings->check_out_time,
                'check_in_max_time' => $settings->check_in_max_time,
                'check_out_min_time' => $settings->check_out_min_time,
            ]);
        }
    }

    public function save()
    {
        $settings = AttendanceSetting::firstOrNew();
        $settings->check_in_time = $this->check_in_time;
        $settings->check_out_time = $this->check_out_time;
        $settings->check_in_max_time = $this->check_in_max_time;
        $settings->check_out_min_time = $this->check_out_min_time;
        $settings->save();

        $this->notify('success', 'Settings saved successfully!');
    }

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save'),
        ];
    }
}