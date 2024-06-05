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
    <!-- Small boxes (Stat box) -->
    <section class="row justify-content-center">
        <div class="col-12">
            <div class="row justify-content-center">
                @if (!empty($newDataPlans) && isset($newDataPlans[0]))
                @foreach ($newDataPlans as $item)
                <div class="d-flex flex-row justify-content-between">
                    <!-- small box -->
                    {{-- <div class="small-box bg-white">
                        <h4 class="text-center px-2 py-2 mb-0 bg-secondary rounded-top"> {{$loop->iteration.'.
                            '.$item->bed_models }}
                        </h4>
                        <div class="inner">
                            <div class="d-flex flex-wrap justify-content-around align-items-center">
                                <p style="flex-basis: 40%;" class="text-center mb-1 p-1 rounded bg-dark">{{
                                    $item->start_time }}</p>
                                <p style="flex-basis: 40%;" class="text-center mb-1 p-1 rounded bg-dark">{{
                                    $item->end_time }}</p>
                            </div>
                            <div class="d-flex flex-wrap justify-content-around align-items-center">
                                <div class="text-center p-1 m-1 flex-item rounded bg-danger" style="flex-basis: 40%;">
                                    Target: {{ $item->target_quantity }} </div>
                                <div id="actualQty{{ $loop->iteration }}"
                                    class="text-center p-1 m-1 flex-item rounded bg-primary" style="flex-basis: 40%;">
                                    Actual: 0
                                </div>
                                <div class="text-center p-1 m-1 flex-item rounded bg-info" style="flex-basis: 40%;">
                                    RT: {{ $item->tact_time }} min </div>
                            </div>
                        </div>
                    </div> --}}
                    {{-- <div class="card">
                        <div class="card-header border-0 pt-1">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Production Summary
                        </div>
                        <div class="card-body pt-0 pb-1">
                            <table class="table table-striped">
                                <thead>
                                    <tr class="py-0 my-0">
                                        <th class="py-0 my-0">No</th>
                                        <th class="py-0 my-0">Model</th>
                                        <th class="py-0 my-0">RT</th>
                                        <th class="py-0 my-0">Start Time</th>
                                        <th class="py-0 my-0">Finish Time</th>
                                        <th class="py-0 my-0">Target</th>
                                        <th class="py-0 my-0">Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($newDataPlans as $item)
                                    <tr class="py-0 my-0">
                                        <td class="py-0 my-0">1</td>
                                        <td class="py-0 my-0">1</td>
                                        <td class="py-0 my-0">1</td>
                                        <td class="py-0 my-0">2024-09-09 09:90:90</td>
                                        <td class="py-0 my-0">2024-09-09 09:90:90</td>
                                        <td class="py-0 my-0">1</td>
                                        <td class="py-0 my-0">1</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div> --}}
                    <div class="text-center mx-1 border py-1 px-2 bg-white shadow-sm rounded">
                        <div class="border-bottom">
                            <h5 class="mb-0"><strong>{{ $loop->iteration.'. '.$item->bed_models }}</strong></h5>
                        </div>
                        <div class="row">
                            <div class="col text-center">
                                <span><strong>Target Qty</strong></span>
                                <h4 class="border rounded"><strong>{{ $item->target_quantity }}</strong></h4>
                            </div>
                            <div class="col text-center">
                                <span><strong>Actual Qty</strong></span>
                                <h4 id="actualQty{{ $loop->iteration }}" class="border rounded"><strong>2</strong></h4>
                            </div>
                        </div>
                        <div><strong>Start Time:</strong> {{ $item->start_time }}</div>
                        <div class=""><strong>Finish Time:</strong> {{ $item->end_time }}</div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="small-box bg-white">
                    <h4 class="text-center px-4 py-2 mb-0 bg-secondary rounded-top"> Data Plan tidak tersedia.
                    </h4>
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
    <section class="row mb-3 justify-content-center">
        <div class="col-lg-10">
            <form class="form-inline" method="get" action="{{ route('production') }}">
                @csrf
                <div hidden>
                    <label for="line_id">Line Number:</label>
                    <select name="line_id" class="mx-sm-3 form-control">
                        <option value="1" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id ==
                            '1')
                            selected @endif>Line 1</option>
                        <option value="2" @if (!empty($dataPlans) && isset($dataPlans[0]) && $dataPlans[0]->line_id ==
                            '2')
                            selected @endif>Line 2</option>
                        <!-- Tambahkan pilihan lainnya sesuai kebutuhan -->
                    </select>
                </div>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date"
                    value="{{ (!empty($dataPlans) && isset($dataPlans[0])) ? date('Y-m-d', strtotime($dataPlans[0]->date)) : '' }}"
                    class="mx-sm-3 form-control">
                <button type="submit" class="btn btn-primary">Submit</button>

                <!-- Button trigger modal -->
                {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                    Launch demo modal
                </button> --}}
            </form>
        </div>
    </section>
    <section class="row justify-content-center">
        <div class="col-lg-4">
            @if($message !== '')
            <div class="alert alert-danger text-center">{{ $message }}</div>
            @endif
        </div>
        <div class="col-lg-10">
            <!-- Custom tabs (Charts with tabs)-->

            <div class="card">
                <div class="d-flex py-2 px-3 border-bottom align-items-center justify-content-between">
                    <h3 class="m-0 d-flex align-items-center" style="font-size: 1.2rem">
                        <i class="fas fa-chart-line mr-1"></i>
                        @if (!empty($dataPlans) && isset($dataPlans[0]))
                        {{-- <span>Plan Production Line {{ $dataPlans[0]->line_id }} Chart</span> --}}
                        <span>Plan Production Chart | <a
                                href="line/{{ \Carbon\Carbon::parse($dataPlans[0]->date)->format('Y-m-d') }}">Details
                                data <i class="fas fa-arrow-circle-right"></i></a>
                        </span>
                        @else
                        <span>Plan Production Chart </span>
                        @endif
                    </h3>
                    @php
                    $today = now()->format('Y-m-d'); // Mendapatkan tanggal hari ini
                    @endphp

                    @if (!empty($newDataPlans[0]) && (request('date') == $today || date('Y-m-d',
                    strtotime($newDataPlans[0]->date)) == $today))
                    <div id="activeModel">
                        <h3>Selected Model: -</h3>
                    </div>
                    @endif
                </div><!-- /.card-header -->
                <div class="card-body">
                    <div class="tab-content p-0">
                        <!-- Morris chart - Sales -->
                        <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 50vh;">
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
                {{-- <div class="card-footer bg-transparent">
                    <div class="row">
                        <div class="col-4">
                            <a href="/power/51">More details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                        <!-- ./col -->
                    </div>
                    <!-- /.row -->
                </div> --}}
            </div>
            <!-- /.card -->

        </div>
    </section>
    <!-- /.row (main row) -->
