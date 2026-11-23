<?php

namespace Tests\Feature;

use App\Livewire\Auth\AdminBar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bar_verifies_correct_pin_and_grants_access(): void
    {
        Livewire::test(AdminBar::class)
            ->call('openLogin')
            ->assertSet('showModal', true)
            ->set('adminPin', '9999')
            ->call('verifyPin')
            ->assertHasErrors(['adminPin'])
            ->set('adminPin', '1210')
            ->call('verifyPin')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertTrue(session('is_admin'));

        // Test logout
        Livewire::test(AdminBar::class)
            ->call('logout');

        $this->assertNull(session('is_admin'));
    }

    public function test_guest_is_redirected_away_from_create_tournament_page(): void
    {
        $response = $this->get('/tournaments/create');
        $response->assertRedirect(route('home'));
        $this->assertEquals('Bạn cần nhập mã PIN quản trị (1210) để tạo giải đấu mới.', session('auth_required'));
    }

    public function test_admin_can_access_create_tournament_page(): void
    {
        $this->withSession(['is_admin' => true]);
        $response = $this->get('/tournaments/create');
        $response->assertStatus(200);
    }
}
