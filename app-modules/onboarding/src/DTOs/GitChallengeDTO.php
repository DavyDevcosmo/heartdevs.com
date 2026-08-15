<?php

declare(strict_types=1);

namespace He4rt\Onboarding\DTOs;

use He4rt\Onboarding\Contracts\OnboardingStepDTO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Payload da conclusão do desafio do Git.
 *
 * TODO: #353 aperta o contrato com os campos do PR aprovado
 * (repo, pr_number, approved_at) quando o evento GithubPullRequestApproved existir.
 */
final readonly class GitChallengeDTO implements OnboardingStepDTO
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        public array $data = [],
    ) {}

    /**
     * @throws ValidationException
     */
    public function validate(array $payload): static
    {
        $validated = Validator::make($payload, [
            'data' => ['sometimes', 'array'],
        ])->validate();

        $data = $validated['data'] ?? [];

        return new self(
            data: is_array($data) ? $data : [],
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
