<?php

namespace Tests\Feature;

use App\Models\CalonSiswa;
use App\Models\User;
use App\Models\PembayaranDaftarUlang;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDaftarUlangAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected ?User $admin = null;
    protected ?User $panitia = null;
    protected ?User $ortu = null;
    protected ?CalonSiswa $casis = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first();
        $this->panitia = User::where('role', 'Panitia')->first();
        $this->ortu = User::where('role', 'Orang Tua')->first();
        $this->casis = CalonSiswa::whereNull('deleted_at')->whereHas('seleksi.daftarUlang')->first();
    }

    public function test_guest_is_redirected_on_catat_pembayaran(): void
    {
        $response = $this->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/catat-pembayaran", [
            'jml_bayar_du' => 50000,
            'tgl_bayar_du' => '2026-11-08',
        ]);
        $response->assertStatus(302);
    }

    public function test_panitia_is_forbidden_on_catat_pembayaran(): void
    {
        $response = $this->actingAs($this->panitia)->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/catat-pembayaran", [
            'jml_bayar_du' => 50000,
            'tgl_bayar_du' => '2026-11-08',
        ]);
        $response->assertStatus(403);
    }

    public function test_ortu_is_forbidden_on_catat_pembayaran(): void
    {
        $response = $this->actingAs($this->ortu)->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/catat-pembayaran", [
            'jml_bayar_du' => 50000,
            'tgl_bayar_du' => '2026-11-08',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_is_authorized_on_catat_pembayaran(): void
    {
        // Gunakan calon siswa yang masih memiliki sisa tagihan
        $casisWithSisa = CalonSiswa::whereNull('deleted_at')
            ->whereHas('seleksi.daftarUlang')
            ->get()
            ->first(function ($c) {
                $du = $c->seleksi?->daftarUlang;
                $paid = $du ? $du->pembayaranDaftarUlang()->whereIn('status_du', ['Lunas', 'Diterima'])->sum('jml_bayar_du') : 0;
                $total = (float) ($c->tahunAjaran?->biaya_daftar_ulang ?? 1000000);
                return ($total - $paid) >= 50000;
            }) ?? $this->casis;

        $response = $this->actingAs($this->admin)->post("/admin/verifikasi-daftar-ulang/{$casisWithSisa->id_calon_siswa}/catat-pembayaran", [
            'jml_bayar_du' => 50000,
            'tgl_bayar_du' => '2026-11-08',
            'keterangan' => 'Uji otorisasi admin',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_guest_is_redirected_on_terima_berkas(): void
    {
        $response = $this->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/terima-berkas", [
            'status_berkas' => 'Diterima',
            'jenis_berkas' => 'Asli',
        ]);
        $response->assertStatus(302);
    }

    public function test_panitia_is_forbidden_on_terima_berkas(): void
    {
        $response = $this->actingAs($this->panitia)->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/terima-berkas", [
            'status_berkas' => 'Diterima',
            'jenis_berkas' => 'Asli',
        ]);
        $response->assertStatus(403);
    }

    public function test_ortu_is_forbidden_on_terima_berkas(): void
    {
        $response = $this->actingAs($this->ortu)->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/terima-berkas", [
            'status_berkas' => 'Diterima',
            'jenis_berkas' => 'Asli',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_is_authorized_on_terima_berkas(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/terima-berkas", [
            'status_berkas' => 'Diterima',
            'jenis_berkas' => 'Asli',
            'catatan_berkas' => 'Uji terima berkas admin',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_guest_is_redirected_on_kuitansi(): void
    {
        $response = $this->get("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/kuitansi");
        $response->assertStatus(302);
    }

    public function test_panitia_is_forbidden_on_kuitansi(): void
    {
        $response = $this->actingAs($this->panitia)->get("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/kuitansi");
        $response->assertStatus(403);
    }

    public function test_ortu_is_forbidden_on_kuitansi(): void
    {
        $response = $this->actingAs($this->ortu)->get("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/kuitansi");
        $response->assertStatus(403);
    }

    public function test_admin_is_authorized_on_kuitansi(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/verifikasi-daftar-ulang/{$this->casis->id_calon_siswa}/kuitansi");
        $response->assertStatus(200);
    }

    public function test_admin_can_access_verifikasi_daftar_ulang_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/verifikasi-daftar-ulang');
        $response->assertStatus(200);
        $response->assertSee('Daftar Ulang');
    }

    public function test_panitia_cannot_access_verifikasi_daftar_ulang_index(): void
    {
        $response = $this->actingAs($this->panitia)->get('/admin/verifikasi-daftar-ulang');
        $response->assertStatus(403);
    }

    public function test_ortu_cannot_access_verifikasi_daftar_ulang_index(): void
    {
        $response = $this->actingAs($this->ortu)->get('/admin/verifikasi-daftar-ulang');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_verifikasi_daftar_ulang_index(): void
    {
        $response = $this->get('/admin/verifikasi-daftar-ulang');
        $response->assertStatus(302);
    }

    public function test_admin_can_access_laporan_daftar_ulang(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/laporan/daftar-ulang');
        $response->assertStatus(200);
    }

    public function test_panitia_cannot_access_laporan_daftar_ulang(): void
    {
        $response = $this->actingAs($this->panitia)->get('/admin/laporan/daftar-ulang');
        $response->assertStatus(403);
    }
}
