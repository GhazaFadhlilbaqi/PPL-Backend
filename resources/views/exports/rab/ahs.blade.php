<table>
    @if ($company)
        <tr></tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $company->name }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $company->address }}</td>
            <td></td>
        </tr>
        <tr></tr>
        <tr></tr>
    @endif
    <tr></tr>
        <tr>
            <td><b>KEGIATAN</b></td>
            <td><b>{{ $project->activity }}</b></td>
        </tr>
        <tr>
            <td><b>NAMA PEKERJAAN</b></td>
            <td><b>{{ $project->job }}</b></td>
        </tr>
        <tr>
            <td><b>TAHUN ANGGARAN</b></td>
            <td><b>{{ $project->fiscal_year }}</b></td>
        </tr>
</table>
@foreach ($ahs as $a)
<table>
    <thead>
        <tr>
            <td><b>{{ $a->code }}</b></td>
            <td><b>{{ $a->name }}</b></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Uraian</th>
            <th>Kode</th>
            <th>Satuan</th>
            <th>Koefisien</th>
            <th>Harga Satuan (Rp.)</th>
            <th>Jumlah (Rp.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>A</b></td>
            <td><b>TENAGA KERJA</b></td>
        </tr>
        @php $laborAhsSum = 0 @endphp
        @foreach ($a['item_arranged']['labor'] ?? [] as $laborAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($laborAhs) }}</td>
                <td>{{ $laborAhs->customAhsItemable->code }}</td>
                <td>{{ $laborAhs->customAhsItemable ? ($laborAhs->custom_ahs_itemable_type == 'App\\Models\\CustomAhs' ? $laborAhs->unit->name : $laborAhs->customAhsItemable->unit->name) : $laborAhs->unit->name }}</td>
                <td>{{ $laborAhs->coefficient }}</td>
                {{-- <td>{{ $laborAhs->customAhsItemable->subtotal }}</td> --}}
                <td>{{ $laborAhs->customAhsItemable->price }}</td>
                <td>{{ $laborAhs->customAhsItemable->price * $laborAhs->coefficient }}</td>
                {{-- <td>{{ $laborAhs->customAhsItemable->subtotal * $laborAhs->coefficient }}</td> --}}
            </tr>
            @php $laborAhsSum += $laborAhs->subtotal @endphp
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH TENAGA KERJA (A)</b></td>
            <td><b>{{ $laborAhsSum }}</b></td>
        </tr>
        <tr>
            <td><b>B</b></td>
            <td><b>BAHAN</b></td>
        </tr>
        @php $ingredientsAhsSum = 0 @endphp
        @foreach ($a['item_arranged']['ingredients'] ?? [] as $ingredientsAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($ingredientsAhs) }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->code }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->unit->name }}</td>
                <td>{{ $ingredientsAhs->coefficient }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->price }}</td>
                {{-- <td>{{ $ingredientsAhs->customAhsItemable->subtotal }}</td> --}}
                <td>{{ $ingredientsAhs->customAhsItemable->price * $ingredientsAhs->coefficient }}</td>
                {{-- <td>{{ $ingredientsAhs->customAhsItemable->subtotal * $ingredientsAhs->coefficient }}</td> --}}
            </tr>
            @php $laborAhsSum += $ingredientsAhs->subtotal @endphp
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH BAHAN (B)</b></td>
            <td><b>{{ $ingredientsAhsSum }}</b></td>
        </tr>
        <tr>
            <td><b>C</b></td>
            <td><b>PERALATAN</b></td>
        </tr>
        @php $toolsAhsSum = 0 @endphp
        @foreach ($a['item_arranged']['tools'] ?? [] as $toolsAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($toolsAhs) }}</td>
                <td>{{ $toolsAhs->customAhsItemable->code }}</td>
                <td>{{ $toolsAhs->custom_ahs_itemable_type == 'App\\Models\\CustomAhp' ? $toolsAhs->unit->name : ($toolsAhs->customAhsItemable->unit ? $toolsAhs->customAhsItemable->unit->name : 'Tidak ada satuan') }}</td>
                <td>{{ $toolsAhs->customAhsItemable->unit ? $toolsAhs->customAhsItemable->unit->name : 'Tidak ada satuan' }}</td>
                <td>{{ $toolsAhs->coefficient }}</td>
                {{-- <td>{{ $toolsAhs->customAhsItemable->subtotal }}</td> --}}
                <td>{{ $toolsAhs->customAhsItemable->price }}</td>
                <td>{{ $toolsAhs->customAhsItemable->price * $toolsAhs->coefficient }}</td>
                {{-- <td>{{ $toolsAhs->customAhsItemable->subtotal * $toolsAhs->coefficient }}</td> --}}
            </tr>
            @php $toolsAhsSum += $toolsAhs->subtotal @endphp
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH PERALATAN (C)</b></td>
            <td><b>{{ $toolsAhsSum }}</b></td>
        </tr>
        <tr>
            <td><b>D</b></td>
            <td><b>LAIN - LAIN</b></td>
        </tr>
        @php $othersAhsSum = 0 @endphp
        @foreach ($a['item_arranged']['others'] ?? [] as $othersAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($othersAhs) }}</td>
                <td>{{ $othersAhs->customAhsItemable->code }}</td>
                <td>{{ $othersAhs->customAhsItemable->unit->name }}</td>
                <td>{{ $othersAhs->coefficient }}</td>
                {{-- <td>{{ $othersAhs->customAhsItemable->subtotal }}</td> --}}
                <td>{{ $othersAhs->customAhsItemable->price }}</td>
                <td>{{ $othersAhs->customAhsItemable->price * $othersAhs->coefficient }}</td>
                {{-- <td>{{ $othersAhs->customAhsItemable->subtotal * $othersAhs->coefficient }}</td> --}}
            </tr>
            @php $othersAhsSum += $othersAhs->subtotal @endphp
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH LAIN - LAIN (C)</b></td>
            <td><b>{{ $othersAhsSum }}</b></td>
        </tr>
        <tr>
            <td><b>E</b></td>
            <td><b>JUMLAH HARGA TENAGA, BAHAN, PERALATAN, DAN LAIN LAIN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(A+B+C+D)</b></td>
            <td><b>{{ $a->subtotal }}</b></td>
        </tr>
        <tr>
            <td><b>F</b></td>
            <td><b>OVERHEAD DAN PROFIT</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>x E</b></td>
            @php $profitMarginAndOverhead = ($project->profit_margin / 100) * $a->subtotal @endphp
            <td><b>{{ $profitMarginAndOverhead }}</b></td>
        </tr>
        <tr>
            <td><b>G</b></td>
            <td><b>HARGA SATUAN PEKERJAAN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(E+F)</b></td>
            <td><b>{{ $a->subtotal + $profitMarginAndOverhead }}</b></td>
        </tr>
    </tbody>
</table>
@endforeach
<table>
    <tr></tr>
    <tr></tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $project->province->name }}, {{ date('d-m-Y') }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>Dibuat Oleh</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $company->name }}</td>
    </tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr></tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $company->director_name }}</td>
    </tr>
</table>
