<?php

namespace Tests\Feature;

use App\Models\TahunAjaran;
use App\Models\User;
use Tests\TestCase;

class RegistrasiUserTest extends TestCase
{
    public function test_registration_with_custom_username_saves_correctly(): void
    {
        $testEmail = 'ortu_custom_' . time() . '@example.com';
        $testUsername = 'user_custom_' . time();

        $response = $this->post(route('registrasi'), [
            'name'     => 'Orang Tua Custom',
            'username' => $testUsername,
            'email'    => $testEmail,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $user = User::where('email', $testEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals($testUsername, $user->username);
        $this->assertEquals('Orang Tua', $user->role);

        // Clean up
        $user->delete();
    }

    public function test_registration_with_empty_username_auto_generates_from_email(): void
    {
        $uniqueSuffix = time();
        $testEmail = 'ortu_auto_' . $uniqueSuffix . '@example.com';

        $response = $this->post(route('registrasi'), [
            'name'     => 'Orang Tua Auto',
            'username' => '',
            'email'    => $testEmail,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $user = User::where('email', $testEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('ortuauto' . $uniqueSuffix, $user->username);

        // Clean up
        $user->delete();
    }

    public function test_registration_fails_if_custom_username_is_taken(): void
    {
        $existing = User::first();
        $testEmail = 'new_ortu_' . time() . '@example.com';

        $response = $this->post(route('registrasi'), [
            'name'     => 'Orang Tua Baru',
            'username' => $existing->username,
            'email'    => $testEmail,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
    }
}

