<?php

declare(strict_types=1);

use He4rt\Message\Actions\PersistMessage;
use He4rt\Message\Contracts\MessageRepository;
use He4rt\Message\DTO\NewMessageDTO;
use He4rt\Message\Entities\MessageEntity;
use He4rt\Provider\Enums\ProviderEnum;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->messageRepositoryStub = Mockery::mock(MessageRepository::class);
    $this->messageEntity = validMessageEntity();
    $this->messageDTO = new NewMessageDTO(
        ProviderEnum::Discord,
        $this->messageEntity->providerId,
        $this->messageEntity->providerMessageId,
        $this->messageEntity->channelId,
        $this->messageEntity->content,
        Date::parse('2023-01-24')->toDateTimeImmutable() // sentAt in string
    );
});

afterEach(function (): void {
    Mockery::close();
});

test('persist message success', function (): void {
    $this->messageRepositoryStub
        ->shouldReceive('create')
        ->with($this->messageDTO, 'canhassi', $this->messageEntity->obtainedExperience)
        ->once()
        ->andReturn($this->messageEntity);

    $test = new PersistMessage($this->messageRepositoryStub);

    $test->handle($this->messageDTO, $this->messageEntity->obtainedExperience, 'canhassi');
});

function validMessagePayload(array $fields = []): array
{
    return [
        'id' => 'canhassi-id',
        'provider_id' => 'é-o-canhas-id',
        'provider_message_id' => 'he4rtDevelopers',
        'season_id' => 12,
        'channel_id' => 'canal-foda',
        'content' => 'conteudo-foda',
        'sent_at' => new DateTimeImmutable(Date::now()->toString()),
        'obtained_experience' => 12,
        ...$fields,
    ];
}

function validMessageEntity(): MessageEntity
{
    return MessageEntity::make(validMessagePayload());
}
