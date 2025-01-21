<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Line Graph</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0px;
        }

        h2 {
            font-size: 14px;
            margin: 0px;
        }

        th {
            background-color: #00365a;
            color: white;
            font-weight: 500;
            text-align: center;
        }

        td {
            text-align: left;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
        }

        .week-column {
            text-align: center;
        }

        @media print {
    @page {
        size: landscape;
    }
}
    </style>
</head>

<body>
    <div class="chart-container">
        <div style="margin-bottom: 24px;">
            <h1 style="display: flex; font-size: 21px;">{{ $data['company'] }}</h1>
            <h2 style="display: flex; margin-bottom: 4px;"><span style="min-width: 150px;">PEKERJAAN</span>:
                {{ strtoupper($data['project_name']) }}</h2>
            <h2 style="display: flex;"><span style="min-width: 150px;">FISCAL YEAR</span>: {{ $data['fiscal_year'] }}
            </h2>
        </div>
        <table style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th rowspan="2">URAIAN PEKERJAAN</th>
                    <th rowspan="2">VOLUME</th>
                    <th rowspan="2">SATUAN</th>
                    <th rowspan="2">TOTAL HARGA (Rp)</th>
                    <th rowspan="2">BOBOT (%)</th>
                    <th colspan="{{ $data['implementation_duration'] }}" style="text-align: center;">WAKTU PELAKSANAAN</th>
                </tr>
                <tr>
                    @for ($i = 0; $i < $data['implementation_duration']; $i++)
                        <th>M{{$i + 1}}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($data['works'] as $work)
                    <tr>
                        <td
                            colspan="{{5 + $data['implementation_duration']}}"
                            style="font-weight: bold;"
                        >
                            A. {{ $work['name'] }}
                        </td>
                    </tr>
                    @foreach ($work['rab_items'] as $rab_item)
                        <tr class="work-row">
                            <td>{{ $rab_item['name'] }}</td>
                            <td>{{ $rab_item['volume'] }}</td>
                            <td>{{ $rab_item['unit_name'] }}</td>
                            <td style="text-align: right;">{{ number_format($rab_item['price'], 0, ',', '.') }}</td>
                            <td>{{ $rab_item['effort'] }}</td>
                            @foreach($rab_item['weeks_efforts'] as $week_effort)
                                <td class="week-column">{{ $week_effort }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    @foreach ($work['rab_item_headers'] as $rab_item_header)
                        <tr class="work-row">
                            <td colspan="{{5 + $data['implementation_duration']}}">I. {{ $rab_item_header['name'] }}</td>
                        </tr>
                        @foreach ($rab_item_header['rab_items'] as $rab_item)
                            <tr class="work-row">
                                <td>{{ $rab_item['name'] }}</td>
                                <td>{{ $rab_item['volume'] }}</td>
                                <td>{{ $rab_item['unit_name'] }}</td>
                                <td style="text-align: right;">{{ number_format($rab_item['price'], 0, ',', '.') }}
                                </td>
                                <td>{{ $rab_item['effort'] }}</td>
                                @foreach($rab_item['weeks_efforts'] as $week_effort)
                                    <td class="week-column">{{ $week_effort }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach

                <tr>
                    <td colspan="3" style="text-align: center;">Total (SEBELUM PPN)</td>
                    <td style="text-align: right;">{{ number_format($data['total_pretax_price'], 0, ',', '.') }}</td>
                    <td>{{ $data['total_effort'] }}</td>
                    <td colspan="{{ $data['implementation_duration'] }}"></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align: center;">BOBOT PEKERJAAN PER-MINGGU</td>
                    @foreach ($data['total_weekly_efforts'] as $total_weekly_effort)
                        <td>{{$total_weekly_effort}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td colspan="5" style="text-align: center;">BOBOT KUMULATIF PEKERJAAN PER-MINGGU</td>
                    @foreach ($data['total_accumulative_weekly_efforts'] as $total_accumulative_weekly_effort)
                        <td class="workload-column">{{$total_accumulative_weekly_effort}}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        <canvas id="lineGraph" width="2000" height="400" style="position: absolute; top: 0; left: 0;"></canvas>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        const table = document.querySelector("table");
        const canvas = document.querySelector("canvas");
        
        const { jsPDF } = window.jspdf;

        function setupChartCanvas() {
            const tableRect = table.getBoundingClientRect();
            canvas.width = tableRect.x + tableRect.width;
            canvas.height = tableRect.bottom;
            drawLineGraph();
        }

        setupChartCanvas();

        window.addEventListener("resize", function() {
            setupChartCanvas();
        });

        function drawLineGraph() {
            const ctx = canvas.getContext('2d');

            // Move start point into first week
            ctx.beginPath();
            const workRows = Array.from(document.getElementsByClassName('work-row'));
            const workColumns = workRows[workRows.length - 1].querySelectorAll('.week-column');
            const startColumnRect = workColumns[0].getBoundingClientRect();
            ctx.moveTo(startColumnRect.x, (startColumnRect.y + startColumnRect.height));

            // Move line for each week based on its effort
            const workloadCols = Array.from(document.getElementsByClassName('workload-column'));
            workloadCols.forEach((workloadCol) => {
                const rect = workloadCol.getBoundingClientRect();
                ctx.lineTo(
                    rect.x + rect.width,
                    (startColumnRect.y - ((workRows.length * startColumnRect.height) * (workloadCol.innerHTML /100))) + startColumnRect.height
                );
            })

            // Setup line styling
            ctx.strokeStyle = '#FE8D01';
            ctx.lineWidth = 2;
            ctx.stroke();
        }
    </script>
</body>

</html>
