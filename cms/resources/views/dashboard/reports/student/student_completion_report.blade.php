@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        <!-- Sidebar -->
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap">
            <!-- Navbar -->
            @include('dashboard.layouts.navbar')
            <!-- Main Content -->
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="card">
                                <div class="card-header" style="display:flex; justify-content: space-between; align-items:center;">
                                    <h5 class="title">Student Completion Report</h5>
                                    @if (isset($counts))
                                    <div class="d-flex" style="gap: 5px;">
                                        <button id="generate-pdf" class="btn btn-primary">Download PDF</button>
                                        <button id="generate-excel" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
                                    </div>
                                    @endif
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.completionReport') }}" id="page-form">
                                        <div class="row">
                                            <!-- School Filter -->
                                            @role('Admin')
                                            <div class="col-md-4">
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id" required>
                                                    <option value="" selected disabled>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endrole
                                            @if(auth()->user()->hasRole('school') || auth()->user()->hasRole('Cordinator'))
                                            <input type="hidden" name="school_id" id="school_id" value="{{ auth()->user()->school_id }}">
                                            @endif


                                            <div class="col-md-4">
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id" id="student_id" required>
                                                    <option value="" selected disabled>No Available Students</option>
                                                </select>
                                            </div>
                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" disabled selected>No Available Programs</option>
                                                </select>
                                            </div>

                                            @role('school')
                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status">Select Status</label>
                                                <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" selected {{ old('status', $request['status'] ?? '') == '' ? 'selected' : '' }}>All Status</option>
                                                    <option value="Completed" {{ old('status', $request['status'] ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Overdue" {{ old('status', $request['status'] ?? '') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="Pending" {{ old('status', $request['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                            @endrole
                                        </div>
                                        <div class="row mt-4">
                                            @role('Admin')
                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status">Select Status</label>
                                                <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" selected {{ old('status', $request['status'] ?? '') == '' ? 'selected' : '' }}>All Status</option>
                                                    <option value="Completed" {{ old('status', $request['status'] ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Overdue" {{ old('status', $request['status'] ?? '') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="Pending" {{ old('status', $request['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                            @endrole
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <!-- <input type="date" class="form-control" name="from_date" id="from_date"> -->
                                                <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                                            </div>

                                            <!-- To Date Filter -->
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <!-- <input type="date" class="form-control" name="to_date" id="to_date"> -->
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-md-4 mt-3">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Report Section -->
                            <section id="reports-section">
                                @if (isset($counts))
                                @if ($counts['completed'] != 0 || $counts['overdue'] != 0 || $counts['pending'] != 0 || $assignments_percentages['completed'] || $assignments_percentages['overdue'] || $assignments_percentages['pending'] )
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="containerchart" style="display: flex;align-items: center;justify-content: center;">
                                            <div>
                                                <canvas id="completionpieChart" width="600" height="600"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header text-white bg-success" style="font-size: 1.0rem;font-weight: 700;">Completed</div>
                                            <div class=" card-body" style="background-color: white;">
                                                <h6 class="card-title">Count: {{ $counts['completed'] }}</h6>
                                                <p class="card-text">Percentage: {{ $assignments_percentages['completed'] }}%</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header text-white" style="font-size: 1.0rem;font-weight: 700;background-color: #f4bd0eb3;">Pending</div>
                                            <div class=" card-body" style="background-color: white;">
                                                <h6 class="card-title">Count: {{ $counts['pending'] }}</h6>
                                                <p class="card-text">Percentage: {{ $assignments_percentages['pending'] }}%</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header text-white bg-danger" style="font-size: 1.0rem;font-weight: 700;">Overdue</div>
                                            <div class=" card-body" style="background-color: white;">
                                                <h6 class="card-title">Count: {{ $counts['overdue'] }}</h6>
                                                <p class="card-text">Percentage: {{ $assignments_percentages['overdue'] }}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class="mb-4">Details</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Class Name</th>
                                                    <th>Assignment Name</th>
                                                    <th>Start Date</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tests as $test)
                                                <tr>
                                                    <td>{{App\Models\User::find($test->student_id)->name}}</td>
                                                    <?php
                                                    $groupId = App\Models\GroupStudent::where('student_id', $test->student_id)->value('group_id');
                                                    $groupName = $groupId ? App\Models\Group::find($groupId)?->name : null;

                                                    if ($groupName) {
                                                        echo '<td>' . $groupName . '</td>';
                                                    } else {
                                                        echo '<td>No Group Found</td>';
                                                    }
                                                    ?>
                                                    <td>{{ $test->tests->name }}</td>
                                                    <td>{{ $test->start_date }}</td>
                                                    <td>{{ $test->due_date }}</td>
                                                    <td>
                                                        {{$test->status == 1 ? 'Completed' : (\Carbon\Carbon::parse($test->due_date)->endOfDay()->isPast() ? 'Overdue' : 'Pending') }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>

                            <!-- <div style="margin-top: 20px; margin-left:10px;">
                                <p>No data available for the selected filters.</p>
                            </div> -->
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>

@if (isset($sessionKey))
<script>
    function downloadExcel() {
        var sessionKey = "{{ $sessionKey }}";
        window.location.href = "{{ route('reports.exportStudentCompletionReport', $sessionKey) }}";
    }
</script>
@endif
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let pdfButton = document.getElementById("generate-pdf");
        let excelButton = document.getElementById("generate-excel");

        // Disable the button initially
        pdfButton.disabled = true;
        excelButton.disabled = true;

        // Enable after 3 seconds
        setTimeout(() => {
            pdfButton.disabled = false;
            excelButton.disabled = false;
        }, 1000);
    });
    document.getElementById('generate-pdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        let chartCanvas = document.getElementById('completionpieChart');

        // Get the selected values from the dropdowns
        // let schoolName = document.getElementById('school_id')?.options[document.getElementById('school_id')?.selectedIndex]?.text || "N/A";
        var schoolData = @json(App\Models\School::pluck('name', 'id'));
        let schoolElement = document.getElementById("school_id");

        let schoolName = schoolElement?.tagName === "SELECT" ?
            schoolElement.options[schoolElement.selectedIndex]?.text || "N/A" :
            schoolData[schoolElement?.value] || "N/A";
        let studentName = document.getElementById('student_id')?.options[document.getElementById('student_id')?.selectedIndex]?.text || "N/A";
        let programName = document.getElementById('program_id')?.options[document.getElementById('program_id')?.selectedIndex]?.text || "N/A";
        let status = document.getElementById('status')?.options[document.getElementById('status')?.selectedIndex]?.text || "N/A";

        fetch('/assets/fonts/Amiri-Regular.ttf')
            .then(response => response.arrayBuffer())
            .then(fontBuffer => {
                const fontBlob = new Blob([fontBuffer]);
                const reader = new FileReader();

                reader.onloadend = function() {
                    const fontBase64 = reader.result.split(',')[1]; // Extract Base64

                    let pdf = new jsPDF('p', 'mm', 'a4');

                    // Register the font
                    pdf.addFileToVFS('Amiri-Regular.ttf', fontBase64);
                    pdf.addFont('Amiri-Regular.ttf', 'Amiri', 'bold');

                    const pageWidth = pdf.internal.pageSize.width;
                    const pageHeight = pdf.internal.pageSize.height;

                    // --- Add Page Header ---
                    pdf.setFillColor(209, 126, 0); // Dark Orange Background
                    pdf.rect(0, 0, pageWidth, 20, 'F'); // Header Rectangle
                    pdf.setTextColor(255, 255, 255); // White Title
                    pdf.setFontSize(18);
                    pdf.text("Student Completion Report", pageWidth / 2, 12, {
                        align: "center"
                    });

                    let startY = 30; // Content starts after the header

                    // --- Add School, Student, Program, and Status details ---
                    pdf.setFontSize(12);
                    pdf.setTextColor(0, 0, 0); // Black text
                    pdf.text(`School Name: ${schoolName}`, 15, startY);
                    startY += 7;
                    pdf.text(`Student Name: ${studentName}`, 15, startY);
                    startY += 7;
                    pdf.text(`Program Name: ${programName}`, 15, startY);
                    startY += 7;
                    pdf.text(`Status: ${status}`, 15, startY);
                    startY += 10; // More space after placeholders

                    // Convert the Chart Canvas to an Image and Add it to the PDF
                    domtoimage.toPng(chartCanvas, {
                            quality: 1,
                            scale: 2
                        })
                        .then(function(chartImage) {
                            // Table Data
                            let tableData = [];
                            let headers = ["Student Name", "Class Name", "Assignment Name", "Start Date", "Due Date", "Status"];

                            document.querySelectorAll("tbody tr").forEach(row => {
                                let rowData = [];
                                row.querySelectorAll("td").forEach((cell, index) => {
                                    let text = cell.innerText;
                                    if (index === 2) { // Apply Arabic font for "Test Name"
                                        pdf.setFont("Amiri");
                                        pdf.setFontSize(12);
                                    } else {
                                        pdf.setFont("Amiri");
                                        pdf.setFontSize(10);
                                    }
                                    rowData.push(text);
                                });
                                tableData.push(rowData);
                            });

                            // Add Table to PDF
                            pdf.autoTable({
                                startY: startY,
                                head: [headers],
                                body: tableData,
                                headStyles: {
                                    fillColor: [209, 126, 0],
                                    textColor: 255,
                                    fontSize: 11,
                                    fontStyle: 'bold'
                                },
                                styles: {
                                    fontSize: 10,
                                    font: "Amiri",
                                    cellPadding: 3
                                },
                                alternateRowStyles: {
                                    fillColor: [245, 245, 245]
                                }, // Light grey background
                                margin: {
                                    left: 15,
                                    right: 15
                                }
                            });

                            // --- Add Footer (Page Number) ---
                            let pageCount = pdf.internal.getNumberOfPages();
                            for (let i = 1; i <= pageCount; i++) {
                                pdf.setPage(i);
                                pdf.setFillColor(255, 255, 255);
                                pdf.rect(0, pageHeight - 15, pageWidth, 15, 'F'); // Footer Rectangle
                                pdf.setTextColor(44, 44, 44);
                                pdf.setFontSize(10);
                                pdf.text(`Page ${i} of ${pageCount}`, pageWidth / 2, pageHeight - 5, {
                                    align: "center"
                                });
                            }

                            // Save the PDF
                            pdf.save("Student_Completion_Report.pdf");
                        })
                        .catch(function(error) {
                            console.error("Error generating PDF: ", error);
                        });
                };

                // Trigger FileReader to read as Data URL (Base64)
                reader.readAsDataURL(fontBlob);
            })
            .catch(error => {
                console.error("Error loading font:", error);
            });
    });
</script>
@if(isset($counts))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var data = @json($counts);
        const labels = Object.keys(data).map(label => label.charAt(0).toUpperCase() + label.slice(1));
        const values = Object.values(data);
        var ctx = document.getElementById("completionpieChart").getContext("2d");

        // Destroy previous chart instance if it exists
        if (window.completionChart) {
            window.completionChart.destroy();
        }
        window.completionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Count',
                    data: values,
                    backgroundColor: ['#1cd0a0', '#ff0000cf', '#f4bd0eb3'],
                    borderColor: ['#1cd0a0', '#ff0000cf', '#f4bd0eb3'],
                    borderWidth: 0,
                    barThickness: 100
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                },
                layout: {
                    padding: {
                        left: 50,
                        right: 50
                    }
                },
                onClick: function(evt, item) {
                    if (item.length > 0) {
                        const index = item[0].index;
                        const clickedLabel = labels[index];
                        let filterValue;
                        if (clickedLabel === 'Completed') {
                            filterValue = 'Completed';
                        } else if (clickedLabel === 'Overdue') {
                            filterValue = 'Overdue';
                        } else if (clickedLabel === 'Pending') {
                            filterValue = 'Pending';
                        }
                        if (filterValue) {
                            document.getElementById('status').value = filterValue;
                            document.getElementById('page-form').submit();
                        }
                    }
                }
            }
        });
    });
