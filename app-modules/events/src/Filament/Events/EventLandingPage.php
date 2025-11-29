<?php

declare(strict_types=1);

namespace He4rt\Events\Filament\Events;

use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class EventLandingPage extends Dashboard
{
    public $tenant;

    protected static ?string $title = 'Evento 3Pontos';

    protected static ?string $navigationLabel = 'Evento 3Pontos';

    protected static bool $shouldRegisterNavigation = false;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(): void
    {
        $this->tenant = filament()->getTenant();

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => Blade::render('he4rt::components.metatags', [
                'url' => url()->current(),
                'title' => $this->getTitle(),
                'description' => 'eae',
                'coverImage' => 'https://3pontos.work/images/seo.png',
                'icon' => asset('logo3p-1.png'),
            ]),
        );
    }

    public function getView(): string
    {
        $view = sprintf('events::components.themes.%s.homepage', $this->tenant->slug);

        abort_unless(view()->exists($view), 403, 'Forbidden Tenant');

        return $view;
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getLayout(): string
    {
        return 'he4rt::components.base.index';
    }

    protected function getViewData(): array
    {
        return [
            'event' => $this->tenant->events()->first(),
        ];
    }
}
