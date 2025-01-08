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

// Initialize variables for search results
$jobs = [];
$totalResults = 0;

// Check if search or export is requested
if (isset($_POST['search']) || isset($_POST['export'])) {
    // Retrieve POST parameters
    $columns = isset($_POST['columns']) ? $_POST['columns'] : [];
    $mainSearch = isset($_POST['mainSearch']) ? trim($_POST['mainSearch']) : '';
    $client = isset($_POST['client']) ? trim($_POST['client']) : '';
    $jobNumber = isset($_POST['jobNumber']) ? trim($_POST['jobNumber']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $typeOfWork = isset($_POST['typeOfWork']) ? trim($_POST['typeOfWork']) : '';
    $fromDate = isset($_POST['fromDate']) ? trim($_POST['fromDate']) : '';
    $toDate = isset($_POST['toDate']) ? trim($_POST['toDate']) : '';

    // Ensure that at least one column is selected
    if (empty($columns)) {
        echo "Error: No columns selected for export.";
        exit();
    }

    // Build the base SQL query with selected columns
    $selectColumns = implode(', ', $columns);
    $sql = "SELECT $selectColumns FROM jayantha_1500_table WHERE 1=1";
    $params = [];

    // Add main search condition
    if (!empty($mainSearch)) {
        $searchConditions = [];
        $searchColumns = [
            'Client', 'DTJobNumber', 'HOJobNumber', 'Year', 'TypeOfWork',
            'DescriptionOfWork', 'Remarks'
        ];

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

    // Export to Excel if export button is clicked
    if (isset($_POST['export'])) {
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

    // If not exporting, fetch results for display
    try {
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        foreach ($params as $key => &$value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($jobs);
    } catch (PDOException $e) {
        echo "Error fetching data: " . htmlspecialchars($e->getMessage());
    }
}

// All columns for checkbox selection
$allColumns = [
    'sr_no', 'Year', 'Month', 'DTJobNumber', 'HOJobNumber', 'Client',
    'DateOpened', 'DescriptionOfWork', 'TargetDate', 'CompletionDate',
    'DeliveredDate', 'FileClosed', 'LabourHours', 'MaterialCost',
    'TypeOfWork', 'Remarks'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Search and Export</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-blue-300 p-6">
    <div class="container mx-auto bg-white shadow-md rounded-lg p-6">
        <!-- Back Button -->
        <a href="view_jobs.php" class="absolute top-0 left-0 mt-4 ml-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Back
        </a>

        <h1 class="text-2xl font-bold text-center mb-4">Job Search and Export</h1>

        <form method="POST" class="space-y-4">
            <!-- Main Search -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Main Search</label>
                    <input type="text" name="mainSearch" placeholder="Search across all fields"
                           value="<?php echo htmlspecialchars($mainSearch ?? ''); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Client Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Client</label>
                    <select name="client" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Clients</option>
                        <?php foreach ($clients as $clientOption): ?>
                            <option value="<?php echo htmlspecialchars($clientOption); ?>"
                                    <?php echo ($client == $clientOption) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($clientOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Job Number Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Job Number</label>
                    <input type="text" name="jobNumber" placeholder="Specific Job Number"
                           value="<?php echo htmlspecialchars($jobNumber ?? ''); ?>"
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
                            <option value="<?php echo htmlspecialchars($yearOption); ?>"
                                    <?php echo ($year == $yearOption) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($yearOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type of Work Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type of Work</label>
                    <select name="typeOfWork" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Types</option>
                        <?php foreach ($typeOfWorks as $typeOption): ?>
                            <option value="<?php echo htmlspecialchars($typeOption); ?>"
                                    <?php echo ($typeOfWork == $typeOption) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($typeOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" name="fromDate"
                               value="<?php echo htmlspecialchars($fromDate ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <input type="date" name="toDate"
                               value="<?php echo htmlspecialchars($toDate ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Column Selection -->
            <div class="mt-4">
                <h2 class="text-lg font-semibold mb-2">Select Columns to Export</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <?php foreach ($allColumns as $column): ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="columns[]" value="<?php echo $column; ?>"
                                   class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label><?php echo $column; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4 mt-4">
                <button type="submit" name="search"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Search
                </button>
                <button type="submit" name="export"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Export to Excel
                </button>
            </div>
        </form>

        <!-- Search Results -->
        <?php if (!empty($jobs)): ?>
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4">Search Results (<?php echo $totalResults; ?> records)</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <?php foreach ($columns as $column): ?>
                                    <th class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr class="hover:bg-gray-100">
                                    <?php foreach ($columns as $column): ?>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <?php echo htmlspecialchars($job[$column] ?? ''); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
