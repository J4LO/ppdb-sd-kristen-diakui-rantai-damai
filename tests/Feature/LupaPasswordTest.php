<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LupaPasswordTest extends TestCase
{
    public function test_landing_page_renders_email_or_username_and_forgot_password_modal(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Email / Username');
        $response->assertSee('Lupa password?');
        $response->assertSee('Reset Password');
        $response->assertSee('Nomor HP Terdaftar / NIK Siswa');
        $response->assertSee('Hubungi Panitia (0812-3456-7890)');
    }

    public function test_reset_password_succeeds_with_matching_email_and_phone(): void
    {
        $testEmail = 'reset_test_' . time() . '@example.com';
        $testPhone = '081298765432';

        $user = User::create([
            'name'     => 'User Reset Test',
            'username' => 'user_reset_' . time(),
            'email'    => $testEmail,
            'no_hp'    => $testPhone,
            'password' => Hash::make('oldpassword123'),
            'role'     => 'Orang Tua',
        ]);

        $num1 = 4;
        $num2 = 7;
        $response = $this->post(route('password.reset.public'), [
            'email_reset'           => $testEmail,
            'no_hp_reset'           => $testPhone,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'num1'                  => $num1,
            'num2'                  => $num2,
            'captcha'               => $num1 + $num2,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $response->assertSessionHas('login_modal', true);

        // Verify user password has been updated
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Clean up
        $user->delete();
    }

    public function test_reset_password_succeeds_with_username_and_phone(): void
    {
        $testUsername = 'user_by_uname_' . time();
        $testPhone = '081211112222';

        $user = User::create([
            'name'     => 'User Reset By Username',
            'username' => $testUsername,
            'email'    => 'byuname_' . time() . '@example.com',
            'no_hp'    => $testPhone,
            'password' => Hash::make('oldpassword123'),
            'role'     => 'Orang Tua',
        ]);

        $num1 = 3;
        $num2 = 5;
        $response = $this->post(route('password.reset.public'), [
            'email_reset'           => $testUsername,
            'no_hp_reset'           => $testPhone,
            'password'              => 'newsecretpass99',
            'password_confirmation' => 'newsecretpass99',
            'num1'                  => $num1,
            'num2'                  => $num2,
            'captcha'               => $num1 + $num2,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newsecretpass99', $user->password));

        // Clean up
        $user->delete();
    }

    public function test_reset_password_fails_if_phone_does_not_match(): void
    {
        $testEmail = 'mismatch_' . time() . '@example.com';
        $user = User::create([
            'name'     => 'User Mismatch Test',
            'username' => 'mismatch_' . time(),
            'email'    => $testEmail,
            'no_hp'    => '081299998888',
            'password' => Hash::make('oldpassword123'),
            'role'     => 'Orang Tua',
        ]);

        $num1 = 2;
        $num2 = 2;
        $response = $this->post(route('password.reset.public'), [
            'email_reset'           => $testEmail,
            'no_hp_reset'           => '081200000000', // salah nomor HP
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'num1'                  => $num1,
            'num2'                  => $num2,
            'captcha'               => 4,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('forgot_error');

        // Password must not have changed
        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));

        // Clean up
        $user->delete();
    }

    public function test_reset_password_fails_if_captcha_is_wrong(): void
    {
        $testEmail = 'captcha_fail_' . time() . '@example.com';
        $user = User::create([
            'name'     => 'User Captcha Fail',
            'username' => 'captchafail_' . time(),
            'email'    => $testEmail,
            'no_hp'    => '081299998888',
            'password' => Hash::make('oldpassword123'),
            'role'     => 'Orang Tua',
        ]);

        $response = $this->post(route('password.reset.public'), [
            'email_reset'           => $testEmail,
            'no_hp_reset'           => '081299998888',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'num1'                  => 5,
            'num2'                  => 5,
            'captcha'               => 99, // Jawaban salah
        ]);

        $response->assertSessionHasErrors('captcha');

        // Clean up
        $user->delete();
    }
}

