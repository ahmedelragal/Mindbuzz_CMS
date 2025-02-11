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
                                    <h5 class="title">Student Content Engagement Report</h5>
                                    @if (isset($chartLabels) || isset($chartValues))
                                    <div class="d-flex" style="gap: 5px;">
                                        <button id="generate-pdf" class="btn btn-primary">Download PDF</button>
                                        <button id="generate-excel" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
                                    </div>
                                    @endif
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.studentContentEngagementReport') }}">
                                        <div class="row">
                                            <!-- School Filter -->
                                            @role('Admin')
                                            <div class="col-md-4">
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id" required>
                                                    <option value="" disabled {{ old('school_id', $request['school_id'] ?? '') == '' ? 'selected' : '' }}>Choose a School</option>
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
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Student</option>
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schStudents = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 2)
                                                    ->where('is_student', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schStudents as $student)
                                                    <option value="{{ $student->id }}" {{ old('student_id', $request['student_id'] ?? '') == $student->id ? 'selected' : '' }}>
                                                        {{ $student->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>

                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id" required>
                                                    <option value="" disabled selected>No Available Programs</option>
                                                </select>
                                            </div>

                                            <!-- Filter By -->
                                            @role("school")
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="Unit" selected{{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
                                                </select>
                                            </div>
                                            @endrole
                                        </div>
                                        <div class="row mt-3">
                                            <!-- Filter By -->
                                            @role("Admin")
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="Unit" selected{{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
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
                            @if (isset($chartLabels) || isset($chartValues))
                            <div class="card mt-4">
                                <div class="card-body">
                                    <!-- Display Chart if Data is Available -->
                                    <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                        <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                        <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</span></button>
                                    </div>
                                    <div class="container mt-5">
                                        <canvas id="engagementChart" width="400" height="200"></canvas>
                                    </div>

                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-body report-data">
                                    @if (isset($unitsEngagement) || isset($lessonsEngagement) || isset($gamesEngagement) || isset($skillsEngagement))

                                    @if (isset($unitsEngagement))
                                    <h5>Units Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($unitsEngagement as $engagement)
                                            <tr>
                                                <td>{{ $engagement['name'] }}</td>
                                                <td>{{ $engagement['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($lessonsEngagement))
                                    <h5>Lessons Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lessonsEngagement as $unit)
                                            <?php $inc = 0; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            @if ($inc==0)
                                            <tr>
                                                <td>{{$unit['name']}}</td>
                                                <td>{{$lesson['name']}}</td>
                                                <td>{{ $lesson['engagement_percentage'] }}%</td>
                                            </tr>
                                            <?php $inc = 1; ?>
                                            @else
                                            <tr>
                                                <td></td>
                                                <td>{{$lesson['name']}}</td>
                                                <td>{{ $lesson['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($gamesEngagement))
                                    <h5>Game Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Game</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($gamesEngagement as $unit)
                                            <?php $unitPrinted = false; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            <?php $lessonPrinted = false; ?>
                                            @foreach ($lesson['games'] as $game)
                                            <?php $gamePrinted = false; ?>
                                            <tr>
                                                <!-- Print unit name only once per unit -->
                                                @if (!$unitPrinted)
                                                <td>{{ $unit['name'] }}</td>
                                                <?php $unitPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print lesson name only once per lesson -->
                                                @if (!$lessonPrinted)
                                                <td>{{ $lesson['name'] }}</td>
                                                <?php $lessonPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print game name only once per game -->
                                                @if (!$gamePrinted)
                                                <td>{{ $game['name'] }}</td>
                                                <?php $gamePrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Game details (always printed) -->
                                                <td>{{ $game['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($skillsEngagement))
                                    <h5>Skills Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Game</th>
                                                <th>Skill</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skillsEngagement as $unit)
                                            <?php $unitPrinted = false; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            <?php $lessonPrinted = false; ?>
                                            @foreach ($lesson['games'] as $game)
                                            <?php $gamePrinted = false; ?>
                                            @foreach ($game['skills'] as $skill)
                                            <tr>
                                                <!-- Print unit name only once per unit -->
                                                @if (!$unitPrinted)
                                                <td>{{ $unit['name'] }}</td>
                                                <?php $unitPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print lesson name only once per lesson -->
                                                @if (!$lessonPrinted)
                                                <td>{{ $lesson['name'] }}</td>
                                                <?php $lessonPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print game name only once per game -->
                                                @if (!$gamePrinted)
                                                <td>{{ $game['name'] }}</td>
                                                <?php $gamePrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Skill details (always printed for each skill) -->
                                                <td>{{ $skill['name'] }}</td>
                                                <td>{{ $skill['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                            @endforeach
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @else
                                    <p>No data available for the selected filters.</p>
                                    @endif
                                </div>
                            </div>
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
        window.location.href = "{{ route('reports.exportStudentContentEngagementReport', $sessionKey) }}";
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
                    pdf.text("Student Content Engagement Report", pageWidth / 2, 12, {
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
                    pdf.save("Student_Content_Engagement_Report.pdf");
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

        // Group lessons by unit using the "-" separator
        const units = [];
        let currentUnit = [];
        names.forEach((label, index) => {
            if (label !== "-") {
                currentUnit.push({
                    label: label,
                    value: usageCounts[index],
                    chartName: chartNames[index]
                });
            } else if (currentUnit.length > 0) {
                units.push(currentUnit);
                currentUnit = [];
            }
        });
        // Push the last unit if it's not empty
        if (currentUnit.length > 0) {
            units.push(currentUnit);
        }

        // Initialize dynamic pagination variables
        let currentPage = 0;

        const ctx = document.getElementById('engagementChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';
        toggleButtons();
        // Initialize the chart with the first unit's data
        let usageChart = initializeChart(
            ctx,
            units[currentPage].map(item => item.label), // x-axis labels
            units[currentPage].map(item => item.value), // bar values
            units[currentPage].map(item => item.chartName) // game names from controller
        );

        // Function to initialize chart
        function initializeChart(ctx, labels, data, chartNames) {
            // Function to determine color based on percentage
            function getBarColor(value) {
                if (value <= 20) return '#ff3030';
                if (value > 20 && value <= 40) return '#ff6230';
                if (value > 40 && value <= 60) return '#f7d156';
                if (value > 60 && value <= 80) return '#f77556';
                return '#1cd0a0';
            }

            // Generate the backgroundColor array dynamically
            const backgroundColors = data.map(value => getBarColor(value));

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels, // x-axis labels
                    datasets: [{
                        label: 'Engagement Levels',
                        data: data, // bar values
                        chartNames: chartNames, // store the game names
                        backgroundColor: backgroundColors, // Dynamically set colors
                        borderWidth: 1,
                        maxBarThickness: 120
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
                            max: 100, // Set the max value to 100 for percentage
                            ticks: {
                                callback: function(value) {
                                    return value + '%'; // Append '%' to each tick value
                                },
                                stepSize: 10 // Set the step size (optional)
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
                                            text: 'Low (≤ 20%)',
                                            fillStyle: '#ff3030', // Red
                                            strokeStyle: '#ff3030',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Low Average (21% - 40%)',
                                            fillStyle: '#ff6230',
                                            strokeStyle: '#ff6230',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Average (41% - 60%)',
                                            fillStyle: '#f7d156',
                                            strokeStyle: '#f7d156',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'High Average (61% - 80%)',
                                            fillStyle: '#f77556',
                                            strokeStyle: '#f77556',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'High (> 80%)',
                                            fillStyle: '#1cd0a0',
                                            strokeStyle: '#1cd0a0',
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
                                    const value = tooltipItem.raw; // Get the value (y-axis data)
                                    return `${chartName}: ${value}%`; // Show "Game Name: Value%"
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

        // Function to update the chart with the current page data (current unit)
        function updateChart() {
            const currentUnit = units[currentPage];
            if (usageChart) {
                // Update chart labels, data, and chartNames
                usageChart.data.labels = currentUnit.map(item => item.label);
                usageChart.data.datasets[0].data = currentUnit.map(item => item.value);
                usageChart.data.datasets[0].chartNames = currentUnit.map(item => item.chartName); // Update chartNames

                // Dynamically update backgroundColor based on value
                usageChart.data.datasets[0].backgroundColor = currentUnit.map(item => {
                    if (item.value <= 20) return '#ff3030';
                    if (item.value > 20 && item.value <= 40) return '#ff6230';
                    if (item.value > 40 && item.value <= 60) return '#f7d156';
                    if (item.value > 60 && item.value <= 80) return '#f77556';
                    return '#1cd0a0';
                });

                // Update the chart
                usageChart.update();
            }
            toggleButtons(); // Ensure navigation buttons are updated
        }




        // Function to go to the previous unit (previous page)
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to go to the next unit (next page)
        window.nextPage = function() {
            if (currentPage < units.length - 1) {
                currentPage++;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to toggle the visibility of the previous and next buttons
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            // If only one page, hide both buttons
            if (units.length <= 1) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
            } else {
                // Show or hide buttons based on the current page
                prevButton.style.display = (currentPage === 0) ? 'none' : 'block';
                nextButton.style.display = (currentPage === units.length - 1) ? 'none' : 'block';
            }
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
                        '<option value="" disabled selected>Choose a Program</option>'
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