<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\PerusahaanManager;
use App\Livewire\PilihPerusahaan;
use App\Models\Company;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_tanpa_company_ditolak_akses_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_user_dengan_satu_company_langsung_masuk_dashboard(): void
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'RS Tunggal', 'slug' => 'rs-tunggal']);
        $user->companies()->attach($company, ['role' => 'manajemen']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $this->assertEquals($company->id, session('current_company_id'));
    }

    public function test_user_dengan_dua_company_diarahkan_ke_halaman_pilih_perusahaan(): void
    {
        $user = User::factory()->create();
        $a = Company::create(['name' => 'RS A', 'slug' => 'rs-a']);
        $b = Company::create(['name' => 'RS B', 'slug' => 'rs-b']);
        $user->companies()->attach([$a->id, $b->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('perusahaan.pilih'));
    }

    public function test_memilih_perusahaan_menyimpan_ke_session_dan_redirect_ke_dashboard(): void
    {
        $user = User::factory()->create();
        $a = Company::create(['name' => 'RS A', 'slug' => 'rs-a']);
        $b = Company::create(['name' => 'RS B', 'slug' => 'rs-b']);
        $user->companies()->attach([$a->id, $b->id]);

        $this->actingAs($user);

        Livewire::test(PilihPerusahaan::class)
            ->call('pilih', $b->id)
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($b->id, session('current_company_id'));
    }

    public function test_user_tidak_bisa_memilih_company_milik_orang_lain(): void
    {
        $user = User::factory()->create();
        $lainnya = Company::create(['name' => 'RS Bukan Milik User', 'slug' => 'rs-bukan-milik']);

        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(PilihPerusahaan::class)->call('pilih', $lainnya->id);
    }

    public function test_data_operasional_berbeda_untuk_company_dengan_kapasitas_beda(): void
    {
        $user = User::factory()->create();
        $kecil = Company::create(['name' => 'RS Kecil', 'slug' => 'rs-kecil', 'bed_capacity' => 60]);
        $besar = Company::create(['name' => 'RS Besar', 'slug' => 'rs-besar', 'bed_capacity' => 240]);
        $user->companies()->attach([$kecil->id, $besar->id]);

        $this->actingAs($user);

        $end = CarbonImmutable::now();
        $start = $end->subDays(29);

        session(['current_company_id' => $kecil->id]);
        $repoKecil = app(\App\Contracts\HospitalDataRepository::class);
        $bedsKecil = $repoKecil->getBedCensus($start, $end)['total_beds'];

        $this->app->forgetInstance(\App\Support\CurrentCompany::class);
        $this->app->forgetInstance(\App\Contracts\HospitalDataRepository::class);

        session(['current_company_id' => $besar->id]);
        $repoBesar = app(\App\Contracts\HospitalDataRepository::class);
        $bedsBesar = $repoBesar->getBedCensus($start, $end)['total_beds'];

        $this->assertSame(60, $bedsKecil);
        $this->assertSame(240, $bedsBesar);
    }

    public function test_membuat_perusahaan_baru_otomatis_menjadikan_pembuat_sebagai_direktur(): void
    {
        $user = User::factory()->create();
        $existing = Company::create(['name' => 'RS Existing', 'slug' => 'rs-existing']);
        $user->companies()->attach($existing, ['role' => 'manajemen']);
        session(['current_company_id' => $existing->id]);

        $this->actingAs($user);

        Livewire::test(PerusahaanManager::class)
            ->set('name', 'RS Baru')
            ->set('bedCapacity', 100)
            ->call('create');

        $this->assertDatabaseHas('companies', ['name' => 'RS Baru', 'bed_capacity' => 100]);

        $baru = Company::where('name', 'RS Baru')->first();
        $this->assertTrue($user->fresh()->companies->contains($baru));
        $this->assertEquals('direktur', $user->fresh()->companies()->find($baru->id)->pivot->role);
    }
}
