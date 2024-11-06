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
                                    <div class="modal-content">
                                        <a href="#" class="close" data-bs-dismiss="modal">
                                            <em class="icon ni ni-cross-sm"></em>
                                        </a>
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Edit Student</h5>

                                            <form method="POST" class="mt-3" action="{{ route('students.update', $student->id) }}" enctype="multipart/form-data">
                                                @csrf
                                                @method('put')
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="student-info">
                                                        <div class="row gy-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="full-name">Name</label>
                                                                    <input type="text" class="form-control" id="full-name"
                                                                        placeholder="Name" value="{{ old('name', $student->name) }}"
                                                                        name="name">
                                                                    @error('name')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="email">Email Address</label>
                                                                    <input type="email" class="form-control" id="email"
                                                                        name="email" placeholder="Email Address"
                                                                        value="{{ old('email', $student->email) }}">
                                                                    @error('email')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="phone-no">Phone Number</label>
                                                                    <input type="text" class="form-control" id="phone-no"
                                                                        placeholder="Phone Number" name="phone"
                                                                        value="{{ old('phone', $student->phone) }}">
                                                                    @error('phone')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>



                                                            <!-- Hidden input for school_id -->
                                                            <input type="hidden" name="school_id" id="school_id" value="{{ $student->school_id }}">


                                                            <!-- Password Inputs (Optional) -->
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="password">Password</label>
                                                                    <input type="password" class="form-control"
                                                                        id="password" placeholder="Password" name="password">
                                                                    @error('password')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
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
                                                                            class="btn btn-primary">Update</button>
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

                            <!-- Modal for class selection (like in create page) -->
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
        // Fetch classes based on the student's school and populate chips
        function fetchClasses() {
            var schoolId = $('#school_id').val(); // Get school_id from hidden input
            $.ajax({
                url: '/cms/public/get-groups-student/' + schoolId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    var selectedGroups = {
                        !!json_encode($selectedGroups) !!
                    }; // Pre-selected groups
                    $('#group_id_inputs').empty(); // Clear hidden inputs
                    $('#selectedClassesContainer').empty(); // Clear class chips

                    $.each(data, function(key, value) {
                        console.log(value); // For debugging

                        // Check if 'program', 'course', and 'stage' are defined
                        var program = value.program ? value.program : {};
                        var courseName = program.course ? program.course.name : 'No Course';
                        var stageName = value.stage ? value.stage.name : 'No Stage';

                        var isSelected = selectedGroups.includes(value.id);
                        var chipClass = isSelected ? 'btn-primary' : 'btn-outline-primary';

                        // Append chip buttons for each class (displaying course and stage name)
                        $('#selectedClassesContainer').append(`
                    <span class="badge ${chipClass} mr-2 btn toggleClass" data-id="${value.id}">
                        ${courseName} - ${stageName} - ${value.name}
                    </span>
                `);

                        // If selected, add it to the hidden input
                        if (isSelected) {
                            $('#group_id_inputs').append(`
                        <input type="hidden" name="group_id[]" value="${value.id}">
                    `);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }


        // Toggle class selection on chip click
        $(document).on('click', '.toggleClass', function() {
            var classId = $(this).data('id');
            var chip = $(this);

            if (chip.hasClass('btn-outline-primary')) {
                // Select the class (turn chip to primary and add hidden input)
                chip.removeClass('btn-outline-primary').addClass('btn-primary');
                $('#group_id_inputs').append(`
                    <input type="hidden" name="group_id[]" value="${classId}">
                `);
            } else {
                // Deselect the class (turn chip to outline and remove hidden input)
                chip.removeClass('btn-primary').addClass('btn-outline-primary');
                $(`input[name="group_id[]"][value="${classId}"]`).remove();
            }
        });

        // When class is selected in the dropdown, show the modal
        $('#class_id').change(function() {
            var selectedClass = $('#class_id').val();
            var selectedSchool = $('#school_id').val();

            if (selectedClass) {
                // Open the modal popup to show duplicate class names
                $('#classPopup').modal('show');

                // Fetch duplicate classes via AJAX
                $.ajax({
                    url: '/cms/public/get-duplicate-classes/' + selectedClass + '/' + selectedSchool,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#classCheckboxList').empty();

                        // Populate the modal with checkboxes for duplicate classes
                        $.each(data, function(key, value) {
                            var courseName = value.program && value.program.course ? value.program.course.name : 'No Course';
                            var stageName = value.program.stage ? value.program.stage.name : 'No Stage';

                            $('#classCheckboxList').append(`
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="${value.id}" id="class_${value.id}">
                                    <label class="form-check-label" for="class_${value.id}">
                                        ${courseName} - ${stageName} - ${value.name}
                                    </label>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }
        });

        // Save selected classes from the modal
        $('#saveSelectedClasses').click(function() {
            var selectedClasses = [];
            var selectedClassNames = [];

            $('#classCheckboxList input:checked').each(function() {
                selectedClasses.push($(this).val());
                selectedClassNames.push($(this).next('label').text());
            });

            // Update the hidden input field with selected class IDs
            $('#group_id_inputs').empty();
            $.each(selectedClasses, function(index, classId) {
                $('#group_id_inputs').append(`
                    <input type="hidden" name="group_id[]" value="${classId}">
                `);
            });

            // Display selected classes as chips
            $('#selectedClassesContainer').empty();
            $.each(selectedClassNames, function(index, className) {
                $('#selectedClassesContainer').append(`
                    <span class="badge btn btn-primary mr-2">${className}</span>
                `);
            });

            // Close the modal
            $('#classPopup').modal('hide');
        });

        // Fetch classes on page load
        fetchClasses();
    });
</script>

@endsection