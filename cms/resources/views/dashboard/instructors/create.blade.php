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
                        <div class="nk-content-body">
                            @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif
                            <div class="" role="dialog" id="student-add">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content"><a href="#" class="close"
                                            data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Add Teachers</h5>
                                            <form method="POST" class="mt-3" action="{{ route('instructors.store') }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="student-info">
                                                        <div class="row gy-4">
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

                                                            <!-- Email Input -->
                                                            <div class="col-md-6">
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
                                                            <div class="col-md-6">
                                                                <div class="form-group">
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

                                                            @role('Admin')
                                                            <!-- School Select Input -->
                                                            <div class="col-md-6 mt-3">
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

                                                            <!-- Class Select Input -->
                                                            <!--<div class="col-md-6">-->
                                                            <!--    <div class="form-group">-->
                                                            <!--        <label class="form-label">Class</label>-->
                                                            <!--        <div class="form-control-wrap">-->
                                                            <!--            <select class="form-select js-select2" name="class_id" id="class_id" data-placeholder="Select a class">-->
                                                            <!--                <option></option>-->
                                                            <!--                @foreach ($uniqueClasses as $class)-->
                                                            <!--                    <option value="{{ $class->id }}">{{ $class->name }}</option>-->
                                                            <!--                @endforeach-->
                                                            <!--            </select>-->
                                                            <!--            @error('class_id')-->
                                                            <!--                <div class="text-danger">{{ $message }}</div>-->
                                                            <!--            @enderror-->
                                                            <!--        </div>-->
                                                            <!--    </div>-->
                                                            <!--</div>-->

                                                            <!-- Display selected classes as highlighted chips/tags -->
                                                            <!--<div class="col-md-12">-->
                                                            <!--    <div class="form-group">-->
                                                            <!--        <label class="form-label">Selected Classes</label>-->
                                                            <!--        <div id="selectedClassesContainer" class="form-control-wrap">-->
                                                            <!-- Chips for selected classes will be appended here -->
                                                            <!--        </div>-->
                                                            <!--    </div>-->
                                                            <!--</div>-->

                                                            <!-- Hidden input to store selected class IDs -->
                                                            <div id="group_id_inputs"></div>

                                                            <!-- Password Inputs -->
                                                            <div class="col-md-6 mt-0">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="password">Password</label>
                                                                    <input type="password" class="form-control"
                                                                        id="password" placeholder="Password" name="password">
                                                                    @error('password')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mt-0">
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

                                                            <!-- Profile Picture Input -->
                                                            <!-- <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="profile-picture">Profile Picture</label>
                                                                    <input type="file" id="profile-picture" name="parent_image">
                                                                    @error('parent_image')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div> -->

                                                            <!-- Submit Button -->
                                                            <div class="col-md-12">
                                                                <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                    <li>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Create</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal for class selection -->
                            <div class="modal fade" id="classPopup" tabindex="-1" role="dialog"
                                aria-labelledby="classPopupLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="classPopupLabel">Select Classes</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="classSelectionForm">
                                                <!-- Dynamically populated checkboxes -->
                                                <div id="classCheckboxList">
                                                    <!-- Checkboxes will be populated with AJAX -->
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" id="saveSelectedClasses">Save</button>
                                        </div>
                                    </div>
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
        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            console.log(schoolId);

            $.ajax({
                url: '/cms/public/get-groups-student/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    console.log(data);
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

            // Debugging: Check the values of selectedClass and selectedSchool
            console.log('Selected Class:', selectedClass);
            console.log('Selected School:', selectedSchool);

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