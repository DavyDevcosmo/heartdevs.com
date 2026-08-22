<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\ContentEntries\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use He4rt\PanelAdmin\Filament\Resources\ContentEntries\ContentEntryResource;

class ViewContentEntry extends ViewRecord
{
    protected static string $resource = ContentEntryResource::class;

    /**
     * @return array<int, EditAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
