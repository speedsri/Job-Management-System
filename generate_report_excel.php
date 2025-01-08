<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_conn.php');
require 'vendor/autoload.php'; // Include the PHPSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch the selected columns and search parameters
$columns = isset($_POST['columns']) ? $_POST['columns'] : [];
$mainSearch = isset($_POST['mainSearch']) ? $_POST['mainSearch'] : '';
$client = isset($_POST['client']) ? $_POST['client'] : '';
$jobNumber = isset($_POST['jobNumber']) ? $_POST['jobNumber'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';
$typeOfWork = isset($_POST['typeOfWork']) ? $_POST['typeOfWork'] : '';
$fromDate = isset($_POST['fromDate']) ? $_POST['fromDate'] : '';
$toDate = isset($_POST['toDate']) ? $_POST['toDate'] : '';

// Build the WHERE clause based on the search parameters
$where = [];
$params = [];

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

$whereClause = '';
if (!empty($where)) {
    $whereClause = 'WHERE ' . implode(' AND ', $where);
}

// Fetch the data based on the selected columns
$selectColumns = implode(', ', $columns);
$query = "SELECT $selectColumns FROM jayantha_1500_table $whereClause";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the column headers
$columnIndex = 1;
foreach ($columns as $column) {
    $sheet->setCellValueByColumnAndRow($columnIndex, 1, $column);
    $columnIndex++;
}

// Set the data rows
$rowIndex = 2;
foreach ($jobs as $job) {
    $columnIndex = 1;
    foreach ($columns as $column) {
        $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $job[$column]);
        $columnIndex++;
    }
    $rowIndex++;
}

// Set the file name
$fileName = 'job_report_' . date('YmdHis') . '.xlsx';

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
