<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: #fff; }

    .page { padding: 32px 36px; }

    /* Header */
    .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 14px; margin-bottom: 18px; }
    .school-name { font-size: 16px; font-weight: bold; color: #1d4ed8; }
    .report-title { font-size: 13px; font-weight: bold; color: #374151; margin-top: 2px; }
    .report-meta { font-size: 10px; color: #6b7280; margin-top: 3px; }

    /* Info siswa */
    .info-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; }
    .info-grid { width: 100%; }
    .info-grid td { padding: 2px 0; font-size: 11px; }
    .info-label { color: #6b7280; width: 110px; }
    .info-value { color: #111827; font-weight: bold; }

    /* Summary */
    .summary { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .summary td { width: 33.33%; text-align: center; padding: 8px 6px; border: 1px solid #e5e7eb; }
    .summary .s-val { font-size: 14px; font-weight: bold; }
    .summary .s-lbl { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .red   { color: #dc2626; }
    .green { color: #16a34a; }
    .gray  { color: #374151; }

    /* Year group */
    .year-header { background: #f3f4f6; border: 1px solid #d1d5db; border-bottom: none;
                   padding: 6px 10px; font-size: 11px; font-weight: bold; color: #374151;
                   border-radius: 4px 4px 0 0; }
    .badge-aktif { background: #dcfce7; color: #15803d; font-size: 9px; font-weight: bold;
                   padding: 1px 6px; border-radius: 999px; margin-left: 6px; }

    /* Table */
    .tagihan-table { width: 100%; border-collapse: collapse; margin-bottom: 18px;
                     border: 1px solid #d1d5db; border-top: none; font-size: 10px; }
    .tagihan-table th { background: #f9fafb; padding: 6px 8px; text-align: left;
                        border-bottom: 1px solid #d1d5db; color: #6b7280; font-weight: 600; font-size: 9.5px; }
    .tagihan-table th.right { text-align: right; }
    .tagihan-table th.center { text-align: center; }
    .tagihan-table td { padding: 7px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .tagihan-table tr:last-child td { border-bottom: none; }
    .tagihan-table td.right { text-align: right; }
    .tagihan-table td.center { text-align: center; }

    /* Kategori badges */
    .badge { display: inline-block; padding: 1px 6px; border-radius: 999px; font-size: 9px; font-weight: 600; }
    .badge-spp       { background: #e0e7ff; color: #4338ca; }
    .badge-kas_kelas { background: #dbeafe; color: #1d4ed8; }
    .badge-buku_lks  { background: #f3e8ff; color: #7e22ce; }
    .badge-kegiatan  { background: #ffedd5; color: #c2410c; }
    .badge-seragam   { background: #ccfbf1; color: #0f766e; }
    .badge-lainnya   { background: #f3f4f6; color: #4b5563; }

    /* Status */
    .status { display: inline-block; padding: 2px 7px; border-radius: 999px; font-size: 9px; font-weight: 600; }
    .status-lunas    { background: #dcfce7; color: #15803d; }
    .status-cicilan  { background: #fef9c3; color: #92400e; }
    .status-belum    { background: #fee2e2; color: #b91c1c; }

    /* Cap Lunas */
    .stamp-wrap { position: relative; }
    .lunas-stamp {
        display: inline-block;
        border: 2px solid #16a34a;
        color: #16a34a;
        font-size: 9px;
        font-weight: bold;
        padding: 1px 5px;
        border-radius: 3px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-left: 4px;
        vertical-align: middle;
    }

    /* Row lunas highlight */
    .row-lunas td { background: #f0fdf4; }

    /* Tanggal lunas */
    .tgl-lunas { color: #15803d; font-size: 9.5px; }

    /* Footer */
    .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 10px;
              font-size: 9px; color: #9ca3af; text-align: center; }
    .sign-area { margin-top: 20px; width: 100%; }
    .sign-area td { width: 50%; padding: 4px 10px; vertical-align: top; font-size: 10px; }
    .sign-box { border-bottom: 1px solid #374151; width: 140px; height: 55px; margin-top: 8px; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="school-name">SMP PGRI 7 Bandung</div>
        <div class="report-title">Laporan Tagihan Siswa</div>
        <div class="report-meta">Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    {{-- Info Siswa --}}
    <div class="info-box">
        <table class="info-grid">
            <tr>
                <td class="info-label">Nama Siswa</td>
                <td class="info-value">: {{ $siswa->nama }}</td>
                <td class="info-label">Kelas</td>
                <td class="info-value">: {{ $siswa->kelas?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">NIS</td>
                <td class="info-value">: {{ $siswa->nis }}</td>
                <td class="info-label">Tahun Ajaran</td>
                <td class="info-value">: {{ $siswa->kelas?->tahunAjaran?->nama ?? '-' }}
                    @if($siswa->kelas?->tahunAjaran?->is_aktif)
                    <span style="color:#15803d;">(Aktif)</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @php
        $semuaTagihan = $siswa->tagihanSiswa;
        $totalNominal = $semuaTagihan->sum('nominal_total');
        $totalBayar   = $semuaTagihan->sum('nominal_terbayar');
        $totalSisa    = $semuaTagihan->whereIn('status', ['belum_bayar','cicilan'])->sum(fn($t) => $t->nominal_total - $t->nominal_terbayar);

        $kategoriLabel = \App\Models\JenisTagihan::kategoriLabel();

        $byTahunAjaran = $semuaTagihan
            ->groupBy(fn($t) => $t->jenisTagihan->kelas?->tahunAjaran?->id ?? 0)
            ->map(fn($group) => [
                'tahunAjaran' => $group->first()->jenisTagihan->kelas?->tahunAjaran,
                'tagihan'     => $group,
            ])
            ->sortByDesc(fn($g) => $g['tahunAjaran']?->tanggal_mulai ?? '');
    @endphp

    {{-- Ringkasan --}}
    <table class="summary">
        <tr>
            <td>
                <div class="s-val gray">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                <div class="s-lbl">Total Tagihan</div>
            </td>
            <td>
                <div class="s-val green">Rp {{ number_format($totalBayar, 0, ',', '.') }}</div>
                <div class="s-lbl">Sudah Terbayar</div>
            </td>
            <td>
                <div class="s-val red">Rp {{ number_format($totalSisa, 0, ',', '.') }}</div>
                <div class="s-lbl">Sisa Tunggakan</div>
            </td>
        </tr>
    </table>

    {{-- Tagihan per Tahun Ajaran --}}
    @foreach($byTahunAjaran as $group)
    @php
        $ta  = $group['tahunAjaran'];
        $isAktif = $ta?->is_aktif;
    @endphp

    <div class="year-header">
        Tahun Ajaran: {{ $ta?->nama ?? 'Tidak Diketahui' }}
        @if($isAktif)<span class="badge-aktif">AKTIF</span>@endif
    </div>

    <table class="tagihan-table">
        <thead>
            <tr>
                <th style="width:28%">Nama Tagihan</th>
                <th style="width:13%">Kategori</th>
                <th class="right" style="width:14%">Total</th>
                <th class="right" style="width:14%">Terbayar</th>
                <th class="right" style="width:13%">Sisa</th>
                <th style="width:11%">Tgl. Bayar</th>
                <th class="center" style="width:7%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group['tagihan'] as $t)
            @php
                $kat     = $t->jenisTagihan->kategori;
                $lastPay = $t->pembayaran->first();
                $tglBayar = $lastPay ? \Carbon\Carbon::parse($lastPay->tanggal_bayar ?? $lastPay->created_at) : null;
                $isLunas = $t->status === 'lunas';
            @endphp
            <tr class="{{ $isLunas ? 'row-lunas' : '' }}">
                <td>
                    <strong>{{ $t->jenisTagihan->nama }}</strong>
                    @if($isLunas)
                    <span class="lunas-stamp">LUNAS</span>
                    @endif
                    @if($t->jenisTagihan->is_cicilan)
                    <br><span style="color:#6b7280;font-size:9px;">{{ $t->jenisTagihan->jumlah_cicilan }}x cicilan</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $kat }}">{{ $kategoriLabel[$kat] ?? $kat }}</span>
                </td>
                <td class="right">Rp {{ number_format($t->nominal_total, 0, ',', '.') }}</td>
                <td class="right" style="color:#15803d;">Rp {{ number_format($t->nominal_terbayar, 0, ',', '.') }}</td>
                <td class="right" style="{{ $t->sisa_tagihan > 0 ? 'color:#dc2626;' : 'color:#9ca3af;' }}">
                    Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                </td>
                <td>
                    @if($isLunas && $tglBayar)
                        <span class="tgl-lunas">{{ $tglBayar->format('d M Y') }}</span>
                    @else
                        <span style="color:#6b7280;">{{ $t->due_date?->format('d M Y') ?? '-' }}</span>
                    @endif
                </td>
                <td class="center">
                    @if($isLunas)
                        <span class="status status-lunas">Lunas</span>
                    @elseif($t->status === 'cicilan')
                        <span class="status status-cicilan">Cicilan</span>
                    @else
                        <span class="status status-belum">Belum</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    {{-- Tanda Tangan --}}
    <table class="sign-area">
        <tr>
            <td></td>
            <td style="text-align:right;">
                <div>Bandung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div style="color:#6b7280;font-size:9px;margin-top:2px;">Wali Kelas / Tata Usaha</div>
                <div class="sign-box"></div>
                <div style="margin-top:4px;">( _________________________ )</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini dicetak secara otomatis oleh Sistem Informasi Keuangan Siswa (SiKas) &mdash; SMP PGRI 7 Bandung
    </div>
</div>
</body>
</html>
