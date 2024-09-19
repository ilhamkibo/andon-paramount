@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href={{ asset('css/inputplan.css') }}>
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Small boxes (Stat box) -->
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

        <div class="row mb-3 justify-content-center">
            <div class="col-lg-10 col-12 col-md-10 col-sm-10">
                {{-- <button id="toggleFormButton" class="border-0 bg-transparent"><i class="fas fa-chevron-right"></i>
                <strong>Input Plan</strong>
            </button> --}}
                <button type="button" class="btn btn-primary rounded mt-2" data-toggle="modal" data-target="#exampleModal"
                    onclick="setModalType('production')">+ Production Plan</button>
                <button type="button" class="btn btn-primary rounded mt-2" data-toggle="modal" data-target="#exampleModal"
                    onclick="setModalType('master')">+ Production Master Data</button>
                <button type="button" class="btn btn-primary rounded mt-2" data-toggle="modal" data-target="#exampleModal"
                    onclick="setModalType('operation')">+ Operation Time</button>
            </div>
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row justify-content-center">
            <!-- Up Row -->
            <section class="col-lg-10 connectedSortable">
                <!-- Custom tabs (Charts with tabs)-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Production Plan
                        </h3>
                        {{-- <div class="card-tools">

                    </div> --}}
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <form class="form-inline" method="get" action="{{ route('input-plan') }}">
                                @csrf
                                <label for="date">Select Date:</label>
                                <input type="date" id="date" name="date"
                                    value="{{ request('date') ? request('date') : now()->format('Y-m-d') }}"
                                    class="mx-sm-3 form-control">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                            @php
                                $today = now()->format('Y-m-d'); // Mendapatkan tanggal hari ini
                            @endphp

                            @if (!empty($plans[0]) && (request('date') >= $today || date('Y-m-d', strtotime($plans[0]->date)) == $today))
                                <form class="form-inline px-2" action="{{ route('planData.bulkDestroy') }}" method="POST"
                                    id="bulkDeleteForm">
                                    @csrf
                                    @method('delete')
                                    <!-- Bulk Delete button triggers modal -->
                                    <input type="hidden" name="date"
                                        value="{{ request('date') ? request('date') : now()->format('Y-m-d') }}">
                                    <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deleteConfirmationModal">
                                        Bulk Delete
                                    </button>
                                </form>
                            @endif

                        </div>
                        <table id="productionPlanTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="no">No <i class="fas fa-sort ml-2"></i></th>
                                    <th class="sortable" data-sort="time_spend">Models<i class="fas fa-sort ml-2"></i>
                                    </th>
                                    <th class="sortable" data-sort="quantity">Quantity <i class="fas fa-sort ml-2"></i></th>
                                    <th class="sortable" data-sort="cycle">RT (minutes) <i class="fas fa-sort ml-2"></i>
                                    </th>
                                    <th class="sortable" data-sort="date">Start Datetime <i class="fas fa-sort ml-2"></i>
                                    </th>
                                    <th class="sortable" data-sort="time_spend">Estimated Spent Time<i
                                            class="fas fa-sort ml-2"></i>
                                    </th>
                                    <th class="sortable" data-sort="time_spend">Estimated Finish Time<i
                                            class="fas fa-sort ml-2"></i>
                                    </th>

                                    @if (!empty($plans[0]) && (request('date') == $today || date('Y-m-d', strtotime($plans[0]->date)) == $today))
                                        <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plans as $key => $plan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $plan->bed_models }} </td>
                                        <td>{{ $plan->target_quantity }}</td>
                                        <td>{{ $plan->tact_time }}</td>
                                        <td>{{ $plan->start_time }}</td>
                                        <td>{{ Carbon\Carbon::parse($plan->end_time)->diffInMinutes(Carbon\Carbon::parse($plan->start_time)) }}
                                            minutes
                                        </td>
                                        <td>{{ $plan->end_time }} </td>
                                        @if (!empty($plans[0]) && (request('date') == $today || date('Y-m-d', strtotime($plans[0]->date)) == $today))
                                            <td>
                                                <button type="button" class="btn btn-info" data-toggle="modal"
                                                    data-target="#editPlanModal{{ $plan->id }}">Edit</button>
                                                {{-- <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deletePlanModal{{ $plan->id }}">Delete</button> --}}
                                            </td>
                                        @endif
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="editPlanModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editPlanModalLabel">Edit Data</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('planData.update', $plan->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('post')
                                                        <div class="modal-body">
                                                            <!-- Your edit form goes here -->
                                                            <!-- Example: -->
                                                            <div class="form-group" hidden>
                                                                <label for="line_id">Select Line</label>
                                                                @error('line_id')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                                <select required
                                                                    class="form-control @error('line_id') is-invalid @enderror"
                                                                    id="line_id" name="line_id">
                                                                    @foreach ($lines as $line)
                                                                        <option value={{ $line->id }}
                                                                            {{ $line->id == $plan->line_id ? 'selected' : '' }}>
                                                                            {{ $line->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="bed_models_id" class="form-label">Bed
                                                                    Models</label>
                                                                @error('bed_models_id')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                                <select required
                                                                    class="form-control @error('bed_models_id') is-invalid @enderror"
                                                                    id="bed_models_id_plan" name="bed_models_id">
                                                                    @foreach ($bedModels as $bedmodel)
                                                                        @if ($bedmodel->name !== null)
                                                                            <option value="{{ $bedmodel->id }}"
                                                                                {{ $bedmodel->id == $plan->bed_models_id ? 'selected' : '' }}>
                                                                                {{ $bedmodel->name }}
                                                                                ({{ $bedmodel->tact_time }})
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="target_quantity">Quantity</label>
                                                                @error('target_quantity')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                                <input required type="number"
                                                                    class="form-control @error('target_quantity') is-invalid @enderror"
                                                                    id="target_quantity" name="target_quantity"
                                                                    placeholder="Enter quantity"
                                                                    value={{ $plan->target_quantity }}>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save
                                                                changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Edit Modal -->
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deletePlanModal{{ $plan->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="deletePlanModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deletePlanModalLabel">Delete
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
                                                        <form action="{{ route('planData.destroy', $plan->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Delete Modal -->

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->

            </section>
            <!-- /.Up Row -->
        </div>
        <div class="row justify-content-center">
            <div class="col-10">
                <div class="card p-2">
                    <div class="d-flex align-items-center">
                        <div class="px-2">Timer Settings: </div>
                        <div id="show-section">
                            <strong class="border px-3 py-2 mt-1">
                                <span id="timer-value">{{ $timer->timer }}</span>00 miliseconds
                            </strong>
                            <button class="btn btn-info ml-2" id="edit-btn">Edit</button>
                        </div>
                        <div id="update-section">
                            <form action="{{ route('update-timer', $timer->id) }}" method="POST">
                                @csrf
                                @method('post')
                                <input type="number" required id="timer" value="{{ $timer->timer }}"
                                    name="timer" class="form-control d-inline" style="width: auto;">
                                <button class="btn btn-primary ml-2" type="submit">Submit</button>
                                <div class="btn btn-secondary ml-2" id="cancel-btn">Cancel</div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <!-- bottom row (We are only adding the ID to make the widgets sortable)-->
            <section class="col-lg-6 connectedSortable">
                <!-- Map card -->
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-box mr-1"></i>
                            Production Master Data
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="text" name="search" id="search" class="form-control"
                                placeholder="Search Master Data" />
                        </div>
                        <div class="masterData table-responsive">
                            <table id="masterPartTable" class="table text-center table-bordered my-0 table-hover">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Code</th>
                                        <th>RT</th>
                                        <th>Set Time</th>
                                        <th style="width: 20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="masterPartTableBody">
                                </tbody>
                            </table>
                            <div id="pagination" class="mt-4">
                                <!-- Pagination links will be appended here -->
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body-->
                </div>
                <!-- /.card -->

            </section>
            <section class="col-lg-4 connectedSortable">
                <!-- Map card -->
                <div class="card">
                    <div class="card-header border-0 d-flex align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-bell mr-1"></i>
                            Operation Time
                        </h3>
                    </div>
                    <div class="card-body">

                        <table class="table text-center table-bordered my-0 table-hover">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Operation Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operationNames as $item)
                                    <tr>
                                        <td class="py-1 text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="py-1 text-center align-middle">{{ $item->name }}</td>
                                        <td class="py-1 text-center align-middle">
                                            <button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#editOperationTimeModal{{ $item->id }}">Show</button>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div class="modal fade" id="editOperationTimeModal{{ $item->id }}"
                                        tabindex="-1" role="dialog"
                                        aria-labelledby="editOperationTimeModalLabel{{ $item->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="editOperationTimeModalLabel{{ $item->id }}">
                                                        Operation Times for {{ $item->name }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST"
                                                        action="{{ route('OperationTimeData.update', $item->id) }}">
                                                        @csrf
                                                        <div class="form-group col-md-4">
                                                            <label for="nama_operation">Name</label>
                                                            <input step="300" required type="text"
                                                                class="form-control" id="nama_operation"
                                                                value="{{ $item->name }}" name="nama_operation">
                                                        </div>
                                                        @php $firstIteration = true; @endphp
                                                        <div class="form-row-container">
                                                            <!-- Iterate through operation times -->
                                                            @foreach ($operationTimes->where('name_id', $item->id)->sortBy('id') as $operationTime)
                                                                <div class="form-row">
                                                                    <div class="form-group col-md-1">
                                                                        @if ($firstIteration)
                                                                            <label>No</label>
                                                                        @endif
                                                                        <h6 class="mt-2">{{ $loop->iteration }}</h6>
                                                                    </div>
                                                                    <div class="form-group col-md-3">
                                                                        @if ($firstIteration)
                                                                            <label
                                                                                for="start{{ $operationTime->id }}">Start</label>
                                                                        @endif
                                                                        <input step="300"
                                                                            {{ $operationTime->start ? 'required' : '' }}
                                                                            type="time" class="form-control"
                                                                            id="start{{ $operationTime->id }}"
                                                                            value="{{ $operationTime->start }}"
                                                                            name="start[]">
                                                                    </div>
                                                                    <div class="form-group col-md-3">
                                                                        @if ($firstIteration)
                                                                            <label
                                                                                for="finish{{ $operationTime->id }}">Finish</label>
                                                                        @endif
                                                                        <input step="300"
                                                                            {{ $operationTime->start ? 'required' : '' }}
                                                                            type="time" class="form-control"
                                                                            id="finish{{ $operationTime->id }}"
                                                                            value="{{ $operationTime->finish }}"
                                                                            name="finish[]">
                                                                    </div>
                                                                    <div class="form-group col-md-2">
                                                                        @if ($firstIteration)
                                                                            <label
                                                                                for="status{{ $operationTime->id }}">Status</label>
                                                                        @endif
                                                                        <select class="custom-select" name="status[]">
                                                                            <option value="1"
                                                                                {{ $operationTime->status == '1' ? 'selected' : '' }}>
                                                                                Work</option>
                                                                            <option value="2"
                                                                                {{ $operationTime->status == '2' ? 'selected' : '' }}>
                                                                                Break</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group col-md-2">
                                                                        @if ($firstIteration)
                                                                            <label>Action</label>
                                                                        @endif
                                                                        @if ($operationTime->status)
                                                                            <div>
                                                                                <button type="button"
                                                                                    class="btn btn-danger"
                                                                                    data-toggle="modal"
                                                                                    data-target="#deleteModal{{ $operationTime->id }}">Delete</button>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @php $firstIteration = false; @endphp
                                                            @endforeach
                                                        </div>

                                                        <button type="submit" class="btn btn-info mt-2">Save
                                                            Changes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Confirmation Modals -->
                                    @foreach ($operationTimes->where('name_id', $item->id)->sortBy('start') as $operationTime)
                                        <div class="modal fade" id="deleteModal{{ $operationTime->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="deleteModalLabel{{ $operationTime->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="deleteModalLabel{{ $operationTime->id }}">Delete
                                                            Confirmation</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this operation time?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form method="POST"
                                                            action="{{ route('OperationTimeData.destroy', $operationTime->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <!-- /.card-body-->
                </div>
                <!-- /.card -->
            </section>
            <!-- bottom row -->
        </div>
        <!-- /.row (main row) -->


        <!-- Add Modal by Form -->
        <div class="modal fade" id="addDataModalByForm" tabindex="-1" role="dialog"
            aria-labelledby="addPlanProductionLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="modalProduction">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPlanProductionLabel">Add Data Production Plan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {{-- <form action="{{ route('store-data') }}" method="post">
                        <div class="modal-body">
                            @csrf
                            <label for="row-count">Jumlah Baris:</label>
                            <select id="row-count" onchange="updateRowCount()" name="row-count">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option selected value="6">6</option>
                            </select>
                            <div class="form-group">
                                <label for="date">Date</label>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <input required type="date" name="date"
                                    class="form-control @error('date') is-invalid 
                                @enderror"
                                    id="date">
                            </div>
                            <table class="table-sm">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th hidden>Line ID</th>
                                        <th>Bed Models</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="form-rows">
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="1" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_1') is-invalid @enderror"
                                                id="bed_models_id_1" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="2" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_2') is-invalid @enderror"
                                                id="bed_models_id_2" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="3" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_3') is-invalid @enderror"
                                                id="bed_models_id_3" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="4" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_4') is-invalid @enderror"
                                                id="bed_models_id_4" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="5" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_5') is-invalid @enderror"
                                                id="bed_models_id_5" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 15%"> <input type="number" required class="form-control"
                                                id="queue" name="queue[]" value="6" readonly /> </td>
                                        <td>
                                            <select required
                                                class="form-control @error('bed_models_id_6') is-invalid @enderror"
                                                id="bed_models_id_6" name="bed_models_id[]">
                                                @foreach ($bedModels as $bedmodel)
                                                    @if ($bedmodel->name !== null)
                                                        <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                            ({{ $bedmodel->tact_time }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input required type="number"
                                                class="form-control @error('target_quantity') is-invalid @enderror"
                                                id="target_quantity" name="target_quantity[]"
                                                placeholder="Enter quantity">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form> --}}
                    <form action="{{ route('store-data') }}" method="post">
                        <div class="modal-body">
                            @csrf
                            <label for="row-count">Jumlah Baris:</label>
                            <select id="row-count" name="row-count">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option selected value="6">6</option>
                            </select>
                            <div class="form-group">
                                <label for="date">Date</label>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <input required type="date" name="date"
                                    class="form-control @error('date') is-invalid @enderror" id="date">
                            </div>
                            <table class="table-sm">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th hidden>Line ID</th>
                                        <th>Bed Models</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="form-rows">
                                    @for ($i = 1; $i <= 6; $i++)
                                        <tr>
                                            <td style="width: 15%">
                                                <input type="number" required class="form-control"
                                                    id="queue-{{ $i }}" name="queue[]"
                                                    value="{{ $i }}" readonly />
                                            </td>
                                            <td>
                                                <select required
                                                    class="form-control bed-models-select @error('bed_models_id_{{ $i }}') is-invalid @enderror"
                                                    id="bed_models_id-{{ $i }}" name="bed_models_id[]">
                                                    @foreach ($bedModels as $bedmodel)
                                                        @if ($bedmodel->name !== null)
                                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }}
                                                                ({{ $bedmodel->tact_time }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="width: 35%">
                                                <input required type="number"
                                                    class="form-control @error('target_quantity') is-invalid @enderror"
                                                    id="target_quantity-{{ $i }}" name="target_quantity[]"
                                                    placeholder="Enter quantity">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="modal-content" id="modalOperation">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOperationTimeModalLabel">Insert New Data On Existed Op Time</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('store-operation') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <!-- Your edit form goes here -->
                            <!-- Example: -->
                            <div class="form-group">
                                <label for="opTime">Operation Time</label>
                                <select required class="form-control @error('opTime') is-invalid @enderror" id="opTime"
                                    name="opTime">
                                    @foreach ($operationNames as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start">Start</label>
                                <input step="300" required type="time" class="form-control" id="start"
                                    name="start">
                            </div>
                            <div class="form-group">
                                <label for="finish">Finish</label>
                                <input step="300" required type="time" class="form-control" id="finish"
                                    name="finish">
                            </div>
                            <div class="form-group">
                                <label for="status">status</label>
                                <select required class="form-control @error('status') is-invalid @enderror" id="status"
                                    name="status">
                                    <option value="1">Work</option>
                                    <option value="2">Break</option>
                                </select>
                            </div>
                            <!-- Add other form fields as needed -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Data</button>
                        </div>
                    </form>
                </div>
                <div class="modal-content" id="modalMaster">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMasterModalLabel">Add New Bed Model</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('masterData.add', 101) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <!-- Your edit form goes here -->
                            <!-- Example: -->
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="form-group">
                                <label for="tact_time">Tact Time</label>
                                <input type="number" step="0.01" value="0" class="form-control" id="tact_time"
                                    name="tact_time">
                            </div>
                            <div class="form-group">
                                <label for="setting_time">Setup Time</label>
                                <input type="number" step="0.01" value="0" class="form-control"
                                    id="setting_time" name="setting_time">
                            </div>
                            <!-- Add other form fields as needed -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Add Modal -->
        <!-- Add Modal by Csv -->
        <div class="modal fade" id="addDataModalByCsv" tabindex="-1" role="dialog"
            aria-labelledby="addPlanProductionLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="modalProductionCsv">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upload File Plan Production</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex justify-content-center">
                        @if (session()->has('message'))
                            <div class="row">
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            </div>
                        @endif
                        <form action="{{ route('file.import-plan') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="fileExcel" class="form-label">File Excel</label>
                                <input type="file" class="form-control py-1 @error('fileExcel') is-invalid @enderror"
                                    name="fileExcel" id="fileExcel" accept=".xlsx, .xls, .csv" required>
                                <small>Note <b class="text-danger">*</b>: Type file harus xlsx, xls, dan csv</small>
                                @error('fileExcel')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="resetInput" type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button>
                        {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
                    </div>
                </div>
                <div class="modal-content" id="modalOperationCsv">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upload File Operation Time</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex justify-content-center">
                        @if (session()->has('message'))
                            <div class="row">
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            </div>
                        @endif
                        <form action="{{ route('file.import-time') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="fileExcel" class="form-label">File Excel</label>
                                <input type="file" class="form-control py-1 @error('fileExcel') is-invalid @enderror"
                                    name="fileExcel" id="fileExcel" accept=".xlsx, .xls, .csv" required> <small>Note <b
                                        class="text-danger">*</b>: Type file harus xlsx, xls, dan csv</small>
                                @error('fileExcel')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="resetInput" type="button" class="btn btn-secondary"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>
                <div class="modal-content" id="modalMasterCsv">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upload File Bed Models Master </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex justify-content-center">
                        @if (session()->has('message'))
                            <div class="row">
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            </div>
                        @endif
                        <form action="{{ route('file.import-models') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="fileExcel" class="form-label">File Excel</label>
                                <input type="file" class="form-control py-1 @error('fileExcel') is-invalid @enderror"
                                    name="fileExcel" id="fileExcel" accept=".xlsx, .xls, .csv" required>
                                <small>Note <b class="text-danger">*</b>: Type file harus xlsx, xls, dan csv</small>
                                @error('fileExcel')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add Modal -->
        <!-- Option Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" id="modalOptionAnother">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Method</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex justify-content-center">
                        <button type="button" class="btn btn-success mx-1" data-toggle="modal"
                            data-target="#addDataModalByCsv" style="width: 100%" data-dismiss="modal">
                            By CSV
                        </button>
                        <button type="button" class="btn btn-info mx-1" data-toggle="modal"
                            data-target="#addDataModalByForm" style="width: 100%" data-dismiss="modal">
                            By Form
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <div class="modal-content" id="modalOptionOperation">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Select Method</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="p-1">
                            <span><strong>By CSV</strong></span>
                            <button type="button" class="btn btn-success w-100" data-toggle="modal"
                                data-target="#addDataModalByCsv" data-dismiss="modal">
                                Insert Bulk Data
                            </button>
                        </div>
                        <div class="p-1">
                            <div>
                                <span><strong>By Form</strong></span>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn btn-info w-100" data-toggle="modal"
                                        data-target="#newOpTimeModal" data-dismiss="modal">
                                        Add New Op Time
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-info w-100" data-toggle="modal"
                                        data-target="#addDataModalByForm" data-dismiss="modal">
                                        Insert New Line
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Option Modal -->
        <!-- Add New Op Time Modal -->
        <div class="modal fade" id="newOpTimeModal" tabindex="-1" aria-labelledby="newOpTimeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" id="modalProduction">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newOpTimeModalLabel">Add Data Operation Time</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('store-data-time') }}" method="post">
                        <div class="modal-body">
                            @csrf
                            <label for="row-count2">Jumlah Baris:</label>
                            <select id="row-count2" onchange="updateRowCount2()" name="row-count2">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option selected value="20">20</option>
                            </select>
                            <div class="form-group">
                                <label for="operation_name">Name</label>
                                @error('operation_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <input required type="text" name="operation_name"
                                    class="form-control @error('operation_name') is-invalid 
                                @enderror"
                                    id="operation_name">
                            </div>
                            <div class="table-container">
                                <table class="table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%">No.</th>
                                            <th>Start</th>
                                            <th>Finish</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="form-rows2">
                                        <!-- Rows will be generated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Add New Op Time Modal -->
        <!-- Bulk Delete Modal -->
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to proceed with the deletion of all records dated
                        {{ \Carbon\Carbon::parse(request('date') ?? \Carbon\Carbon::today())->format('F j, Y') }}
                        ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Delete Modal -->

    </div><!-- /.container-fluid -->


@endsection

@section('scripts')
    <script src={{ asset('js/inputplan.js') }}></script>
    <script>
        $(document).ready(function() {
            fetch_customer_data();

            function fetch_customer_data(query = '', page = 1) {
                $.ajax({
                    url: "{{ route('action') }}",
                    method: 'GET',
                    data: {
                        query: query,
                        page: page
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#masterPartTableBody').html(data.table_data);
                        $('#pagination').html(data.pagination);
                    }
                });
            }

            $(document).on('keyup', '#search', function() {
                var query = $(this).val();
                fetch_customer_data(query);
            });

            $(document).on('click', '.pagination a', function(event) {
                event.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                var query = $('#search').val();
                fetch_customer_data(query, page);
            });
        });
    </script>
@endsection
