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
          <td><b>LOKASI PEKERJAAN</b></td>
          <td><b>{{ $project->province->name }}</b></td>
      </tr>
      <tr>
          <td><b>TAHUN ANGGARAN</b></td>
          <td><b>{{ $project->fiscal_year }}</b></td>
      </tr>
      <tr></tr>
      <tr>
          <th>No</th>
          <th>URAIAN PEKERJAAN</th>
          <th>KOEFISIEN AHS</th>
          <th>VOLUME RAB</th>
          <th>HARGA SATUAN (Rp)</th>
          <th>SATUAN</th>
          <th>KEBUTUHAN BAHAN</th>
          <th>HITUNG HARGA TOTAL (Rp)</th>
      </tr>
  </thead>
  <tbody>
      @php 
        $rabSum = 0;
      @endphp
      @foreach ($rabs ?? [] as $rab)
        @php $globalAhsItemIteration = 1; @endphp
          <tr>
              <td style="background-color: #153346; color: white; border: 1px solid black;"><b>{{ numToAlphabet($loop->index) }}</b></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"><b>{{ $rab->name }}</b></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
              <td style="background-color: #153346; color: white; border: 1px solid black;"></td>
          </tr>
          @foreach ($rab->rabItem ?? [] as $rabItem)
              <tr>
                  <td style="background-color: #465059; color: white; border: 1px solid black;">{{ $globalAhsItemIteration++ }}</td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;">{{ $rabItem->name }}</td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
              </tr>
              @if ($rabItem->customAhs)
                  @foreach ($rabItem->customAhs->customAhsItem as $cAhs)
                      <tr>
                          <td style="border: 1px solid black;"></td>
                          <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->name }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->coefficient }}</td>
                          <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price ) }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->unit ? $cAhs->customAhsItemable->unit->name : ($cAhs->unit ? $cAhs->unit->name : 'Satuan tidak diketaui') }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->coefficient * $rabItem->volume }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )  }}</td>
                      </tr>
                  @endforeach
                @else
                    <tr>
                      <td style="border: 1px solid black;"></td>
                      <td style="border: 1px solid black;">{{ $rabItem->name }}</td>
                      <td style="border: 1px solid black;">1</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->price }}</td>
                      <td style="border: 1px solid black;">{{ getUnitNameByHashedId($rabItem->hashed_unit_id) }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume * 1 }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->subtotal }}</td>
                    </tr>
              @endif
              @php $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume @endphp
          @endforeach
          @foreach($rab->rabItemHeader ?? [] as $rabItemHeader)
            @foreach ($rabItemHeader->rabItem ?? [] as $rabItem)
                <tr>
                  <td style="background-color: #465059; color: white; border: 1px solid black;">{{ $globalAhsItemIteration++ }}</td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;">{{ $rabItem->name }}</td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                  <td style="background-color: #465059; color: white; border: 1px solid black;"></td>
                </tr>
                @if ($rabItem->customAhs)
                    @foreach ($rabItem->customAhs->customAhsItem as $cAhs)
                        <tr>
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->name }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->coefficient }}</td>
                            <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price ) }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->unit ? $cAhs->customAhsItemable->unit->name : ($cAhs->unit ? $cAhs->unit->name : 'Satuan tidak diketaui') }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->coefficient * $rabItem->volume }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )  }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                      <td style="border: 1px solid black;"></td>
                      <td style="border: 1px solid black;">{{ $rabItem->name }}</td>
                      <td style="border: 1px solid black;">1</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->price }}</td>
                      <td style="border: 1px solid black;">{{ getUnitNameByHashedId($rabItem->hashed_unit_id) }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume * 1 }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->subtotal }}</td>
                    </tr>
                @endif
                @php $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume @endphp
            @endforeach
          @endforeach
      @endforeach
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
