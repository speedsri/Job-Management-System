<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('db_conn.php');

// Fetch job details based on the job ID
$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($jobId <= 0) {
    header("Location: view_job.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM jayantha_1500_table WHERE sr_no = :id");
    $stmt->bindParam(':id', $jobId, PDO::PARAM_INT);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        header("Location: view_job.php");
        exit();
    }
} catch (PDOException $e) {
    // Handle error appropriately
    header("Location: view_job.php");
    exit();
}

// Fetch clients from the client_list table
try {
    $stmt = $pdo->prepare("SELECT client FROM client_list ORDER BY client ASC");
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle query errors
    $clients = [];
}
?>

<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Details</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-green-50">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4 text-blue-600">Edit Job Details</h2>

        <!-- Success or Error Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 <?php echo ($_SESSION['message_type'] == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php
                    echo htmlspecialchars($_SESSION['message']);
                    unset($_SESSION['message'], $_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <form action="update_job.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($job['sr_no']); ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="Year" class="block text-sm font-semibold text-gray-700">Year</label>
                    <input type="number" name="Year" id="Year" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['Year']); ?>" required>
                </div>
                <div>
                    <label for="Month" class="block text-sm font-semibold text-gray-700">Month</label>
                    <select name="Month" id="Month" class="mt-1 p-2 w-full border rounded-md" required>
                        <option value="" disabled>Select a month</option>
                        <option value="January" <?php echo ($job['Month'] == 'January') ? 'selected' : ''; ?>>January</option>
                        <option value="February" <?php echo ($job['Month'] == 'February') ? 'selected' : ''; ?>>February</option>
                        <option value="March" <?php echo ($job['Month'] == 'March') ? 'selected' : ''; ?>>March</option>
                        <option value="April" <?php echo ($job['Month'] == 'April') ? 'selected' : ''; ?>>April</option>
                        <option value="May" <?php echo ($job['Month'] == 'May') ? 'selected' : ''; ?>>May</option>
                        <option value="June" <?php echo ($job['Month'] == 'June') ? 'selected' : ''; ?>>June</option>
                        <option value="July" <?php echo ($job['Month'] == 'July') ? 'selected' : ''; ?>>July</option>
                        <option value="August" <?php echo ($job['Month'] == 'August') ? 'selected' : ''; ?>>August</option>
                        <option value="September" <?php echo ($job['Month'] == 'September') ? 'selected' : ''; ?>>September</option>
                        <option value="October" <?php echo ($job['Month'] == 'October') ? 'selected' : ''; ?>>October</option>
                        <option value="November" <?php echo ($job['Month'] == 'November') ? 'selected' : ''; ?>>November</option>
                        <option value="December" <?php echo ($job['Month'] == 'December') ? 'selected' : ''; ?>>December</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="DTJobNumber" class="block text-sm font-semibold text-gray-700">DT Job Number</label>
                <input type="text" name="DTJobNumber" id="DTJobNumber" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['DTJobNumber']); ?>" required>
            </div>
            <div class="mt-4">
                <label for="HOJobNumber" class="block text-sm font-semibold text-gray-700">HO Job Number</label>
                <input type="text" name="HOJobNumber" id="HOJobNumber" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['HOJobNumber']); ?>">
            </div>
            <div class="mt-4">
                <label for="Client" class="block text-sm font-semibold text-gray-700">Client</label>
                <select name="Client" id="Client" class="mt-1 p-2 w-full border rounded-md" required>
                    <option value="" disabled>Select a client</option>
                    <?php
                        if (!empty($clients)) {
                            foreach ($clients as $client) {
                                $clientName = htmlspecialchars($client['client']);
                                echo "<option value=\"{$clientName}\" " . ($job['Client'] == $clientName ? 'selected' : '') . ">{$clientName}</option>";
                            }
                        } else {
                            echo '<option value="" disabled>No clients available</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="mt-4">
                <label for="DateOpened" class="block text-sm font-semibold text-gray-700">Date Opened</label>
                <input type="date" name="DateOpened" id="DateOpened" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['DateOpened']); ?>" required>
            </div>
            <div class="mt-4">
                <label for="DescriptionOfWork" class="block text-sm font-semibold text-gray-700">Description of Work</label>
                <textarea name="DescriptionOfWork" id="DescriptionOfWork" rows="4" class="mt-1 p-2 w-full border rounded-md" required><?php echo htmlspecialchars($job['DescriptionOfWork']); ?></textarea>
            </div>
            <div class="mt-4">
                <label for="TargetDate" class="block text-sm font-semibold text-gray-700">Target Date</label>
                <input type="date" name="TARGET_DATE" id="TARGET_DATE" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['TARGET_DATE']); ?>" required>
            </div>
            <div class="mt-4">
                <label for="CompletionDate" class="block text-sm font-semibold text-gray-700">Completion Date</label>
                <input type="date" name="CompletionDate" id="CompletionDate" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['CompletionDate']); ?>">
            </div>
            <div class="mt-4">
                <label for="DeliveredDate" class="block text-sm font-semibold text-gray-700">Delivered Date</label>
                <input type="date" name="DeliveredDate" id="DeliveredDate" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['DeliveredDate']); ?>">
            </div>
            <div class="mt-4">
                <label for="FileClosed" class="block text-sm font-semibold text-gray-700">File Closed</label>
                <select name="FileClosed" id="FileClosed" class="mt-1 p-2 w-full border rounded-md" required>
                    <option value="0" <?php echo ($job['FileClosed'] == 0) ? 'selected' : ''; ?>>No</option>
                    <option value="1" <?php echo ($job['FileClosed'] == 1) ? 'selected' : ''; ?>>Yes</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="LabourHours" class="block text-sm font-semibold text-gray-700">Labour Hours</label>
                <input type="number" step="0.01" name="LabourHours" id="LabourHours" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['LabourHours']); ?>">
            </div>
            <div class="mt-4">
                <label for="MaterialCost" class="block text-sm font-semibold text-gray-700">Material Cost</label>
                <input type="number" step="0.01" name="MaterialCost" id="MaterialCost" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['MaterialCost']); ?>">
            </div>
            <div class="mt-4">
                <label for="TypeOfWork" class="block text-sm font-semibold text-gray-700">Type of Work</label>
                <input type="text" name="TypeOfWork" id="TypeOfWork" class="mt-1 p-2 w-full border rounded-md" value="<?php echo htmlspecialchars($job['TypeOfWork']); ?>">
            </div>
            <div class="mt-4">
                <label for="Remarks" class="block text-sm font-semibold text-gray-700">Remarks</label>
                <textarea name="Remarks" id="Remarks" rows="4" class="mt-1 p-2 w-full border rounded-md"><?php echo htmlspecialchars($job['Remarks']); ?></textarea>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Update</button>
            </div>
        </form>
    </div>
</body>
</html>
