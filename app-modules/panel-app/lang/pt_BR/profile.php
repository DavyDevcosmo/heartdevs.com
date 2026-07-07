<?php

declare(strict_types=1);

return [
    'sections' => [
        'personal' => 'Pessoal',
        'professional' => 'Profissional',
        'about' => 'Sobre',
        'address' => 'Localização',
        'social_links' => 'Links Sociais',
        'availability' => 'Disponibilidade',
        'connections' => 'Conexões',
    ],

    'fields' => [
        'nickname' => 'Apelido',
        'birthdate' => 'Data de Nascimento',
        'headline' => 'Título Profissional',
        'seniority_level' => 'Senioridade',
        'years_experience' => 'Anos de Experiência',
        'about' => 'Sobre',
        'platform' => 'Plataforma',
        'handle' => 'Handle / URL',
        'country' => 'País',
        'state' => 'Estado',
        'city' => 'Cidade',
        'avatar' => 'Foto',
        'cover' => 'Capa',
        'available_for_proposals' => 'Disponível para propostas',
        'start_availability' => 'Disponibilidade para início',
    ],

    'placeholders' => [
        'nickname' => 'Como você é conhecido?',
        'headline' => 'Seu cargo ou título profissional',
        'about' => 'Conte um pouco sobre você...',
        'handle' => '@usuario ou https://...',
        'city_search' => 'Buscar cidade...',
    ],

    'hints' => [
        'headline' => 'Ex: Frontend Developer, Product Designer',
        'available_for_proposals' => 'Quando ativo, recrutadores verão um badge verde no seu perfil',
        'city' => 'Se sua cidade não estiver na listagem, pesquise.',
    ],

    'actions' => [
        'save' => 'Salvar perfil',
        'add_social_link' => 'Adicionar link social',
    ],

    'notifications' => [
        'saved' => 'Perfil salvo com sucesso!',
        'no_profile' => 'Perfil não encontrado para este tenant.',
    ],
];
