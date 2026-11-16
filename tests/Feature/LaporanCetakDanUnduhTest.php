<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LaporanCetakDanUnduhTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first() ?? User::factory()->create(['role' => 'Admin']);
    }

    public function test_admin_can_download_laporan_pendaftaran_pdf()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.unduhpendaftaran'));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_can_download_laporan_pembayaran_pdf()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.unduhpembayaran'));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_can_view_laporan_daftar_ulang_cetak()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.daftar-ulang.cetak'));
        $response->assertStatus(200);
        $response->assertSee('SD KRISTEN DIAKUI RANTAI DAMAI');
        $response->assertSee('LAPORAN DAFTAR ULANG CALON SISWA');
        $response->assertSee('No. Registrasi');
        $response->assertSee('Status Berkas');
    }

    public function test_view_laporan_pendaftaran_renders_masked_nik()
    {
        $view = $this->actingAs($this->admin)->view('laporan.unduhpendaftaran', [
            'data' => \App\Models\CalonSiswa::all(),
            'activeFilter' => [],
            'filterTahunAjaran' => null,
        ]);

        $view->assertSee('SD KRISTEN DIAKUI RANTAI DAMAI');
        $view->assertSee('LAPORAN PENDAFTARAN CALON SISWA');
        $view->assertSee('****'); // Samaran NIK harus ada
    }

    public function test_view_laporan_pembayaran_renders_header_and_table()
    {
        $view = $this->actingAs($this->admin)->view('laporan.unduhpembayaran', [
            'data' => \App\Models\Pembayaran::with('calonSiswa.tahunAjaran')->get(),
            'activeFilter' => [],
            'filterTahunAjaran' => null,
        ]);

        $view->assertSee('SD KRISTEN DIAKUI RANTAI DAMAI');
        $view->assertSee('LAPORAN PEMBAYARAN FORMULIR PENDAFTARAN');
        $view->assertSee('Order ID');
        $view->assertSee('Nominal');
    }

    public function test_laporan_renders_two_column_signatures()
    {
        $view = $this->actingAs($this->admin)->view('laporan.unduhpendaftaran', [
            'data' => \App\Models\CalonSiswa::all(),
            'activeFilter' => [],
            'filterTahunAjaran' => null,
            'namaKepalaSekolah' => 'Serni, S.Pd.K',
            'jabatanPetugas' => 'Administrator',
            'namaPetugas' => 'Marini Pangeno, S.Pd',
            'tanggalCetak' => '9 November 2026',
            'kotaSekolah' => 'Rantai Damai',
        ]);

        $view->assertSee('Mengetahui,');
        $view->assertSee('Kepala Sekolah');
        $view->assertSee('Serni, S.Pd.K');
        $view->assertSee('Rantai Damai, 9 November 2026');
        $view->assertSee('Petugas: Administrator');
        $view->assertSee('Marini Pangeno, S.Pd');
    }

    public function test_form_edit_tahun_ajaran_has_nama_kepala_sekolah()
    {
        $response = $this->actingAs($this->admin)->get('/admin/tahun-ajaran/edit/1');
        $response->assertStatus(200);
        $response->assertSee('Nama Kepala Sekolah');
        $response->assertSee('nama_kepala_sekolah');
        $response->assertSee('Serni, S.Pd.K');
    }
}

