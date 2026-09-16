<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * View centang biru. Klaim dianggap terverifikasi kalau ada verifikasi TERBARU
 * (id terbesar per bukti) yang disetujui, belum kedaluwarsa, dan menautkan klaim itu
 * lewat bukti_keahlian. Isinya sama persis dengan SIBUKER_PT.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_status_keahlian');
        DB::statement(<<<'SQL'
            CREATE VIEW v_status_keahlian AS
            SELECT
                kk.id AS klaim_keahlian_id,
                kk.pencari_kerja_id,
                kk.keahlian_id,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM bukti_keahlian bk
                        JOIN verifikasi v ON v.id = bk.verifikasi_id
                        WHERE bk.klaim_keahlian_id = kk.id
                          AND v.id = (
                              SELECT MAX(v2.id)
                              FROM verifikasi v2
                              WHERE v2.bukti_id = v.bukti_id
                          )
                          AND v.keputusan = 'disetujui'
                          AND (v.berlaku_sampai IS NULL OR v.berlaku_sampai >= CURRENT_DATE)
                    ) THEN TRUE
                    ELSE FALSE
                END AS terverifikasi
            FROM klaim_keahlian kk
            WHERE kk.aktif = TRUE
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_status_keahlian');
    }
};
