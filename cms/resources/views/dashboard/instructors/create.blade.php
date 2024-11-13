@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap">
            @include('dashboard.layouts.navbar')
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body d-flex justify-content-sm-center">
                            <div class=" card" role="dialog" style="width:60%;">
                                <div class="card-header">
                                    <h5 class="title">Add Teacher</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="mt-3" action="{{ route('instructors.store') }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row mb-3">
                                            <!-- Name Input -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="full-name">Name</label>
                                                    <input type="text" class="form-control" id="full-name"
                                                        placeholder="Name" value="{{ old('name') }}"
                                                        name="name">
                                                    @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Phone Input -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="phone-no">Phone Number</label>
                                                    <input type="text" class="form-control" id="phone-no"
                                                        placeholder="Phone Number" name="phone"
                                                        value="{{ old('phone') }}">
                                                    @error('phone')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <!-- Email Input -->
                                            <div class="form-group">
                                                <label class="form-label" for="email">Email Address</label>
                                                <input type="email" class="form-control" id="email"
                                                    name="email" placeholder="Email Address"
                                                    value="{{ old('email') }}">
                                                @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            @role('Admin')
                                            <!-- School Select Input -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">School</label>
                                                    <div class="form-control-wrap">
                                                        <select class="form-select js-select2" name="school_id"
                                                            id="school_id"
                                                            data-placeholder="Select a school">
                                                            <option></option>
                                                            @foreach ($schools as $school)
                                                            <option value="{{ $school->id }}">
                                                                {{ $school->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        @error('school_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @endrole
                                            @role('school')
                                            <input type="hidden" name="school_id" value="{{ $schools[0]->id }}">
                                            @endrole
                                            <div class="col-md-6">
                                                <label class="form-label">Gender</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="gender_id"
                                                        id="gender_id"
                                                        data-placeholder="Select a gender">
                                                        <option></option>

                                                        <option value="boy">Boy</option>
                                                        <option value="girl">Girl</option>

                                                    </select>
                                                    @error('school_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <!-- Password Inputs -->
                                            <div class="col-md-6">
                                                <label class="form-label" for="password">Password</label>
                                                <input type="password" class="form-control"
                                                    id="password" placeholder="Password" name="password">
                                                @error('password')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="confirm-password">Confirm Password</label>
                                                    <input type="password" class="form-control"
                                                        id="confirm-password" placeholder="Confirm Password"
                                                        name="password_confirmation">
                                                    @error('password_confirmation')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-md-12">
                                            <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                <li>
                                                    <button type="submit"
                                                        class="btn btn-primary mb-3 mt-3">Add Teacher</button>
                                                </li>
                                            </ul>
                                        </div>


                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>

@endsection

@section('page_js')
<script>
    $(document).ready(function() {
        $('.js-select2').select2();
        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();

            $.ajax({
                url: '/cms/public/get-groups-student/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $('select[name="class_id"]').empty();
                    $('select[name="class_id"]').append('<option value="">Select a class</option>');

                    $.each(data, function(key, value) {
                        $('select[name="class_id"]').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        });

        $('#class_id').change(function() {
            var selectedClass = $('#class_id').val();
            var selectedSchool = $('#school_id').val();

            // If a class is selected, trigger the modal and fetch data
            if (selectedClass && selectedSchool) {
                // Open the modal popup to show duplicate class names
                $('#classPopup').modal('show');

                // Fetch classes with the same name via AJAX (real data)
                $.ajax({
                    url: '/cms/public/get-duplicate-classes/' + selectedClass + '/' + selectedSchool,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        console.log('AJAX Response:', data);

                        var checkboxList = $('#classCheckboxList');
                        checkboxList.empty();

                        // Populate the modal with checkboxes for duplicate classes
                        $.each(data, function(key, value) {
                            checkboxList.append(`
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="${value.id}" id="class_${value.id}">
                                    <label class="form-check-label" for="class_${value.id}">
                                      ${value.name}  ${value.program.course.name}  ${value.program.stage.name}
                                    </label>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            } else {
                console.error('Class or School is not selected.');
            }
        });

        $('#saveSelectedClasses').click(function() {
            var selectedClasses = [];
            var selectedClassNames = [];

            $('#classCheckboxList input:checked').each(function() {
                selectedClasses.push($(this).val());
                selectedClassNames.push($(this).next('label').text());
            });

            // Add new hidden input fields for each selected group_id (check for duplicates)
            $.each(selectedClasses, function(index, groupId) {
                // Check if the hidden input already exists
                if ($('#group_id_inputs input[value="' + groupId + '"]').length === 0) {
                    // If not, append the new hidden input
                    $('#group_id_inputs').append(`
                <input type="hidden" name="group_id[]" value="${groupId}">
            `);

                    // Also, display the selected class as a chip/tag
                    var selectedClassesContainer = $('#selectedClassesContainer');
                    selectedClassesContainer.append(`
                <span class="badge badge-info mr-2 btn btn-primary class-chip" data-group-id="${groupId}">
                    ${selectedClassNames[index]} <span class="remove-chip" style="cursor:pointer;">&times;</span>
                </span>
            `);
                }
            });

            // Close the modal
            $('#classPopup').modal('hide');

            // Add click event for removing chips
            bindChipRemoval();
        });

        // Function to bind chip removal
        function bindChipRemoval() {
            $('.class-chip').off('click').on('click', function() {
                var groupId = $(this).data('group-id');

                // Remove the hidden input
                $('#group_id_inputs input[value="' + groupId + '"]').remove();

                // Remove the chip from view
                $(this).remove();
            });
        }

    });
</script>

@endsection