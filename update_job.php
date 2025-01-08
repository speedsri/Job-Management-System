<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $Year = $_POST['Year'];
    $Month = $_POST['Month'];
    $DTJobNumber = $_POST['DTJobNumber'];
    $HOJobNumber = $_POST['HOJobNumber'];
    $Client = $_POST['Client'];
    $DateOpened = $_POST['DateOpened'];
    $DescriptionOfWork = $_POST['DescriptionOfWork'];
    $TARGET_DATE = $_POST['TARGET_DATE'];
    $CompletionDate = $_POST['CompletionDate'];
    $DeliveredDate = $_POST['DeliveredDate'];
    $FileClosed = $_POST['FileClosed'];
    $LabourHours = $_POST['LabourHours'];
    $MaterialCost = $_POST['MaterialCost'];
    $TypeOfWork = $_POST['TypeOfWork'];
    $Remarks = $_POST['Remarks'];

    try {
        $stmt = $pdo->prepare("UPDATE jayantha_1500_table SET
            Year = :Year,
            Month = :Month,
            DTJobNumber = :DTJobNumber,
            HOJobNumber = :HOJobNumber,
            Client = :Client,
            DateOpened = :DateOpened,
            DescriptionOfWork = :DescriptionOfWork,
            TARGET_DATE = :TARGET_DATE,
            CompletionDate = :CompletionDate,
            DeliveredDate = :DeliveredDate,
            FileClosed = :FileClosed,
            LabourHours = :LabourHours,
            MaterialCost = :MaterialCost,
            TypeOfWork = :TypeOfWork,
            Remarks = :Remarks
            WHERE sr_no = :id");

        $stmt->bindParam(':Year', $Year);
        $stmt->bindParam(':Month', $Month);
        $stmt->bindParam(':DTJobNumber', $DTJobNumber);
        $stmt->bindParam(':HOJobNumber', $HOJobNumber);
        $stmt->bindParam(':Client', $Client);
        $stmt->bindParam(':DateOpened', $DateOpened);
        $stmt->bindParam(':DescriptionOfWork', $DescriptionOfWork);
        $stmt->bindParam(':TARGET_DATE', $TARGET_DATE);
        $stmt->bindParam(':CompletionDate', $CompletionDate);
        $stmt->bindParam(':DeliveredDate', $DeliveredDate);
        $stmt->bindParam(':FileClosed', $FileClosed);
        $stmt->bindParam(':LabourHours', $LabourHours);
        $stmt->bindParam(':MaterialCost', $MaterialCost);
        $stmt->bindParam(':TypeOfWork', $TypeOfWork);
        $stmt->bindParam(':Remarks', $Remarks);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        $_SESSION['message'] = 'Job details updated successfully.';
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error updating job details: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }

    header("Location: view_jobs.php");
    exit();
} else {
    header("Location: view_jobs.php");
    exit();
}
?>
