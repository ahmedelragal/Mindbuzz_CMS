@if (isset($data))
<div class="filter-form-container">
    <form class="filter-form" method="GET" action="{{ route('reports.numOfTrialsReport') }}">
        <div class="form-row">
            <div class="col-md-3">
                <label for="program_id">Program</label>
                <select class="form-select js-select2" name="program_id" id="program_id">
                    @foreach ($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="unit_id">Unit</label>
                <select class="form-select js-select2" name="unit_id" id="unit_id">
                    @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="lesson_id">Lesson</label>
                <select class="form-select js-select2" name="lesson_id" id="lesson_id">
                    @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}">{{ $lesson->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="game_id">Game</label>
                <select class="form-select js-select2" name="game_id" id="game_id">
                    @foreach ($games as $game)
                    <option value="{{ $game->id }}">{{ $game->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row mt-3">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
</div>
<div class="report-container mt-4">
    <!-- Number of Trials Report -->
    <h4>Number of Trials Report</h4>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Test</th>
                <th>Start Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['tprogress'] as $trial)
            <tr>
                <td>{{ $trial['test_name'] }}</td>
                <td>{{ $trial['start_date'] }}</td>
                <td>{{ $trial['due_date'] }}</td>
                <td>{{ $trial['status'] == 1 ? 'Completed' : (new Date($trial['due_date']) < new Date() ? 'Overdue' : 'Pending') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="alert alert-warning">No data available</div>
@endif


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
<!-- SweetAlert validation messages -->
@if($errors->any())
<script>
    Swal.fire({
        title: 'Error!',
        text: '{{ implode('\
        n ', $errors->all()) }}',
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif

@if(session('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonText: 'Ok'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        title: 'Error!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif
@endsection