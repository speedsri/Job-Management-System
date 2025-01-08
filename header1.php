<nav class="bg-blue-600 p-4">
        <div class="flex items-center justify-between">
            <div class="text-white font-semibold text-lg">
                Welcome, <?php echo $_SESSION['username']; ?>
            </div>
            <div class="space-x-4">
            <a href="view_jobs.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded">View Job Details</a>
                <a href="dashboard.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded">Add Job Details</a>
               
                <a href="logout.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>