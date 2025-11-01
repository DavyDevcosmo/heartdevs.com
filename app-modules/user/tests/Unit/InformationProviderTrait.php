<?php

declare(strict_types=1);

namespace He4rt\User\Tests\Unit;

use He4rt\User\Entities\InformationEntity;

trait InformationProviderTrait
{
    public function validInformationPayload(array $fields = []): array
    {
        return [
            'id' => 'canhassi-id',
            'user_id' => 'user-id-foda',
            'name' => 'canhssi',
            'nickname' => 'canhas',
            'linkedin_url' => 'https://www.linkedin.com/in/arthur-canhassi-a0546114b/',
            'github_url' => 'https://github.com/Canhassi12',
            'birthdate' => '12-11-2003',
            'about' => 'me arruma um emprego pf!! necessito ir nos eventos da he4rt quando não for em sp tmb!',
            ...$fields,
        ];
    }

    public function validInformationEntity(): InformationEntity
    {
        return InformationEntity::make($this->validInformationPayload());
    }
}
