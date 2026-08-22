<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Marketing\ShortLink\Actions\UpdateShortLink as UpdateShortLinkAction;
use He4rt\Marketing\ShortLink\DTOs\ShortLinkChanges;
use He4rt\Marketing\ShortLink\Exceptions\InvalidDestinationUrl;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\ShortLinkResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @property ShortLink $record
 */
class EditShortLink extends EditRecord
{
    protected static string $resource = ShortLinkResource::class;

    /**
     * `utm` e `tags` são Value Objects. Nem `AsUtmParameters`/`AsTagList` implementam
     * `SerializesCastableAttributes`, nem os VOs são `Arrayable`, então
     * `attributesToArray()` devolve os objetos crus — que o Livewire não consegue
     * desidratar. Achatamos aqui, na camada de apresentação, no formato plano que
     * o `FormPayloadNormalizer` já sabe ler de volta.
     *
     * A chave `utm` precisa sair do payload: `FormPayloadNormalizer::utm()` prioriza
     * `$data['utm']` e ignoraria os campos planos se ela continuasse presente.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ShortLink $record */
        $record = $this->getRecord();

        unset($data['utm']);

        return [
            ...$data,
            ...$record->utm->toArray(),
            'tags' => $record->tags->toArray(),
        ];
    }

    /**
     * Quem grava é a Action: ela fecha o intervalo de destino anterior e abre o
     * novo. Um `$record->update($data)` daqui apagaria o histórico silenciosamente.
     *
     * @param  ShortLink  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return resolve(UpdateShortLinkAction::class)->execute(
                $record,
                ShortLinkChanges::fromForm($data, $this->currentUserId()),
            );
        } catch (InvalidDestinationUrl $invalidDestinationUrl) {
            throw ValidationException::withMessages([
                'data.destination_url' => $invalidDestinationUrl->getMessage(),
            ]);
        }
    }

    /**
     * @return array<int, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ShortLinkResource::getUrl('view', ['record' => $this->getRecord()]);
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
