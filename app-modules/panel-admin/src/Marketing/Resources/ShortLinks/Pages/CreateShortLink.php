<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use He4rt\Marketing\ShortLink\Actions\CreateShortLink as CreateShortLinkAction;
use He4rt\Marketing\ShortLink\DTOs\NewShortLinkData;
use He4rt\Marketing\ShortLink\Exceptions\InvalidDestinationUrl;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\ShortLinkResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @property ShortLink $record
 */
class CreateShortLink extends CreateRecord
{
    protected static string $resource = ShortLinkResource::class;

    /**
     * Quem grava é a Action. Um `ShortLink::create($data)` daqui pularia a geração
     * do slug com sufixo, a validação de esquema do destino e — o mais grave — a
     * abertura do primeiro intervalo de destino, deixando todo clique futuro
     * sem destino atribuível.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return resolve(CreateShortLinkAction::class)->execute(
                NewShortLinkData::fromForm($data, $this->currentUserId()),
            );
        } catch (InvalidDestinationUrl $invalidDestinationUrl) {
            throw ValidationException::withMessages([
                'data.destination_url' => $invalidDestinationUrl->getMessage(),
            ]);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('panel-admin::marketing.short_links.notifications.created.title'))
            ->body(__('panel-admin::marketing.short_links.notifications.created.body', [
                'url' => ShortLinkResource::shortUrl($this->record),
            ]));
    }

    protected function getRedirectUrl(): string
    {
        return ShortLinkResource::getUrl('view', ['record' => $this->record]);
    }

    /**
     * O `id` do User é um UUID (string); `auth()->id()` continua declarado como
     * `int|string|null` por causa das PKs auto-incremento que o contrato ainda
     * admite. Estreitar aqui é o que mantém o DTO honesto.
     */
    private function currentUserId(): ?string
    {
        $id = auth()->id();

        return is_string($id) ? $id : null;
    }
}