</div><!-- /.container-fluid -->

<div id="breakModal" class="modal">
    <div class="modal-content">
        {{-- <span class="close" id="closeBtn" onclick="closeModal()">&times;</span> --}}
        <img src={{ asset("assets/image/break-time.png") }} alt="">
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
        isTooltipVisible = [true, true, true, true, true, true, true, true, true, true, true, true]; //variabel untuk visibilitas tooltip
    // const breakValue = {!! $breakValue ?? 'null' !!}; // Memasukkan data dari kontroler 

    if (dataPlans.length > 0) {
        // Menggunakan format YYYY-MM-DD
        const inputDate = new Date(dataPlans[0].date);
        // Tambahkan 7 jam ke waktu
        inputDate.setHours(inputDate.getHours() + 7);
        const currentDate = inputDate.toISOString().split('T')[0];
        const startTime = new Date(`${currentDate} ${startTimeString}`);
        const endTime = new Date(`${currentDate} ${endTimeString}`);
        endTime.setMinutes(endTime.getMinutes() + 10);
        startTime.setMinutes(startTime.getMinutes() - 10);

        const colorsArray = [
            "#1f78b4", // Biru
            "#33a02c", // Hijau
            "#e31a1c", // Merah
            "#ff7f0e", // Jingga
            "#6a3d9a", // Ungu
            "#fb9a99", // Cokelat
            "#a6cee3", // Biru Muda
            "#d2cfa6", // Oranye
            "#cab2d6", // Ungu Muda
            "#6b6ecf", // Biru Tua
            "#b2df8a", // Hijau Muda
            "#993d9a"  // Merah Muda
        ];

        let labels = ["2023-10-30 06:52:06", "2023-10-30 07:52:06", "2023-10-30 08:52:06", "2023-10-30 09:52:06", "2023-10-30 10:52:06"];
        const datasets = [];

        for (let i = 0; i < dataPlans.length * 2; i++) {
            const isPlanDataset = i % 2 === 0;

            if (biggestQtyPlan < dataPlans[i]?.target_quantity) {
                biggestQtyPlan = dataPlans[i]?.target_quantity;
            }

            datasets.push({
                label: `${isPlanDataset ? 'Plan' : 'Actual'} ${dataPlans[Math.floor(i/2)].bed_models.name}`,
                data: [
                    { x: new Date("2022-01-13 08:35:00"), y: 0 },
                    { x: new Date("2022-01-13 09:00:00"), y: 0 },
                    { x: new Date("2022-01-13 09:30:00"), y: 0 },
                ],
                // backgroundColor: isPlanDataset ? "#FF5733" : "#3366FF",
                // borderColor: isPlanDataset ? "#FF5733" : "#3366FF",
                backgroundColor: colorsArray[i],
                borderColor: colorsArray[i],
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 0
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
                            const { x, y } = datapoint.tooltipPosition();
                            if (chart.data.datasets[i].data[index].y !== null && (isTooltipVisible[i])) {
                                if(i % 2 === 0) {
                                    if ((typeof chart.data.datasets[i].data[index].x === 'string' &&
                                        forTooltip1.some(time => chart.data.datasets[i].data[index].x.endsWith(time))) || index == chart.data.datasets[i].data.length - 1 || index == 0) {
                                        const text = chart.data.datasets[i].data[index].y;
                                        const textWidth = chart.ctx.measureText(text).width;
    
                                        if (i !== chart.getDatasetMeta(i).data[index].datasetIndex) {
                                            chart.ctx.fillStyle = dataset.backgroundColor;
                                        } else {
                                            chart.ctx.fillStyle = dataset.backgroundColor;
                                        }
    
                                        chart.ctx.fillRect(x - ((textWidth + 10) / 2), y - 25, textWidth + 10, 20);
    
                                        chart.ctx.beginPath();
                                        chart.ctx.moveTo(x, y);
                                        chart.ctx.lineTo(x - 5, y - 5);
                                        chart.ctx.lineTo(x + 5, y - 5);
                                        chart.ctx.fill();
    
                                        chart.ctx.font = '15px Arial';
                                        chart.ctx.fillStyle = 'white';
                                        chart.ctx.fillText(text, x - (textWidth / 2), y - 13);
                                    }
                                } else {
                                    if (typeof chart.data.datasets[i].data[index].x === 'string' &&
                                        forTooltip1.some(time => chart.data.datasets[i].data[index].x.endsWith(time) || index == chart.data.datasets[i].data.length - 1)
                                    ) {
                                        const text = chart.data.datasets[i].data[index].y;
                                        const textWidth = chart.ctx.measureText(text).width;
    
                                        if (i !== chart.getDatasetMeta(i).data[index].datasetIndex) {
                                            chart.ctx.fillStyle = dataset.backgroundColor;
                                        } else {
                                            chart.ctx.fillStyle = dataset.backgroundColor;
                                        }
    
                                        chart.ctx.fillRect(x - ((textWidth + 10) / 2), y + 5, textWidth + 10, 20);
    
                                        chart.ctx.beginPath();
                                        chart.ctx.moveTo(x, y);
                                        chart.ctx.lineTo(x - 5, y + 5);
                                        chart.ctx.lineTo(x + 5, y + 5);
                                        chart.ctx.fill();
    
                                        chart.ctx.font = '15px Arial';
                                        chart.ctx.fillStyle = 'white';
                                        chart.ctx.fillText(text, x - (textWidth / 2), y + 16);
                                    }
                                }
                            }
                        });
                    });
                }
            };
    
            salesChartOptions = {
                maintainAspectRatio: false,
                animations: false,
                responsive: true,
                plugins: {
                    tooltip: {
                        enabled: false
                    },
                    legend: {
                        display: true,
                        onClick: function (e) {
                            e.stopPropagation(); // Menghentikan event klik
                        },
                        title: {
                            padding: 2
                        }
                        // onClick: function(e, legendItem) {
                        //     const datasetIndex = legendItem.datasetIndex;
                        //     toggleDatasetVisibility(datasetIndex);
                        // }
                        // onClick(e, legendItem, legend) {
                        //     const index = legendItem.datasetIndex;
                        //     const ci = legend.chart;
                        //     // Mendapatkan metadata dari semua dataset
                        //     const datasetMeta = salesChart.getSortedVisibleDatasetMetas();
                        //     const legendItems = salesChart.legend.legendItems;
                        //     let a = 20;
    
                        //     // Mendapatkan semua indeks dari legenda
                        //     if (ci.isDatasetVisible(index) && a != index) {
                        //         datasetMeta.map(meta => {
                        //             if (meta.index != index) {
                        //                 ci.hide(meta.index);
                        //                 legendItem.hidden = true;
                        //                 a=index;
                        //             }
                        //         }); 
                        //     } else if(ci.isDatasetVisible(index) && a == index) {
                        //         legendItems.map(meta => {
                        //             if(!ci.isDatasetVisible(meta.datasetIndex)) {
                        //                 ci.show(meta.datasetIndex);
                        //                 legendItem.hidden = false;
                        //             }
                        //         }); 
                        //     } else {
                        //         ci.show(index);
                        //         legendItem.hidden = false;
                        //         a=index;
                        //     }       
                        // },
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
                        max: biggestQtyPlan+5,
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
                        element.y = (element.y % 1) > 0.99 ? Math.round(element.y) : Math.floor(element.y);
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
            checkBreakTime();

            // Lakukan permintaan ke rute untuk mengambil data terbaru
            axios.get(`api/get-latest-data/${tanggalSaja}`)
            .then(response => {
                // console.log("🚀 ~ oneSecondFunction ~ response:", response)

                //memasukkan data dari api kedalam chart
                response.data.forEach((chartDataValue,index) => {
                    const indexChartData = (index*2)+1;
                    salesChartData.datasets[indexChartData].data = chartDataValue;
                })
                
                // console.log('0', salesChartData.datasets[0].data)
                // console.log('resss', salesChartData.datasets[1].data)
                
                //membuat null data pada chart yang berada di dalam jam break
                breakTimes1.forEach(breakTime => {
                    salesChartData.datasets.forEach((dataset, index) => {
                        if (index % 2 === 1) {
                            const breakTimeIndex = dataset.data.findIndex(item => item.x.endsWith(breakTime));
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
                console.log("🚀 ~ oneSecondFunction ~ response:", response.data.bed_models[0]?.name)
                document.getElementById('activeModel').innerHTML = `<h3>Selected Model: <span style="background-color: ${colorsArray[(response.data.id*2)+1]};">${response.data.bed_models[0]?.name}</span></h3>`;
            })
            .catch(error => {
                console.error('Gagal mendapatkan model yang aktif pada modbus:', error);
            });

            for (let i = 1; i <= Math.min(dataPlans.length, 6); i++) {
                const actualQty = document.getElementById(`actualQty${i}`);

                if (actualQty) {
                    const datasetIndex = 2 * i - 1;
                    actualQty.innerHTML = `<strong>${salesChartData.datasets[datasetIndex].data[salesChartData.datasets[datasetIndex].data.length - 1].y}</strong> `;
                }
            }

        }   

        function openModal() {
            breakModal.style.display = 'block';
        }

        function closeModal() {
            breakModal.style.display = 'none';
        }

        // console.log(timeBreakModal)
        
        function checkBreakTime() {
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