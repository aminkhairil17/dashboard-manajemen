<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class Konversi extends Component
{
    public array $groups;

    public function mount(HospitalDataRepository $repo): void
    {
        $this->groups = $repo->getKonversiData();
    }

    public function render()
    {
        return view('livewire.dashboard.konversi')->layout('layouts.app');
    }
}
