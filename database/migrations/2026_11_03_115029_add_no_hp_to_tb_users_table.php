<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_users', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_users', 'no_hp')) {
                $table->string('no_hp', 20)->nullable()->after('email');
            }
        });

        // Backfill data nomor HP dari tb_calon_siswa.no_hp_orang_tua
        // Mengambil record terbaru per id_user (berdasarkan id_calon_siswa desc)
        if (Schema::hasTable('tb_calon_siswa')) {
            $students = DB::table('tb_calon_siswa')
                ->whereNotNull('no_hp_orang_tua')
                ->where('no_hp_orang_tua', '!=', '')
                ->orderBy('id_calon_siswa', 'desc')
                ->get(['id_user', 'no_hp_orang_tua']);

            foreach ($students as $student) {
                if (!empty($student->id_user) && !empty($student->no_hp_orang_tua)) {
                    DB::table('tb_users')
                        ->where('id_user', $student->id_user)
                        ->whereNull('no_hp')
                        ->update(['no_hp' => trim($student->no_hp_orang_tua)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_users', function (Blueprint $table) {
            if (Schema::hasColumn('tb_users', 'no_hp')) {
                $table->dropColumn('no_hp');
            }
        });
    }
};
