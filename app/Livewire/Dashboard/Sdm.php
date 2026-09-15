<?php

namespace App\Livewire\Dashboard;

use App\Contracts\HospitalDataRepository;
use Livewire\Component;

class Sdm extends Component
{
    public array $staffCounts;

    public array $nurseRatio;

    public array $metrics;

    public function mount(HospitalDataRepository $repo): void
    {
        $data = $repo->getSdmData();

        $this->staffCounts = $data['staff_counts'];
        $this->nurseRatio = $data['nurse_ratio'];
        $this->metrics = $data['metrics'];
    }

    public function render()
    {
        return view('livewire.dashboard.sdm')->layout('layouts.app');
    }
}
