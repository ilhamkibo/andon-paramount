@extends('layouts.main')

@section('content')
<div class="container-fluid">
    @if(session()->has('sukses'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('sukses') }}
    </div>
    @endif
    @if(session()->has('gagal'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('gagal') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="m-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="row justify-content-center">
        <div class="col-lg-10">
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
                    <div class="text-center m-1 border py-1 px-2 bg-white shadow-sm rounded">
                        <div class="border-bottom">
                            <h5 class="mb-0"><strong>{{ $loop->iteration.'. '.$item->bed_models }}</strong></h5>
                        </div>
                        <div class="row">
                            <div class="col text-center">
                                <span class="text-danger"><strong>Target Qty</strong></span>
                                <h3 class="border rounded"><strong>{{ $item->target_quantity }}</strong></h3>
                            </div>
                            <div class="col text-center">
                                <span class="text-info"><strong>Actual Qty</strong></span>
                                <h3 id="actualQty{{ $loop->iteration }}" class="border rounded"><strong>-</strong></h3>
                            </div>
                        </div>
                        <div><strong class="text-success">Start Time:</strong> {{ $item->start_time }}</div>
                        <div class=""><strong class="text-warning">Finish Time:</strong> {{ $item->end_time }}</div>
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
            <a href="{{ route('production') }}" class="btn btn-secondary mb-2">Back</a>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Production Log History
                    </h3>
                </div><!-- /.card-header -->
                <div class="card-body">
                    {{-- <table class="table text-center">
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
                    </table> --}}
                    <table id="logProductionDataTable" class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Models</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loggers as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->plan->bed_models->name }}</td>
                                <td>{{ $item->status }}</td>
                                <td>
                                    @if ($item->note)
                                    <div>
                                        <span><strong>Problem</strong>: {{ $item->note->problem }} | </span>
                                        <span><strong>Reason</strong>: {{ $item->note->reason }}</span>
                                    </div>
                                    <div><small>Op: {{ $item->note->operator }}</small></div>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    @if ($item->note)
                                    <button type="button" class="btn btn-info" data-toggle="modal"
                                        data-target="#notesModal{{ $item->id }}"
                                        onclick="setModalType('insert', {{ $item->id }})">Show</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#notesModal{{ $item->id }}"
                                        onclick="setModalType('delete', {{ $item->id }})">Delete</button>
                                    @else
                                    <button type="button" class="btn btn-warning" data-toggle="modal"
                                        data-target="#notesModal{{ $item->id }}"
                                        onclick="setModalType('insert', {{ $item->id }})">Add
                                        Notes</button>
                                    @endif

                                    <div class="modal fade" id="notesModal{{ $item->id }}" tabindex="-1" role="dialog"
                                        aria-labelledby="notesModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable" role="document">
                                            <div class="modal-content" id="addAndEditNotes{{ $item->id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="notesModalLabel{{ $item->id }}">
                                                        Notes for {{ $item->plan->bed_models->name }} at {{
                                                        \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('store-note', $item->id) }}">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label for="operator">Operator</label>
                                                            <input required type="text" class="form-control"
                                                                id="operator" name="operator"
                                                                value="{{ $item->note ? $item->note->operator : '' }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="problem">Problem</label>
                                                            <input required type="text" class="form-control"
                                                                id="problem" name="problem"
                                                                value="{{ $item->note ? $item->note->problem : '' }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="reason">Reason</label>
                                                            <textarea required name="reason" id="reason"
                                                                placeholder="Insert the reason" class="form-control"
                                                                rows="5">{{ $item->note ? $item->note->reason : '' }}</textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="modal-content" id="deleteNotes{{ $item->id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="notesModalLabel{{ $item->id }}">Delete
                                                        Confirmation
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this plan?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('delete-note', $item->id) }}" method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No data exist!</td>
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
    // $(function () {
    //     $('#logProductionDataTable1').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    //     $('#logProductionDataTable2').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    //     $('#logProductionDataTable3').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    //     $('#logProductionDataTable4').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    //     $('#logProductionDataTable5').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    //     $('#logProductionDataTable6').DataTable({
    //         "paging": true,
    //         "lengthChange": false,
    //         "lengthMenu": [ 5, 10, 25, 50, 100 ],
    //         "searching": false,
    //         "ordering": true,
    //         "info": false,
    //         "autoWidth": true,
    //         "responsive": true,
    //         buttons: [],
    //         dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
    //             "<'row'<'col-md-12'tr>>" +
    //             "<'row'<'col-md-7'p>>",
    //     });
    // });

    $(function () {
        $('#logProductionDataTable').DataTable({
            "paging": true,
            // "scrollY": "250px",
            // "scrollCollapse": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
            columnDefs: [{ width: '40%', targets: 3 }]

        });

    });

    function setModalType(type, id) {
    if (type === 'insert') {
        // Show fields relevant to Plan Production
        document.getElementById('addAndEditNotes' + id).style.display = 'block';
        document.getElementById('deleteNotes' + id).style.display = 'none';
    } else if (type === 'delete') {
        document.getElementById('addAndEditNotes' + id).style.display = 'none';
        document.getElementById('deleteNotes' + id).style.display = 'block';
    } 
}

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