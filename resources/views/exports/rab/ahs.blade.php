@foreach ($ahs as $a)
<table>
    <thead>
        <tr>
            <td>{{ $a->code }}</td>
            <td>{{ $a->name }}</td>
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
        @foreach ($a['item_arranged']['labor'] ?? [] as $laborAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($laborAhs) }}</td>
                <td>{{ $laborAhs->customAhsItemable->code }}</td>
                <td>{{ $laborAhs->unit->name }}</td>
                <td>{{ $laborAhs->coefficient }}</td>
                <td>{{ $laborAhs->customAhsItemable->subtotal }}</td>
                <td>{{ $laborAhs->customAhsItemable->subtotal * $laborAhs->coefficient }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>B</b></td>
            <td><b>BAHAN</b></td>
        </tr>
        @foreach ($a['item_arranged']['ingredients'] ?? [] as $ingredientsAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($ingredientsAhs) }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->code }}</td>
                <td>{{ $ingredientsAhs->unit->name }}</td>
                <td>{{ $ingredientsAhs->coefficient }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->subtotal }}</td>
                <td>{{ $ingredientsAhs->customAhsItemable->subtotal * $ingredientsAhs->coefficient }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>C</b></td>
            <td><b>PERALATAN</b></td>
        </tr>
        @foreach ($a['item_arranged']['tools'] ?? [] as $toolsAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($toolsAhs) }}</td>
                <td>{{ $toolsAhs->customAhsItemable->code }}</td>
                <td>{{ $toolsAhs->unit->name }}</td>
                <td>{{ $toolsAhs->coefficient }}</td>
                <td>{{ $toolsAhs->customAhsItemable->subtotal }}</td>
                <td>{{ $toolsAhs->customAhsItemable->subtotal * $toolsAhs->coefficient }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>D</b></td>
            <td><b>LAIN - LAIN</b></td>
        </tr>
        @foreach ($a['item_arranged']['others'] ?? [] as $othersAhs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ determineCustomAhsItemName($othersAhs) }}</td>
                <td>{{ $othersAhs->customAhsItemable->code }}</td>
                <td>{{ $othersAhs->unit->name }}</td>
                <td>{{ $othersAhs->coefficient }}</td>
                <td>{{ $othersAhs->customAhsItemable->subtotal }}</td>
                <td>{{ $othersAhs->customAhsItemable->subtotal * $othersAhs->coefficient }}</td>
            </tr>
        @endforeach
        <tr>
            <td><b>E</b></td>
            <td><b>JUMLAH HARGA TENAGA, BAHAN DAN PERALATAN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(A+B+C)</b></td>
            <td><b>{{ $a->subtotal }}</b></td>
        </tr>
        <tr>
            <td><b>F</b></td>
            <td><b>OVERHEAD DAN PROFIT</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>x D</b></td>
            {{-- FIXME: Add overhead --}}
            <td><b>{{ ($project->profit_margin / 100) * $a->subtotal }}</b></td>
        </tr>
        <tr>
            <td><b>H</b></td>
            <td><b>HARGA SATUAN PEKERJAAN</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>(D+E)</b></td>
            <td><b>{{ $a->subtotal + 0 }}</b></td>
        </tr>
    </tbody>
</table>
@endforeach
