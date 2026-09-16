<?php

namespace App\Listeners;

use App\Models\Company;
use Illuminate\Support\Str;
use RuntimeException;
use Syifa\KeycloakSso\Events\KeycloakLoginSucceeded;

/**
 * Menyamakan keanggotaan company user ini dengan field `company` dari data
 * karyawan HRIS (lihat Syifa\KeycloakSso\Support\HrisDirectoryClient),
 * supaya user yang pindah RS/entitas di HRIS otomatis ikut ter-update di
 * sini setiap login — tanpa perlu di-assign manual lewat "Kelola Perusahaan".
 *
 * Hanya MENAMBAHKAN keanggotaan (attach), tidak pernah menghapus — supaya
 * anggota yang sengaja ditambahkan manual ke company lain (mis. direktur
 * grup yang mengawasi banyak RS) tidak ikut tercabut oleh sinkronisasi ini.
 *
 * PENTING: format nilai `company` dari HRIS belum diverifikasi terhadap
 * instans HRIS sungguhan (apakah berupa nama resmi seperti "RS Syifa
 * Medika" atau kode/slug) — cocokkan `companies.slug`/`companies.name` di
 * bawah begitu sudah bisa dites dengan akses HRIS_API_* yang sesungguhnya.
 */
class SyncCompanyFromHris
{
    public function handle(KeycloakLoginSucceeded $event): void
    {
        $nip = $event->claims['nip'] ?? $event->claims['preferred_username'] ?? null;

        if (! $nip || ! function_exists('hris_employee')) {
            return;
        }

        $employee = hris_employee($nip);
        $companyLabel = $employee['company'] ?? null;

        if (! $companyLabel) {
            return;
        }

        $company = Company::where('slug', Str::slug($companyLabel))
            ->orWhere('name', $companyLabel)
            ->first();

        if (! $company) {
            report(new RuntimeException("SSO: company \"{$companyLabel}\" dari HRIS tidak cocok dengan company manapun di aplikasi ini."));

            return;
        }

        if (! $event->user->companies->contains($company)) {
            $event->user->companies()->attach($company, ['role' => 'manajemen']);
        }
    }
}
