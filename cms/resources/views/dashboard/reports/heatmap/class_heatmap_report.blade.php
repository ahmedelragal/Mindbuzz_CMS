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
                                    <h5 class="title">Class Heatmap Report</h5>
                                    @if (isset($programUsages))
                                    <div class="d-flex" style="gap: 5px;">
                                        <button id="generate-pdf" class="btn btn-primary">Download PDF</button>
                                        <button id="generate-excel" class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
                                    </div>
                                    @endif
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classHeatmapReport') }}">
                                        <div class="row">
                                            <!-- Group 1 Filter -->
                                            <div class="col-md-4">
                                                <label for="group_id">Select Classes</label>
                                                <select class="form-select js-select2" name="group_id[]" id="group_id" multiple required>
                                                    @role('Admin')
                                                    @if (isset($request['group_id']))
                                                    <option value="" disabled {{ empty(old('group_id', $request['group_id'] ?? [])) ? 'selected' : '' }}></option>
                                                    @endif
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}"
                                                        {{ in_array($group->id, old('group_id', $request['group_id'] ?? [])) ? 'selected' : '' }}>
                                                        {{ $sch->name }} / {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                    @if(auth()->user()->hasRole('school') || auth()->user()->hasRole('Cordinator'))
                                                    @if (isset($request['group_id']))
                                                    <option value="" disabled {{ empty(old('group_id', $request['group_id'] ?? [])) ? 'selected' : '' }}></option>
                                                    @endif
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}"
                                                        {{ in_array($group->id, old('group_id', $request['group_id'] ?? [])) ? 'selected' : '' }}>
                                                        {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>

                                            </div>

                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" selected disabled>No Available Programs</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="" selected>No Filter</option>
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <!-- <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>  -->
                                                </select>
                                            </div>
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

                                            <!-- Submit Button -->
                                            <div class="col-md-4 mt-4">
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Report Section -->
                            <div id="report_container" style="display:none">
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <!-- Display Chart if Data is Available -->
                                        <div class="row">
                                            <div class="container mt-5">
                                                <canvas id="usageChart" width="400" height="200"></canvas>
                                            </div>
                                            <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                                <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                                <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-4 report-data">
                                    <div class="card-body">
                                        @if (isset($programsUsage))
                                        <h5>Programs Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Program</th>
                                                    @if (isset($classNames))
                                                    @foreach ($classNames as $className )
                                                    <th>{{$className}} Usage(%)</th>
                                                    @endforeach
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($programsUsage as $program)
                                                <tr>
                                                    <td>{{ $program['name'] }}</td>
                                                    @foreach ($classIds as $classId )
                                                    <td> {{$programUsages[$classId][$program['program_id']]['usage_percentage']}}%</td>
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($unitsUsage))
                                        <h5>Units Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    @if (isset($classNames))
                                                    @foreach ($classNames as $className )
                                                    <th>{{$className}} Usage(%)</th>
                                                    @endforeach
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unitsUsage as $unit)
                                                <tr>
                                                    <td>{{ $unit['name'] }}</td>
                                                    @foreach ($classIds as $classId )
                                                    <td> {{$programUsages[$classId][$request['program_id']]['units'][$unit['unit_id']]['usage_percentage']}}%</td>
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($lessonsUsage))
                                        <h5>Lessons Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>lesson</th>
                                                    @if (isset($classNames))
                                                    @foreach ($classNames as $className )
                                                    <th>{{$className}} Usage(%)</th>
                                                    @endforeach
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lessonsUsage as $unit)
                                                <?php $inc = 0; ?>
                                                @foreach ($unit['lessons'] as $lesson)
                                                @if ($inc == 0)
                                                <tr>
                                                    <td>{{$unit['name']}}</td>
                                                    <td>{{$lesson['name']}}</td>
                                                    @foreach ($classIds as $classId )
                                                    <td> {{$programUsages[$classId][$request['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage']}}%</td>
                                                    @endforeach
                                                </tr>
                                                <?php $inc = 1; ?>
                                                @else
                                                <tr>
                                                    <td></td>
                                                    <td>{{$lesson['name']}}</td>
                                                    @foreach ($classIds as $classId )
                                                    <td> {{$programUsages[$classId][$request['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage']}}%</td>
                                                    @endforeach
                                                </tr>
                                                @endif
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif
                                        @if (isset($gamesUsage))
                                        <h5>Games Usage</h5>
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Lesson</th>
                                                    <th>Game</th>
                                                    @if (isset($classNames))
                                                    @foreach ($classNames as $className )
                                                    <th>{{$className}} Usage(%)</th>
                                                    @endforeach
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($gamesUsage as $unit)
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
                                                    @foreach ($classIds as $classId )
                                                    @if($programUsages[$classId][$request['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['games'][$game['game_id']]['assigned'] == 1)
                                                    <td>Assigned</td>
                                                    @else
                                                    <td>Unassigned</td>
                                                    @endif
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                                @endforeach
                                                @endforeach


                                            </tbody>
                                        </table>

                                        @endif

                                    </div>
                                </div>
                            </div>
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
        window.location.href = "{{ route('reports.exportClassHeatmapReport', $sessionKey) }}";
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
                    pdf.text("Class Heatmap Report", pageWidth / 2, 12, {
                        align: "center"
                    });

                    let startY = 30; // Content starts after the header

                    // --- Add School, Teacher, and Program details ---
                    pdf.setFontSize(12);
                    pdf.setTextColor(0, 0, 0); // Black text
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
                    pdf.save("Class_Heatmap_Report.pdf");
                };

                reader.readAsDataURL(fontBlob); // Convert to Base64
            })
            .catch(error => console.error("Error loading font:", error));
    });
