<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiswaImportExportController extends Controller
{
    public function index(): View
    {
        $tahunAjaranList = TahunAjaran::orderByDesc('tanggal_mulai')->get();
        return view('admin.siswa-import.index', compact('tahunAjaranList'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ], [
            'tahun_ajaran_id.required' => 'Pilih tahun ajaran terlebih dahulu.',
        ]);

        $ta = TahunAjaran::findOrFail($request->tahun_ajaran_id);
        $filename = 'siswa-' . Str::slug($ta->nama) . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new SiswaExport((int) $request->tahun_ajaran_id), $filename);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new SiswaTemplateExport(), 'template-import-siswa.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'file'            => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'tahun_ajaran_id.required' => 'Pilih tahun ajaran terlebih dahulu.',
            'file.required'            => 'File Excel wajib diunggah.',
            'file.mimes'               => 'File harus berformat .xlsx atau .xls.',
            'file.max'                 => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new SiswaImport((int) $request->tahun_ajaran_id);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->with('error', 'File tidak valid: ' . $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }

        session()->flash('import_result', [
            'created' => $import->created,
            'updated' => $import->updated,
            'errors'  => $import->errors,
        ]);

        return redirect()->route('admin.siswa-import.index');
    }
}
