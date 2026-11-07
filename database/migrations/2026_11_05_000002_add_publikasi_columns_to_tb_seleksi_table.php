<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_seleksi', function (Blueprint $table) {
            $table->dateTime('diumumkan_pada')->nullable()->after('hasil_seleksi');
            $table->integer('diumumkan_oleh')->nullable()->after('diumumkan_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_seleksi', function (Blueprint $table) {
            $table->dropColumn(['diumumkan_pada', 'diumumkan_oleh']);
        });
    }
};

