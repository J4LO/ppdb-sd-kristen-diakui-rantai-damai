<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanitiaDashboardViewTest extends TestCase
{
    public function test_panitia_can_access_dashboard(): void
    {
        $panitia = User::where('role', 'Panitia')->first();
        if (!$panitia) {
            $this->markTestSkipped('User with role Panitia not found.');
        }

        $response = $this->actingAs($panitia)->get('/beranda/panitia');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Penilaian');
        $response->assertSee('Total Peserta');
        $response->assertSee('Belum Dinilai');
        $response->assertSee('Lulus Seleksi');
        $response->assertSee('Tidak Lulus');
        $response->assertSee('Hasil Penilaian');
        $response->assertDontSee('Pendaftar Diterima');
        $response->assertDontSee('Top 5 Calon Siswa (Lolos Seleksi)');
    }

    public function test_guest_is_redirected_from_panitia_dashboard(): void
    {
        $response = $this->get('/beranda/panitia');
        $response->assertRedirect('/');
    }
}

