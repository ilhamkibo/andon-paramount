@extends('layouts.main')


@section('styles')
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            background-color: #fefefe;
            width: 40vh;
            /* Lebar dan tinggi modal sama, sesuaikan nilai sesuai kebutuhan */
            height: 40vh;
            /* Lebar dan tinggi modal sama, sesuaikan nilai sesuai kebutuhan */
            margin: 15% auto;
            /* Margin atas dan bawah, dan auto untuk tengah secara horizontal */
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Penempatan Break Time di tengah vertikal */
            align-items: center;
            /* Penempatan Break Time di tengah horizontal */
        }

        .modal-content img {
            max-width: 100%;
            /* Maksimum lebar gambar adalah lebar dari container */
            max-height: 100%;
            /* Maksimum tinggi gambar adalah tinggi dari container */
            object-fit: contain;
            /* Memastikan gambar menjaga aspek rasio dan mengisi container sepenuhnya */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
        }
    </style>
@endsection

@section('content')

    <div class="container-fluid">
        @if (session()->has('sukses'))
            <div class="alert alert-success alert-sukses alert-dismissible fade show" role="alert">
                {{ session('sukses') }}
            </div>
        @endif
        @if (session()->has('gagal'))
            <div class="alert alert-danger alert-gagal alert-dismissible fade show" role="alert">
                {{ session('gagal') }}
            </div>
        @endif

        @php
            $colorsOut = [
                '#1f78b4',
                '#33a02c',
                '#e31a1c',
                '#ff7f0e',
                '#6a3d9a',
                '#fb9a99',
                '#a6cee3',
                '#d2cfa6',
                '#cab2d6',
                '#6b6ecf',
                '#b2df8a',
                '#993d9a',
            ];
        @endphp

        <!-- Small boxes (Stat box) -->
        <section class="row justify-content-center pt-4">
            <div class="col-12">
                <div class="row justify-content-center">
                    @if (!empty($newDataPlans) && isset($newDataPlans[0]))
                        @foreach ($newDataPlans as $index => $item)
                            @php
                                // Menghitung indeks warna
                                $colorIndex = ($index * 2) % count($colorsOut); // Melakukan modifikasi indeks agar berulang
                            @endphp

                            <div class="col-lg-4 col-xl-2"> <!-- Sesuaikan ukuran kolom sesuai kebutuhan -->
                                <div class="text-center m-1 border py-1 px-2 bg-white shadow-sm rounded">
                                    <div class="border-bottom">
                                        <h3 class="mb-0" style="font-size:30px;">
                                            <strong>{{ $index + 1 . '. ' . $item->bed_models }}</strong>
                                        </h3>
                                    </div>
                                    <div class="mt-1"
                                        style="display: flex; align-items: center; justify-content: center; text-align: center">
                                        <h3 class="border rounded p-1 mr-1"
                                            style="font-size:30px; flex: 1; background-color: {{ $colorsOut[$colorIndex] }}; margin: 0; line-height: 1;">
                                            <strong>Target</strong>
                                        </h3>
                                        <h1 class="border rounded p-1"
                                            style="font-size:30px; margin: 0; flex: 1; line-height: 1;">
                                            <strong>{{ $item->target_quantity }}</strong>
                                        </h1>
                                    </div>
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; text-align: center">
                                        <h1 class="border rounded p-1 mr-1"
                                            style="font-size:30px; flex: 1; background-color: {{ $colorsOut[$colorIndex + 1] }}; margin: 0; line-height: 1;">
                                            <strong>Actual</strong>
                                        </h1>
                                        <h1 id="actualQty{{ $index + 1 }}" class="border rounded p-1"
                                            style="font-size:30px;  margin: 0; flex: 1; line-height: 1;">-</h1>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="small-box bg-white">
                            <h4 class="text-center px-4 py-2 mb-0 bg-secondary rounded-top">Data Plan tidak tersedia.</h4>
                            <div class="inner">
                                <h3 class="text-center">-</h3>
                            </div>
                        </div>
                    @endif
                    <!-- ./col -->
                </div>
            </div>
        </section>

        <hr>
        <!-- /.row -->
        <!-- Main row -->
        {{-- <section class="row mb-3 justify-content-center">
            <div class="col-lg-12">
                
            </div>
        </section> --}}
        <section class="row justify-content-center">
            <div class="col-lg-4">
                @if ($message !== '')
                    <div class="alert alert-danger text-center">{{ $message }}</div>
                @endif
            </div>
            <div class="col-lg-12">
                <!-- Custom tabs (Charts with tabs)-->

                <div class="card">
                    <div class="d-flex py-2 px-3 border-bottom align-items-center justify-content-between">
                        <h3 class="m-0 d-flex align-items-center" style="font-size: 1.2rem">
                            <i class="fas fa-chart-line mr-1"></i>
                            @if (!empty($dataPlans) && isset($dataPlans[0]))
                                {{-- <span>Plan Production Line {{ $dataPlans[0]->line_id }} Chart</span> --}}
                                <span class="mr-2">Plan Production Chart</span>
                                <form
                                    action="{{ route('operationTimePlan.update', ['date' => request('date') ? request('date') : now()->format('Y-m-d')]) }}"
                                    method="POST" class="form-inline border-left border-right px-2">
                                    @csrf
                                    <div class="form-group mr-sm-2 mb-2">
                                        <select required class="form-control @error('opTime') is-invalid @enderror"
                                            id="opTime" name="opTime">
                                            @foreach ($operationNames as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $operationTimes[0]->name_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-2">Submit Change</button>
                                </form>
                                <form class="form-inline border-right px-2" method="get"
                                    action="{{ route('production') }}">
                                    @csrf
                                    <div hidden>
                                        <label for="line_id">Line Number:</label>
                                        <select name="line_id" class="mx-sm-3 form-control">
                                            <option value="1" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id == '1') selected @endif>Line 1
                                            </option>
                                            <option value="2" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id == '2') selected @endif>Line 2
                                            </option>
                                            <!-- Tambahkan pilihan lainnya sesuai kebutuhan -->
                                        </select>
                                    </div>
                                    <label for="date">Date:</label>
                                    <input type="date" id="date" name="date"
                                        value="{{ !empty($dataPlans) && isset($dataPlans[0]) ? date('Y-m-d', strtotime($dataPlans[0]->date)) : '' }}"
                                        class="mx-sm-3 form-control">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                                <a class="ml-2"
                                    href="line/{{ \Carbon\Carbon::parse($dataPlans[0]->date)->format('Y-m-d') }}">Details
                                    data <i class="fas fa-arrow-circle-right"></i></a>
                            @else
                                <span class="pr-2">Plan Production Chart </span>
                                <form class="form-inline border-left px-2" method="get"
                                    action="{{ route('production') }}">
                                    @csrf
                                    <div hidden>
                                        <label for="line_id">Line Number:</label>
                                        <select name="line_id" class="mx-sm-3 form-control">
                                            <option value="1" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id == '1') selected @endif>Line 1
                                            </option>
                                            <option value="2" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id == '2') selected @endif>Line 2
                                            </option>
                                            <!-- Tambahkan pilihan lainnya sesuai kebutuhan -->
                                        </select>
                                    </div>
                                    <label for="date">Date:</label>
                                    <input type="date" id="date" name="date"
                                        value="{{ !empty($dataPlans) && isset($dataPlans[0]) ? date('Y-m-d', strtotime($dataPlans[0]->date)) : '' }}"
                                        class="mx-sm-3 form-control">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            @endif
                        </h3>
                        @php
                            $today = now()->format('Y-m-d'); // Mendapatkan tanggal hari ini
                        @endphp

                        @if (
                            !empty($newDataPlans[0]) &&
                                (request('date') == $today || date('Y-m-d', strtotime($newDataPlans[0]->date)) == $today))
                            <div id="activeModel">
                                <h3>Selected Model: -</h3>
                            </div>
                        @endif
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <!-- Morris chart - Sales -->
                            <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 55vh;">
                                @if (!empty($dataPlans) && isset($dataPlans[0]))
                                    <canvas id="revenue-chart-canvas" class="chartjs-render-monitor"></canvas>
                                @else
                                    <div class="text-center">
                                        <h3>Plan Production Chart (No Data)</h3>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->

            </div>
        </section>
        <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->

    <div id="breakModal" class="modal">
        <div class="modal-content">
            {{-- <span class="close" id="closeBtn" onclick="closeModal()">&times;</span> --}}
            <img src={{ asset('assets/image/break-time.png') }} alt="">
            <h1>Break Time</h1>
        </div>
    </div>

    @php
        $dataPlans = json_encode($dataPlans);
        $breakTimes0 = json_encode($breakTimes0);
        $breakTimes1 = json_encode($breakTimes1);
        $forTooltip0 = json_encode($forTooltip0);
        $forTooltip1 = json_encode($forTooltip1);
        $chartTime = json_encode($chartTime);
        $timeBreakModal = json_encode($timeBreakModal);
        $dataChartObject = json_encode($dataChartObject);
        // $breakValue = json_encode($breakValue);
    @endphp