</script>
@if (isset($gamesLabels) || isset($gamesValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from the controller for the two charts
        const names = @json($gamesLabels);
        const usageCounts = @json($gamesValues);
        const classNames = @json($classNames);



        function groupByClass(names, usageCounts) {
            const classes = [];
            let currentClass = [];
            names.forEach((label, index) => {
                if (label !== "/") {
                    currentClass.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentClass.length > 0) {
                    classes.push(currentClass);
                    currentClass = [];
                }
            });
            if (currentClass.length > 0) {
                classes.push(currentClass);
            }
            return classes;
        }
        const classes = groupByClass(names, usageCounts);
        // Function to group lessons by unit using the "-" separator
        function groupByUnit(names, usageCounts) {
            const units = [];
            let currentUnit = [];
            names.forEach((label, index) => {
                if (label !== "-") {
                    currentUnit.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentUnit.length > 0) {
                    units.push(currentUnit);
                    currentUnit = [];
                }
            });
            if (currentUnit.length > 0) {
                units.push(currentUnit);
            }
            return units;
        }

        const unitsPerClass = [];
        classes.forEach(currentClass => {
            const labels = currentClass.map(item => item.label);
            const values = currentClass.map(item => item.value);
            unitsPerClass.push(groupByUnit(labels, values));
        });
        // Initialize dynamic pagination variables
        let currentPage = 0;
        let index = 0;
        const datasets = [];
        const colors = ['#E9C874', '#f77556', '#1cd0a0', '#f7d156', '#ff6230', ];
        colorIndex = 0;
        unitsPerClass.forEach(unit => {
            datasets.push({
                label: classNames[index],
                data: unit[currentPage].map(item => item.value),
                backgroundColor: colors[colorIndex],
                borderColor: colors[colorIndex],
                borderWidth: 1,
                maxBarThickness: 80
            });
            index++;
            colorIndex = (colorIndex + 1) % 5;
        });
        // Initialize the chart context
        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';

        toggleButtons();

        // Initialize the chart with the first page data
        let usageChart = initializeChart(
            ctx,
            unitsPerClass[0][currentPage].map(item => item.label),
            datasets
        );

        // Function to initialize the chart with grouped bars
        function initializeChart(ctx, labels, datasets) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            stacked: false, // Ensure bars are grouped, not stacked
                            barPercentage: 1.0, // Remove gap between bars within the same group
                            categoryPercentage: 1.0 // Remove gap between groups of bars
                        },
                        y: {
                            beginAtZero: true,
                            max: 1,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index', // Ensure tooltips display grouped values
                            intersect: false, // Show tooltip for all bars in a group
                            callbacks: {
                                label: function(tooltipItem) {
                                    let datasetLabel = tooltipItem.dataset.label; // Dataset name
                                    let value = tooltipItem.raw; // Bar value
                                    if (value == 1) {
                                        value = 'Assigned';
                                    } else {
                                        value = 'Unassigned';
                                    }
                                    return `${datasetLabel}: ${value}`; // Return label with dataset name and value
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

        // Function to update the chart with new page data
        function updateChart() {
            usageChart.data.labels = unitsPerClass[0][currentPage].map(item => item.label);
            let index = 0;
            unitsPerClass.forEach(unit => {
                usageChart.data.datasets[index].data = unit[currentPage].map(item => item.value);
                index++;
            })

            usageChart.update(); // Refresh the chart with new data
            toggleButtons();
        }

        // Optimized function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart();
            }
        };

        // Optimized function to go to the next page
        window.nextPage = function() {
            if (currentPage < unitsPerClass[0].length - 1) {
                currentPage++;
                updateChart();
            }
        };

        // Function to toggle button visibility based on the current page
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            prevButton.style.display = currentPage === 0 ? 'none' : 'block';
            nextButton.style.display = currentPage === unitsPerClass[0].length - 1 ? 'none' : 'block';
        }
    });
</script>
@endif

