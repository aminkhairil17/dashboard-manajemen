<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class Efisiensi extends Component
{
    public array $tat;

    public array $otRooms;

    public array $waitTimes;

    public function mount(HospitalDataRepository $repo): void
    {
        $data = $repo->getEfisiensiData();

        $this->tat = $data['tat'];
        $this->otRooms = $data['ot_rooms'];
        $this->waitTimes = $data['wait_times'];
    }

    public function render()
    {
        return view('livewire.dashboard.efisiensi')->layout('layouts.app');
    }
}
