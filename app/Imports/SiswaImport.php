<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;
    public array $errors = [];

    public function __construct(private int $tahunAjaranId) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            $nis       = trim((string) ($row['nis'] ?? ''));
            $nama      = trim((string) ($row['nama'] ?? ''));
            $kelamin   = strtoupper(trim((string) ($row['kelamin'] ?? '')));
            $namaKelas = strtoupper(trim((string) ($row['kelas'] ?? '')));
            $alamat    = trim((string) ($row['alamat'] ?? ''));

            if ($nis === '' && $nama === '') {
                continue;
            }

            if ($nis === '') {
                $this->errors[] = "Baris {$rowNum}: NIS tidak boleh kosong.";
                continue;
            }
            if ($nama === '') {
                $this->errors[] = "Baris {$rowNum}: Nama tidak boleh kosong.";
                continue;
            }
            if (!in_array($kelamin, ['L', 'P'])) {
                $this->errors[] = "Baris {$rowNum}: Kolom Kelamin harus L atau P, ditemukan \"{$kelamin}\".";
                continue;
            }
            if ($namaKelas === '') {
                $this->errors[] = "Baris {$rowNum}: Kelas tidak boleh kosong.";
                continue;
            }

            $kelas = Kelas::where('nama', $namaKelas)
                ->where('tahun_ajaran_id', $this->tahunAjaranId)
                ->first();

            if (!$kelas) {
                $this->errors[] = "Baris {$rowNum}: Kelas \"{$namaKelas}\" tidak ditemukan di tahun ajaran yang dipilih.";
                continue;
            }

            try {
                DB::transaction(function () use ($nis, $nama, $kelamin, $alamat, $kelas) {
                    $siswa = Siswa::where('nis', $nis)->first();

                    if ($siswa) {
                        $siswa->update([
                            'nama'          => $nama,
                            'kelas_id'      => $kelas->id,
                            'jenis_kelamin' => $kelamin,
                            'alamat'        => $alamat !== '' ? $alamat : $siswa->alamat,
                        ]);
                        $this->updated++;
                    } else {
                        $user = User::firstOrCreate(
                            ['username' => $nis],
                            [
                                'name'      => $nama,
                                'email'     => $nis . '@siswa.smppgri7.sch.id',
                                'password'  => Hash::make($nis),
                                'is_active' => true,
                            ]
                        );
                        $user->syncRoles(['siswa']);

                        Siswa::create([
                            'user_id'       => $user->id,
                            'nis'           => $nis,
                            'nama'          => $nama,
                            'kelas_id'      => $kelas->id,
                            'jenis_kelamin' => $kelamin,
                            'alamat'        => $alamat,
                        ]);
                        $this->created++;
                    }
                });
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$rowNum}: Gagal diproses — {$e->getMessage()}.";
            }
        }
    }
}