</script>
@endif
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

@if(isset($error))
<script>
    Swal.fire({
        title: @json($error),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
    var canvas = document.getElementById("reports-section");
    canvas.style.display = 'none';
</script>
@endif

<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        // Get previously selected program_id from localStorage if exists
        var selectedProgramId = "{{$request['program_id'] ?? '' }}";
        var selectedStudentId = "{{$request['student_id'] ?? '' }}";

        // Trigger getProgramsByGroup on group change
        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getSchoolStudents(schoolId, selectedStudentId);
        });
        $('#student_id').change(function() {
            var studentId = $('#student_id').val();
            getProgramsByStudent(studentId, selectedProgramId);
        });

        // Trigger change on page load to fetch programs for the selected group
        $('#school_id').trigger('change');
        $('#student_id').trigger('change');

        // Save the selected program_id to localStorage when it changes
        $('select[name="program_id"]').change(function() {
            var programId = $(this).val();
            localStorage.setItem('selectedProgramId', programId);
        });
    });

    function getProgramsByStudent(studentId, selectedProgramId) {
        $.ajax({
            url: '/get-student-programs/' + studentId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="program_id"]').empty();

                if (!data || data.length === 0) {
                    $('select[name="program_id"]').append(
                        '<option value="" selected disabled>No Available Programs</option>'
                    );
                } else {
                    $('select[name="program_id"]').append(
                        '<option value="" selected>All Programs</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="program_id"]').append(
                            '<option value="' + value.id + '">' + value.program_details + '</option>'
                        );
                    });
                    if (selectedProgramId) {
                        $('select[name="program_id"]').val(selectedProgramId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function getSchoolStudents(schoolId, selectedStudentId) {
        $.ajax({
            url: '/get-students-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="student_id"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>No Available Students</option>'
                    );
                } else {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>Choose a Student</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="student_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedStudentId) {
                        $('select[name="student_id"]').val(selectedStudentId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>

@endsection