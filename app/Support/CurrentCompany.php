<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Perusahaan yang sedang aktif untuk user yang login di request ini.
 * Dipilih lewat halaman "Pilih Perusahaan" atau company switcher, disimpan
 * di session, divalidasi ulang tiap kali diakses supaya user tidak bisa
 * mengintip company yang bukan miliknya lewat session lama/tampering.
 */
class CurrentCompany
{
    private const SESSION_KEY = 'current_company_id';

    private ?Company $resolved = null;

    private bool $hasResolved = false;

    public function get(): ?Company
    {
        if ($this->hasResolved) {
            return $this->resolved;
        }

        $this->hasResolved = true;

        $user = Auth::user();
        $companyId = Session::get(self::SESSION_KEY);

        if (! $user || ! $companyId) {
            return $this->resolved = null;
        }

        return $this->resolved = $user->companies()->find($companyId);
    }

    public function id(): ?int
    {
        return $this->get()?->id;
    }

    public function set(Company $company): void
    {
        Session::put(self::SESSION_KEY, $company->id);
        $this->resolved = $company;
        $this->hasResolved = true;
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        $this->resolved = null;
        $this->hasResolved = true;
    }
}
