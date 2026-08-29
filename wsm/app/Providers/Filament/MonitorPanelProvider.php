<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\IncidentReportWidget;
use App\Filament\Pages\AnalyticDashboard;
use Filament\Navigation\MenuItem;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;

class MonitorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('monitor')
            ->path('monitor')
            ->login()
            ->maxContentWidth(MaxWidth::Full)
            ->colors([
                'primary' => Color::Amber,
            ])

           ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* Aturan ini HANYA berlaku di layar HP (lebar maksimal 640px) */
                    @media (max-width: 640px) {
                        /* 1. Sikat habis padding di container paling luar */
                        .fi-main-ctn, .fi-page {
                            padding-left: 5px !important;
                            padding-right: 5px !important;
                            overflow-x: hidden !important;
                        }
                        
                        /* 2. Beri sedikit nafas untuk Header (Judul & Tombol) agar tidak nabrak bezel */
                        .fi-header, .fi-topbar {
                            padding-left: 1rem !important;
                            padding-right: 1rem !important;
                        }

                        /* 3. Paksa tabel benar-benar full width tanpa batas */
                        .fi-ta-ctn {
                            margin-left: 2px !important;
                            margin-right: 2px !important;
                            border-radius: 0 !important;
                            border-left: none !important;
                            border-right: none !important;
                            box-shadow: none !important;
                        }
                    }
                </style>'
            )
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                AnalyticDashboard::class,
                
            ])
            
            ->userMenuItems([
                MenuItem::make()
                    ->label('Pengaturan Akun')
                    ->url(fn (): string => '/monitor/profile')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])

            ->sidebarCollapsibleOnDesktop() // Menjadikan sidebar sebagai burger menu di PC
            // Opsi jika nanti pakai Vite untuk custom theme
            // Atau jika pakai file statis:
            // ->assets([ \Filament\Support\Assets\Css::make('neon-style', public_path('css/neon.css')) ])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                IncidentReportWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        // 1. Panggil neon.css
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            fn (): string => '
                <link rel="stylesheet" href="' . asset('css/neon.css') . '?v=' . time() . '">
                
                <style>
                    /* Class Paksaan untuk Chart Ungu */
                    .neon-forced-chart {
                        background-color: #151e32 !important;
                        border: 2px solid #a855f7 !important;
                        box-shadow: 0 0 25px rgba(168, 85, 247, 0.3), inset 0 0 15px rgba(168, 85, 247, 0.1) !important;
                        border-radius: 12px !important;
                        overflow: hidden !important;
                    }
                    /* Tarik teks ke kiri */
                    .neon-forced-chart .fi-section-header { 
                        padding-left: 1.5rem !important; 
                        padding-bottom: 0.5rem !important; 
                        border-bottom: none !important; 
                    }
                    /* Hancurkan garis abu-abu melintang */
                    .neon-forced-chart .fi-section-content-ctn { 
                        border-top: none !important; 
                    }
                    /* Rapikan jarak kanvas */
                    .neon-forced-chart .fi-section-content { 
                        padding-top: 0.5rem !important; 
                        padding-left: 1.5rem !important; 
                        padding-right: 1.5rem !important; 
                    }
                </style>

                <script>
                    // 2. Javascript Pemburu Chart (Berjalan otomatis & melawan Livewire)
                    document.addEventListener("DOMContentLoaded", () => {
                        setInterval(() => {
                            // Cari semua elemen judul <h3> di halaman
                            document.querySelectorAll("h3").forEach(el => {
                                // Jika judulnya mengandung kata "NETWORK TRAFFIC"
                                if(el.innerText.includes("NETWORK TRAFFIC")) {
                                    // Cari <section> induknya dan paksa masukkan class neon-forced-chart
                                    let section = el.closest("section");
                                    if(section && !section.classList.contains("neon-forced-chart")) {
                                        section.classList.add("neon-forced-chart");
                                    }
                                }
                            });
                        }, 100); // Kecepatan eksekusi 0.1 detik, Filament tidak akan bisa berkutik!
                    });
                </script>
            '
        );
    }
}

