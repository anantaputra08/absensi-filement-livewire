<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use App\Models\AttendanceSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

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
            Card::make()
                ->schema([
                    Section::make('Check In Settings')
                        ->icon('tabler-door-enter')
                        ->iconColor('primary')
                        ->compact()
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TimePicker::make('check_in_time')
                                        ->label('Check In Time')
                                        ->required(),
                                    TimePicker::make('check_in_max_time')
                                        ->label('Until')
                                        ->required(),
                                ]),
                        ]),
                    Section::make('Check Out Settings')
                        ->icon('tabler-door-exit')
                        ->iconColor('danger')
                        ->compact()
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TimePicker::make('check_out_time')
                                        ->label('Check Out Time')
                                        ->required(),
                                    TimePicker::make('check_out_min_time')
                                        ->label('Until')
                                        ->required(),
                                ]),
                        ]),
                ]),
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

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
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