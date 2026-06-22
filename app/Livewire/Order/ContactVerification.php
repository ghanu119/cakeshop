<?php

namespace App\Livewire\Order;

use Livewire\Component;

class ContactVerification extends Component
{
    public string $guest_name = '';

    public string $guest_email = '';

    public string $guest_phone = '';

    public function mount(): void
    {
        $this->guest_name = old('guest_name', '');
        $this->guest_email = old('guest_email', '');
        $this->guest_phone = old('guest_phone', '');
    }

    public function render()
    {
        return view('livewire.order.contact-verification');
    }
}
