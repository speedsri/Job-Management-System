<?php
include('db_conn.php');

// Retrieve POST parameters
$mainSearch = isset($_POST['mainSearch']) ? trim($_POST['mainSearch']) : '';
$client = isset($_POST['client']) ? trim($_POST['client']) : '';
$jobNumber = isset($_POST['jobNumber']) ? trim($_POST['jobNumber']) : '';
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$typeOfWork = isset($_POST['typeOfWork']) ? trim($_POST['typeOfWork']) : '';
$fromDate = isset($_POST['fromDate']) ? trim($_POST['fromDate']) : '';
$toDate = isset($_POST['toDate']) ? trim($_POST['toDate']) : '';

// Build the base SQL query
$sql = "SELECT * FROM jayantha_1500_table WHERE 1=1";
$params = [];

// Add main search condition
if (!empty($mainSearch)) {
    $sql .= " AND (Client LIKE :mainSearch 
                OR DTJobNumber LIKE :mainSearch 
                OR Year LIKE :mainSearch 
                OR TypeOfWork LIKE :mainSearch 
                OR DescriptionOfWork LIKE :mainSearch 
                OR Remarks LIKE :mainSearch)";
    $params[':mainSearch'] = '%' . $mainSearch . '%';
}

// Add conditions based on individual search inputs
if (!empty($client)) {
    $sql .= " AND Client LIKE :client";
    $params[':client'] = '%' . $client . '%';
}
if (!empty($jobNumber)) {
    $sql .= " AND DTJobNumber LIKE :jobNumber";
    $params[':jobNumber'] = '%' . $jobNumber . '%';
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
    header("Content-Disposition: attachment; filename=job_details.xls");

    // Start of the Excel file
    echo "<table border='1'>";
    echo "<tr>
         
            <th>Year</th>
            <th>DT Job Number</th>
            <th>Client</th>
            <th>Date Opened</th>
            <th>Description of Work</th>
            <th>Remarks</th>
        </tr>";

    // Populate the table with data
    foreach ($jobs as $job) {
        echo "<tr>
             
                <td>" . htmlspecialchars($job['Year']) . "</td>
                <td>" . htmlspecialchars($job['DTJobNumber']) . "</td>
                <td>" . htmlspecialchars($job['Client']) . "</td>
                <td>" . htmlspecialchars($job['DateOpened']) . "</td>
                <td>" . htmlspecialchars($job['DescriptionOfWork']) . "</td>
                <td>" . htmlspecialchars($job['Remarks']) . "</td>
            </tr>";
    }

    echo "</table>";
} catch (PDOException $e) {
    // Handle error appropriately
    echo "Error exporting data.";
}
?>
