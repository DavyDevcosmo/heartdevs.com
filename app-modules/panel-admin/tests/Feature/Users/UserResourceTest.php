<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Identity\Tenant\Models\Tenant;
use He4rt\Identity\User\Models\User;
use He4rt\PanelAdmin\Filament\Resources\Users\Pages\EditUser;
use He4rt\Profile\Enums\StartAvailability;
use He4rt\Profile\Models\Profile;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant = Tenant::factory()->create(['slug' => 'test-tenant']);
    $this->tenant->members()->attach($this->member);

    $this->actingAs($this->admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::setTenant($this->tenant);

    $this->profile = Profile::query()
        ->where('user_id', $this->member->id)
        ->where('tenant_id', $this->tenant->id)
        ->first();
});

test('admin sees profile tab on user resource', function (): void {
    livewire(EditUser::class, ['record' => $this->member->getRouteKey()])
        ->assertSeeText('Profile');
});

test('profile tab loads member data', function (): void {
    $this->profile->update(['headline' => 'Backend Dev']);

    livewire(EditUser::class, ['record' => $this->member->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'profile.headline' => 'Backend Dev',
        ]);
});

test('admin can edit member bio', function (): void {
    livewire(EditUser::class, ['record' => $this->member->getRouteKey()])
        ->fillForm([
            'profile.about' => 'Bio moderada pelo admin',
        ])
        ->call('save')
        ->assertNotified();

    expect($this->profile->fresh()->about)->toBe('Bio moderada pelo admin');
});

test('validates bio max length', function (): void {
    livewire(EditUser::class, ['record' => $this->member->getRouteKey()])
        ->fillForm([
            'profile.about' => str_repeat('a', 501),
        ])
        ->call('save')
        ->assertHasFormErrors(['profile.about']);
});

test('toggle availability shows start availability field', function (): void {
    $this->profile->update([
        'available_for_proposals' => true,
        'start_availability' => StartAvailability::Immediate,
    ]);

    livewire(EditUser::class, ['record' => $this->member->getRouteKey()])
        ->assertSchemaStateSet([
            'profile.available_for_proposals' => true,
            'profile.start_availability' => StartAvailability::Immediate,
        ]);
});
