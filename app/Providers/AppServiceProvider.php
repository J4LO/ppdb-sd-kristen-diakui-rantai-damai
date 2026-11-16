<?php

namespace App\Providers;

use App\Models\tahunajar;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     * Share active Tahun Ajaran to all views globally.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $aktivTahunAjaran = tahunajar::where('status', 'Berlangsung')->first()
                ?? tahunajar::where('status', 'Berakhir')->orderBy('updated_at', 'desc')->first();

            $view->with('aktivTahunAjaran', $aktivTahunAjaran);
        });

        View::composer('layout.tampilan', function ($view) {
            $antreanDokumenMenunggu = \App\Models\CalonSiswa::whereNull('deleted_at')
                ->whereHas('dokumen', function ($q) {
                    $q->where('status_verifikasi', 'Menunggu');
                })
                ->count();

            $twentyFourHoursAgo = now('UTC')->subHours(24);
            $pembayaranPerluPerhatian = \Illuminate\Support\Facades\DB::table('tb_pembayaran')
                ->where(function ($q) use ($twentyFourHoursAgo) {
                    $q->where(function ($sub) use ($twentyFourHoursAgo) {
                        $sub->whereIn('status_pembayaran', ['Pending', 'Menunggu'])
                            ->where(\Illuminate\Support\Facades\DB::raw('COALESCE(waktu_dibuat, tgl_pembayaran)'), '<=', $twentyFourHoursAgo);
                    })->orWhereIn('status_pembayaran', ['Gagal', 'Ditolak']);
                })
                ->count();

            $view->with('antreanDokumenMenunggu', $antreanDokumenMenunggu);
            $view->with('pembayaranPerluPerhatian', $pembayaranPerluPerhatian);
        });
    }
}
