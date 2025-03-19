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
              @for ($i = 1; $i <= $project->implementation_duration; $i++)
                <td></td>
              @endfor
              <td>{{ $company->name }}</td>
              <td></td>
          </tr>
          <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              @for ($i = 1; $i <= $project->implementation_duration; $i++)
                <td></td>
              @endfor
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
          @for ($i = 1; $i <= $project->implementation_duration; $i++)
            <td></td>
          @endfor
          <td style="text-align: right;"><b>LOKASI PEKERJAAN: {{ $project->province->name }}</b></td>
      </tr>
      <tr>
          <td><b>NAMA PEKERJAAN</b></td>
          <td><b>{{ $project->job }}</b></td>
          <td></td>
          <td></td>
          <td></td>
          @for ($i = 1; $i <= $project->implementation_duration; $i++)
            <td></td>
          @endfor
          <td style="text-align: right;"><b>TAHUN ANGGARAN: {{ $project->fiscal_year }}</b></td>
      </tr>
      <tr></tr>
      <tr></tr>
      <tr>
          <th><b>No</b></th>
          <th><b>URAIAN PEKERJAAN</b></th>
          <th><b>VOLUME</b></th>
          <th><b>SATUAN</b></th>
          <th><b>JUMLAH HARGA (Rp)</b></th>
          <th><b>BOBOT (%)</b></th>
          @for ($i = 1; $i <= $project->implementation_duration; $i++)
            <th><b>W{{ $i }}</b></th>
          @endfor
      </tr>
  </thead>
  <tbody>
      @php
      
      $rabSum = 0;
      $weightPerWeeks = [];

      foreach($rabs ?? [] as $rab) {
        foreach ($rab->rabItem ?? [] as $rabItem) {
          $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume;
        }

        foreach($rab->rabItemHeader ?? [] as $rabItemHeader) {
          foreach ($rabItemHeader->rabItem ?? [] as $rabItem) {
            $rabSum += ($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume;
          }
        }
      }

      if ($rabSum == 0) {
        $rabSum = 1;
      }
      
      @endphp

      @foreach ($rabs ?? [] as $rab)
          <tr>
              <td><b>{{ numToAlphabet($loop->index) }}</b></td>
              <td><b>{{ $rab->name }}</b></td>
              <td></td>
              <td></td>
              <td></td>
          </tr>
          @foreach ($rab->rabItem ?? [] as $rabItem)
              <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $rabItem->name }}</td>
                  <td>{{ $rabItem->volume }}</td>
                  <td>{{ $rabItem->unit->name }}</td>
                  <td style="text-align: right;">="Rp{{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) == 0 ? '-' : number_format(($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume, 2, ',', '.') }}"</td>
                  <td style="text-align: right;">="%{{ (((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100) == 0 ? '-' : number_format(((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100, 2, ',', '.') }}"</td>
                  @if ($rabItem->implementationSchedule)
                    @php

                      $colsToDraw = [];

                      foreach ($rabItem->implementationSchedule as $is) {
                        $colsToDraw = array_merge($colsToDraw, range($is->start_of_week, $is->end_of_week));
                      }

                    @endphp

                    @for ($i = 1; $i <= $project->implementation_duration; $i++)
                      @if (in_array($i, $colsToDraw))
                        <td style="background-color: yellow;">
                          @php

                          $weight = (((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100) / count($colsToDraw);

                          if (isset($weightPerWeeks[$i])) {
                            $weightPerWeeks[$i][] = $weight;
                          } else {
                            $weightPerWeeks[$i] = [$weight];
                          }
                          
                          @endphp

                          {{ $weight }}
                        </td>
                      @else
                        <td></td>
                      @endif
                    @endfor
                  @endif
              </tr>
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
                      <td>{{ $rabItem->volume }}</td>
                      <td>{{ $rabItem->unit->name }}</td>
                      <td style="text-align: right;">="Rp{{ (($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) == 0 ? '-' : number_format(($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume, 2, ',', '.') }}"</td>
                      <td style="text-align: right;">="%{{ (((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100) == 0 ? '-' : number_format(((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100, 2, ',', '.') }}"</td>
                      @if ($rabItem->implementationSchedule)
                      @php

                        $colsToDraw = [];

                        foreach ($rabItem->implementationSchedule as $is) {
                          $colsToDraw = array_merge($colsToDraw, range($is->start_of_week, $is->end_of_week));
                        }

                      @endphp

                      @for ($i = 1; $i <= $project->implementation_duration; $i++)
                        @if (in_array($i, $colsToDraw))
                          <td style="background-color: yellow;">
                            @php

                            $weight = (((($rabItem->customAhs ? $rabItem->customAhs->price : $rabItem->price) * $rabItem->volume) / $rabSum) * 100) / count($colsToDraw);

                            if (isset($weightPerWeeks[$i])) {
                              $weightPerWeeks[$i][] = $weight;
                            } else {
                              $weightPerWeeks[$i] = [$weight];
                            }

                            @endphp

                            {{ $weight }}
                          </td>
                        @else
                          <td></td>
                        @endif
                      @endfor
                    @endif
                      {{-- <td>{{ $rabItem->custom_ahs_id != 'null' ? 'true' : $rabItem->price }}</td>
                      <td>{{ ($rabItem->custom_ahs_id != 'null' ? 'true' : $rabItem->price)}}</td> --}}
                      {{-- <td>{{ $rabItem }}</td>
                      <td>{{ $rabItem }}</td> --}}
                  </tr>
              @endforeach
          @endforeach
      @endforeach
      <tr>
        <td></td>
        <td>TOTAL HARGA PENAWARAN SEBELUM PPN</td>
        <td></td>
        <td></td>
        <td style="text-align: right;">="Rp{{ $rabSum == 0 ? '-' : number_format($rabSum, 2, ',', '.') }}"</td>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="2">Bobot Pekerjaan per Minggu</td>
        @php $weeklyRecap = [] @endphp
        @for ($i = 1; $i <= $project->implementation_duration; $i++)
          @if (isset($weightPerWeeks[$i]))
            <td>{{ array_sum($weightPerWeeks[$i]) }}</td>
            @php $weeklyRecap[$i] = array_sum($weightPerWeeks[$i]) @endphp
          @else
            <td>0</td>
            @php $weeklyRecap[$i] = 0 @endphp
          @endif
        @endfor
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="2">Bobot Kumulatif Pekerjaan Per Minggu</td>
        @for ($i = 1; $i <= $project->implementation_duration; $i++)
          <td>{{ array_sum(array_slice($weeklyRecap, 0, $i)) }}</td>
        @endfor
      </tr>
  </tbody>
</table>