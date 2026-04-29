<?php

namespace App\Services;

use App\Models\Cicilan;
use App\Models\KasKelas;
use App\Models\MutasiKas;
use App\Models\Pembayaran;
use App\Models\TagihanSiswa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PembayaranService
{
    public function bayarTunai(TagihanSiswa $tagihan, array $data, int $userId): Pembayaran
    {
        return DB::transaction(function () use ($tagihan, $data, $userId) {
            $cicilanId = $data['cicilan_id'] ?? null;

            $tanggal = $data['tanggal_bayar'] ?? now()->toDateString();

            $pembayaran = Pembayaran::create([
                'tagihan_siswa_id' => $tagihan->id,
                'cicilan_id' => $cicilanId,
                'nominal' => $data['nominal'],
                'metode' => $data['metode'] ?? 'tunai',
                'status_verifikasi' => 'approved',
                'verified_by' => $userId,
                'verified_at' => $tanggal,
                'catatan' => $data['catatan'] ?? null,
                'created_by' => $userId,
                'created_at' => $tanggal,
            ]);

            $this->updateStatusTagihan($tagihan, $data['nominal'], $cicilanId);
            $this->updateKasKelas($tagihan, $data['nominal']);

            return $pembayaran;
        });
    }

    public function uploadBuktiBayar(TagihanSiswa $tagihan, array $data, UploadedFile $file, int $userId, bool $autoApprove = false): Pembayaran
    {
        return DB::transaction(function () use ($tagihan, $data, $file, $userId, $autoApprove) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bukti_bayar', $filename, config('filesystems.disks.private') ? 'private' : 'local');

            $cicilanId = $data['cicilan_id'] ?? null;

            $tanggal = $data['tanggal_bayar'] ?? now()->toDateString();

            $pembayaran = Pembayaran::create([
                'tagihan_siswa_id'  => $tagihan->id,
                'cicilan_id'        => $cicilanId,
                'nominal'           => $data['nominal'],
                'metode'            => $data['metode'],
                'bukti_bayar_path'  => $path,
                'status_verifikasi' => $autoApprove ? 'approved' : 'pending',
                'verified_by'       => $autoApprove ? $userId : null,
                'verified_at'       => $autoApprove ? $tanggal : null,
                'catatan'           => $data['catatan'] ?? null,
                'created_by'        => $userId,
                'created_at'        => $tanggal,
            ]);

            if ($autoApprove) {
                $this->updateStatusTagihan($tagihan, $data['nominal'], $cicilanId);
                $this->updateKasKelas($tagihan, $data['nominal']);
            }

            return $pembayaran;
        });
    }

    public function approveVerifikasi(Pembayaran $pembayaran, int $userId): void
    {
        DB::transaction(function () use ($pembayaran, $userId) {
            $pembayaran->update([
                'status_verifikasi' => 'approved',
                'verified_by' => $userId,
                'verified_at' => now(),
            ]);

            $tagihan = $pembayaran->tagihanSiswa;
            $this->updateStatusTagihan($tagihan, $pembayaran->nominal, $pembayaran->cicilan_id);
            $this->updateKasKelas($tagihan, $pembayaran->nominal);
        });
    }

    public function rejectVerifikasi(Pembayaran $pembayaran, string $catatan, int $userId): void
    {
        $pembayaran->update([
            'status_verifikasi' => 'rejected',
            'catatan_tolak' => $catatan,
            'verified_by' => $userId,
            'verified_at' => now(),
        ]);
    }

    private function updateStatusTagihan(TagihanSiswa $tagihan, float $nominal, ?int $cicilanId): void
    {
        $tagihan->increment('nominal_terbayar', $nominal);
        $tagihan->refresh();

        if ($cicilanId) {
            Cicilan::where('id', $cicilanId)->update(['status' => 'lunas']);
        }

        if ($tagihan->nominal_terbayar >= $tagihan->nominal_total) {
            $tagihan->update(['status' => 'lunas']);
        } elseif ($tagihan->nominal_terbayar > 0) {
            $tagihan->update(['status' => 'cicilan']);
        }
    }

    private function updateKasKelas(TagihanSiswa $tagihan, float $nominal): void
    {
        $kelas = $tagihan->jenisTagihan->kelas;
        $tahunAjaran = $kelas->tahunAjaran;

        $kas = KasKelas::firstOrCreate(
            ['kelas_id' => $kelas->id, 'tahun_ajaran_id' => $tahunAjaran->id],
            ['saldo' => 0]
        );

        $kas->increment('saldo', $nominal);

        MutasiKas::create([
            'kas_kelas_id' => $kas->id,
            'tipe' => 'masuk',
            'nominal' => $nominal,
            'keterangan' => 'Pembayaran ' . $tagihan->jenisTagihan->nama . ' - ' . $tagihan->siswa->nama,
            'created_by' => auth()->id(),
        ]);
    }

    public function terapkanBeasiswaSiswa(TagihanSiswa $tagihan, int $userId, string $namaBeasiswa = 'Beasiswa / Subsidi Penuh'): Pembayaran
    {
        return DB::transaction(function () use ($tagihan, $userId, $namaBeasiswa) {
            $sisa = $tagihan->sisa_tagihan;

            $pembayaran = Pembayaran::create([
                'tagihan_siswa_id' => $tagihan->id,
                'cicilan_id'       => null,
                'nominal'          => $sisa,
                'metode'           => 'beasiswa',
                'status_verifikasi'=> 'approved',
                'verified_by'      => $userId,
                'verified_at'      => now(),
                'catatan'          => $namaBeasiswa,
                'created_by'       => $userId,
            ]);

            $tagihan->increment('nominal_terbayar', $sisa);
            $tagihan->increment('nominal_subsidi', $sisa);
            $tagihan->update(['status' => 'lunas']);

            return $pembayaran;
        });
    }

    public function voidPembayaran(Pembayaran $pembayaran, string $catatan): void
    {
        DB::transaction(function () use ($pembayaran, $catatan) {
            if ($pembayaran->status_verifikasi === 'approved') {
                $tagihan = $pembayaran->tagihanSiswa;
                $tagihan->decrement('nominal_terbayar', $pembayaran->nominal);

                if ($pembayaran->metode === 'beasiswa') {
                    $tagihan->decrement('nominal_subsidi', $pembayaran->nominal);
                }

                $tagihan->refresh();

                if ($tagihan->nominal_terbayar <= 0) {
                    $tagihan->update(['status' => 'belum_bayar', 'nominal_terbayar' => 0, 'nominal_subsidi' => 0]);
                } else {
                    $tagihan->update(['status' => 'cicilan']);
                }

                if ($pembayaran->cicilan_id) {
                    Cicilan::where('id', $pembayaran->cicilan_id)->update(['status' => 'belum_bayar']);
                }
            }

            $pembayaran->update([
                'is_void'     => true,
                'catatan_void'=> $catatan,
            ]);
        });
    }
}
