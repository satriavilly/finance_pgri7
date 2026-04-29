{{--
  Partial: Tahun Ajaran Select
  Props (via @include):
    $allTahunAjaran  - Collection of TahunAjaran
    $selectedTa      - Currently selected TahunAjaran (nullable)
    $taRoute         - Named route string for form action
    $taExtra         - (optional) extra hidden inputs as assoc array ['name' => 'value']
--}}
<form method="GET" action="{{ route($taRoute) }}" class="inline-flex items-center gap-2">
    @if(!empty($taExtra))
        @foreach($taExtra as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    @endif
    <label class="text-xs text-gray-500 whitespace-nowrap">Tahun Ajaran:</label>
    <select name="ta" onchange="this.form.submit()"
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
        @foreach($allTahunAjaran as $ta)
        <option value="{{ $ta->id }}" {{ $selectedTa?->id == $ta->id ? 'selected' : '' }}>
            {{ $ta->nama }}{{ $ta->is_aktif ? ' ★' : '' }}
        </option>
        @endforeach
    </select>
</form>
