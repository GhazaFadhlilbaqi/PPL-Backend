<table>
    <thead>
        <tr>
            <th>No</th>
            <th>URAIAN PEKERJAAN</th>
            <th>KODE ANALISA</th>
            <th>VOLUME</th>
            <th>SATUAN</th>
            <th>HARGA SATUAN (Rp)</th>
            <th>JUMLAH HARGA (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $rabSum = 0; @endphp
        @foreach ($rabs ?? [] as $rab)
            @php $rabSum += $rab->subtotal @endphp
            <tr>
                <td><b>I</b></td>
                <td><b>{{ $rab->name }}</b></td>
                <td></td>
                <td></td>
                <td><b>TOTAL I</b></td>
                <td>{{ $rab->subtotal }}</td>
            </tr>
            @foreach ($rab->rabItem ?? [] as $rabItem)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $rabItem->name }}</td>
                    <td>{{ $rabItem->customAhs ? $rabItem->customAhs->code : '' }}</td>
                    <td>{{ $rabItem->volume }}</td>
                    <td>{{ $rabItem->unit->name }}</td>
                    <td>{{ $rabItem->custom_ahs_id ? $rabItem['custom_ahs']['subtotal'] : $rabItem->price }}</td>
                    <td>{{ ($rabItem->custom_ahs_id ? $rabItem['custom_ahs']['subtotal'] : $rabItem->price) * $rabItem->volume }}</td>
                </tr>
            @endforeach
            @foreach($rab->rabItemHeader ?? [] as $rabItemHeader)
                <tr>
                    <td><b>A</b></td>
                    <td><b>{{ $rabItemHeader->name }}</b></td>
                </tr>
                @foreach ($rabItemHeader->rab_item ?? [] as $rabItem)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rabItem->name }}</td>
                        <td>{{ $rabItem->customAhs ? $rabItem->customAhs->code : '' }}</td>
                        <td>{{ $rabItem->volume }}</td>
                        <td>{{ $rabItem->unit->name }}</td>
                        <td>{{ $rabItem->custom_ahs_id ? $rabItem['custom_ahs']['subtotal'] : $rabItem->price }}</td>
                        <td>{{ ($rabItem->custom_ahs_id ? $rabItem['custom_ahs']['subtotal'] : $rabItem->price) * $rabItem->volume }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH TOTAL A + B</b></td>
            <td>{{ $rabSum }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>PPN {{ $project->ppn }}%</b></td>
            <td>{{ $project->ppn / 100 * $rabSum }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH TOTAL DENGAN PPN {{ $project->ppn }}%</b></td>
            <td>{{ $rabSum + ($project->ppn / 100 * $rabSum) }}</td>
        </tr>
    </tbody>
</table>
