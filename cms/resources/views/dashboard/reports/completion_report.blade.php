@if (isset($response))
<div class="filter-form-container">
    <form class="filter-form" method="GET" action="{{ route('reports.completionReport') }}">
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
                <label for="from_date">From Date</label>
                <input type="date" class="form-control" name="from_date" id="from_date">
            </div>
            <div class="col-md-3">
                <label for="to_date">To Date</label>
                <input type="date" class="form-control" name="to_date" id="to_date">
            </div>
            <div class="col-md-3">
                <label for="status">Status</label>
                <select class="form-select js-select2" name="status" id="status">
                    <option value="Completed">Completed</option>
                    <option value="Overdue">Overdue</option>
                    <option value="Pending">Pending</option>
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
<div class="report-container mt-4"></div>
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