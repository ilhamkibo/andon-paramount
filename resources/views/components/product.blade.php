@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <section class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row justify-content-center">
                @if (!empty($newDataPlans) && isset($newDataPlans[0]))
                @foreach ($newDataPlans as $item)
                <div class="col-lg-4 col-12">
                    <!-- small box -->
                    <div class="small-box bg-white">
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
                                <div class="text-center p-1 m-1 flex-item rounded bg-primary" style="flex-basis: 40%;">
                                    Actual: 320
                                </div>
                                <div class="text-center p-1 m-1 flex-item rounded bg-info" style="flex-basis: 40%;">
                                    RT: {{ $item->tact_time }} min </div>
                            </div>
                        </div>
                        {{-- <div class="icon">
                            <i class="fas fa-industry"></i>
                        </div> --}}
                        {{-- <a href="#" class="small-box-footer">More info <i
                                class="fas fa-arrow-circle-right"></i></a> --}}
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
            </div>
        </div>
    </section>

    <hr>
    <!-- Main row -->
    <div class="row justify-content-center">

        <section class="col-lg-10 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <a href="{{ url()->previous() }}" class="btn btn-secondary mb-2">Back</a>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Production Log History
                    </h3>
                </div><!-- /.card-header -->
                <div class="card-body">
                    <table class="table text-center">
                        <thead>
                            <tr class="border-bottom-0">
                                <th class="sortable" data-sort="no">No <i class="fas fa-sort ml-2"></i></th>
                                <th class="sortable" data-sort="models">Models <i class="fas fa-sort ml-2"></i></th>
                                <th class="sortable" data-sort="date_start">Time Start <i class="fas fa-sort ml-2"></i>
                                <th class="sortable" data-sort="date_start">Time Finish <i class="fas fa-sort ml-2"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($newDataPlans as $key => $value)
                            <tr>
                                <td>
                                    <h5><strong>{{ $loop->iteration }}</strong></h5>
                                </td>
                                <td>
                                    <h5><strong>{{ $value->bed_models }}</strong></h5>
                                </td>
                                <td>
                                    <h5><strong>{{ $value->start_time }}</strong></h5>
                                </td>
                                <td>
                                    <h5><strong>{{ $value->end_time }}</strong></h5>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="py-0">
                                    <table id="logProductionDataTable{{ $loop->iteration }}"
                                        class="table mb-0 table-hover">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($logProductions[$key] as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->created_at }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No data exist!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div><!-- /.card-body -->

            </div>
            <!-- /.card -->
        </section>
    </div>
    <!-- /.row (main row) -->
</div><!-- /.container-fluid -->

<script src={{ asset("js/jquery-3.6.0.min.js") }}></script>

<script>
    $(function () {
        $('#logProductionDataTable1').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
        $('#logProductionDataTable2').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
        $('#logProductionDataTable3').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
        $('#logProductionDataTable4').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
        $('#logProductionDataTable5').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
        $('#logProductionDataTable6').DataTable({
            "paging": true,
            "lengthChange": false,
            "lengthMenu": [ 5, 10, 25, 50, 100 ],
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
        });
    });

    // Variabel global untuk melacak header yang diklik sebelumnya
    document.querySelectorAll('.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            // Hapus tanda panah di semua header
            document.querySelectorAll('.sortable i').forEach(function(icon) {
                if (icon !== th.querySelector('i')) {
                    icon.classList.remove('fa-sort');
                    icon.classList.remove('fa-sort-up');
                    icon.classList.remove('fa-sort-down');
                }
            });

            const icon = th.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-sort')) {
                    icon.classList.remove('fa-sort');
                    icon.classList.add('fa-sort-up');
                } else if (icon.classList.contains('fa-sort-up')) {
                    icon.classList.remove('fa-sort-up');
                    icon.classList.add('fa-sort-down');
                } else {
                    icon.classList.remove('fa-sort-down');
                    icon.classList.add('fa-sort-up');
                }
            } else {
                icon.classList.add('fa-sort-up');
            }
        });
    });
</script>

@endsection