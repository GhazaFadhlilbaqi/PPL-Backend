<table>
    <thead>
        <tr style="background-color: #08283D">
            <th>NO</th>
            <th>Uraian</th>
            <th>Satuan</th>
            <th>Kode</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
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
        </tr>
        <tr>
            <td><b>PEKERJAAN</b></td>
            <td><b>{{ $project->job }}</b></td>
        </tr>
        <tr>
            <td><b>TAHUN ANGGARAN</b></td>
            <td><b>{{ $project->fiscal_year }}</b></td>
        </tr>
        <tr></tr>
        <tr></tr>
        @foreach ($customItemPricesGroups as $customItemPriceGroup)
            <tr>
                <td>{{ numToAlphabet($loop->index) }}</td>
                <td>{{ $customItemPriceGroup->name }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @foreach ($customItemPriceGroup->customItemPrice as $customItemPrice)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $customItemPrice->name }}</td>
                    <td>{{ $customItemPrice->unit->name }}</td>
                    <td>{{ $customItemPrice->code }}</td>
                    <td>{{ $customItemPrice->price }}</td>
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
    </tbody>
</table>
