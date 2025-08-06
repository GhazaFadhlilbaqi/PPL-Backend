<table>
    <thead>
        <tr>
            <td>{{ $project->name }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>a) Jangan mengubah, mengurangi atau menambahkan nama kolom</td>
        </tr>
        <tr>
            <td>b) Silakan isi kolom Harga satuan (D), Pajak (E) dan Keterangan (H)</td>
        </tr>
        <tr>
            <td>c) Tidak boleh mengubah dan menambah rincian barang/jasa, satuan, dan volume</td>
        </tr>
        <tr></tr>
        <tr>
            <th>Jenis barang/jasa</th>
            <th>Satuan</th>
            <th>Volume</th>
            <th>Harga satuan (Rp.)</th>
            <th>Pajak (%)</th>
            <th>Pajak (Rp.)</th>
            <th>Total (Rp.)</th>
            <th>Keterangan</th>
            <th>PDN</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rabs ?? [] as $rab)
            @foreach ($rab->rabItem ?? [] as $rabItem)
                <tr>
                    <td>{{ $rabItem->name }}</td>
                    <td>{{ $rabItem->unit->name ?? '-' }}</td>
                    <td style="text-align: right;">{{ $rabItem->volume }}</td>
                    <td style="text-align: right;">
                        {{ ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) ?? 0 }}
                    </td>
                    <td style="text-align: right;">{{ $project->ppn }}</td>
                    <td style="text-align: right;">0</td>
                    <td style="text-align: right;">
                        {{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) ?? 0) * $rabItem->volume }}
                    </td>
                    <td></td>
                    <td>TRUE</td>
                </tr>
            @endforeach

            @foreach ($rab->rabItemHeader ?? [] as $rabItemHeader)
                <tr>
                    {{-- <td colspan="9"><strong>{{ $rabItemHeader->name }}</strong></td> --}}
                    <td>{{ $rabItemHeader->name }}</td>
                    <td></td>
                    <td style="text-align: right;">0</td>
                    <td style="text-align: right;">0</td>
                    <td style="text-align: right;">0</td>
                    <td style="text-align: right;">0</td>
                    <td style="text-align: right;">0</td>
                    <td></td>
                    <td>TRUE</td>
                </tr>

                @foreach ($rabItemHeader->rabItem ?? [] as $rabItem)
                    <tr>
                        <td>{{ $rabItem->name }}</td>
                        <td>{{ $rabItem->unit->name ?? '-' }}</td>
                        <td style="text-align: right;">{{ $rabItem->volume }}</td>
                        <td style="text-align: right;">
                            {{ ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) ?? 0 }}
                        </td>
                        <td style="text-align: right;">{{ $project->ppn }}</td>
                        <td style="text-align: right;">0</td>
                        <td style="text-align: right;">
                            {{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) ?? 0) * $rabItem->volume }}
                        </td>
                        <td></td>
                        <td>TRUE</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
