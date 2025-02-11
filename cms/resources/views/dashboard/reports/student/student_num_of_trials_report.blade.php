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
                                    <h5 class="title">Student Number of Trials Report</h5>
                                    @if (isset($testsData))
                                    <div class="d-flex" style="gap: 5px;">
                                        <button id="generate-pdf" class="btn btn-primary">Download PDF</button>
                                        <button id="generate-excel" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
                                    </div>
                                    @endif
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.numOfTrialsReport') }}">
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

                                            <!-- Student Filter -->
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
                                        </div>

                                        <div class="row mt-4">
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                                            </div>

                                            <!-- To Date Filter -->
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-md-4 mt-4">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>

                            </div>

                            <!-- Report Section -->
                            @if (isset($testsData))
                            <section id="reports-section">
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <!-- Display Chart if Data is Available -->
                                        <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                            <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                            <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</span></button>
                                        </div>
                                        <div class="container mt-5">
                                            <canvas id="trialsChart" width="400" height="200"></canvas>
                                        </div>

                                    </div>
                                </div>
                                <div class="card mt-4 report-data">
                                    <div class="card-body">
                                        <h5 class="mb-3">Details</h5>
                                        <table class="table table-striped mt-4">
                                            <thead>
                                                <tr>
                                                    @if ($ProgramFlag == 0)
                                                    <th>Program</th>
                                                    @endif
                                                    <th>Unit</th>
                                                    <th>Lesson</th>
                                                    <th>Game</th>
                                                    <th>Assignment Name</th>
                                                    <th>Completion Date</th>
                                                    <th>Number of Trials</th>
                                                    <th>Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($testsData as $test)
                                                <tr>
                                                    @if ($ProgramFlag == 0)
                                                    <td>{{ $test['test_program'] }}</td>
                                                    @endif
                                                    <td>{{ $test['test_unit'] }}</td>
                                                    <td>{{ $test['test_lesson'] }}</td>
                                                    <td>{{ $test['test_game'] }}</td>
                                                    <td>{{ $test['test_name'] }}</td>
                                                    <td>{{ $test['completion_date'] }}</td>
                                                    <td>{{ $test['num_trials'] }}</td>
                                                    <td>{{ $test['score'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection
@php
if(isset($progress)){
$data = [$oneStarDisplayedPercentage, $twoStarDisplayedPercentage, $threeStarDisplayedPercentage];
}
@endphp

@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
@if (isset($sessionKey))
<script>
    function downloadExcel() {
        var sessionKey = "{{ $sessionKey }}";
        window.location.href = "{{ route('reports.exportStudentTrialsReport', $sessionKey) }}";
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

        // Get the selected values from the dropdowns
        // let schoolName = document.getElementById('school_id')?.options[document.getElementById('school_id')?.selectedIndex]?.text || "N/A";
        var schoolData = @json(App\Models\School::pluck('name', 'id'));
        let schoolElement = document.getElementById("school_id");

        let schoolName = schoolElement?.tagName === "SELECT" ?
            schoolElement.options[schoolElement.selectedIndex]?.text || "N/A" :
            schoolData[schoolElement?.value] || "N/A";
        let studentName = document.getElementById('student_id')?.options[document.getElementById('student_id')?.selectedIndex]?.text || "N/A";
        let programName = document.getElementById('program_id')?.options[document.getElementById('program_id')?.selectedIndex]?.text || "N/A";

        fetch('/assets/fonts/Amiri-Regular.ttf')
            .then(response => response.arrayBuffer())
            .then(fontBuffer => {
                const fontBlob = new Blob([fontBuffer]);
                const reader = new FileReader();

                reader.onloadend = function() {
                    const fontBase64 = reader.result.split(',')[1]; // Extract Base64

                    let pdf = new jsPDF('p', 'mm', 'a4');

                    // Register and set the font
                    pdf.addFileToVFS('Amiri-Regular.ttf', fontBase64);
                    pdf.addFont('Amiri-Regular.ttf', 'Amiri', 'normal');
                    pdf.setFont("Amiri", "normal");

                    const pageWidth = pdf.internal.pageSize.width;
                    const pageHeight = pdf.internal.pageSize.height;

                    // --- Add Page Header ---
                    pdf.setFillColor(209, 126, 0); // Dark Orange Background
                    pdf.rect(0, 0, pageWidth, 20, 'F'); // Header Rectangle
                    pdf.setTextColor(255, 255, 255); // White Title
                    pdf.setFontSize(18);
                    pdf.text("Student Trials Report", pageWidth / 2, 12, {
                        align: "center"
                    });

                    let startY = 30; // Content starts after the header

                    // --- Add School, Teacher, and Program details ---
                    pdf.setFontSize(12);
                    pdf.setTextColor(0, 0, 0); // Black text
                    pdf.text(`School Name: ${schoolName}`, 15, startY);
                    startY += 7;
                    pdf.text(`Student Name: ${studentName}`, 15, startY);
                    startY += 7;
                    pdf.text(`Program Name: ${programName}`, 15, startY);
                    startY += 10; // More space after placeholders

                    // Extract report data
                    let reportDataDiv = document.querySelector('.report-data');
                    // let headings = reportDataDiv.querySelectorAll('h5');

                    // headings.forEach((h5, index) => {
                    //     let text = h5.innerText.trim();
                    //     if (text) {
                    //         pdf.setFontSize(14);
                    //         pdf.setTextColor(209, 126, 0);
                    //         pdf.text(text, 15, startY);
                    //         startY += 8;
                    //     }
                    // });

                    // Extract tables
                    let tables = reportDataDiv.querySelectorAll('table');
                    if (tables.length > 0) {
                        tables.forEach((table) => {
                            let headers = [];
                            let rows = [];

                            // Extract headers
                            table.querySelectorAll('thead th').forEach(header => {
                                headers.push(header.innerText.trim());
                            });

                            // Extract rows
                            table.querySelectorAll('tbody tr').forEach(row => {
                                let rowData = [];
                                row.querySelectorAll('td').forEach(cell => {
                                    rowData.push(cell.innerText.trim());
                                });
                                rows.push(rowData);
                            });

                            // Add table to PDF with alternating row colors
                            pdf.autoTable({
                                startY: startY,
                                head: [headers],
                                body: rows,
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

                            startY = pdf.lastAutoTable.finalY + 15;
                        });
                    } else {
                        pdf.setFontSize(12);
                        pdf.setTextColor(0, 0, 0);
                        pdf.text("No data available.", pageWidth / 2, startY, {
                            align: "center"
                        });
                    }

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
                    pdf.save("Student_Trials_Report.pdf");
                };

                reader.readAsDataURL(fontBlob); // Convert to Base64
            })
            .catch(error => console.error("Error loading font:", error));
    });
</script>

@if (isset($chartLabels) || isset($chartValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller
        const names = @json($chartLabels);
        const usageCounts = @json($chartValues);
        const chartNames = @json($chartNames);

        // Define constants and variables for pagination
        const itemsPerPage = 10; // Number of entries per graph page
        const totalEntries = names.length;
        const totalPages = Math.ceil(totalEntries / itemsPerPage); // Calculate total pages
        let currentPage = 0; // Start from the first page

        // Initialize the chart
        const ctx = document.getElementById('trialsChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';
        toggleButtons();

        // Create the chart with the initial data
        let usageChart = initializeChart(
            ctx,
            names.slice(0, itemsPerPage), // First 7 entries for labels
            usageCounts.slice(0, itemsPerPage), // First 7 entries for data
            chartNames.slice(0, itemsPerPage) // First 7 entries for chart names
        );

        // Function to initialize chart
        function initializeChart(ctx, labels, data, chartNames) {
            // Function to determine color based on percentage
            function getBarColor(value) {
                if (value <= 1) return '#1cd0a0';
                if (value <= 2) return '#f7d156';
                return '#ff3030';
            }

            // Generate the backgroundColor array dynamically
            const backgroundColors = data.map(value => getBarColor(value));

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels, // x-axis labels
                    datasets: [{
                        label: 'Trials Count',
                        data: data, // bar values
                        chartNames: chartNames, // Store the game names
                        backgroundColor: backgroundColors, // Dynamically set colors
                        borderColor: backgroundColors, // Match border color with bar color
                        borderWidth: 1,
                        maxBarThickness: 80
                    }]
                },
                options: {
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                generateLabels: function(chart) {
                                    return [{
                                            text: 'First Trial (1st)',
                                            fillStyle: '#1cd0a0', // Green
                                            strokeStyle: '#1cd0a0',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Second Trial (2nd)',
                                            fillStyle: '#f7d156', // Yellow
                                            strokeStyle: '#f7d156',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Third Trial or More (3rd+)',
                                            fillStyle: '#ff3030', // Red
                                            strokeStyle: '#ff3030',
                                            lineWidth: 0
                                        }
                                    ];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    // Access the game name from chartNames
                                    const chartName = tooltipItem.dataset.chartNames[tooltipItem.dataIndex];
                                    const value = tooltipItem.raw;
                                    return [`${chartName}`, `Number of Trials: ${value}`];
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: 50,
                            right: 50
                        }
                    }
                }
            });
        }

        // Function to update the chart with the current page data
        function updateChart() {
            // Calculate the slice of data for the current page
            const startIndex = currentPage * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Update chart labels, data, and chartNames
            usageChart.data.labels = names.slice(startIndex, endIndex);
            usageChart.data.datasets[0].data = usageCounts.slice(startIndex, endIndex);
            usageChart.data.datasets[0].chartNames = chartNames.slice(startIndex, endIndex);

            // Dynamically update backgroundColor based on value
            usageChart.data.datasets[0].backgroundColor = usageCounts.slice(startIndex, endIndex).map(value => {
                if (value <= 1) return '#1cd0a0';
                if (value <= 2) return '#f7d156';
                return '#ff3030';
            });

            // Update the chart
            usageChart.update();

            // Update button visibility
            toggleButtons();
        }

        // Function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart();
            }
        };

        // Function to go to the next page
        window.nextPage = function() {
            if (currentPage < totalPages - 1) {
                currentPage++;
                updateChart();
            }
        };

        // Function to toggle the visibility of the previous and next buttons
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            // Show or hide buttons based on the current page
            prevButton.style.display = (currentPage === 0) ? 'none' : 'block';
            nextButton.style.display = (currentPage === totalPages - 1) ? 'none' : 'block';
        }
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>