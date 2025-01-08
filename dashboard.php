<?php
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include('db_conn.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
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
    $client_error = 'Error fetching clients: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>End to End Job Tracking and Management Platform</title>
    <!-- Include necessary scripts and styles -->
    <script src="css/jquery-3.4.1.min.js"></script>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the form */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #e4edec;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
            color: #333;
        }
        .form-container label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<?php include('header.php'); ?>
<body class="bg-gray-100">
    <div class="form-container mt-8">
        <h2 class="text-center">Add Job Details</h2>

        <!-- Success or Error Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 <?php echo ($_SESSION['message_type'] == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php
                    echo htmlspecialchars($_SESSION['message']);
                    unset($_SESSION['message'], $_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Display client fetch error if any -->
        <?php if (isset($client_error)): ?>
            <div class="p-4 mb-4 bg-red-100 text-red-800">
                <?php echo htmlspecialchars($client_error); ?>
            </div>
        <?php endif; ?>

        <form action="submit_job.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="Year">Year</label>
                    <input type="number" name="Year" id="Year" required>
                </div>
                <div>
                    <label for="Month">Month</label>
                    <select name="Month" id="Month" required>
                        <option value="" disabled selected>Select a month</option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                </div>
                <div>
                    <label for="DTJobNumber">DT Job Number</label>
                    <input type="text" name="DTJobNumber" id="DTJobNumber" required>
                </div>
                <div>
                    <label for="HOJobNumber">HO Job Number</label>
                    <input type="text" name="HOJobNumber" id="HOJobNumber">
                </div>
                <div>
                    <label for="Client">Client</label>
                    <select name="Client" id="Client" required>
                        <option value="" disabled selected>Select a client</option>
                        <?php
                            if (!empty($clients)) {
                                foreach ($clients as $client) {
                                    // Adjust 'client_name' if your column is named differently
                                    $clientName = htmlspecialchars($client['client']);
                                    echo "<option value=\"{$clientName}\">{$clientName}</option>";
                                }
                            } else {
                                echo '<option value="" disabled>No clients available</option>';
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="DateOpened">Date Opened</label>
                    <input type="date" name="DateOpened" id="DateOpened" required>
                </div>
                <div>
                    <label for="DescriptionOfWork">Description of Work</label>
                    <textarea name="DescriptionOfWork" id="DescriptionOfWork" rows="4" required></textarea>
                </div>
                <div>
                    <label for="TargetDate">Target Date</label>
                    <input type="date" name="TARGET_DATE" id="TARGET_DATE" required>
                </div>
                <div>
                    <label for="CompletionDate">Completion Date</label>
                    <input type="date" name="CompletionDate" id="CompletionDate">
                </div>
                <div>
                    <label for="DeliveredDate">Delivered Date</label>
                    <input type="date" name="DeliveredDate" id="DeliveredDate">
                </div>
                <div>
                    <label for="FileClosed">File Closed</label>
                    <select name="FileClosed" id="FileClosed" required>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div>
                    <label for="LabourHours">Labour Hours</label>
                    <input type="number" step="0.01" name="LabourHours" id="LabourHours">
                </div>
                <div>
                    <label for="MaterialCost">Material Cost</label>
                    <input type="number" step="0.01" name="MaterialCost" id="MaterialCost">
                </div>
                <div>
                    <label for="TypeOfWork">Type of Work</label>
                    <input type="text" name="TypeOfWork" id="TypeOfWork">
                </div>
                <div>
                    <label for="Remarks">Remarks</label>
                    <textarea name="Remarks" id="Remarks" rows="4"></textarea>
                </div>
            </div>
            <div class="col-span-2 flex justify-end">
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
