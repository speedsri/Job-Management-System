<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include('db_conn.php');

// Function to generate dropdown options
function generateDropdownOptions($pdo, $column) {
    $query = "SELECT DISTINCT $column FROM jayantha_1500_table ORDER BY $column";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch distinct values for dropdowns
$clients = generateDropdownOptions($pdo, 'Client');
$years = generateDropdownOptions($pdo, 'Year');
$typeOfWorks = generateDropdownOptions($pdo, 'TypeOfWork');

// Check if export is requested
if (isset($_POST['export'])) {
    // Retrieve POST parameters
    $columns = isset($_POST['columns']) ? $_POST['columns'] : ['Year', 'DTJobNumber', 'Client', 'DateOpened', 'DescriptionOfWork', 'Remarks'];
    $mainSearch = isset($_POST['mainSearch']) ? trim($_POST['mainSearch']) : '';
    $client = isset($_POST['client']) ? trim($_POST['client']) : '';
    $jobNumber = isset($_POST['jobNumber']) ? trim($_POST['jobNumber']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $typeOfWork = isset($_POST['typeOfWork']) ? trim($_POST['typeOfWork']) : '';
    $fromDate = isset($_POST['fromDate']) ? trim($_POST['fromDate']) : '';
    $toDate = isset($_POST['toDate']) ? trim($_POST['toDate']) : '';

    // Build the base SQL query with selected columns
    $selectColumns = implode(', ', $columns);
    $sql = "SELECT $selectColumns FROM jayantha_1500_table WHERE 1=1";
    $params = [];

    // Add main search condition
    if (!empty($mainSearch)) {
        $searchConditions = [];
        $searchColumns = ['Client', 'DTJobNumber', 'Year', 'TypeOfWork', 'DescriptionOfWork', 'Remarks'];
        
        foreach ($searchColumns as $searchColumn) {
            $searchConditions[] = "$searchColumn LIKE :mainSearch";
        }
        
        $sql .= " AND (" . implode(' OR ', $searchConditions) . ")";
        $params[':mainSearch'] = '%' . $mainSearch . '%';
    }

    // Add conditions based on individual search inputs
    if (!empty($client)) {
        $sql .= " AND Client = :client";
        $params[':client'] = $client;
    }

    if (!empty($jobNumber)) {
        $sql .= " AND DTJobNumber = :jobNumber";
        $params[':jobNumber'] = $jobNumber;
    }

    if (!empty($year)) {
        $sql .= " AND Year = :year";
        $params[':year'] = $year;
    }

    if (!empty($typeOfWork)) {
        $sql .= " AND TypeOfWork = :typeOfWork";
        $params[':typeOfWork'] = $typeOfWork;
    }

    if (!empty($fromDate) && !empty($toDate)) {
        $sql .= " AND DateOpened BETWEEN :fromDate AND :toDate";
        $params[':fromDate'] = $fromDate;
        $params[':toDate'] = $toDate;
    }

    // Execute the query
    try {
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => &$value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers to download the file as Excel
        header("Content-Type: application/vnd.ms-excel");
        
        // Generate dynamic filename based on filters
        $filename = 'job_details';
        if (!empty($client)) $filename .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $client);
        if (!empty($year)) $filename .= '_' . $year;
        if (!empty($typeOfWork)) $filename .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $typeOfWork);
        $filename .= '_' . date('YmdHis');
        
        header("Content-Disposition: attachment; filename={$filename}.xls");
        
        // Start of the Excel file
        echo "<table border='1'>";
        
        // Print headers based on selected columns
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        // Populate the table with data
        foreach ($jobs as $job) {
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<td>" . htmlspecialchars($job[$column]) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        exit();
    } catch (PDOException $e) {
        // Handle error appropriately
        echo "Error exporting data: " . htmlspecialchars($e->getMessage());
        exit();
    }
}

// If not exporting, proceed with displaying the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Search and Export</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Job Search and Export</h1>
        
        <form method="POST" class="space-y-4">
            <!-- Main Search -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Main Search</label>
                    <input type="text" name="mainSearch" placeholder="Search across all fields" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Client Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Client</label>
                    <select name="client" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Clients</option>
                        <?php foreach ($clients as $clientOption): ?>
                            <option value="<?php echo htmlspecialchars($clientOption); ?>"><?php echo htmlspecialchars($clientOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Job Number Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Job Number</label>
                    <input type="text" name="jobNumber" placeholder="Specific Job Number" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <!-- Year Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Year</label>
                    <select name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Years</option>
                        <?php foreach ($years as $yearOption): ?>
                            <option value="<?php echo htmlspecialchars($yearOption); ?>"><?php echo htmlspecialchars($yearOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type of Work Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type of Work</label>
                    <select name="typeOfWork" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Types</option>
                        <?php foreach ($typeOfWorks as $typeOption): ?>
                            <option value="<?php echo htmlspecialchars($typeOption); ?>"><?php echo htmlspecialchars($typeOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" name="fromDate" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <input type="date" name="toDate" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Column Selection -->
            <div class="mt-4">
                <h2 class="text-lg font-semibold mb-2">Select Columns to Export</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <?php 
                    $allColumns = ['Year', 'Month', 'DTJobNumber', 'HOJobNumber', 'Client', 'DateOpened', 'DescriptionOfWork', 'TypeOfWork', 'Remarks'];
                    foreach ($allColumns as $column): ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="columns[]" value="<?php echo $column; ?>" 
                                   class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   <?php echo in_array($column, ['Year', 'DTJobNumber', 'Client', 'DateOpened', 'DescriptionOfWork', 'Remarks']) ? 'checked' : ''; ?>>
                            <label><?php echo $column; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4 mt-4">
                <button type="submit" name="export" 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Export to Excel
                </button>
            </div>
        </form>
    </div>
</body>
</html>