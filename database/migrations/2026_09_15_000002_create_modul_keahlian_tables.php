<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul keahlian dan verifikasi:
 * keahlian, jenis_bukti, kewenangan_verifikator, klaim_keahlian, bukti, verifikasi, bukti_keahlian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keahlian', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 120);
            $t->string('kategori', 120)->nullable();
            $t->text('deskripsi')->nullable();
            $t->boolean('aktif')->default(true);

            $t->unique('nama', 'uq_keahlian_nama');
        });

        Schema::create('jenis_bukti', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 100);

            $t->unique('nama', 'uq_jenis_bukti_nama');
        });

        Schema::create('kewenangan_verifikator', function (Blueprint $t) {
            $t->unsignedBigInteger('verifikator_id');
            $t->unsignedBigInteger('keahlian_id');
            $t->timestamp('diberikan_pada')->useCurrent();

            $t->primary(['verifikator_id', 'keahlian_id']);
            $t->foreign('verifikator_id', 'fk_kewenangan_verifikator')
                ->references('id')->on('verifikator')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('keahlian_id', 'fk_kewenangan_keahlian')
                ->references('id')->on('keahlian')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('klaim_keahlian', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pencari_kerja_id');
            $t->unsignedBigInteger('keahlian_id');
            $t->enum('level_klaim', ['pemula', 'menengah', 'mahir', 'ahli'])->nullable();
            $t->timestamp('tanggal_ditambahkan')->useCurrent();
            $t->boolean('aktif')->default(true);

            $t->unique(['pencari_kerja_id', 'keahlian_id'], 'uq_klaim_pencari_keahlian');
            $t->foreign('pencari_kerja_id', 'fk_klaim_pencari_kerja')
                ->references('id')->on('pencari_kerja')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('keahlian_id', 'fk_klaim_keahlian')
                ->references('id')->on('keahlian')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('bukti', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pengguna_id');
            $t->unsignedBigInteger('jenis_bukti_id');
            $t->string('judul', 200);
            $t->string('penerbit', 200)->nullable();
            $t->string('url_berkas', 500);
            $t->date('tanggal_terbit')->nullable();
            $t->timestamp('diunggah_pada')->useCurrent();

            $t->foreign('pengguna_id', 'fk_bukti_pengguna')
                ->references('id')->on('pengguna')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('jenis_bukti_id', 'fk_bukti_jenis')
                ->references('id')->on('jenis_bukti')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('verifikasi', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('bukti_id');
            $t->unsignedBigInteger('verifikator_id');
            $t->enum('keputusan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $t->text('catatan')->nullable();
            $t->timestamp('diverifikasi_pada')->nullable();
            $t->date('berlaku_sampai')->nullable();
            $t->timestamp('dibuat_pada')->useCurrent();

            $t->index(['bukti_id', 'id'], 'idx_verifikasi_bukti_terbaru');
            $t->index('verifikator_id', 'idx_verifikasi_verifikator');
            $t->foreign('bukti_id', 'fk_verifikasi_bukti')
                ->references('id')->on('bukti')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('verifikator_id', 'fk_verifikasi_verifikator')
                ->references('id')->on('verifikator')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('bukti_keahlian', function (Blueprint $t) {
            $t->unsignedBigInteger('verifikasi_id');
            $t->unsignedBigInteger('klaim_keahlian_id');

            $t->primary(['verifikasi_id', 'klaim_keahlian_id']);
            $t->index('klaim_keahlian_id', 'idx_bukti_keahlian_klaim');
            $t->foreign('verifikasi_id', 'fk_bukti_keahlian_verifikasi')
                ->references('id')->on('verifikasi')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('klaim_keahlian_id', 'fk_bukti_keahlian_klaim')
                ->references('id')->on('klaim_keahlian')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_keahlian');
        Schema::dropIfExists('verifikasi');
        Schema::dropIfExists('bukti');
        Schema::dropIfExists('klaim_keahlian');
        Schema::dropIfExists('kewenangan_verifikator');
        Schema::dropIfExists('jenis_bukti');
        Schema::dropIfExists('keahlian');
    }
};
