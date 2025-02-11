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
                            <div class="card">
                                <div class="card-header" style="display:flex; justify-content: space-between; align-items:center;">
                                    <h5 class="title">Class Login Report</h5>
                                    @if(isset($studentName, $numLogin, $teacherName, $teacherLogin))
                                    <div class="d-flex" style="gap: 5px;">
                                        <button id="generate-pdf" class="btn btn-primary">Download PDF</button>
                                        <button id="generate-excel" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
                                    </div>
                                    @endif

                                </div>
                                <div class="card-body">

                                    <form method="GET" action="{{ route('reports.classLoginReport') }}">
                                        <div class="row">
                                            <!-- Group Filter -->
                                            <div class="col-md-4">
                                                @role('Admin')
                                                <label for="group_id">Select school/class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled {{ old('group_id', $request['group_id'] ?? '') == '' ? 'selected' : '' }}>Choose a school/class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <!-- <option value="{{ $group->id }}" data-school="{{ $sch->id }}">{{ $sch->name }} / {{ $group->name }}</option> -->
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}" {{ old('group_id', $request['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                        {{ $sch->name }} / {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole
                                                @if(auth()->user()->hasRole('school') || auth()->user()->hasRole('Cordinator'))
                                                <label for="group_id">Select Class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled {{ old('group_id', $request['group_id'] ?? '') == '' ? 'selected' : '' }}>Choose a Class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <!-- <option value="{{ $group->id }}" data-school="{{ $sch->id }}">{{ $sch->name }} / {{ $group->name }}</option> -->
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}" {{ old('group_id', $request['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                        {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-row mt-3">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">View Report</button>
                                            </div>
                                        </div>
                                    </form>
                                    @if(isset($studentName) && isset($numLogin))
                                    <ul class="nav nav-tabs mt-4" id="reportTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="teacher-login-tab" data-toggle="tab"
                                                href="#teacher-login" role="tab" aria-controls="teacher-login-report"
                                                aria-selected="true">Teachers</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="student-login-tab" data-toggle="tab"
                                                href="#student-login-report" role="tab"
                                                aria-controls="student-login-report" aria-selected="false">Students</a>
                                        </li>
                                    </ul>

                                    <!-- Display Chart if Data is Available -->

                                    <div class="container mt-5" id="student-container" style="min-width: 100%;">
                                        <div id="student-canvas-cont">
                                            <div class="chart-buttons" id="chart-buttons" style="display: flex; justify-content: flex-end; gap: 10px;">
                                                <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                                <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</button>
                                            </div>
                                            <canvas id="studentloginChart" width="400" height="200"></canvas>
                                        </div>
                                        <div class="alert alert-danger mb-3" id="student-error" style="display:none"></div>
                                    </div>

                                    <div class="container mt-5" id="teacher-container" style="min-width: 100%;">
                                        <div id="teacher-canvas-cont">
                                            <canvas id="teacherloginChart" width="400" height="200"></canvas>
                                        </div>
                                        <div class="alert alert-danger mb-3" id="teacher-error" style="display:none"></div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @if(isset($studentName, $numLogin, $teacherName, $teacherLogin))
                            <div class="report-data">
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Teacher Name</th>
                                                    <th>Number of Logins</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($teacherName as $index => $teacher)
                                                <tr>
                                                    <td>{{ $teacher }}</td>
                                                    <td>{{ $teacherLogin[$index] ?? '0' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Number of Logins</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($studentName as $index => $student)
                                                <tr>
                                                    <td>{{ $student }}</td>
                                                    <td>{{ $numLogin[$index] ?? '0' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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

@section('page_js')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
@if (isset($sessionKey))
<script>
    function downloadExcel() {
        var sessionKey = "{{ $sessionKey }}";
        // First file download
        window.location.href = "{{ route('reports.exportTeacherLoginReport', $sessionKey) }}";

        // Delay the second file download by 2 seconds to ensure the first completes
        setTimeout(() => {
            window.location.href = "{{ route('reports.exportStudentLoginReport', $sessionKey) }}";
        }, 1000);
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
        document.getElementById('generate-pdf').addEventListener('click', function() {
            console.log(pdfButton);
            const {
                jsPDF
            } = window.jspdf;

            // Get the selected values from the dropdowns
            let className = document.getElementById('group_id')?.options[document.getElementById('group_id')?.selectedIndex]?.text || "N/A";

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
                        pdf.text("Class Login Report", pageWidth / 2, 12, {
                            align: "center"
                        });

                        let startY = 30; // Content starts after the header

                        // --- Add School, Teacher, and Program details ---
                        pdf.setFontSize(12);
                        pdf.setTextColor(0, 0, 0); // Black text
                        pdf.text(`Class Name: ${className}`, 15, startY);
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
                        pdf.save("Class_Login_Report.pdf");
                    };

                    reader.readAsDataURL(fontBlob); // Convert to Base64
                })
                .catch(error => console.error("Error loading font:", error));
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).ready(function() {
            // Initialize select2 for the filters
            $('.js-select2').select2();
        });
    });
</script>

@if(isset($studentName) && isset($numLogin) && isset($teacherName) && isset($teacherLogin))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pageSize = 6;
        let currentPage = 0;

        // Data from controller
        const studentNames = @json($studentName);
        const numLogins = @json($numLogin);
        const teacherNames = @json($teacherName);
        const teacherLogins = @json($teacherLogin);

        // Initialize the student login chart
        const ctx = document.getElementById('studentloginChart').getContext('2d');
        const ctx2 = document.getElementById('teacherloginChart').getContext('2d');

        if (studentNames.length == 0) {
            studenterror = document.getElementById('student-error');
            studenterror.innerText = 'No Students Found';
            studenterror.style.display = 'block';
            document.getElementById('student-canvas-cont').style.display = 'none';
        }

        let studentloginChart = initializeChart(ctx, studentNames.slice(0, pageSize), numLogins.slice(0, pageSize));

        // Function to initialize chart
        function initializeChart(ctx, labels, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Student Logins',
                        data: data,
                        backgroundColor: '#E9C874',
                        borderColor: '#E9C874',
                        borderWidth: 1,
                        maxBarThickness: 100
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
                                stepSize: 1,
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
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
        if (teacherNames.length == 0) {
            teachererror = document.getElementById('teacher-error');
            teachererror.innerText = 'No Teachers Found';
            teachererror.style.display = 'block';
            document.getElementById('teacher-canvas-cont').style.display = 'none';
        }
        // Initialize teacher login chart
        let teacherloginChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: teacherNames,
                datasets: [{
                    label: 'Teacher Logins',
                    data: teacherLogins,
                    backgroundColor: '#E9C874',
                    borderColor: '#E9C874',
                    borderWidth: 1,
                    barThickness: 120
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
                            stepSize: 1,
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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

        // Function to get data for the current page
        function getCurrentPageData() {
            const start = currentPage * pageSize;
            const end = start + pageSize;
            return {
                labels: studentNames.slice(start, end),
                data: numLogins.slice(start, end)
            };
        }

        // Function to update the chart with the current page data
        function updateChart() {
            const pageData = getCurrentPageData();
            if (studentloginChart) {
                studentloginChart.data.labels = pageData.labels;
                studentloginChart.data.datasets[0].data = pageData.data;
                studentloginChart.update();
            }
        }

        // Function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to go to the next page
        window.nextPage = function() {
            if ((currentPage + 1) * pageSize < studentNames.length) {
                currentPage++;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Handle tab click events to show/hide charts
        document.getElementById('student-login-tab').addEventListener('click', function() {
            document.getElementById('student-container').style.display = 'block';
            document.getElementById('teacher-container').style.display = 'none';
            document.getElementById('chart-buttons').style.display = 'flex';
            // Update aria-selected attributes
            document.getElementById('student-login-tab').setAttribute('aria-selected', 'true');
            document.getElementById('teacher-login-tab').setAttribute('aria-selected', 'false');

            // Add and remove active class
            document.getElementById('student-login-tab').classList.add('active');
            document.getElementById('teacher-login-tab').classList.remove('active');
        });

        document.getElementById('teacher-login-tab').addEventListener('click', function() {
            document.getElementById('teacher-container').style.display = 'block';
            document.getElementById('student-container').style.display = 'none';
            document.getElementById('chart-buttons').style.display = 'none';
            // Update aria-selected attributes
            document.getElementById('teacher-login-tab').setAttribute('aria-selected', 'true');
            document.getElementById('student-login-tab').setAttribute('aria-selected', 'false');
            // Add and remove active class
            document.getElementById('teacher-login-tab').classList.add('active');
            document.getElementById('student-login-tab').classList.remove('active');

        });

        // By default, show the student chart and hide the teacher chart
        document.getElementById('teacher-container').style.display = 'block';
        document.getElementById('student-container').style.display = 'none';
    });
</script>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>


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