@endsection

@section('scripts')
    <script>
        // Hanlde sukses and gagal alert
        // Menentukan waktu (dalam milidetik) setelah itu elemen akan disembunyikan
        const waktuTampil = 2000; // 2 detik

        // Menemukan elemen-elemen yang ingin disembunyikan setelah waktu tertentu
        const suksesAlert = document.querySelector('.alert-sukses');
        const gagalAlert = document.querySelector('.alert-gagal');

        // Fungsi untuk menyembunyikan elemen-elemen setelah waktu tertentu
        const sembunyikanElemen = (elemen) => {
            if (elemen) {
                elemen.style.display = 'none';
            }
        };

        // Setelah waktu tertentu, panggil fungsi untuk menyembunyikan elemen-elemen
        setTimeout(() => {
            sembunyikanElemen(suksesAlert);
            sembunyikanElemen(gagalAlert);
        }, waktuTampil);
    </script>

    <script>
        const dataPlans = {!! $dataPlans ?? 'null' !!}; // Memasukkan data dari kontroler
        const breakTimes0 = {!! $breakTimes0 ?? 'null' !!}; // Memasukkan data dari kontroler
        const breakTimes1 = {!! $breakTimes1 ?? 'null' !!}; // Memasukkan data dari kontroler
        const forTooltip0 = {!! $forTooltip0 ?? 'null' !!}; // Memasukkan data dari kontroler
        const forTooltip1 = {!! $forTooltip1 ?? 'null' !!}; // Memasukkan data dari kontroler
        const chartTime = {!! $chartTime ?? 'null' !!}; // Memasukkan data dari kontroler
        const timeBreakModal = {!! $timeBreakModal ?? 'null' !!}; // Memasukkan data dari kontroler
        const dataChartObject = {!! $dataChartObject ?? 'null' !!}; // Memasukkan data dari kontroler
        const [startTimeString, endTimeString] = chartTime;
        const breakModal = document.getElementById('breakModal'); //element breakmodal
        const closeBtn = document.getElementById('closeBtn'); //button Close Modal

        let salesChartCanvas, //Mengambil id canvas
            salesChartData, //chart Data
            alwaysShowTooltip, //Membuat tooltip pada chart
            salesChartOptions, //Config Chart
            salesChart, //membuat chart
            startTime, //start time chart
            endTime, //end time chart
            biggestQtyPlan = 0,
            isTooltipVisible = [true, true, true, true, true, true, true, true, true, true, true,
                true
            ]; //variabel untuk visibilitas tooltip
        // const breakValue = {!! $breakValue ?? 'null' !!}; // Memasukkan data dari kontroler 

        if (dataPlans.length > 0) {
            // Menggunakan format YYYY-MM-DD
            const inputDate = new Date(dataPlans[0].date);
            // Tambahkan 7 jam ke waktu
            inputDate.setHours(inputDate.getHours() + 7);
            const currentDate = inputDate.toISOString().split('T')[0];
            const startTime = new Date(`${currentDate} ${startTimeString}`);
            const endTime = new Date(`${currentDate} ${endTimeString}`);
            endTime.setMinutes(endTime.getMinutes() + 30);
            startTime.setMinutes(startTime.getMinutes() - 10);

            const colorsArray = [
                // "#003f5c", // Biru
                // "#003f5c", // Mirip dengan Biru
                // "#4caf50", // Hijau
                // "#4caf50", // Mirip dengan Hijau
                // "#ff7f0e", // Oranye
                // "#ff7f0e", // Mirip dengan Oranye
                // "#d62728", // Merah
                // "#d62728", // Mirip dengan Merah
                // "#9467bd", // ungu
                // "#9467bd", // Mirip dengan ungu
                // "#ffb20d", // Kuning
                // "#ffb20d" // Mirip dengan Kuning
                "#1f78b4",
                "#33a02c",
                "#e31a1c",
                "#ff7f0e",
                "#6a3d9a",
                "#fb9a99",
                "#a6cee3",
                "#d2cfa6",
                "#cab2d6",
                "#6b6ecf",
                "#b2df8a",
                "#993d9a"
            ]

            let labels = ["2023-10-30 06:52:06", "2023-10-30 07:52:06", "2023-10-30 08:52:06", "2023-10-30 09:52:06",
                "2023-10-30 10:52:06"
            ];
            const datasets = [];

            for (let i = 0; i < dataPlans.length * 2; i++) {
                const isPlanDataset = i % 2 === 0;

                if (biggestQtyPlan < dataPlans[i]?.target_quantity) {
                    biggestQtyPlan = dataPlans[i]?.target_quantity;
                }

                datasets.push({
                    label: `${isPlanDataset ? 'Plan' : 'Actual'} ${dataPlans[Math.floor(i/2)].bed_models.name}`,
                    data: [{
                            x: new Date("2022-01-13 08:35:00"),
                            y: 0
                        },
                        {
                            x: new Date("2022-01-13 09:00:00"),
                            y: 0
                        },
                        {
                            x: new Date("2022-01-13 09:30:00"),
                            y: 0
                        },
                    ],
                    backgroundColor: colorsArray[i],
                    borderColor: colorsArray[i],
                    // borderDash: isPlanDataset ? [5, 5] : [], 
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 0,
                    borderWidth: 4
                });
            }

            let salesChartData = {
                labels: labels,
                datasets: datasets
            };

            function createChart() {
                salesChartCanvas = document.getElementById('revenue-chart-canvas');

                alwaysShowTooltip = {
                    id: "alwaysShowTooltip",

                    afterDraw(chart, args, options) {
                        chart.data.datasets.forEach((dataset, i) => {
                            chart.getDatasetMeta(i).data.forEach((datapoint, index) => {
                                const {
                                    x,
                                    y
                                } = datapoint.tooltipPosition();
                                if (chart.data.datasets[i].data[index].y !== null && (isTooltipVisible[
                                        i])) {
                                    if (i % 2 === 0) {
                                        if ((typeof chart.data.datasets[i].data[index].x === 'string' &&
                                                forTooltip1.some(time => chart.data.datasets[i].data[
                                                    index].x.endsWith(time))) || index == chart.data
                                            .datasets[i].data.length - 1 || index == 0) {
                                            const text = chart.data.datasets[i].data[index].y;
                                            const textWidth = chart.ctx.measureText(text).width;

                                            if (i !== chart.getDatasetMeta(i).data[index]
                                                .datasetIndex) {
                                                chart.ctx.fillStyle = dataset.backgroundColor;
                                            } else {
                                                chart.ctx.fillStyle = dataset.backgroundColor;
                                            }

                                            chart.ctx.fillRect(x - ((textWidth + 15) / 2), y - 35,
                                                textWidth + 15, 30);

                                            chart.ctx.beginPath();
                                            chart.ctx.moveTo(x, y);
                                            chart.ctx.lineTo(x - 5, y - 5);
                                            chart.ctx.lineTo(x + 5, y - 5);
                                            chart.ctx.fill();

                                            chart.ctx.font = '25px Arial';
                                            chart.ctx.fillStyle = 'white';
                                            chart.ctx.fillText(text, x - (textWidth / 2), y - 10);
                                        }
                                    } else {
                                        if (typeof chart.data.datasets[i].data[index].x === 'string' &&
                                            forTooltip1.some(time => chart.data.datasets[i].data[index]
                                                .x.endsWith(time) || index == chart.data.datasets[i]
                                                .data.length - 1)
                                        ) {

                                            const text = chart.data.datasets[i].data[index].y;
                                            const textWidth = chart.ctx.measureText(text).width;

                                            if (i !== chart.getDatasetMeta(i).data[index]
                                                .datasetIndex) {
                                                chart.ctx.fillStyle = dataset.backgroundColor;
                                            } else {
                                                chart.ctx.fillStyle = dataset.backgroundColor;
                                            }

                                            chart.ctx.fillRect(x - ((textWidth + 10) / 2), y + 5,
                                                textWidth + 10, 30);

                                            chart.ctx.beginPath();
                                            chart.ctx.moveTo(x, y);
                                            chart.ctx.lineTo(x - 5, y + 5);
                                            chart.ctx.lineTo(x + 5, y + 5);
                                            chart.ctx.fill();

                                            chart.ctx.font = '25px Arial';
                                            chart.ctx.fillStyle = 'white';
                                            chart.ctx.fillText(text, x - (textWidth / 2), y + 28);
                                        }
                                    }
                                }
                            });
                        });
                    }
                };

                // alwaysShowTooltip = {
                //     id: "alwaysShowTooltip",

                //     afterDraw(chart, args, options) {
                //         chart.data.datasets.forEach((dataset, i) => {
                //             chart.getDatasetMeta(i).data.forEach((datapoint, index) => {
                //                 const {
                //                     x,
                //                     y
                //                 } = datapoint.tooltipPosition();
                //                 if (chart.data.datasets[i].data[index].y !== null && (isTooltipVisible[
                //                         i])) {
                //                     if (i % 2 === 0) {
                //                         if ((typeof chart.data.datasets[i].data[index].x === 'string' &&
                //                                 forTooltip1.some(time => chart.data.datasets[i].data[
                //                                     index].x.endsWith(time))) || index == chart.data
                //                             .datasets[i].data.length - 1 || index == 0) {
                //                             const text = chart.data.datasets[i].data[index].y;
                //                             const textWidth = chart.ctx.measureText(text).width;

                //                             if (i !== chart.getDatasetMeta(i).data[index]
                //                                 .datasetIndex) {
                //                                 chart.ctx.fillStyle = dataset.backgroundColor;
                //                             } else {
                //                                 chart.ctx.fillStyle = dataset.backgroundColor;
                //                             }

                //                             chart.ctx.fillRect(x - ((textWidth + 10) / 2), y - 25,
                //                                 textWidth + 10, 20);

                //                             chart.ctx.beginPath();
                //                             chart.ctx.moveTo(x, y);
                //                             chart.ctx.lineTo(x - 5, y - 5);
                //                             chart.ctx.lineTo(x + 5, y - 5);
                //                             chart.ctx.fill();

                //                             chart.ctx.font = '15px Arial';
                //                             chart.ctx.fillStyle = 'white';
                //                             chart.ctx.fillText(text, x - (textWidth / 2), y - 13);
                //                         }
                //                     } else {
                //                         if (typeof chart.data.datasets[i].data[index].x === 'string' &&
                //                             forTooltip1.some(time => chart.data.datasets[i].data[index]
                //                                 .x.endsWith(time) || index == chart.data.datasets[i]
                //                                 .data.length - 1)
                //                         ) {

                //                             const text = chart.data.datasets[i].data[index].y;
                //                             const textWidth = chart.ctx.measureText(text).width;

                //                             if (i !== chart.getDatasetMeta(i).data[index]
                //                                 .datasetIndex) {
                //                                 chart.ctx.fillStyle = dataset.backgroundColor;
                //                             } else {
                //                                 chart.ctx.fillStyle = dataset.backgroundColor;
                //                             }

                //                             chart.ctx.fillRect(x - ((textWidth + 10) / 2), y + 5,
                //                                 textWidth + 10, 20);

                //                             chart.ctx.beginPath();
                //                             chart.ctx.moveTo(x, y);
                //                             chart.ctx.lineTo(x - 5, y + 5);
                //                             chart.ctx.lineTo(x + 5, y + 5);
                //                             chart.ctx.fill();

                //                             chart.ctx.font = '15px Arial';
                //                             chart.ctx.fillStyle = 'white';
                //                             chart.ctx.fillText(text, x - (textWidth / 2), y + 16);
                //                         }
                //                     }
                //                 }
                //             });
                //         });
                //     }
                // };

                salesChartOptions = {
                    maintainAspectRatio: false,
                    animations: false,
                    responsive: true,
                    plugins: {
                        tooltip: {
                            enabled: true
                        },
                        legend: {
                            display: false,
                            onClick: function(e) {
                                e.stopPropagation(); // Menghentikan event klik
                            },
                            title: {
                                padding: 2
                            },

                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            min: new Date(startTime),
                            max: new Date(endTime),
                            type: 'time',
                            time: {
                                unit: "hour",
                                displayFormats: {
                                    hour: 'HH:mm',
                                }
                            },
                        },
                        y: {
                            display: true,
                            min: 0,
                            max: biggestQtyPlan + 5,
                            // max: function(context) {
                            //     dataPlans.forEach(val => {
                            //         let biggestQtyPlan = 0
                            //         if (biggestQtyPlan < val.target_quantity) {
                            //             biggestQtyPlan = val.target_quantity+10;
                            //         }
                            //         return biggestQtyPlan;
                            //     }) // Nilai default jika tidak ada data
                            // },
                            // ticks: {
                            //     stepSize: 5,
                            //     callback: function(value, index, values) {
                            //         if (index % 2 === 0) {
                            //             if (Number.isInteger(value)) {
                            //                 return value;
                            //             } else {
                            //                 return value.toFixed(1);
                            //             }
                            //         } else {
                            //             return '';
                            //         }
                            //     }
                            // },
                        },
                    },
                };

                salesChart = new Chart(salesChartCanvas, {
                    type: 'line',
                    data: salesChartData,
                    options: salesChartOptions,
                    plugins: [alwaysShowTooltip]
                });

                // console.log(salesChartData.datasets[0].data)
                //Membuat data plan produksi
                dataChartObject.forEach((chart, ind) => {
                    chart.forEach(element => {
                        if (element.y !== null) {
                            element.y = (element.y % 1) > 0.99 ? Math.round(element.y) : Math.floor(element
                                .y);
                        }
                    });
                    salesChartData.datasets[ind * 2].data = chart;
                });
                salesChart.update();

            }

            createChart();

            // Fungsi untuk mengubah visibilitas dataset
            function toggleDatasetVisibility(datasetIndex) {
                isTooltipVisible[datasetIndex] = !isTooltipVisible[datasetIndex];

                // Mengubah visibilitas dataset dalam chart
                salesChart.data.datasets[datasetIndex].hidden = !isTooltipVisible[datasetIndex];
                salesChart.update();
            }
            let tanggalSaja = dataPlans[0].date.substring(0, 10); // Mengambil 10 karakter pertama (bagian tanggal)


            function oneSecondFunction() {
                // Inisialisasi saat memuat halaman
                checkBreakTimeModal();

                // console.log("🚀 ~ oneSecondFunction ~ salesChartData:", salesChartData.datasets)

                // Lakukan permintaan ke rute untuk mengambil data terbaru
                axios.get(`api/get-latest-data/${tanggalSaja}`)
                    .then(response => {
                        // console.log("🚀 ~ oneSecondFunction ~ response:", response)

                        //memasukkan data dari api kedalam chart
                        response.data.forEach((chartDataValue, index) => {
                            const indexChartData = (index * 2) + 1;
                            salesChartData.datasets[indexChartData].data = chartDataValue;
                        })

                        // console.log('0', salesChartData.datasets[0].data)
                        // console.log('resss', salesChartData.datasets[1].data)

                        //membuat null data pada chart yang berada di dalam jam break
                        breakTimes1.forEach(breakTime => {
                            salesChartData.datasets.forEach((dataset, index) => {
                                if (index % 2 === 1) {
                                    const breakTimeIndex = dataset.data.findIndex(item => item.x
                                        .endsWith(breakTime));
                                    if (breakTimeIndex !== -1) {
                                        dataset.data[breakTimeIndex].y = null;
                                    }
                                }
                            });
                        });
                        salesChart.update();
                    })
                    .catch(error => {
                        console.error('Gagal melakukan polling data:', error);
                    });

                axios.get(`http://127.0.0.1:3003/api/active-model-modbus`)
                    .then(response => {
                        document.getElementById('activeModel').innerHTML =
                            `<h3>Selected Model: <span style="background-color: ${colorsArray[(response.data.id*2)+1]};">${response.data.bed_models[0]?.name}</span></h3>`;
                    })
                    .catch(error => {
                        console.error('Gagal mendapatkan model yang aktif pada modbus:', error);
                    });

                for (let i = 1; i <= Math.min(dataPlans.length, 6); i++) {
                    const actualQty = document.getElementById(`actualQty${i}`);
                    if (actualQty) {
                        const datasetIndex = 2 * i - 1;
                        const dataset = salesChartData.datasets[datasetIndex]?.data;
                        const yValue = dataset?.[dataset.length - 1]?.y ?? 0; // Gunakan nullish coalescing untuk default 0
                        const yValueReal = yValue !== 0 ? yValue : 0;
                        actualQty.innerHTML =
                            `<strong>${yValueReal}</strong> `;
                    }
                    // if (actualQty) {
                    //     const datasetIndex = 2 * i - 1;
                    //     actualQty.innerHTML =
                    //         `<strong>${salesChartData.datasets[datasetIndex].data[salesChartData.datasets[datasetIndex].data.length - 1].y}</strong> `;
                    // }
                }

            }

            function openModal() {
                breakModal.style.display = 'block';
            }

            function closeModal() {
                breakModal.style.display = 'none';
            }

            // console.log(timeBreakModal)

            function checkBreakTimeModal() {
                const now = new Date();
                const currentHour = now.getHours();
                const currentMinutes = now.getMinutes();

                // Cek apakah saat ini berada dalam waktu istirahat
                for (const breakTime of timeBreakModal) {
                    const startTime = new Date(now);
                    startTime.setHours(...breakTime.start.split(':'));

                    const finishTime = new Date(now);
                    finishTime.setHours(...breakTime.finish.split(':'));

                    if (now >= startTime && now <= finishTime) {
                        openModal();
                        return;
                    }
                }

                // Jika diluar semua jangka waktu break, tutup modal
                closeModal();
            }

            // Mulai polling setiap detik
            setInterval(oneSecondFunction, 1000);
        }
    </script>
@endsection
