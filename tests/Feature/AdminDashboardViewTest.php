<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminDashboardViewTest extends TestCase
{
    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Tahun Ajaran');
        $response->assertSee('Kuota Siswa');
        $response->assertSee('Total Pendaftar');
        $response->assertSee('Berkas Valid');
        $response->assertSee('Lulus Seleksi');
        $response->assertSee('Daftar Ulang Selesai');
        $response->assertSee('role="progressbar"', false);
        $response->assertDontSee('Gagal');
        $response->assertDontSee('Lihat Data Penolakan');

        // Card navigation links
        $response->assertSee('href="/admin/data/tahun-ajaran"', false);
        $response->assertSee('href="/admin/data/casis"', false);
        $response->assertSee('href="/admin/pendaftaran"', false);
        $response->assertSee('href="/admin/data/pengumuman-seleksi"', false);
        $response->assertSee(route('admin.daftar_ulang.index'), false);
    }
}

