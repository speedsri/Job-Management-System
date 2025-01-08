<?php
session_start();
include('db_conn.php');

// Initialize response array
$response = [
    'success' => false,
    'groupedData' => [],
    'pagination' => ''
];

// Define how many records per page
$records_per_page = 10;

// Retrieve and sanitize POST parameters
$mainSearch = trim($_POST['mainSearch'] ?? '');
$client = trim($_POST['client'] ?? '');
$jobNumber = trim($_POST['jobNumber'] ?? '');
$year = trim($_POST['year'] ?? '');
$typeOfWork = trim($_POST['typeOfWork'] ?? '');
$fromDate = trim($_POST['fromDate'] ?? '');
$toDate = trim($_POST['toDate'] ?? '');
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;

// Initialize query parts
$where = [];
$params = [];

// Apply filters
if (!empty($mainSearch)) {
    $where[] = "(Year LIKE :mainSearch OR Month LIKE :mainSearch OR DTJobNumber LIKE :mainSearch OR HOJobNumber LIKE :mainSearch OR Client LIKE :mainSearch OR DescriptionOfWork LIKE :mainSearch OR TypeOfWork LIKE :mainSearch OR Remarks LIKE :mainSearch)";
    $params[':mainSearch'] = '%' . $mainSearch . '%';
}

if (!empty($client)) {
    $where[] = "Client = :client";
    $params[':client'] = $client;
}

if (!empty($jobNumber)) {
    $where[] = "DTJobNumber = :jobNumber";
    $params[':jobNumber'] = $jobNumber;
}

if (!empty($year)) {
    $where[] = "Year = :year";
    $params[':year'] = $year;
}

if (!empty($typeOfWork)) {
    $where[] = "TypeOfWork = :typeOfWork";
    $params[':typeOfWork'] = $typeOfWork;
}

if (!empty($fromDate)) {
    $where[] = "DateOpened >= :fromDate";
    $params[':fromDate'] = $fromDate;
}

if (!empty($toDate)) {
    $where[] = "DateOpened <= :toDate";
    $params[':toDate'] = $toDate;
}

// Build the WHERE clause
$whereClause = '';
if (!empty($where)) {
    $whereClause = 'WHERE ' . implode(' AND ', $where);
}

// Count total records for pagination
try {
    $countQuery = "SELECT COUNT(*) FROM jayantha_1500_table $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error appropriately
    echo json_encode($response);
    exit();
}

// Calculate pagination
$total_pages = ceil($total_records / $records_per_page);
$page = max($page, 1);
$page = min($page, $total_pages);
$offset = ($page - 1) * $records_per_page;

// Fetch the relevant records
try {
    // Modified ORDER BY to sort months numerically
    $dataQuery = "SELECT * FROM jayantha_1500_table $whereClause ORDER BY Year ASC, MONTH(STR_TO_DATE(Month, '%M')) ASC LIMIT :limit OFFSET :offset";
    $dataStmt = $pdo->prepare($dataQuery);

    // Bind parameters
    foreach ($params as $key => &$val) {
        $dataStmt->bindParam($key, $val);
    }
    $dataStmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $dataStmt->execute();
    $jobs = $dataStmt->fetchAll();
} catch (PDOException $e) {
    // Handle error appropriately
    echo json_encode($response);
    exit();
}

// Group the data by Year and then by Month
$groupedData = [];
foreach ($jobs as $job) {
    $jobYear = $job['Year'];
    $jobMonth = $job['Month'];

    if (!isset($groupedData[$jobYear])) {
        $groupedData[$jobYear] = [];
    }

    if (!isset($groupedData[$jobYear][$jobMonth])) {
        $groupedData[$jobYear][$jobMonth] = [];
    }

    $groupedData[$jobYear][$jobMonth][] = $job;
}

// Generate "Previous" and "Next" buttons
$pagination = '';

// Previous Button
if ($page > 1) {
    $prevPage = $page - 1;
    $pagination .= '<button class="pagination-link prev" data-page="' . $prevPage . '">Previous</button>';
} else {
    $pagination .= '<button class="pagination-link prev" disabled>Previous</button>';
}

// Next Button
if ($page < $total_pages) {
    $nextPage = $page + 1;
    $pagination .= '<button class="pagination-link next" data-page="' . $nextPage . '">Next</button>';
} else {
    $pagination .= '<button class="pagination-link next" disabled>Next</button>';
}

// Populate the response
$response['success'] = true;
$response['groupedData'] = $groupedData;
$response['pagination'] = $pagination;

// Return the response as JSON
echo json_encode($response);
exit();
?>
