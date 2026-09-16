<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul pengguna dan profil (SIBUKER_PT.sql bagian pertama):
 * pengguna, pencari_kerja, verifikator, perusahaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->string('email', 191);
            $t->string('password', 255);
            $t->string('nomor_telepon', 25)->nullable();
            $t->string('foto_profil', 500)->nullable();
            $t->boolean('is_admin')->default(false);
            $t->enum('status_akun', ['aktif', 'nonaktif', 'ditangguhkan'])->default('aktif');
            $t->timestamp('dibuat_pada')->useCurrent();
            $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $t->unique('email', 'uq_pengguna_email');
        });

        Schema::create('pencari_kerja', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pengguna_id');
            $t->string('headline', 200)->nullable();
            $t->text('ringkasan')->nullable();
            $t->string('lokasi', 150)->nullable();
            $t->date('tanggal_lahir')->nullable();

            $t->unique('pengguna_id', 'uq_pencari_kerja_pengguna');
            $t->foreign('pengguna_id', 'fk_pencari_kerja_pengguna')
                ->references('id')->on('pengguna')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('verifikator', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pengguna_id');
            $t->string('instansi', 200)->nullable();
            $t->string('jabatan', 150)->nullable();
            $t->enum('status_verifikator', ['aktif', 'nonaktif'])->default('aktif');

            $t->unique('pengguna_id', 'uq_verifikator_pengguna');
            $t->foreign('pengguna_id', 'fk_verifikator_pengguna')
                ->references('id')->on('pengguna')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('perusahaan', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pengguna_id');
            $t->string('nama', 200);
            $t->string('nib', 50)->nullable();
            $t->text('deskripsi')->nullable();
            $t->text('alamat')->nullable();
            $t->string('situs_web', 500)->nullable();
            $t->string('logo', 500)->nullable();
            $t->enum('status_perusahaan', ['aktif', 'nonaktif'])->default('aktif');

            $t->unique('pengguna_id', 'uq_perusahaan_pengguna');
            $t->unique('nib', 'uq_perusahaan_nib');
            $t->foreign('pengguna_id', 'fk_perusahaan_pengguna')
                ->references('id')->on('pengguna')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaan');
        Schema::dropIfExists('verifikator');
        Schema::dropIfExists('pencari_kerja');
        Schema::dropIfExists('pengguna');
    }
};
