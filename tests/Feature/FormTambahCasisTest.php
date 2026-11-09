<?php

namespace Tests\Feature;

use App\Models\CalonSiswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Tests\TestCase;

class FormTambahCasisTest extends TestCase
{
    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first();
    }

    public function test_admin_can_access_form_tambah_siswa(): void
    {
        $response = $this->actingAs($this->admin)->get(route('formpendaftaran'));
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Calon Siswa Baru');
        $response->assertSee('Akun Akses Orang Tua / Wali');
        $response->assertSee('Biodata Pokok Calon Siswa');
        $response->assertSee('Data Orang Tua / Wali & Kontak', false);
    }

    public function test_form_validation_rejects_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('prosespendaftaran'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password', 'nik', 'nama', 'jenis_kelamin', 'alamat', 'nama_ortu', 'no_hp']);
    }

    public function test_admin_can_submit_tambah_siswa_successfully(): void
    {
        $testEmail = 'test_casis_' . time() . '@example.com';
        $testNik = '7302010101' . rand(100000, 999999);

        $response = $this->actingAs($this->admin)->post(route('prosespendaftaran'), [
            'name'            => 'Bapak Test Unit',
            'email'           => $testEmail,
            'password'        => 'password123',
            'nik'             => $testNik,
            'nama'            => 'Anak Test Unit',
            'jenis_kelamin'   => 'Laki-Laki',
            'tempat_lahir'    => 'Makassar',
            'tanggal_lahir'   => '2019-05-10',
            'alamat'          => 'Jl. Merpati Putih No. 12',
            'nama_ortu'       => 'Bapak Test Unit',
            'pendidikan_ortu' => 'S1',
            'no_hp'           => '081234567890',
        ]);

        $response->assertRedirect(route('indexcasis'));
        $response->assertSessionHas('success');

        // Verify DB
        $user = User::where('email', $testEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('Orang Tua', $user->role);
        $this->assertEquals('081234567890', $user->no_hp);

        $casis = CalonSiswa::where('nik_calon_siswa', $testNik)->first();
        $this->assertNotNull($casis);
        $this->assertEquals('Anak Test Unit', $casis->nama_calon_siswa);
        $this->assertEquals($user->id_user, $casis->id_user);
        $this->assertEquals('Menunggu', $casis->status_pendaftaran);

        // Clean up test records
        $casis->forceDelete();
        $user->delete();
    }
}
