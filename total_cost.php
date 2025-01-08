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
    // Retrieve POST parameters (existing search filters)
    $columns = isset($_POST['columns']) ? $_POST['columns'] : [
        'sr_no', 'Year', 'Month', 'DTJobNumber', 'HOJobNumber', 'Client',
        'DateOpened', 'DescriptionOfWork', 'TargetDate', 'CompletionDate',
        'DeliveredDate', 'FileClosed', 'LabourHours', 'MaterialCost',
        'TypeOfWork', 'Remarks'
    ];
    $mainSearch = isset($_POST['mainSearch']) ? trim($_POST['mainSearch']) : '';
    $client = isset($_POST['client']) ? trim($_POST['client']) : '';
    $jobNumber = isset($_POST['jobNumber']) ? trim($_POST['jobNumber']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $typeOfWork = isset($_POST['typeOfWork']) ? trim($_POST['typeOfWork']) : '';
    $fromDate = isset($_POST['fromDate']) ? trim($_POST['fromDate']) : '';
    $toDate = isset($_POST['toDate']) ? trim($_POST['toDate']) : '';

    // Build the SQL query with selected columns and filters
    $selectColumns = implode(', ', $columns);
    $sql = "SELECT $selectColumns FROM jayantha_1500_table WHERE 1=1";
    $params = [];

    // Add conditions to the SQL query based on POST filters
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

    // Add other conditions like client, job number, etc.
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

    try {
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        foreach ($params as $key => &$value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($jobs);

        // Calculate and insert total cost into the new table
        foreach ($jobs as $job) {
            // Calculate total cost
            $labourCost = $job['LabourHours'] * 50; // Assuming 50 is the labour rate per hour
            $materialCost = $job['MaterialCost'];
            $totalCost = $labourCost + $materialCost;

            // Insert total cost into the new table
            $insertSql = "INSERT INTO total_costs (job_id, labour_cost, material_cost, total_cost) 
                          VALUES (:job_id, :labour_cost, :material_cost, :total_cost)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindValue(':job_id', $job['sr_no']);
            $insertStmt->bindValue(':labour_cost', $labourCost);
            $insertStmt->bindValue(':material_cost', $materialCost);
            $insertStmt->bindValue(':total_cost', $totalCost);
            $insertStmt->execute();
        }

    } catch (PDOException $e) {
        echo "Error fetching data: " . htmlspecialchars($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Search and Total Cost</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Job Search and Total Cost Calculation</h1>

        <!-- Filter and search form -->
        <!-- Existing form elements -->

        <!-- Display the jobs and their total costs -->
        <?php if (!empty($jobs)): ?>
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4">Search Results (<?php echo $totalResults; ?> records)</h2>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <?php foreach ($columns as $column): ?>
                                <th class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                            <th class="border border-gray-300 px-4 py-2">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr class="hover:bg-gray-100">
                                <?php foreach ($columns as $column): ?>
                                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($job[$column] ?? ''); ?></td>
                                <?php endforeach; ?>
                                <td class="border border-gray-300 px-4 py-2">
                                    <?php 
                                        $labourCost = $job['LabourHours'] * 50; // Example Labour Rate
                                        $totalCost = $labourCost + $job['MaterialCost'];
                                        echo number_format($totalCost, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
