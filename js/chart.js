<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cost Report</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <link href="css/all.min.css" rel="stylesheet">
    <link href="font/css/all.min.css" rel="stylesheet">
    <link href="css/select2.min.css" rel="stylesheet" />
    <script src="css/jquery-3.6.0.min.js"></script>
    <script src="css/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Existing CSS styles */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #e2e8f0;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .pagination-link {
            padding: 8px 16px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            background-color: #6b7280;
            color: white;
            cursor: pointer;
        }
        .pagination-link:hover {
            background-color: #555b66;
        }
        .pagination-link.disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        /* Export button styles */
        .export-button {
            padding: 8px 16px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        .export-button:hover {
            background-color: #218838;
        }
        /* Group Header Styles */
        .group-header {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        /* Fixed header styles */
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            position: relative;
        }
        .table-container thead th {
            position: -webkit-sticky; /* For Safari */
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #e2e8f0;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold text-center text-teal-600 mb-6">Cost Report</h2>

    <!-- Display Success or Error Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="p-4 mb-4 <?php echo ($_SESSION['message_type'] == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-end mb-6">
        <button id="exportButton" class="export-button">Export to Excel</button>
    </div>

    <!-- Main Search Bar -->
    <div class="mb-6 flex flex-wrap gap-4">
        <input type="text" id="mainSearch" placeholder="Search..." class="p-2 border rounded w-full md:w-1/2">
    </div>

    <!-- Search Filters -->
    <div class="mb-6 flex flex-wrap gap-4">
        <!-- Client Dropdown -->
        <select id="clientSearch" class="p-2 border rounded select2">
            <option></option> <!-- Empty option for placeholder -->
            <option value="">All Clients</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?php echo htmlspecialchars($client); ?>"><?php echo htmlspecialchars($client); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- DT Job Number Dropdown -->
        <select id="jobNumberSearch" class="p-2 border rounded select2">
            <option></option> <!-- Empty option for placeholder -->
            <option value="">All DT Job Numbers</option>
            <?php foreach ($dtJobNumbers as $jobNumber): ?>
                <option value="<?php echo htmlspecialchars($jobNumber); ?>"><?php echo htmlspecialchars($jobNumber); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Year Dropdown -->
        <select id="yearSearch" class="p-2 border rounded select2">
            <option></option> <!-- Empty option for placeholder -->
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Type of Work Dropdown -->
        <select id="typeOfWorkSearch" class="p-2 border rounded select2">
            <option></option> <!-- Empty option for placeholder -->
            <option value="">All Types of Work</option>
            <?php foreach ($typesOfWork as $type): ?>
                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Date Range Filters -->
        <div class="flex items-center gap-2">
            <label for="fromDate" class="text-sm font-semibold text-gray-700">From:</label>
            <input type="date" id="fromDate" placeholder="From Date" class="p-2 border rounded">
        </div>
        <div class="flex items-center gap-2">
            <label for="toDate" class="text-sm font-semibold text-gray-700">To:</label>
            <input type="date" id="toDate" placeholder="To Date" class="p-2 border rounded">
        </div>
    </div>

    <!-- Table wrapper -->
    <div class="table-container">
        <table class="min-w-full table-auto border-collapse bg-gray-100 rounded-lg overflow-hidden">
            <thead class="bg-teal-200 text-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left">Job ID</th>
                    <th class="px-6 py-3 text-left">Year</th>
                    <th class="px-6 py-3 text-left">Month</th>
                    <th class="px-6 py-3 text-left">DT Job Number</th>
                    <th class="px-6 py-3 text-left">Labour Hours</th>
                    <th class="px-6 py-3 text-left">Material Cost</th>
                    <th class="px-6 py-3 text-left">Total Cost</th>
                </tr>
            </thead>
            <tbody id="jobTable">
                <!-- Data will be dynamically loaded via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center mt-4">
        <!-- "Previous" and "Next" buttons will be dynamically loaded -->
    </div>

    <!-- Chart Container -->
    <div class="mt-8">
        <canvas id="costChart" width="400" height="200"></canvas>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for each dropdown with specific placeholders
    $('#clientSearch').select2({
        placeholder: "Select a Client",
        allowClear: true,
        width: 'resolve' // Ensure the width matches the original element
    });

    $('#jobNumberSearch').select2({
        placeholder: "Select a DT Job Number",
        allowClear: true,
        width: 'resolve'
    });

    $('#yearSearch').select2({
        placeholder: "Select a Year",
        allowClear: true,
        width: 'resolve'
    });

    $('#typeOfWorkSearch').select2({
        placeholder: "Select a Type of Work",
        allowClear: true,
        width: 'resolve'
    });

    function loadJobs(page = 1) {
        var mainSearch = $('#mainSearch').val();
        var client = $('#clientSearch').val();
        var jobNumber = $('#jobNumberSearch').val();
        var year = $('#yearSearch').val();
        var typeOfWork = $('#typeOfWorkSearch').val();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();

        $.ajax({
            url: 'search.php',
            type: 'POST',
            data: {
                mainSearch: mainSearch,
                client: client,
                jobNumber: jobNumber,
                year: year,
                typeOfWork: typeOfWork,
                fromDate: fromDate,
                toDate: toDate,
                page: page
            },
            success: function(response) {
                // Parse the JSON response
                if(response.success){
                    var tableHtml = '';
                    $.each(response.groupedData, function(yearKey, months) {
                        tableHtml += '<tr class="group-header"><td colspan="7">Year: ' + escapeHtml(yearKey) + '</td></tr>';
                        $.each(months, function(monthKey, jobs) {
                            tableHtml += '<tr class="group-header"><td colspan="7">Month: ' + escapeHtml(monthKey.toLowerCase()) + '</td></tr>';
                            $.each(jobs, function(index, job) {
                                tableHtml += '<tr>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.sr_no) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.Year) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.Month.toLowerCase()) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.DTJobNumber) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.LabourHours) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + escapeHtml(job.MaterialCost) + '</td>';
                                tableHtml += '<td class="px-6 py-4 border-b">' + (parseFloat(job.LabourHours) + parseFloat(job.MaterialCost)) + '</td>';
                                tableHtml += '</tr>';
                            });
                        });
                    });
                    $('#jobTable').html(tableHtml);
                    $('#pagination').html(response.pagination + ' Page ' + page + ' of ' + response.total_pages);
                } else {
                    $('#jobTable').html('<tr><td colspan="7" class="text-center text-red-500">Error loading data.</td></tr>');
                    $('#pagination').html('');
                }
            },
            dataType: 'json'
        });
    }

    // Function to escape HTML to prevent XSS
    function escapeHtml(text) {
        if(!text) return '';
        return $('<div>').text(text).html();
    }

    // Load jobs on page load
    loadJobs();

    // Trigger search on input change or dropdown selection
    $('#mainSearch, #clientSearch, #jobNumberSearch, #yearSearch, #typeOfWorkSearch, #fromDate, #toDate').on('input change', function() {
        loadJobs();
    });

    // Handle pagination click for "Previous" and "Next"
    $(document).on('click', '.pagination-link.prev:not([disabled])', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadJobs(page);
    });

    $(document).on('click', '.pagination-link.next:not([disabled])', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadJobs(page);
    });

    // Handle Export Button Click
    $('#exportButton').on('click', function() {
        var mainSearch = $('#mainSearch').val();
        var client = $('#clientSearch').val();
        var jobNumber = $('#jobNumberSearch').val();
        var year = $('#yearSearch').val();
        var typeOfWork = $('#typeOfWorkSearch').val();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();

        // Create a form dynamically to submit the data
        var form = $('<form action="export.php" method="POST"></form>');
        form.append('<input type="hidden" name="mainSearch" value="' + encodeURIComponent(mainSearch) + '">');
        form.append('<input type="hidden" name="client" value="' + encodeURIComponent(client) + '">');
        form.append('<input type="hidden" name="jobNumber" value="' + encodeURIComponent(jobNumber) + '">');
        form.append('<input type="hidden" name="year" value="' + encodeURIComponent(year) + '">');
        form.append('<input type="hidden" name="typeOfWork" value="' + encodeURIComponent(typeOfWork) + '">');
        form.append('<input type="hidden" name="fromDate" value="' + encodeURIComponent(fromDate) + '">');
        form.append('<input type="hidden" name="toDate" value="' + encodeURIComponent(toDate) + '">');
        $('body').append(form);
        form.submit();
    });

    // Handle Edit Button Click
    $(document).on('click', '.edit-button', function() {
        var jobId = $(this).data('id');
        window.location.href = 'edit_job.php?id=' + jobId;
    });

    // Handle Delete Button Click
    $(document).on('click', '.delete-button', function() {
        var jobId = $(this).data('id');
        if (confirm('Are you sure you want to delete this job?')) {
            $.ajax({
                url: 'delete_job.php',
                type: 'POST',
                data: { id: jobId },
                success: function(response) {
                    if (response.success) {
                        loadJobs();
                    } else {
                        alert('Error deleting job.');
                    }
                },
                dataType: 'json'
            });
        }
    });

    // Generate Chart
    var ctx = document.getElementById('costChart').getContext('2d');
    var costChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function($job) { return $job['Month']; }, $costData)); ?>,
            datasets: [{
                label: 'Total Cost',
                data: <?php echo json_encode(array_map(function($job) { return $job['LabourHours'] + $job['MaterialCost']; }, $costData)); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
</body>
</html>
