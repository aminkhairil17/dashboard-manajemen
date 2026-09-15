<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class Mutu extends Component
{
    public array $kematian;

    public array $hais;

    public array $kepatuhan;

    public array $insiden;

    public array $komplain;

    public function mount(HospitalDataRepository $repo): void
    {
        $data = $repo->getMutuData();

        $this->kematian = $data['kematian'];
        $this->hais = $data['hais'];
        $this->kepatuhan = $data['kepatuhan'];
        $this->insiden = $data['insiden'];
        $this->komplain = $data['komplain'];
    }

    public function render()
    {
        return view('livewire.dashboard.mutu')->layout('layouts.app');
    }
}
