<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bed_capacity',
        'simrs_db_host',
        'simrs_db_port',
        'simrs_db_database',
        'simrs_db_username',
        'simrs_db_password',
    ];

    protected function casts(): array
    {
        return [
            'bed_capacity' => 'integer',
            'simrs_db_port' => 'integer',
            'simrs_db_password' => 'encrypted',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Konfigurasi koneksi database SIMRS GOS milik company ini, siap dipakai
     * lewat Config::set('database.connections.simrs', ...) oleh
     * SimrsGosRepository begitu dibuat. Null kalau belum diisi.
     *
     * @return array<string, mixed>|null
     */
    public function simrsConnectionConfig(): ?array
    {
        if (! $this->simrs_db_host) {
            return null;
        }

        return [
            'driver' => 'mysql',
            'host' => $this->simrs_db_host,
            'port' => $this->simrs_db_port ?? 3306,
            'database' => $this->simrs_db_database,
            'username' => $this->simrs_db_username,
            'password' => $this->simrs_db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }
}
