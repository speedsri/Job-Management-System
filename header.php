<nav class="bg-green-500 p-4">
    <div class="flex items-center justify-between">
        <div class="text-white font-semibold text-lg">
            Welcome, <?php echo $_SESSION['username']; ?>
        </div>
        <div class="space-x-4">
            <a href="home.php" class="text-white hover:bg-green-700 px-4 py-2 rounded">Guest User</a>
			<a href="labour_hrs_mat_cost_filter.php" class="text-white hover:bg-green-700 px-4 py-2 rounded">Labour_Hors/Mat_Cost</a>
			
			<a href="view_jobs.php" class="text-white hover:bg-blue-600 px-4 py-2 rounded">View Job Details</a>
            <a href="dashboard.php" class="text-white hover:bg-blue-600 px-4 py-2 rounded">New Entry</a>
			         
            <a href="comprehensive_job_search_and_export.php" class="text-white hover:bg-green-700 px-4 py-2 rounded">Job Search and Export</a>
            <a href="logout.php" class="text-white hover:bg-red-700 px-4 py-2 rounded">Logout</a>
        </div>
    </div>
</nav>