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
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><b>TAHUN ANGGARAN: {{ $project->fiscal_year }}</b></td>  
        </tr>
        <tr></tr>
</table>
@foreach ($ahs as $a)
<table>
    <thead>
        <tr>
            <td><b>{{ $a->code }}</b></td>
            <td><b>{{ $a->name }}</b></td>
        </tr>
        <tr>
            <th><b>No</b></th>
            <th><b>Uraian</b></th>
            <th><b>Kode</b></th>
            <th><b>Satuan</b></th>
            <th><b>Koefisien</b></th>
            <th><b>Harga Satuan (Rp.)</b></th>
            <th><b>Jumlah (Rp.)</b></th>
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
                <td style="text-align: right;">="Rp{{ $laborAhs->customAhsItemable->price == 0 ? '-' : number_format($laborAhs->customAhsItemable->price, 2, ',', '.') }}"</td>
                <td style="text-align: right;">="Rp{{ ($laborAhs->customAhsItemable->price * $laborAhs->coefficient) == 0 ? '-' : number_format($laborAhs->customAhsItemable->price * $laborAhs->coefficient, 2, ',', '.') }}"</td>
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
            <td style="text-align: right;"><b>="Rp{{ $laborAhsSum == 0 ? '0' : number_format($laborAhsSum, 2, ',', '.') }}"</b></td>
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
                <td style="text-align: right;">="Rp{{ $ingredientsAhs->customAhsItemable->price == 0 ? '-' : number_format($ingredientsAhs->customAhsItemable->price, 2, ',', '.') }}"</td>
                {{-- <td>{{ $ingredientsAhs->customAhsItemable->subtotal }}</td> --}}
                <td style="text-align: right;">="Rp{{ ($ingredientsAhs->customAhsItemable->price * $ingredientsAhs->coefficient) == 0 ? '-' : number_format($ingredientsAhs->customAhsItemable->price * $ingredientsAhs->coefficient, 2 , ',', '.') }}"</td>
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
            <td style="text-align: right;"><b>="Rp{{ $ingredientsAhsSum == 0 ? '-' : number_format($ingredientsAhsSum, 2, ',', '.') }}"</b></td>
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
                <td style="text-align: right;">="Rp{{ $toolsAhs->customAhsItemable->price == 0 ? '-' : number_format($toolsAhs->customAhsItemable->price, 2, ',', '.') }}"</td>
                <td style="text-align: right;">="Rp{{ $toolsAhs->customAhsItemable->price * $toolsAhs->coefficient == 0 ? '-' : number_format($toolsAhs->customAhsItemable->price * $toolsAhs->coefficient, 2, ',', '.') }}"</td>
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
            <td style="text-align: right;"><b>="Rp{{ $toolsAhsSum == 0 ? '-' : number_format($toolsAhsSum, 2, ',', '.') }}"</b></td>
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
                <td style="text-align: right;">="Rp{{ $othersAhs->customAhsItemable->price == 0 ? '-' : number_format($othersAhs->customAhsItemable->price, 2, ',', '.') }}"</td>
                <td style="text-align: right;">="Rp{{ $othersAhs->customAhsItemable->price * $othersAhs->coefficient == 0 ? '-' : number_format($othersAhs->customAhsItemable->price * $othersAhs->coefficient, 2, ',', '.') }}"</td>
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
            <td style="text-align: right;"><b>="Rp{{ $othersAhsSum == 0 ? '-' : number_format($othersAhsSum, 2, ',', '.') }}"</b></td>
        </tr>
        <tr>
            <td><b>E</b></td>
            <td><b>JUMLAH HARGA TENAGA, BAHAN, PERALATAN, DAN LAIN LAIN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(A+B+C+D)</b></td>
            <td style="text-align: right;"><b>="Rp{{ $a->subtotal == 0 ? '-' : number_format($a->subtotal, 2, ',', '.') }}"</b></td>
        </tr>
        <tr>
            <td><b>F</b></td>
            <td><b>OVERHEAD DAN PROFIT</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>x E</b></td>
            @php $profitMarginAndOverhead = ($project->profit_margin / 100) * $a->subtotal @endphp
            <td style="text-align: right;"><b>="Rp{{ $profitMarginAndOverhead == 0 ? '-' : number_format($profitMarginAndOverhead, 2, ',', '.') }}"</b></td>
        </tr>
        <tr>
            <td><b>G</b></td>
            <td><b>HARGA SATUAN PEKERJAAN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(E+F)</b></td>
            <td style="text-align: right;"><b>="Rp{{ $a->subtotal + $profitMarginAndOverhead == 0 ? '-' : number_format($a->subtotal + $profitMarginAndOverhead, 2, ',', '.') }}"</b></td>
        </tr>
    </tbody>
    <tr></tr>
</table>
@endforeach
<table>
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
