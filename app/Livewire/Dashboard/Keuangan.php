<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class Keuangan extends Component
{
    public array $data;

    public function mount(HospitalDataRepository $repo): void
    {
        $this->data = $repo->getKeuanganData();
    }

    public function render()
    {
        return view('livewire.dashboard.keuangan')->layout('layouts.app');
    }
}
