<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_log_verifikasi_dokumen', function (Blueprint $table) {
            $table->increments('id_log');
            $table->integer('id_dokumen')->nullable()->index();
            $table->integer('id_calon_siswa')->index();
            $table->integer('id_user_admin')->index();
            $table->string('status_lama', 50)->nullable();
            $table->string('status_baru', 50);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('id_dokumen')
                ->references('id_dokumen')
                ->on('tb_dokumen')
                ->onDelete('set null');

            $table->foreign('id_calon_siswa')
                ->references('id_calon_siswa')
                ->on('tb_calon_siswa')
                ->onDelete('cascade');

            $table->foreign('id_user_admin')
                ->references('id_user')
                ->on('tb_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_log_verifikasi_dokumen');
    }
};

