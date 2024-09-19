@extends('layouts.main')

@section('content')
    <div class="container-fluid">
        @if (session()->has('sukses'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('sukses') }}
            </div>
        @endif
        @if (session()->has('gagal'))
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
                                <div class="text-center m-1 border py-1 px-2 bg-white shadow-sm rounded">
                                    <div class="border-bottom">
                                        <h5 class="mb-0">
                                            <strong>{{ $loop->iteration . '. ' . $item->bed_models }}</strong>
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col text-center">
                                            <span class="text-danger"><strong>Target Qty</strong></span>
                                            <h3 class="border rounded"><strong>{{ $item->target_quantity }}</strong></h3>
                                        </div>
                                        <div class="col text-center">
                                            <span class="text-info"><strong>Actual Qty</strong></span>
                                            <h3 id="actualQty{{ $loop->iteration }}" class="border rounded">
                                                <strong>{{ $logProductions[$loop->iteration - 1]->where('status', 'Up')->count() }}</strong>
                                            </h3>
                                        </div>
                                    </div>
                                    <div><strong class="text-success">Start Time:</strong> {{ $item->start_time }}</div>
                                    <div class=""><strong class="text-warning">Finish Time:</strong>
                                        {{ $item->end_time }}</div>
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
                        <table id="logProductionDataTable" class="table">
                            <thead>
                                <tr>
                                    <th>Iteration</th>
                                    <th>Number</th>
                                    <th>Models</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Time Up</th>
                                    <th>Time Down</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loggers as $item)
                                    @if ($item->plan && $item->plan->queue && $item->plan->bed_models)
                                        <tr>
                                            <td>{{ $item->plan->queue }}</td>
                                            <td>{{ $item->model }}</td>
                                            <td>{{ $item->plan->bed_models->name }}</td>
                                            <td>{{ $item->status }}</td>
                                            <td>
                                                @if ($item->note)
                                                    <div>
                                                        <span><strong>Problem</strong>: {{ $item->note->problem }} |
                                                        </span>
                                                        <span><strong>Reason</strong>: {{ $item->note->reason }}</span>
                                                    </div>
                                                    <div><small>Op: {{ $item->note->operator }}</small></div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at }}</td>
                                            <td>{{ $item->updated_at ?? '-' }}</td>
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

                                                <div class="modal fade" id="notesModal{{ $item->id }}" tabindex="-1"
                                                    role="dialog" aria-labelledby="notesModalLabel{{ $item->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                                                        <div class="modal-content" id="addAndEditNotes{{ $item->id }}">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="notesModalLabel{{ $item->id }}">
                                                                    Notes for {{ $item->plan->bed_models->name }} at
                                                                    {{ \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }}
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="POST"
                                                                    action="{{ route('store-note', $item->id) }}">
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
                                                                        <textarea required name="reason" id="reason" placeholder="Insert the reason" class="form-control" rows="5">{{ $item->note ? $item->note->reason : '' }}</textarea>
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Submit</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="modal-content" id="deleteNotes{{ $item->id }}">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="notesModalLabel{{ $item->id }}">Delete
                                                                    Confirmation
                                                                </h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete this note?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('delete-note', $item->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button type="submit"
                                                                        class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No data exist!</td>
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

    <script src={{ asset('js/jquery-3.6.0.min.js') }}></script>

    <script>
        $(function() {
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
                buttons: [{
                        extend: 'csv',
                        filename: 'Andon Production List',
                        text: 'CSV',
                        exportOptions: {
                            columns: [2, 3, 5, 6] // Only include columns 2, 3, 6, 7
                        }
                    },
                    {
                        extend: 'excel',
                        filename: 'Andon Production List',
                        text: 'Excel',
                        title: null,
                        exportOptions: {
                            columns: [2, 3, 5, 6] // Only include columns 2, 3, 6, 7
                        }
                    },
                ],
                dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row'<'col-md-7'p>>",
                columnDefs: [{
                    width: '40%',
                    targets: 4
                }]
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
