<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\JenisTagihan;
use App\Models\Kelas;
use App\Models\TagihanSiswa;
use App\Services\CicilanService;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SppController extends Controller
{
    public function __construct(
        private CicilanService $cicilanService,
        private PembayaranService $pembayaranService,
    ) {}

    public function index(): View
    {
        // Group SPP jenis_tagihan by periode (deskripsi = YYYY-MM)
        $periodeList = JenisTagihan::where('kategori', 'spp')
            ->whereNull('deleted_at')
            ->whereHas('kelas.tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->select('deskripsi', DB::raw('count(*) as jumlah_kelas'), DB::raw('min(nama) as nama'), DB::raw('min(due_date) as due_date'), DB::raw('min(total_nominal) as nominal'))
            ->groupBy('deskripsi')
            ->orderByDesc('deskripsi')
            ->get()
            ->map(function ($row) {
                $tagihanQuery = TagihanSiswa::whereHas('jenisTagihan', fn($q) =>
                    $q->where('kategori', 'spp')->where('deskripsi', $row->deskripsi)->whereNull('deleted_at')
                );
                $row->total_siswa  = (clone $tagihanQuery)->count();
                $row->lunas        = (clone $tagihanQuery)->where('status', 'lunas')->count();
                $row->terkumpul    = (clone $tagihanQuery)->sum('nominal_terbayar');
                $row->total_nominal_all = (clone $tagihanQuery)->sum('nominal_total');
                return $row;
            });

        return view('bendahara.spp.index', compact('periodeList'));
    }

    public function create(): View
    {
        $perAngkatan = Kelas::withCount('siswa')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->orderBy('tingkat')->orderBy('nama')
            ->get()
            ->groupBy('tingkat');

        $bulanOptions = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];

        return view('bendahara.spp.create', compact('perAngkatan', 'bulanOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bulan'       => ['required', 'integer', 'between:1,12'],
            'tahun'       => ['required', 'integer', 'min:2020', 'max:2099'],
            'nominal'     => ['required', 'array'],
            'nominal.*'   => ['required', 'numeric', 'min:1000'],
            'due_date'    => ['nullable', 'date'],
            'kelas_ids'   => ['required', 'array', 'min:1'],
            'kelas_ids.*' => ['exists:kelas,id'],
        ], [
            'kelas_ids.required' => 'Pilih minimal satu kelas.',
            'nominal.*.required' => 'Tarif SPP wajib diisi untuk setiap angkatan yang dipilih.',
            'nominal.*.min'      => 'Tarif SPP minimal Rp 1.000.',
        ]);

        $bulanNama = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ][$request->bulan];

        $periode = sprintf('%04d-%02d', $request->tahun, $request->bulan);
        $namaSpp = "SPP {$bulanNama} {$request->tahun}";

        // Map kelas_id => tingkat for nominal lookup
        $kelasMap = Kelas::whereIn('id', $request->kelas_ids)->pluck('tingkat', 'id');

        $jumlahSiswa = 0;

        DB::transaction(function () use ($request, $periode, $namaSpp, $kelasMap, &$jumlahSiswa) {
            foreach ($request->kelas_ids as $kelasId) {
                $tingkat = $kelasMap[$kelasId] ?? null;
                $nominal = $request->input("nominal.$tingkat");

                if (!$nominal) continue;

                $exists = JenisTagihan::where('kategori', 'spp')
                    ->where('kelas_id', $kelasId)
                    ->where('deskripsi', $periode)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) continue;

                $jenisTagihan = JenisTagihan::create([
                    'nama'           => $namaSpp,
                    'deskripsi'      => $periode,
                    'kategori'       => 'spp',
                    'kelas_id'       => $kelasId,
                    'total_nominal'  => $nominal,
                    'is_cicilan'     => false,
                    'jumlah_cicilan' => 1,
                    'due_date'       => $request->due_date ?: null,
                    'is_aktif'       => true,
                    'created_by'     => auth()->id(),
                ]);

                $before = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
                $this->cicilanService->buatTagihanUntukKelas($jenisTagihan);
                $after  = \App\Models\TagihanSiswa::where('jenis_tagihan_id', $jenisTagihan->id)->count();
                $jumlahSiswa += $after - $before;
            }
        });

        return redirect()->route('bendahara.spp.show', $periode)
            ->with('success', "SPP {$bulanNama} {$request->tahun} berhasil dibuat. {$jumlahSiswa} tagihan didistribusikan ke siswa.");
    }

    public function show(string $periode): View
    {
        // Validate periode format YYYY-MM
        abort_unless(preg_match('/^\d{4}-\d{2}$/', $periode), 404);

        $jenisTagihanList = JenisTagihan::where('kategori', 'spp')
            ->where('deskripsi', $periode)
            ->whereNull('deleted_at')
            ->whereHas('kelas.tahunAjaran', fn($q) => $q->where('is_aktif', true))
            ->with('kelas')
            ->get();

        abort_if($jenisTagihanList->isEmpty(), 404);

        $namaSpp = $jenisTagihanList->first()->nama;
        $dueDate = $jenisTagihanList->first()->due_date;

        // Load tagihan per kelas
        $perKelas = $jenisTagihanList->map(function ($jt) {
            $tagihan = TagihanSiswa::where('jenis_tagihan_id', $jt->id)
                ->with(['siswa.user', 'pembayaran' => fn($q) => $q->where('is_void', false)->latest()])
                ->orderBy(
                    \App\Models\Siswa::select('nama')->whereColumn('siswa.id', 'tagihan_siswa.siswa_id')
                )
                ->get();

            return [
                'kelas'    => $jt->kelas,
                'tagihan'  => $tagihan,
                'nominal'  => $jt->total_nominal,
                'lunas'    => $tagihan->where('status', 'lunas')->count(),
                'belum'    => $tagihan->whereIn('status', ['belum_bayar', 'cicilan'])->count(),
                'terkumpul'=> $tagihan->sum('nominal_terbayar'),
            ];
        })->sortBy('kelas.nama');

        // Summary global
        $allTagihan = $perKelas->flatMap(fn($k) => $k['tagihan']);
        $summary = [
            'total'      => $allTagihan->count(),
            'lunas'      => $allTagihan->where('status', 'lunas')->count(),
            'terkumpul'  => $allTagihan->sum('nominal_terbayar'),
            'total_nominal' => $allTagihan->sum('nominal_total'),
        ];

        return view('bendahara.spp.show', compact('periode', 'namaSpp', 'dueDate', 'perKelas', 'summary'));
    }

    public function bayar(Request $request, TagihanSiswa $tagihanSiswa): RedirectResponse
    {
        $request->validate([
            'nominal'       => ['required', 'numeric', 'min:1000', 'max:' . $tagihanSiswa->sisa_tagihan],
            'metode'        => ['required', 'in:tunai,transfer,qris'],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'catatan'       => ['nullable', 'string', 'max:300'],
        ]);

        $this->pembayaranService->bayarTunai($tagihanSiswa, [
            'nominal'       => $request->nominal,
            'metode'        => $request->metode,
            'tanggal_bayar' => $request->tanggal_bayar,
            'catatan'       => $request->catatan,
        ], auth()->id());

        $periode = $tagihanSiswa->jenisTagihan->deskripsi;

        return redirect()->route('bendahara.spp.show', $periode)
            ->with('success', 'Pembayaran SPP atas nama '.$tagihanSiswa->siswa->nama.' berhasil dicatat.');
    }

    public function distribusiUlang(string $periode): RedirectResponse
    {
        abort_unless(preg_match('/^\d{4}-\d{2}$/', $periode), 404);

        $jenisTagihanList = JenisTagihan::where('kategori', 'spp')
            ->where('deskripsi', $periode)
            ->whereNull('deleted_at')
            ->get();

        $jumlahBaru = 0;
        foreach ($jenisTagihanList as $jt) {
            $before = TagihanSiswa::where('jenis_tagihan_id', $jt->id)->count();
            $this->cicilanService->buatTagihanUntukKelas($jt);
            $jumlahBaru += TagihanSiswa::where('jenis_tagihan_id', $jt->id)->count() - $before;
        }

        $pesan = $jumlahBaru > 0
            ? "{$jumlahBaru} tagihan baru berhasil didistribusikan ke siswa baru."
            : 'Semua siswa sudah memiliki tagihan SPP ini.';

        return redirect()->route('bendahara.spp.show', $periode)->with('success', $pesan);
    }
}
