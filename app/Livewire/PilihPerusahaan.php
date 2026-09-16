<?php

namespace App\Livewire;

use App\Support\CurrentCompany;
use Illuminate\Support\Collection;
use Livewire\Component;

class PilihPerusahaan extends Component
{
    public function getCompaniesProperty(): Collection
    {
        return auth()->user()->companies;
    }

    public function pilih(int $companyId, CurrentCompany $currentCompany): void
    {
        $company = auth()->user()->companies()->findOrFail($companyId);

        $currentCompany->set($company);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pilih-perusahaan', [
            'companies' => $this->companies,
        ])->layout('layouts.guest');
    }
}
