<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Syifa\KeycloakSso\Support\UserResolver;
use Tests\TestCase;

class KeycloakSsoIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_fill_array_callable_membuat_user_baru_dengan_password_terisi(): void
    {
        $resolver = new UserResolver;

        $user = $resolver->resolve([
            'sub' => 'kc-sub-123',
            'preferred_username' => 'budi.sso',
            'email' => 'budi.sso@example.com',
            'name' => 'Budi SSO',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('budi.sso@example.com', $user->email);
        $this->assertNotEmpty($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('kc-sub-123', $user->keycloak_sub);
    }

    public function test_login_sso_kedua_mencocokkan_user_yang_sama_lewat_keycloak_sub(): void
    {
        $resolver = new UserResolver;

        $first = $resolver->resolve([
            'sub' => 'kc-sub-456',
            'preferred_username' => 'siti.sso',
            'email' => 'siti.sso@example.com',
            'name' => 'Siti SSO',
        ]);

        $second = $resolver->resolve([
            'sub' => 'kc-sub-456',
            'preferred_username' => 'siti.sso',
            'email' => 'siti.sso@example.com',
            'name' => 'Siti SSO',
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, User::count());
    }
}
