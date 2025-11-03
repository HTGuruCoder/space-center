<?php

namespace App\Livewire;

use Livewire\Component;

class Test extends Component
{
    public string $phoneNumber = '';
    public string $testInput = '';

    public function save()
    {
        $validated = $this->validate([
            'phoneNumber' => 'required|string',
        ]);
    }

    public function render()
    {
        return view('livewire.test');
    }
}
