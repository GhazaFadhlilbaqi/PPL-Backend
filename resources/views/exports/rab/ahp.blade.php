<table>
    @if ($company)
        <tr></tr>
        <tr>
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
        <td style="text-align: right;"><b>LOKASI PEKERJAAN: {{ $project->province->name }}</b></td>
    </tr>
    <tr>
        <td><b>NAMA PEKERJAAN</b></td>
        <td><b>{{ $project->job }}</b></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: right;"><b>TAHUN ANGGARAN: {{ $project->fiscal_year }}</b></td>
    </tr>
    <tr></tr>
</table>
@foreach ($ahps as $ahp)
<table border="1">
    <thead>
        <tr>
            <th>
                <b>{{ $ahp->code }}</b>
            </th>
            <th>
                <b>{{ $ahp->name }}</b>
            </th>
        </tr>
        <tr>
            <th><b>NO.</b></th>
            <th><b>URAIAN</b></th>
            <th><b>KODE</b></th>
            <th><b>KOEFISIEN</b></th>
            <th><b>SATUAN</b></th>
            <th><b>KET.</b></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #D2E5F1"><b>A.</b></td>
            <td style="background-color: #D2E5F1"><b>URAIAN PERALATAN</b></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
        </tr>
        <tr>
            <td>1.</td>
            <td>Jenis Peralatan</td>
            <td>{{ $ahp->code }}</td>
            <td>{{ $ahp->name }}</td>
        </tr>
        <tr>
            <td></td>
            <td>(Merk / Tipe Alat)</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Tenaga</td>
            <td>Pw</td>
            <td>{{ $ahp->Pw }}</td>
            <td>HP</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Kapasitas Bahan Bakar</td>
            <td>Cp</td>
            <td>{{ $ahp->Cp }}</td>
            <td>Liter</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Alat yang Dipakai</td>
            <td>Cp</td>
            <td>{{ $ahp->Cp }}</td>
            <td>Liter</td>
        </tr>
        <tr>
            <td>a.</td>
            <td>Umur Ekonomis</td>
            <td>A</td>
            <td>{{ $ahp->A }}</td>
            <td>Tahun</td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Jam Kerja Dalam 1 Tahun</td>
            <td>W</td>
            <td>{{ $ahp->W }}</td>
            <td>Jam</td>
        </tr>
        <tr>
            <td>c.</td>
            <td>Harga Alat</td>
            <td>B</td>
            <td style="text-align: right;">="Rp{{ $ahp->W == 0 ? '-' : number_format($ahp->W, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td style="background-color: #D2E5F1"><b>B.</b></td>
            <td style="background-color: #D2E5F1"><b>BIAYA PASTI PER JAM KERJA</b></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
        </tr>
        <tr>
            <td>1.</td>
            <td>Nilai Sisa Alat</td>
            <td>C</td>
            <td style="text-align: right;">="Rp{{ $ahp->C == 0 ? '-' : number_format($ahp->C, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Faktor Angsuran Modal</td>
            <td>D</td>
            <td>{{ $ahp->D }}</td>
            <td>-</td>
        </tr>
        <tr>
            <td>a.</td>
            <td>Biaya Pengembalian Modal</td>
            <td>E</td>
            <td style="text-align: right;">="Rp{{ $ahp->E == 0 ? '-' : number_format($ahp->E, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Asuransi, dan lain - lain</td>
            <td>F</td>
            <td style="text-align: right;">="Rp{{ $ahp->F == 0 ? '-' : number_format($ahp->F, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td></td>
            <td><b>Biaya Pasti Perjam</b></td>
            <td>G</td>
            <td style="text-align: right;">="Rp{{ $ahp->G == 0 ? '-' : number_format($ahp->G, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td style="background-color: #D2E5F1"><b>C.</b></td>
            <td style="background-color: #D2E5F1"><b>BIAYA OPERASI PER JAM KERJA</b></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
        </tr>
        <tr>
            <td>1.</td>
            <td>Bahan Bakar</td>
            <td>H</td>
            <td style="text-align: right;">="Rp{{ $ahp->H == 0 ? '-' : number_format($ahp->H, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Pelumas</td>
            <td>I</td>
            <td style="text-align: right;">="Rp{{ $ahp->I == 0 ? '-' : number_format($ahp->I, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Perawatan dan Perbaikan</td>
            <td>K</td>
            <td style="text-align: right;">="Rp{{ $ahp->K == 0 ? '-' : number_format($ahp->K, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Operator</td>
            <td>L</td>
            <td style="text-align: right;">="Rp{{ $ahp->L == 0 ? '-' : number_format($ahp->L, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Pembantu Operator</td>
            <td>M</td>
            <td style="text-align: right;">="Rp{{ $ahp->M == 0 ? '-' : number_format($ahp->M, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td></td>
            <td>Biaya Operasi per Jam</td>
            <td>P</td>
            <td style="text-align: right;">="Rp{{ $ahp->P == 0 ? '-' : number_format($ahp->P, 2, ',', '.') }}"</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td style="background-color: #D2E5F1"><b>D.</b></td>
            <td style="background-color: #D2E5F1"><b>TOTAL BIAYA SEWA ALAT / JAM</b></td>
            <td style="background-color: #D2E5F1"><b>S</b></td>
            <td style="background-color: #D2E5F1; text-align: right;"><b>="Rp{{ $ahp->S == 0 ? '-' : number_format($ahp->S, 2, ',', '.') }}"</b></td>
            <td style="background-color: #D2E5F1"><b>Rupiah</b></td>
            <td style="background-color: #D2E5F1"></td>
        </tr>
        <tr>
            <td style="background-color: #D2E5F1"><b>E.</b></td>
            <td style="background-color: #D2E5F1"><b>LAIN - LAIN</b></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
            <td style="background-color: #D2E5F1"></td>
        </tr>
        <tr>
            <td></td>
            <td>Tingkat Suku Bunga</td>
            <td>i</td>
            <td>{{ $ahp->i }}</td>
            <td>% Tahun</td>
        </tr>
        <tr>
            <td></td>
            <td>Upah Operator / Sopir</td>
            <td>U1</td>
            <td>{{ $ahp->U1 }}</td>
            <td>Rp./jam</td>
        </tr>
        <tr>
            <td></td>
            <td>Upah Pembantu Operator / Pembantu Sopir / Pembantu Mekanik</td>
            <td>U2</td>
            <td>{{ $ahp->U2 }}</td>
            <td>Rp./jam</td>
        </tr>
        <tr>
            <td></td>
            <td>Bahan Bakar Bensin</td>
            <td>Mb</td>
            <td>{{ $ahp->Mb }}</td>
            <td>Liter</td>
        </tr>
        <tr>
            <td></td>
            <td>Bahan Bakar Solar</td>
            <td>Ms</td>
            <td>{{ $ahp->Ms }}</td>
            <td>Liter</td>
        </tr>
        <tr>
            <td></td>
            <td>Minyak Pelumas</td>
            <td>Mp</td>
            <td>{{ $ahp->Mp }}</td>
            <td>Liter</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
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
        <td>{{ $project->province->name }}, {{ date('d-m-Y') }}</td>
    </tr>
    <tr>
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
        <td>{{ $company->director_name }}</td>
    </tr>
</table>
