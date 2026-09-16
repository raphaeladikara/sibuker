<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Modul lamaran dan seleksi: lamaran, tahap_seleksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamaran', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('lowongan_id');
            $t->unsignedBigInteger('pencari_kerja_id');
            $t->enum('status', ['dikirim', 'ditinjau', 'diproses', 'diterima', 'ditolak', 'ditarik'])->default('dikirim');
            $t->decimal('skor_kecocokan', 5, 2)->nullable();
            $t->timestamp('dilamar_pada')->useCurrent();
            $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $t->unique(['lowongan_id', 'pencari_kerja_id'], 'uq_lamaran_lowongan_pencari');
            $t->foreign('lowongan_id', 'fk_lamaran_lowongan')
                ->references('id')->on('lowongan')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('pencari_kerja_id', 'fk_lamaran_pencari_kerja')
                ->references('id')->on('pencari_kerja')->cascadeOnUpdate()->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE lamaran ADD CONSTRAINT chk_lamaran_skor CHECK (skor_kecocokan IS NULL OR skor_kecocokan BETWEEN 0 AND 100)');

        Schema::create('tahap_seleksi', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('lamaran_id');
            $t->string('nama_tahap', 120);
            $t->unsignedSmallInteger('urutan');
            $t->enum('status', ['menunggu', 'berlangsung', 'lulus', 'tidak_lulus'])->default('menunggu');
            $t->dateTime('jadwal')->nullable();
            $t->text('catatan')->nullable();
            $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $t->unique(['lamaran_id', 'urutan'], 'uq_tahap_lamaran_urutan');
            $t->foreign('lamaran_id', 'fk_tahap_lamaran')
                ->references('id')->on('lamaran')->cascadeOnUpdate()->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE tahap_seleksi ADD CONSTRAINT chk_tahap_urutan CHECK (urutan > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tahap_seleksi');
        Schema::dropIfExists('lamaran');
    }
};
