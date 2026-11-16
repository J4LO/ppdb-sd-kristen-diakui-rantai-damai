<?php

namespace Tests\Feature;

use App\Models\foto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoLandingPageTest extends TestCase
{
    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first();
    }

    public function test_admin_can_access_landing_page_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('photo.pengaturan'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Foto Landing Page');
        $response->assertSee('Unggah Foto Baru');
        $response->assertSee('Foto Terkini');
    }

    public function test_admin_can_upload_new_photo(): void
    {
        Storage::fake('public');

        $gifContent = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $file = UploadedFile::fake()->createWithContent('test_banner.gif', $gifContent);

        $response = $this->actingAs($this->admin)->post(route('photo.upload'), [
            'photo' => $file,
        ]);

        $response->assertSessionHas('success');

        $uploaded = foto::where('status', 'Mulai')->latest()->first();
        $this->assertNotNull($uploaded);

        // Clean up
        $uploaded->delete();
    }
}
