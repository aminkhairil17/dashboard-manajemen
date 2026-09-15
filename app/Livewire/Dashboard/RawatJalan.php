<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class RawatJalan extends Component
{
    public array $data;

    public function mount(HospitalDataRepository $repo): void
    {
        $this->data = $repo->getRawatJalanData();
    }

    public function render()
    {
        return view('livewire.dashboard.rawat-jalan')->layout('layouts.app');
    }
}
