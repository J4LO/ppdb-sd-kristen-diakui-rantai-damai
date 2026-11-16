<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminCardRoutesTest extends TestCase
{
    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'Admin')->first();
    }

    public function test_all_admin_card_routes_return_ok(): void
    {
        $routes = [
            '/admin/data/tahun-ajaran',
            '/admin/data/casis',
            '/admin/pendaftaran',
            '/admin/data/pengumuman-seleksi',
            '/admin/verifikasi-daftar-ulang',
        ];

        foreach ($routes as $route) {
            $res = $this->actingAs($this->admin)->get($route);
            $this->assertEquals(200, $res->status(), "Route {$route} failed with status {$res->status()}");
        }
    }
}

