<?php

namespace App\Filament\Widgets;

use App\Models\Miner;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;

class QuickSearchWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    // Menentukan tampilan blade untuk widget ini
    protected static string $view = 'filament.widgets.quick-search-widget';
    
    // Membuat widget ini full width di atas
    protected int | string | array $columnSpan = 'full';
    
    // Urutan paling atas
    protected static ?int $sort = 1;

    // Property untuk menampung hasil ketikan user
    public ?string $miner_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('miner_id')
                    ->hiddenLabel()
                    ->placeholder('Quick Search: Ketik nama mesin (Contoh: SPX-A2)...')
                    ->prefixIcon('heroicon-m-magnifying-glass') // Memanggil ikon kaca pembesar bawaan
                    ->searchable()
                    ->options(Miner::pluck('name', 'id'))
                    ->live() 
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $this->mountAction('lihatRak', ['record' => $state]);
                            $this->miner_id = null; 
                        }
                    })
            ]);
    }

    // Mendefinisikan Action Pop-up (Sama persis dengan yang ada di MinerResource)
    public function lihatRakAction(): Action
    {
        return Action::make('lihatRak')
            ->modalHeading(function (array $arguments) {
                $miner = Miner::find($arguments['record'] ?? null);
                return $miner ? 'Lokasi Mesin: ' . $miner->name : 'Lokasi Mesin';
            })
            ->modalContent(function (array $arguments) {
                $miner = Miner::find($arguments['record'] ?? null);
                if (!$miner) return null;

                // Memanggil visualizer yang sama persis
                return view('filament.components.rack-visualizer', [
                    'shelf_number' => $miner->shelf_number,
                    'shelf_level'  => $miner->shelf_level,
                    'slot_number'  => $miner->slot_number,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}