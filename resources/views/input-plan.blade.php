@extends('layouts.main')

@section('styles')
<style>
    /* .formContainer {
        max-width: 40%;
    } */
</style>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Small boxes (Stat box) -->
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

    <div class="row mb-3 justify-content-center">
        <div class="col-lg-10 col-12 col-md-10 col-sm-10">
            {{-- <button id="toggleFormButton" class="border-0 bg-transparent"><i class="fas fa-chevron-right"></i>
                <strong>Input Plan</strong>
            </button> --}}
            <button type="button" class="btn btn-primary rounded" data-toggle="modal" data-target="#addDataModal"
                onclick="setModalType('production')">+ Production Plan</button>
            <button type="button" class="btn btn-primary rounded" data-toggle="modal" data-target="#addDataModal"
                onclick="setModalType('master')">+ Production Master Data</button>
            <button type="button" class="btn btn-primary rounded" data-toggle="modal" data-target="#addDataModal"
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

                        @if (!empty($plans[0]) && (request('date') >= $today || date('Y-m-d',
                        strtotime($plans[0]->date)) == $today))
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

                                @if (!empty($plans[0]) && (request('date') == $today || date('Y-m-d',
                                strtotime($plans[0]->date)) == $today))
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
                                <td>{{
                                    Carbon\Carbon::parse($plan->end_time)->diffInMinutes(Carbon\Carbon::parse($plan->start_time))}}
                                    minutes
                                </td>
                                <td>{{ $plan->end_time }} </td>
                                @if (!empty($plans[0]) && (request('date') == $today || date('Y-m-d',
                                strtotime($plans[0]->date)) == $today))
                                <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal"
                                        data-target="#editPlanModal{{ $plan->id }}">Edit</button>
                                    {{-- <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deletePlanModal{{ $plan->id }}">Delete</button> --}}
                                </td>
                                @endif
                                <!-- Edit Modal -->
                                <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="editPlanModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editPlanModalLabel">Edit Data</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('planData.update', $plan->id) }}" method="POST">
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
                                                            <option value={{ $line->id }} {{$line->id == $plan->line_id
                                                                ? 'selected' : '' }}>{{ $line->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="bed_models_id" class="form-label">Bed Models</label>
                                                        @error('bed_models_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <select required
                                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                                            id="bed_models_id" name="bed_models_id">
                                                            @foreach ($bedModels as $bedmodel)
                                                            @if ($bedmodel->name !== null)
                                                            <option value="{{ $bedmodel->id }}" {{ ($bedmodel->id ==
                                                                $plan->bed_models_id) ? 'selected' : '' }}>
                                                                {{ $bedmodel->name }} ({{ $bedmodel->tact_time }})
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
                                                            placeholder="Enter quantity" value={{ $plan->target_quantity
                                                        }}>
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
                                <div class="modal fade" id="deletePlanModal{{ $plan->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deletePlanModalLabel" aria-hidden="true">
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
                                                <form action="{{ route('planData.destroy', $plan->id) }}" method="POST">
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
        <!-- bottom row (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-4 connectedSortable">
            <!-- Map card -->
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-1"></i>
                        Production Master Data
                    </h3>
                    <!-- card tools -->
                    {{-- <div class="card-tools">
                        <button type="button" class="btn btn-sm" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div> --}}
                    <!-- /.card-tools -->
                </div>
                <div class="card-body">
                    <div class="masterData">
                        <table id="masterPartTable" class="table text-center table-bordered my-0 table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Code</th>
                                    <th>RT</th>
                                    <th style="width: 20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bedModels as $item)
                                @if ($item->name)
                                <tr>
                                    <td class="py-1 text-center align-middle">{{ $item->id }}</td>
                                    <td class="py-1 text-center align-middle">{{ $item->name }}</td>
                                    <td class="py-1 text-center align-middle">{{ $item->tact_time }}</td>
                                    <td class="py-1 text-center align-middle">
                                        @if ($item->name)
                                        <button type="button" class="btn btn-info" data-toggle="modal"
                                            data-target="#editMasterModal{{ $item->id }}">Edit</button>
                                        {{-- <button type="button" class="btn btn-danger" data-toggle="modal"
                                            data-target="#deleteMasterModal{{ $item->id }}">Delete</button> --}}
                                        @else
                                        <p>Data has not been entered!</p>
                                        @endif
                                    </td>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editMasterModal{{ $item->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="editMasterModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editMasterModalLabel">Edit Data</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('masterData.add', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('post')
                                                    <div class="modal-body">
                                                        <!-- Your edit form goes here -->
                                                        <!-- Example: -->
                                                        <div class="form-group">
                                                            <label for="name">Name</label>
                                                            <input required type="text" class="form-control" id="name"
                                                                value="{{ $item->name }}" name="name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="tact_time">RT</label>
                                                            <input required type="number" step="0.01"
                                                                class="form-control" id="tact_time"
                                                                value="{{ $item->tact_time }}" name="tact_time">
                                                        </div>
                                                        <!-- Add other form fields as needed -->
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
                                    {{-- <div class="modal fade" id="deleteMasterModal{{ $item->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="deleteMasterModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteMasterModalLabel">Delete
                                                        Confirmation
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this item?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('masterData.destroy', $item->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="name" value="">
                                                        <input type="hidden" name="tact_time" value="">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <!-- End Delete Modal -->
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body-->
            </div>
            <!-- /.card -->

        </section>
        <section class="col-lg-6 connectedSortable">
            <!-- Map card -->
            <div class="card">
                <div class="card-header border-0 d-flex align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-1"></i>
                        Operation Time
                    </h3>
                </div>
                <div class="card-body">
                    <table id="operationTimeTable" class="table text-center table-bordered my-0 table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Start</th>
                                <th>~</th>
                                <th>Finish</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operationTimes as $item)
                            <tr>
                                <td class="py-1 text-center align-middle">{{ $item->id }}</td>
                                <td class="py-1 text-center align-middle">{{ substr($item->start, 0, 5) }}</td>
                                <td class="py-1 text-center align-middle">~</td>
                                <td class="py-1 text-center align-middle">{{ substr($item->finish, 0, 5) }}</td>
                                @if ($item->status == 1)
                                <td class="py-1 text-center align-middle text-primary"><strong>Work</strong></td>
                                @else
                                <td class="py-1 text-center align-middle text-danger"><strong>Break</strong></td>
                                @endif
                                <td class="py-1 text-center align-middle">
                                    <button type="button" class="btn btn-info" data-toggle="modal"
                                        data-target="#editOperationTimeModal{{ $item->id }}">Edit</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deleteOperationTimeModal{{ $item->id }}">Delete</button>
                                </td>


                                <!-- Edit Modal -->
                                <div class="modal fade" id="editOperationTimeModal{{ $item->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="editOperationTimeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editOperationTimeModalLabel">Edit Data</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('OperationTimeData.update', $item->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('post')
                                                <div class="modal-body">
                                                    <!-- Your edit form goes here -->
                                                    <!-- Example: -->
                                                    <div class="form-group">
                                                        <label for="start">Start</label>
                                                        <input step="300" required type="time" class="form-control"
                                                            id="start" value={{ $item->start }} name="start">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="finish">Finish</label>
                                                        <input step="300" required type="time" class="form-control"
                                                            id="finish" value={{ $item->finish }} name="finish">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status">status</label>
                                                        <select required
                                                            class="form-control @error('status') is-invalid @enderror"
                                                            id="status" name="status">
                                                            <option value="1" {{ $item->status == 1 ? 'selected' : ''
                                                                }}>Work</option>
                                                            <option value="2" {{ $item->status == 2 ? 'selected' : ''
                                                                }}>Break</option>
                                                        </select>
                                                    </div>
                                                    <!-- Add other form fields as needed -->
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
                                <div class="modal fade" id="deleteOperationTimeModal{{ $item->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="deleteOperationTimeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteOperationTimeModalLabel">Delete
                                                    Confirmation
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this item?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cancel</button>
                                                <form action="{{ route('OperationTimeData.destroy', $item->id) }}"
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
                </div>
                <!-- /.card-body-->
            </div>
            <!-- /.card -->
        </section>
        <!-- bottom row -->
    </div>
    <!-- /.row (main row) -->
    <!-- Add Modal -->
    <div class="modal fade" id="addDataModal" tabindex="-1" role="dialog" aria-labelledby="addPlanProductionLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" id="modalProduction">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlanProductionLabel">Edit Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/store-data" method="post">
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
                            <label for="date">Date</label>@error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input required type="date" name="date" class="form-control @error('date') is-invalid 
                                @enderror" id="date">
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
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%"> <input type="number" required class="form-control"
                                            id="queue" name="queue[]" value="2" readonly /> </td>
                                    <td>
                                        <select required
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%"> <input type="number" required class="form-control"
                                            id="queue" name="queue[]" value="3" readonly /> </td>
                                    <td>
                                        <select required
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%"> <input type="number" required class="form-control"
                                            id="queue" name="queue[]" value="4" readonly /> </td>
                                    <td>
                                        <select required
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%"> <input type="number" required class="form-control"
                                            id="queue" name="queue[]" value="5" readonly /> </td>
                                    <td>
                                        <select required
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%"> <input type="number" required class="form-control"
                                            id="queue" name="queue[]" value="6" readonly /> </td>
                                    <td>
                                        <select required
                                            class="form-control @error('bed_models_id') is-invalid @enderror"
                                            id="bed_models_id" name="bed_models_id[]">
                                            @foreach ($bedModels as $bedmodel)
                                            @if ($bedmodel->name !== null)
                                            <option value="{{ $bedmodel->id }}">{{ $bedmodel->name }} ({{
                                                $bedmodel->tact_time }})</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input required type="number"
                                            class="form-control @error('target_quantity') is-invalid @enderror"
                                            id="target_quantity" name="target_quantity[]" placeholder="Enter quantity">
                                    </td>
                                </tr>
                                <!-- Baris formulir dinamis akan ditambahkan di sini -->
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
                    <h5 class="modal-title" id="addOperationTimeModalLabel">Edit Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/store-operation" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Your edit form goes here -->
                        <!-- Example: -->
                        <div class="form-group">
                            <label for="start">Start</label>
                            <input step="300" required type="time" class="form-control" id="start" name="start">
                        </div>
                        <div class="form-group">
                            <label for="finish">Finish</label>
                            <input step="300" required type="time" class="form-control" id="finish" name="finish">
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
                    <h5 class="modal-title" id="addOperationTimeModalLabel">Edit Data</h5>
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
                            <input type="text" class="form-control" id="name" value="{{ $item->name }}" name="name">
                        </div>
                        <div class="form-group">
                            <label for="tact_time">RT</label>
                            <input type="number" step="0.01" class="form-control" id="tact_time"
                                value="{{ $item->tact_time }}" name="tact_time">
                        </div>
                        <!-- Add other form fields as needed -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save
                            changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->
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
                    Are you sure you want to proceed with the deletion of all records dated {{
                    \Carbon\Carbon::parse(request('date') ??
                    \Carbon\Carbon::today())->format('F j, Y') }}
                    ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Modal -->

</div><!-- /.container-fluid -->
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script src={{ asset("js/jquery-3.6.0.min.js") }}></script>

<script>
    function setModalType(type) {
        if (type === 'production') {
            // Show fields relevant to Plan Production
            document.getElementById('modalProduction').style.display = 'block';
            document.getElementById('modalOperation').style.display = 'none';
            document.getElementById('modalMaster').style.display = 'none';
        } else if (type === 'operation') {
            document.getElementById('modalProduction').style.display = 'none';
            document.getElementById('modalMaster').style.display = 'none';
            document.getElementById('modalOperation').style.display = 'block';
            // Show fields relevant to Operation Time
        } else if (type === 'master') {
            document.getElementById('modalMaster').style.display = 'block';
            document.getElementById('modalProduction').style.display = 'none';
            document.getElementById('modalOperation').style.display = 'none';
        }
    }



    $(function () {
        $('#productionPlanTable').DataTable({
            "paging": false,
            "lengthChange":false,
            "lengthMenu": [6],
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

        // Tambahkan kelas CSS ke dropdown panjang data
        $('.dataTables_length select').addClass('custom-select');

        // Atur margin atas dropdown agar berada di tengah secara vertikal
        $('.dataTables_length select.custom-select').css('margin-top', '6px');

        // Tambahkan kelas CSS ke tombol pencarian
        $('.dataTables_filter input').addClass('custom-search');

        // Atur margin atas tombol pencarian agar sejajar dengan dropdown
        $('.dataTables_filter input.custom-search').css('margin-top', '6px');
    });

    $(function () {
        $('#masterPartTable').DataTable({
            "paging": false,
            "scrollY": "250px",
            "scrollCollapse": true,
            "lengthChange": true,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
            initComplete: function () {
                $('#masterPartTable_wrapper > .row:first').remove();
            }
        });

    });

    $(function () {
        $('#operationTimeTable').DataTable({
            "paging": false,
            "scrollY": "250px",
            "scrollCollapse": true,
            "lengthChange": true,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": true,
            "responsive": true,
            buttons: [],
            dom: "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
                "<'row'<'col-md-12'tr>>" +
                "<'row'<'col-md-7'p>>",
            initComplete: function () {
                $('#operationTimeTable_wrapper > .row:first').remove();
            }
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

<script>
    function updateRowCount() {
        var rowCount = document.getElementById("row-count").value;
        var table = document.getElementById("form-rows");

        // Hide all rows
        var allRows = table.getElementsByTagName("tr");
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].style.display = 'none';
            disableFormElements(allRows[i]);
        }

        // Show selected number of rows
        for (var i = 0; i < rowCount; i++) {
            var currentRow = allRows[i];
            currentRow.style.display = 'table-row';
            enableFormElements(currentRow, i); // Passing index to generate unique IDs
        }
    }

    function disableFormElements(row) {
        var formElements = row.querySelectorAll("input, select");
        for (var i = 0; i < formElements.length; i++) {
            formElements[i].disabled = true;
        }
    }

    function enableFormElements(row, index) {
        var formElements = row.querySelectorAll("input, select");
        for (var i = 0; i < formElements.length; i++) {
            formElements[i].disabled = false;

            // Generate unique IDs based on the row index
            var oldId = formElements[i].id;
            formElements[i].id = oldId + '-' + index;
        }
    }

    $(document).ready(function () {
        // Submit form when modal delete button is clicked
        $('#deleteConfirmationModal').on('click', '.btn-danger', function () {
            // Ambil tanggal dari input hidden
            var date = $('#bulkDeleteForm input[name="date"]').val();

            // Tambahkan tanggal ke formulir penghapusan
            $('#bulkDeleteForm').append('<input type="hidden" name="date" value="' + date + '">');

            // Submit formulir penghapusan
            $('#bulkDeleteForm').submit();
        });
    });
</script>
@endsection