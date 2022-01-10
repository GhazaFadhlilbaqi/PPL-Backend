<table>
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
        <td><b>LOKASI PEKERJAAN</b></td>
        <td><b>{{ $project->province->name }}</b></td>
    </tr>
    <tr>
        <td><b>TAHUN ANGGARAN</b></td>
        <td><b>{{ $project->fiscal_year }}</b></td>
    </tr>
    <tr></tr>
    <tr></tr>
</table>
@foreach ($ahps as $ahp)
<table border="1">
    <thead>
        <tr>
            <th>NO.</th>
            <th>URAIAN</th>
            <th>KODE</th>
            <th>KOEFISIEN</th>
            <th>SATUAN</th>
            <th>KET.</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>A.</b></td>
            <td><b>URAIAN PERALATAN</b></td>
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
            <td>{{ $ahp->W }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td><b>B.</b></td>
            <td><b>BIAYA PASTI PER JAM KERJA</b></td>
        </tr>
        <tr>
            <td>1.</td>
            <td>Nilai Sisa Alat</td>
            <td>C</td>
            <td>{{ $ahp->C }}</td>
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
            <td>{{ $ahp->E }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Asuransi, dan lain - lain</td>
            <td>F</td>
            <td>{{ $ahp->F }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td></td>
            <td><b>Biaya Pasti Perjam</b></td>
            <td>G</td>
            <td>{{ $ahp->G }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td><b>C.</b></td>
            <td><b>BIAYA OPERASI PER JAM KERJA</b></td>
        </tr>
        <tr>
            <td>1.</td>
            <td>Bahan Bakar</td>
            <td>H</td>
            <td>{{ $ahp->H }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Pelumas</td>
            <td>I</td>
            <td>{{ $ahp->I }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Perawatan dan Perbaikan</td>
            <td>K</td>
            <td>{{ $ahp->K }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Operator</td>
            <td>L</td>
            <td>{{ $ahp->L }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Pembantu Operator</td>
            <td>M</td>
            <td>{{ $ahp->M }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td></td>
            <td>Biaya Operasi per Jam</td>
            <td>P</td>
            <td>{{ $ahp->P }}</td>
            <td>Rupiah</td>
        </tr>
        <tr>
            <td><b>D.</b></td>
            <td><b>TOTAL BIAYA SEWA ALAT / JAM</b></td>
            <td><b>S</b></td>
            <td><b>{{ $ahp->S }}</b></td>
            <td><b>Rupiah</b></td>
        </tr>
        <tr>
            <td><b>E.</b></td>
            <td><b>LAIN - LAIN</b></td>
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
