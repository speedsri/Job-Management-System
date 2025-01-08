<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_conn.php');

// Fetch distinct TypeOfWork for the dropdown
try {
    $typeQuery = "SELECT DISTINCT TypeOfWork FROM jayantha_1500_table WHERE TypeOfWork IS NOT NULL AND TypeOfWork != '' ORDER BY TypeOfWork ASC";
    $typeStmt = $pdo->prepare($typeQuery);
    $typeStmt->execute();
    $typesOfWork = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error appropriately
    $typesOfWork = [];
}

// Fetch distinct Years for the dropdown
try {
    $yearQuery = "SELECT DISTINCT Year FROM jayantha_1500_table WHERE Year IS NOT NULL ORDER BY Year ASC";
    $yearStmt = $pdo->prepare($yearQuery);
    $yearStmt->execute();
    $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error appropriately
    $years = [];
}

// Fetch distinct DT Job Numbers for the dropdown
try {
    $jobNumberQuery = "SELECT DISTINCT DTJobNumber FROM jayantha_1500_table WHERE DTJobNumber IS NOT NULL AND DTJobNumber != '' ORDER BY DTJobNumber ASC";
    $jobNumberStmt = $pdo->prepare($jobNumberQuery);
    $jobNumberStmt->execute();
    $dtJobNumbers = $jobNumberStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error appropriately
    $dtJobNumbers = [];
}

// Fetch distinct Clients for the dropdown
try {
    $clientQuery = "SELECT DISTINCT Client FROM jayantha_1500_table WHERE Client IS NOT NULL AND Client != '' ORDER BY Client ASC";
    $clientStmt = $pdo->prepare($clientQuery);
    $clientStmt->execute();
    $clients = $clientStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Handle error appropriately
    $clients = [];
}
?>

<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head Content -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <link href="css/all.min.css" rel="stylesheet">
    <link href="font/css/all.min.css" rel="stylesheet">
    <link href="css/select2.min.css" rel="stylesheet" />
    <script src="css/jquery-3.6.0.min.js"></script>
    <script src="css/select2.min.js"></script>
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
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-3xl font-bold text-center text-teal-600 mb-6">Generate Report</h2>

    <!-- Display Success or Error Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="p-4 mb-4 <?php echo ($_SESSION['message_type'] == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <form action="generate_report_excel.php" method="POST">
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700">Select Columns to Include in Report:</label>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="sr_no" id="JobID" class="mr-2">
                    <label for="JobID">sr_no</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Year" id="Year" class="mr-2">
                    <label for="Year">Year</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Month" id="Month" class="mr-2">
                    <label for="Month">Month</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="DT Job Number" id="DTJobNumber" class="mr-2">
                    <label for="DTJobNumber">DT Job Number</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="HO Job Number" id="HOJobNumber" class="mr-2">
                    <label for="HOJobNumber">HO Job Number</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Client" id="Client" class="mr-2">
                    <label for="Client">Client</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Date Opened" id="DateOpened" class="mr-2">
                    <label for="DateOpened">Date Opened</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Description of Work" id="DescriptionOfWork" class="mr-2">
                    <label for="DescriptionOfWork">Description of Work</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Target Date" id="TargetDate" class="mr-2">
                    <label for="TargetDate">Target Date</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Completion Date" id="CompletionDate" class="mr-2">
                    <label for="CompletionDate">Completion Date</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Delivered Date" id="DeliveredDate" class="mr-2">
                    <label for="DeliveredDate">Delivered Date</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="File Closed" id="FileClosed" class="mr-2">
                    <label for="FileClosed">File Closed</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Labour Hours" id="LabourHours" class="mr-2">
                    <label for="LabourHours">Labour Hours</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Material Cost" id="MaterialCost" class="mr-2">
                    <label for="MaterialCost">Material Cost</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Type of Work" id="TypeOfWork" class="mr-2">
                    <label for="TypeOfWork">Type of Work</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="columns[]" value="Remarks" id="Remarks" class="mr-2">
                    <label for="Remarks">Remarks</label>
                </div>
            </div>
        </div>

        <!-- Main Search Bar -->
        <div class="mb-6 flex flex-wrap gap-4">
            <input type="text" name="mainSearch" placeholder="Search..." class="p-2 border rounded w-full md:w-1/2">
        </div>

        <!-- Search Filters -->
        <div class="mb-6 flex flex-wrap gap-4">
            <!-- Client Dropdown -->
            <select name="client" id="clientSearch" class="p-2 border rounded select2">
                <option></option> <!-- Empty option for placeholder -->
                <option value="">All Clients</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo htmlspecialchars($client); ?>"><?php echo htmlspecialchars($client); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- DT Job Number Dropdown -->
            <select name="jobNumber" id="jobNumberSearch" class="p-2 border rounded select2">
                <option></option> <!-- Empty option for placeholder -->
                <option value="">All DT Job Numbers</option>
                <?php foreach ($dtJobNumbers as $jobNumber): ?>
                    <option value="<?php echo htmlspecialchars($jobNumber); ?>"><?php echo htmlspecialchars($jobNumber); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Year Dropdown -->
            <select name="year" id="yearSearch" class="p-2 border rounded select2">
                <option></option> <!-- Empty option for placeholder -->
                <option value="">All Years</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Type of Work Dropdown -->
            <select name="typeOfWork" id="typeOfWorkSearch" class="p-2 border rounded select2">
                <option></option> <!-- Empty option for placeholder -->
                <option value="">All Types of Work</option>
                <?php foreach ($typesOfWork as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Date Range Filters -->
            <div class="flex items-center gap-2">
                <label for="fromDate" class="text-sm font-semibold text-gray-700">From:</label>
                <input type="date" name="fromDate" id="fromDate" placeholder="From Date" class="p-2 border rounded">
            </div>
            <div class="flex items-center gap-2">
                <label for="toDate" class="text-sm font-semibold text-gray-700">To:</label>
                <input type="date" name="toDate" id="toDate" placeholder="To Date" class="p-2 border rounded">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="export-button">Generate Report</button>
        </div>
    </form>
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
});
</script>
</body>
</html>
