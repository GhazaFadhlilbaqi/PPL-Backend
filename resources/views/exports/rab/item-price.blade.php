<table>
    @if ($company)
        <tr></tr>
        <tr>
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
            <td>{{ $company->address }}</td>
            <td></td>
        </tr>
        <tr></tr>
        <tr></tr>
    @endif
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td><b>KEGIATAN</b></td>
        <td><b>{{ $project->activity }}</b></td>
        <td></td>
        <td></td>
        <td style="text-align: right;"><b>LOKASI PEKERJAAN: {{ $project->province->name }}</b></td>
    </tr>
    <tr>
        <td><b>PEKERJAAN</b></td>
        <td><b>{{ $project->job }}</b></td>
        <td></td>
        <td></td>
        <td style="text-align: right;"><b>TAHUN ANGGARAN: {{ $project->fiscal_year }}</b></td>
    </tr>
    <tr></tr>
</table>
<table>
    <thead>
        <tr style="background-color: #08283D">
            <th><b>NO</b></th>
            <th><b>Uraian</b></th>
            <th><b>Satuan</b></th>
            <th><b>Kode</b></th>
            <th><b>Harga</b></th>
        </tr>
    </thead>
    <tbody>
        <tr></tr>
        @foreach ($customItemPricesGroups as $customItemPriceGroup)
            <tr>
                <td style="background-color: #D2E5F1"><b>{{ numToAlphabet($loop->index) }}</b></td>
                <td style="background-color: #D2E5F1"><b>{{ $customItemPriceGroup->name }}</b></td>
                <td style="background-color: #D2E5F1"></td>
                <td style="background-color: #D2E5F1"></td>
                <td style="background-color: #D2E5F1"></td>
            </tr>
            @foreach ($customItemPriceGroup->customItemPrice as $customItemPrice)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $customItemPrice->name }}</td>
                    <td>{{ $customItemPrice->unit->name }}</td>
                    <td>{{ $customItemPrice->code }}</td>
                    <td style="text-align: right;">="Rp{{ $customItemPrice->price == 0 ? '-' : number_format($customItemPrice->price, 2, ',', '.') }}"</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endforeach
        <tr></tr>
        <tr></tr>
        <tr>
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
            <td>Dibuat Oleh</td>
        </tr>
        <tr>
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
            <td>{{ $company->director_name }}</td>
        </tr>
    </tbody>
</table>
