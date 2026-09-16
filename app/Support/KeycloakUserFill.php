<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pengisian data user baru saat provisioning SSO (config('keycloak-sso.user.fill')).
 *
 * Sengaja static method (bukan closure inline di config/keycloak-sso.php) —
 * closure tidak bisa di-serialize oleh `php artisan config:cache`
 * (LogicException: "could not be serialized"), sedangkan array callable
 * [self::class, 'handle'] aman di-var_export dan tetap bisa dipanggil
 * dengan sintaks $fill($claims, $orgUnits) yang dipakai UserResolver.
 */
class KeycloakUserFill
{
    /**
     * @param  array<string, mixed>  $claims
     * @param  array<string, \Illuminate\Support\Collection>  $orgUnits
     * @return array<string, mixed>
     */
    public static function handle(array $claims, array $orgUnits): array
    {
        $nip = $claims['nip'] ?? $claims['preferred_username'] ?? null;
        $employee = ($nip && function_exists('hris_employee')) ? hris_employee($nip) : null;

        return [
            'name' => $employee['name'] ?? $claims['name'] ?? $nip ?? $claims['email'],
            'email' => $employee['email'] ?? $claims['email'] ?? null,
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ];
    }
}
