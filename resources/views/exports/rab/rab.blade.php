<table>
    <thead>
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
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><b>LOKASI PEKERJAAN: {{ $project->province->name }}</b></td>
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
        <tr></tr>
        <tr>
            <th><b>No</b></th>
            <th><b>URAIAN PEKERJAAN</b></th>
            <th><b>KODE ANALISA</b></th>
            <th><b>VOLUME</b></th>
            <th><b>SATUAN</b></th>
            <th><b>HARGA SATUAN (Rp)</b></th>
            <th><b>JUMLAH HARGA (Rp)</b></th>
        </tr>
    </thead>
    <tbody>
        @php $rabSum = 0; @endphp
        @foreach ($rabs ?? [] as $rab)
            <tr>
                <td><b>{{ numToAlphabet($loop->index) }}</b></td>
                <td><b>{{ $rab->name }}</b></td>
                <td></td>
                <td></td>
                <td><b>TOTAL {{ numToAlphabet($loop->index) }}</b></td>
                <td></td>
                <td style="text-align: right;">="Rp{{ $rab->subtotal == 0 ? '-' : number_format($rab->subtotal, 2, ',', '.') }}"</td>
            </tr>
            @foreach ($rab->rabItem ?? [] as $rabItem)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $rabItem->name }}</td>
                    <td>{{ $rabItem->customAhs ? $rabItem->customAhs->code : '-' }}</td>
                    <td>{{ $rabItem->volume }}</td>
                    <td>{{ $rabItem->unit->name }}</td>
                    <td style="text-align: right;">="Rp{{ ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) == 0 ? '-' : number_format($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price, 2, ',', '.') }}"</td>
                    <td style="text-align: right;">="Rp{{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) == 0 ? '-' : number_format(($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume, 2, ',', '.') }}"</td>
                </tr>
                @php $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume @endphp
            @endforeach
            @foreach($rab->rabItemHeader ?? [] as $rabItemHeader)
                <tr>
                    <td><b>{{ numToRoman($loop->iteration) }}</b></td>
                    <td><b>{{ $rabItemHeader->name }}</b></td>
                </tr>
                @foreach ($rabItemHeader->rabItem ?? [] as $rabItem)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rabItem->name }}</td>
                        <td>{{ $rabItem->customAhs ? $rabItem->customAhs->code : '-' }}</td>
                        <td>{{ $rabItem->volume }}</td>
                        <td>{{ $rabItem->unit->name }}</td>
                        <td style="text-align: right;">="Rp{{ ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) == 0 ? '-' : number_format($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price, 2, ',', '.') }}"</td>
                        <td style="text-align: right;">="Rp{{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) == 0 ? '-' : number_format(($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume, 2, ',', '.') }}"</td>
                        {{-- <td>{{ $rabItem->custom_ahs_id != 'null' ? 'true' : $rabItem->price }}</td>
                        <td>{{ ($rabItem->custom_ahs_id != 'null' ? 'true' : $rabItem->price)}}</td> --}}
                        {{-- <td>{{ $rabItem }}</td>
                        <td>{{ $rabItem }}</td> --}}
                        @php $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume @endphp
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        <tr></tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH TOTAL A + B</b></td>
            <td style="text-align: right;">="Rp{{ number_format($rabSum, 2, ',', '.') }}"</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>PPN {{ $project->ppn }}%</b></td>
            @php $ppn = $project->ppn / 100 * $rabSum @endphp
            <td style="text-align: right;">="Rp{{ number_format($tax, 2, ',', '.') }}"</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>JUMLAH TOTAL DENGAN PPN {{ $project->ppn }}%</b></td>
            <td style="text-align: right;">="Rp{{ number_format($price_after_tax, 2, ',', '.') }}"</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>TERBILANG</b></td>
            <td>{{ strtoupper(terbilang($price_after_tax)) }} RUPIAH</td>
        </tr>
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
    </tbody>
</table>
