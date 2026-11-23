<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\On;
use Livewire\Component;

class AdminBar extends Component
{
    public bool $showModal = false;
    public string $adminPin = '';
    public string $intendedUrl = '';

    #[On('open-admin-login')]
    public function openLogin(string $redirectUrl = ''): void
    {
        $this->intendedUrl = $redirectUrl;
        $this->adminPin = '';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeLogin(): void
    {
        $this->showModal = false;
        $this->adminPin = '';
        $this->resetErrorBag();
    }

    public function verifyPin()
    {
        $this->resetErrorBag();
        $correctPin = (string) env('ADMIN_PIN', '1210');

        if (trim($this->adminPin) !== $correctPin) {
            $this->addError('adminPin', 'Mã PIN quản trị không chính xác.');
            return;
        }

        session(['is_admin' => true]);
        $this->showModal = false;
        $this->dispatch('admin-status-changed');

        if (! empty($this->intendedUrl)) {
            return $this->redirect($this->intendedUrl, navigate: true);
        }

        return $this->redirect(request()->header('Referer', route('home')), navigate: true);
    }

    public function logout()
    {
        session()->forget('is_admin');
        $this->dispatch('admin-status-changed');

        return $this->redirect(route('home'), navigate: true);
    }

    public function clickCreateTournament()
    {
        if (session('is_admin')) {
            return $this->redirect(route('tournament.create'), navigate: true);
        }

        $this->openLogin(route('tournament.create'));
    }

    public function render()
    {
        return view('livewire.auth.admin-bar', [
            'isAdmin' => (bool) session('is_admin'),
        ]);
    }
}
