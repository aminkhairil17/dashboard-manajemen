<?php

namespace App\Livewire\Dashboard;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class PerusahaanManager extends Component
{
    public string $name = '';

    public int $bedCapacity = 120;

    /** @var array<int, string> */
    public array $memberEmail = [];

    /** @var array<int, string> */
    public array $memberRole = [];

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:120',
            'bedCapacity' => 'required|integer|min:1|max:2000',
        ]);

        $slug = Str::slug($this->name);
        $original = $slug;
        $i = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $original.'-'.(++$i);
        }

        $company = Company::create([
            'name' => $this->name,
            'slug' => $slug,
            'bed_capacity' => $this->bedCapacity,
        ]);

        $company->users()->attach(auth()->id(), ['role' => 'direktur']);

        $this->reset(['name', 'bedCapacity']);
        $this->bedCapacity = 120;
        session()->flash('status', 'Perusahaan "'.$company->name.'" berhasil dibuat dan Anda ditambahkan sebagai direktur.');
    }

    public function addMember(int $companyId): void
    {
        $email = trim($this->memberEmail[$companyId] ?? '');
        $role = trim($this->memberRole[$companyId] ?? '') ?: 'manajemen';

        $this->validate([
            'memberEmail.'.$companyId => 'required|email',
        ], [], ['memberEmail.'.$companyId => 'email']);

        $company = auth()->user()->companies()->findOrFail($companyId);
        $user = User::where('email', $email)->first();

        if (! $user) {
            session()->flash('error', 'User dengan email "'.$email.'" belum terdaftar di aplikasi ini.');

            return;
        }

        $company->users()->syncWithoutDetaching([$user->id => ['role' => $role]]);

        unset($this->memberEmail[$companyId], $this->memberRole[$companyId]);
        session()->flash('status', $user->name.' berhasil ditambahkan ke "'.$company->name.'".');
    }

    public function removeMember(int $companyId, int $userId): void
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $company->users()->detach($userId);

        session()->flash('status', 'Anggota berhasil dihapus dari perusahaan.');
    }

    public function getMyCompaniesProperty(): Collection
    {
        return auth()->user()->companies()->with('users')->get();
    }

    public function render()
    {
        return view('livewire.dashboard.perusahaan-manager', [
            'myCompanies' => $this->myCompanies,
        ])->layout('layouts.app');
    }
}