@if (isset($chartLabels) || isset($chartValues) || isset($chartLabels2) || isset($chartValues2))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from the controller for the two charts
        const names = @json($chartLabels);
        const usageCounts = @json($chartValues);
        const classNames = @json($classNames);



        function groupByClass(names, usageCounts) {
            const classes = [];
            let currentClass = [];
            names.forEach((label, index) => {
                if (label !== "/") {
                    currentClass.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentClass.length > 0) {
                    classes.push(currentClass);
                    currentClass = [];
                }
            });
            if (currentClass.length > 0) {
                classes.push(currentClass);
            }
            return classes;
        }
        const classes = groupByClass(names, usageCounts);
        // Function to group lessons by unit using the "-" separator
        function groupByUnit(names, usageCounts) {
            const units = [];
            let currentUnit = [];
            names.forEach((label, index) => {
                if (label !== "-") {
                    currentUnit.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentUnit.length > 0) {
                    units.push(currentUnit);
                    currentUnit = [];
                }
            });
            if (currentUnit.length > 0) {
                units.push(currentUnit);
            }
            return units;
        }

        const unitsPerClass = [];
        classes.forEach(currentClass => {
            const labels = currentClass.map(item => item.label);
            const values = currentClass.map(item => item.value);
            unitsPerClass.push(groupByUnit(labels, values));
        });
        // Initialize dynamic pagination variables
        let currentPage = 0;
        let index = 0;
        const datasets = [];
        const colors = ['#E9C874', '#f77556', '#1cd0a0', '#f7d156', '#ff6230', ];
        colorIndex = 0;
        unitsPerClass.forEach(unit => {
            datasets.push({
                label: classNames[index],
                data: unit[currentPage].map(item => item.value),
                backgroundColor: colors[colorIndex],
                borderColor: colors[colorIndex],
                borderWidth: 1,
                maxBarThickness: 80
            });
            index++;
            colorIndex = (colorIndex + 1) % 5;
        });
        // Initialize the chart context
        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';

        toggleButtons();

        // Initialize the chart with the first page data
        let usageChart = initializeChart(
            ctx,
            unitsPerClass[0][currentPage].map(item => item.label),
            datasets
        );

        // Function to initialize the chart with grouped bars
        function initializeChart(ctx, labels, datasets) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            stacked: false, // Ensure bars are grouped, not stacked
                            barPercentage: 1.0, // Remove gap between bars within the same group
                            categoryPercentage: 1.0 // Remove gap between groups of bars
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%'; // Append '%' to tick values
                                },
                                stepSize: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index', // Ensure tooltips display grouped values
                            intersect: false, // Show tooltip for all bars in a group
                            callbacks: {
                                label: function(tooltipItem) {
                                    let datasetLabel = tooltipItem.dataset.label; // Dataset name
                                    let value = tooltipItem.raw; // Bar value
                                    return `${datasetLabel}: ${value}%`; // Return label with dataset name and value
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

        // Function to update the chart with new page data
        function updateChart() {
            usageChart.data.labels = unitsPerClass[0][currentPage].map(item => item.label);
            let index = 0;
            unitsPerClass.forEach(unit => {
                usageChart.data.datasets[index].data = unit[currentPage].map(item => item.value);
                index++;
            })

            usageChart.update(); // Refresh the chart with new data
            toggleButtons();
        }

        // Optimized function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart();
            }
        };

        // Optimized function to go to the next page
        window.nextPage = function() {
            if (currentPage < unitsPerClass[0].length - 1) {
                currentPage++;
                updateChart();
            }
        };

        // Function to toggle button visibility based on the current page
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            prevButton.style.display = currentPage === 0 ? 'none' : 'block';
            nextButton.style.display = currentPage === unitsPerClass[0].length - 1 ? 'none' : 'block';
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
        title: 'Error!',
        text: @json($error),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
    document.getElementById('report_container').style.display = 'none';
</script>
@endif
@php
if (isset($programsUsage) || isset($unitsUsage) || isset($lessonsUsage) || isset($gamesUsage) || isset($skillsUsage)){
$showReports = 1;
}else{
$showReports = 0;
}
@endphp
<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        showReports = @json($showReports);
        if (showReports) {
            document.getElementById('report_container').style.display = 'block';
        }
        // Get previously selected program_id from localStorage if exists
        var selectedProgramId = "{{$request['program_id'] ?? '' }}";

        // Trigger getCommonGroupsPrograms on group change
        $('#group_id').change(function() {
            var groupIds = $('#group_id').val(); // Get all selected values as an array
            getCommonGroupsPrograms(groupIds, selectedProgramId);
        });

        // Trigger change on page load to fetch programs for the selected group
        $('#group_id').trigger('change');

        // Save the selected program_id to localStorage when it changes
        $('select[name="program_id"]').change(function() {
            var programId = $(this).val();
            localStorage.setItem('selectedProgramId', programId);
        });
    });

    function getCommonGroupsPrograms(groupIds, selectedProgramId) {
        // Convert group1Ids to a string if it's an array (join with commas or other delimiter)
        var groupIdsParam = Array.isArray(groupIds) ? groupIds.join(',') : groupIds;

        $.ajax({
            url: '/get-common-programs-group/' + groupIdsParam,
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

                    // If a program is pre-selected, set it
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
</script>
@endsection