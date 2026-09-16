<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Modul lowongan dan persyaratan: lowongan, syarat_keahlian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongan', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('perusahaan_id');
            $t->string('kode', 50);
            $t->string('posisi', 180);
            $t->text('deskripsi');
            $t->string('lokasi', 150)->nullable();
            $t->enum('tipe_pekerjaan', ['penuh_waktu', 'paruh_waktu', 'kontrak', 'magang', 'freelance'])->default('penuh_waktu');
            $t->enum('status', ['draft', 'dipublikasikan', 'ditutup'])->default('draft');
            $t->timestamp('dipublikasikan_pada')->nullable();
            $t->timestamp('ditutup_pada')->nullable();
            $t->timestamp('dibuat_pada')->useCurrent();
            $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $t->unique('kode', 'uq_lowongan_kode');
            $t->foreign('perusahaan_id', 'fk_lowongan_perusahaan')
                ->references('id')->on('perusahaan')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('syarat_keahlian', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('lowongan_id');
            $t->unsignedBigInteger('keahlian_id');
            $t->enum('level_minimum', ['pemula', 'menengah', 'mahir', 'ahli'])->nullable();
            $t->enum('sifat', ['wajib', 'opsional'])->default('wajib');
            $t->decimal('bobot', 5, 2)->default(1.00);
            $t->boolean('wajib_terverifikasi')->default(false);

            $t->unique(['lowongan_id', 'keahlian_id'], 'uq_syarat_lowongan_keahlian');
            $t->foreign('lowongan_id', 'fk_syarat_lowongan')
                ->references('id')->on('lowongan')->cascadeOnUpdate()->cascadeOnDelete();
            $t->foreign('keahlian_id', 'fk_syarat_keahlian')
                ->references('id')->on('keahlian')->cascadeOnUpdate()->restrictOnDelete();
        });

        DB::statement('ALTER TABLE syarat_keahlian ADD CONSTRAINT chk_syarat_bobot CHECK (bobot >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('syarat_keahlian');
        Schema::dropIfExists('lowongan');
    }
};
