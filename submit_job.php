<?php
session_start();
include('db_conn.php');

// Retrieve and sanitize form inputs
$year = trim($_POST['Year'] ?? '');
$month = trim($_POST['Month'] ?? '');
$dtJobNumber = trim($_POST['DTJobNumber'] ?? '');
$hoJobNumber = trim($_POST['HOJobNumber'] ?? '');
$client = trim($_POST['Client'] ?? '');
$dateOpened = trim($_POST['DateOpened'] ?? '');
$descriptionOfWork = trim($_POST['DescriptionOfWork'] ?? '');
$TARGET_DATE = trim($_POST['TARGET_DATE'] ?? '');
$completionDate = trim($_POST['CompletionDate'] ?? '');
$deliveredDate = trim($_POST['DeliveredDate'] ?? '');
$fileClosed = trim($_POST['FileClosed'] ?? '0');
$labourHours = trim($_POST['LabourHours'] ?? '0.00');
$materialCost = trim($_POST['MaterialCost'] ?? '0.00');
$typeOfWork = trim($_POST['TypeOfWork'] ?? '');
$remarks = trim($_POST['Remarks'] ?? '');

// Initialize an array to hold error messages
$errors = [];

// Validate required fields
if (empty($year)) {
    $errors[] = 'Year is required.';
} elseif (!ctype_digit($year) || (int)$year < 1900 || (int)$year > 2100) {
    $errors[] = 'Year must be a valid four-digit number.';
}

if (empty($month)) {
    $errors[] = 'Month is required.';
}

if (empty($dtJobNumber)) {
    $errors[] = 'DT Job Number is required.';
}

if (empty($client)) {
    $errors[] = 'Client is required.';
}

if (empty($dateOpened)) {
    $errors[] = 'Date Opened is required.';
} else {
    $d = DateTime::createFromFormat('Y-m-d', $dateOpened);
    if (!($d && $d->format('Y-m-d') === $dateOpened)) {
        $errors[] = 'Date Opened must be in YYYY-MM-DD format.';
    }
}

if (empty($descriptionOfWork)) {
    $errors[] = 'Description of Work is required.';
}

if (empty($TARGET_DATE)) {
    $errors[] = 'Target Date is required.';
} else {
    $d = DateTime::createFromFormat('Y-m-d', $TARGET_DATE);
    if (!($d && $d->format('Y-m-d') === $TARGET_DATE)) {
        $errors[] = 'Target Date must be in YYYY-MM-DD format.';
    }
}

// Validate optional date fields if provided
if (!empty($completionDate)) {
    $d = DateTime::createFromFormat('Y-m-d', $completionDate);
    if (!($d && $d->format('Y-m-d') === $completionDate)) {
        $errors[] = 'Completion Date must be in YYYY-MM-DD format.';
    }
}

if (!empty($deliveredDate)) {
    $d = DateTime::createFromFormat('Y-m-d', $deliveredDate);
    if (!($d && $d->format('Y-m-d') === $deliveredDate)) {
        $errors[] = 'Delivered Date must be in YYYY-MM-DD format.';
    }
}

// Validate numeric fields
if (!empty($labourHours) && !is_numeric($labourHours)) {
    $errors[] = 'Labour Hours must be a valid number.';
}

if (!empty($materialCost) && !is_numeric($materialCost)) {
    $errors[] = 'Material Cost must be a valid number.';
}

// If there are errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['message_type'] = 'error';
    header("Location: dashboard.php");
    exit();
}

try {
    // Prepare the SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO jayantha_1500_table (
            Year, 
            Month, 
            DTJobNumber, 
            HOJobNumber, 
            Client, 
            DateOpened, 
            DescriptionOfWork, 
            TARGET_DATE, 
            CompletionDate, 
            DeliveredDate, 
            FileClosed, 
            LabourHours, 
            MaterialCost, 
            TypeOfWork, 
            Remarks
        ) VALUES (
            :year, 
            :month, 
            :dtJobNumber, 
            :hoJobNumber, 
            :client, 
            :dateOpened, 
            :descriptionOfWork, 
            :TARGET_DATE, 
            :completionDate, 
            :deliveredDate, 
            :fileClosed, 
            :labourHours, 
            :materialCost, 
            :typeOfWork, 
            :remarks
        )
    ");

    // Execute the statement with bound parameters
    $stmt->execute([
        ':year' => $year,
        ':month' => $month,
        ':dtJobNumber' => $dtJobNumber,
        ':hoJobNumber' => $hoJobNumber,
        ':client' => $client,
        ':dateOpened' => $dateOpened,
        ':descriptionOfWork' => $descriptionOfWork,
        ':TARGET_DATE' => $TARGET_DATE,
        ':completionDate' => $completionDate ?: null, // Allow NULL
        ':deliveredDate' => $deliveredDate ?: null, // Allow NULL
        ':fileClosed' => $fileClosed,
        ':labourHours' => $labourHours,
        ':materialCost' => $materialCost,
        ':typeOfWork' => $typeOfWork ?: null, // Allow NULL
        ':remarks' => $remarks ?: null // Allow NULL
    ]);

    $_SESSION['message'] = 'Job details added successfully!';
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    // Handle insertion errors
    $_SESSION['message'] = 'Error adding job details: ' . htmlspecialchars($e->getMessage());
    $_SESSION['message_type'] = 'error';
}

header("Location: dashboard.php");
exit();
?>
