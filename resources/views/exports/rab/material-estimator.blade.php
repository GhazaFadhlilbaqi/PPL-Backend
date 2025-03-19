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
          <td></td>
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
          <td></td>
          <td style="text-align: right;"><b>TAHUN ANGGARAN: {{ $project->fiscal_year }}</b></td>  
      </tr>
      <tr></tr>
      <tr></tr>
      <tr>
          <th><b>No</b></th>
          <th><b>URAIAN PEKERJAAN</b></th>
          <th><b>KOEFISIEN AHS</b></th>
          <th><b>VOLUME RAB</b></th>
          <th><b>HARGA SATUAN (Rp)</b></th>
          <th><b>SATUAN</b></th>
          <th><b>KEBUTUHAN BAHAN</b></th>
          <th><b>HITUNG HARGA TOTAL (Rp)</b></th>
      </tr>
  </thead>
  <tbody>
      @php 
        $rabSum = 0;
      @endphp
      @foreach ($rabs ?? [] as $rab)
        @php $globalAhsItemIteration = 1; @endphp
          <tr>
              <td style="background-color: #D2E5F1; border: 1px solid black;"><b>{{ numToAlphabet($loop->index) }}</b></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"><b>{{ $rab->name }}</b></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
              <td style="background-color: #D2E5F1; border: 1px solid black;"></td>
          </tr>
          @foreach ($rab->rabItem ?? [] as $rabItem)
              <tr>
                  <td style="background-color: #D7D7D7; border: 1px solid black;">{{ $globalAhsItemIteration++ }}</td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;">{{ $rabItem->name }}</td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
              </tr>
              @if ($rabItem->customAhs)
                  @foreach ($rabItem->customAhs->customAhsItem as $cAhs)
                      <tr>
                          <td style="border: 1px solid black;"></td>
                          <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->name }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->coefficient }}</td>
                          <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                          <td style="text-align: right; border: 1px solid black;">="Rp{{ ($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )) == 0 ? '-' : number_format($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price), 2, ',', '.') }}"</td>
                          <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->unit ? $cAhs->customAhsItemable->unit->name : ($cAhs->unit ? $cAhs->unit->name : 'Satuan tidak diketaui') }}</td>
                          <td style="border: 1px solid black;">{{ $cAhs->coefficient * $rabItem->volume }}</td>
                          <td style="text-align: right; border: 1px solid black;">="Rp{{ ($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )) == 0 ? '-' : number_format($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price), 2, ',', '.') }}"</td>
                      </tr>
                  @endforeach
                @else
                    <tr>
                      <td style="border: 1px solid black;"></td>
                      <td style="border: 1px solid black;">{{ $rabItem->name }}</td>
                      <td style="border: 1px solid black;">1</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                      <td style="text-align: right; border: 1px solid black;">="Rp{{ $rabItem->price == 0 ? '-' : number_format($rabItem->price, 2, ',', '.') }}"</td>
                      <td style="border: 1px solid black;">{{ getUnitNameByHashedId($rabItem->hashed_unit_id) }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume * 1 }}</td>
                      <td style="text-align: right; border: 1px solid black;">="Rp{{ $rabItem->subtotal == 0 ? '-' : number_format($rabItem->subtotal, 2, ',', '.') }}"</td>
                    </tr>
              @endif
              @php $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume @endphp
          @endforeach
          @foreach($rab->rabItemHeader ?? [] as $rabItemHeader)
            @foreach ($rabItemHeader->rabItem ?? [] as $rabItem)
                <tr>
                  <td style="background-color: #D7D7D7; border: 1px solid black;">{{ $globalAhsItemIteration++ }}</td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;">{{ $rabItem->name }}</td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                  <td style="background-color: #D7D7D7; border: 1px solid black;"></td>
                </tr>
                @if ($rabItem->customAhs)
                    @foreach ($rabItem->customAhs->customAhsItem as $cAhs)
                        <tr>
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->name }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->coefficient }}</td>
                            <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                            <td style="text-align: right; border: 1px solid black;">="Rp{{ ($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )) == 0 ? '-' : number_format($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price), 2, ',', '.') }}"</td>
                            <td style="border: 1px solid black;">{{ $cAhs->customAhsItemable->unit ? $cAhs->customAhsItemable->unit->name : ($cAhs->unit ? $cAhs->unit->name : 'Satuan tidak diketaui') }}</td>
                            <td style="border: 1px solid black;">{{ $cAhs->coefficient * $rabItem->volume }}</td>
                            <td style="text-align: right; border: 1px solid black;">="Rp{{ ($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price )) == 0 ? '-' : number_format($cAhs->custom_ahs_itemable_type == App\Models\CustomitemPrice::class ? $cAhs->customAhsItemable->price : ($cAhs->custom_ahs_itemable_type == App\Models\CustomAhp::class ? $cAhs->customAhsItemable->subtotal : $cAhs->customAhsItemable->price), 2, ',', '.') }}"</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                      <td style="border: 1px solid black;"></td>
                      <td style="border: 1px solid black;">{{ $rabItem->name }}</td>
                      <td style="border: 1px solid black;">1</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume }}</td>
                      <td style="text-align: right; border: 1px solid black;">="Rp{{ $rabItem->price == 0 ? '-' : number_format($rabItem->price, 2, ',', '.') }}"</td>
                      <td style="border: 1px solid black;">{{ getUnitNameByHashedId($rabItem->hashed_unit_id) }}</td>
                      <td style="border: 1px solid black;">{{ $rabItem->volume * 1 }}</td>
                      <td style="text-align: right; border: 1px solid black;">="Rp{{ $rabItem->subtotal == 0 ? '-' : number_format($rabItem->subtotal, 2, ',', '.') }}"</td>
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
