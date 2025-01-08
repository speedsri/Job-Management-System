<?php
session_start();

// Include the database connection
require_once 'db_conn.php';  // Path to your db_conn.php file

// Check if the user is already logged in, if so, redirect them
if (isset($_SESSION['user_id'])) {
    header('Location: view_jobs.php');  // Redirect to your dashboard or home page
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL query to find user by username
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);

    // Check if user exists
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Success message
        $_SESSION['message'] = 'Login successful!';
        $_SESSION['message_type'] = 'success';

        // Redirect to a dashboard or home page
        header('Location: view_jobs.php');  // Replace with your page
        exit;
    } else {
        // Invalid username or password
        $_SESSION['message'] = 'Invalid username or password!';
        $_SESSION['message_type'] = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="css/jquery-3.4.1.min.js"></script>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <script src="3.4.16"></script>
    <style>
        /* Centering the container */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #d4edda; /* Light greenish background */
        }

        .login-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            background-color: #28a745; /* Green background */
            color: white;
            padding: 1rem;
            font-size: 1.5rem;
            text-align: center;
            border-radius: 4px;
        }

        .input-field {
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            border-radius: 8px;
            width: 100%;
            border: 1px solid #ccc;
        }

        .input-field:focus {
            border-color: #28a745;
        }

        .button {
            width: 100%;
            padding: 0.75rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }

        .button:hover {
            background-color: #218838;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .message.success {
            background-color: #28a745;
            color: white;
        }

        .message.error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2 class="login-header">Admin Login</h2>

        <!-- Error or Success Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?= $_SESSION['message_type'] === 'success' ? 'success' : 'error' ?>">
                <?= $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="index.php" method="POST">
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700">Username</label>
                <input type="text" name="username" id="username" class="input-field" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="input-field" required>
            </div>
            <div>
                <button type="submit" class="button">Login</button>
				<center>
                <a href="http://192.168.1.215:4141/home.php" style="color: green; text-decoration: none;">Back to Home</a>
                </center>
							
            </div>
        </form>
    </div>
	
</body>
</html>
