<!-- Mastery Report Tab -->
<div class="tab-pane fade" id="mastery-report" role="tabpanel" aria-labelledby="mastery-report-tab">
    <div class="filter-form-container">
        <form class="filter-form" method="GET" action="{{ route('reports.masteryReport') }}">
            <div class="form-row">
                <div class="col-md-3">
                    <label for="program_id">Program</label>
                    <select class="form-select js-select2" name="program_id" id="program_id">
                        <option value="" selected disabled>Choose a program</option>
                        @foreach ($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row mt-3">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" id="filterUnit"
                            value="unit">
                        <label class="form-check-label" for="filterUnit">Unit</label>
                    </div>
                    <select class="form-select js-select2" name="unit_id" id="unit_id" disabled>
                        <option value="" selected disabled>Choose a unit</option>
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" id="filterLesson"
                            value="lesson">
                        <label class="form-check-label" for="filterLesson">Lesson</label>
                    </div>
                    <select class="form-select js-select2" name="lesson_id" id="lesson_id" disabled>
                        <option value="" selected disabled>Choose a lesson</option>
                        @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}">{{ $lesson->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" id="filterGame"
                            value="game">
                        <label class="form-check-label" for="filterGame">Game</label>
                    </div>
                    <select class="form-select js-select2" name="game_id" id="game_id" disabled>
                        <option value="" selected disabled>Choose a game</option>
                        @foreach ($games as $game)
                        <option value="{{ $game->id }}">{{ $game->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" id="filterSkill"
                            value="skill">
                        <label class="form-check-label" for="filterSkill">Skill</label>
                    </div>
                    <select class="form-select js-select2" name="skill_id" id="skill_id" disabled>
                        <option value="" selected disabled>Choose a skill</option>
                        @foreach ($skills as $skill)
                        <option value="{{ $skill->id }}">{{ $skill->name }}</option>
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
    <div class="report-container mt-4"></div>
</div>

<script>
    $(document).ready(function() {
        $('input[name="filter_type"]').change(function() {
            let selectedFilter = $(this).val();
            $('#unit_id, #lesson_id, #game_id, #skill_id').prop('disabled', true).val('').trigger(
                'change');
            if (selectedFilter == 'unit') {
                $('#unit_id').prop('disabled', false);
            } else if (selectedFilter == 'lesson') {
                $('#lesson_id').prop('disabled', false);
            } else if (selectedFilter == 'game') {
                $('#game_id').prop('disabled', false);
            } else if (selectedFilter == 'skill') {
                $('#skill_id').prop('disabled', false);
            }
        });
    });
</script>

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