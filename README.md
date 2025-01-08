# Job-Management-System
A comprehensive web-based application for managing job entries with powerful search, edit, and delete capabilities. This system demonstrates the integration of a backend database with a frontend form interface, making it an excellent learning resource for understanding database-driven web applications.

## Features

- **User Authentication**
  - Secure login system
  - Session management
  - Admin access control

- **Job Management**
  - Add new job entries
  - Edit existing jobs
  - Delete job records
  - View detailed job information

- **Advanced Search Capabilities**
  - Multi-criteria search
  - Filter by:
    - Client
    - Job Number
    - Year
    - Type of Work
    - Date Range
  - Real-time search results

- **Data Export**
  - Export search results to Excel
  - Customizable column selection
  - Formatted data output

- **Responsive UI**
  - Modern interface using Tailwind CSS
  - Mobile-friendly design
  - Interactive data tables

## Technology Stack

- PHP 7.4+
- MySQL/MariaDB
- HTML5
- JavaScript/jQuery
- Tailwind CSS
- Select2 for enhanced dropdowns
- PDO for database connections

## Installation Guide

### Prerequisites

1. Web server (Apache/Nginx)
2. PHP 7.4 or higher
3. MySQL/MariaDB
4. Composer (for dependency management)

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/job-management-system.git
   cd job-management-system
   ```

2. **Database Setup**
   - Create a new MySQL database
   - Import the provided SQL schema file:
   ```bash
   mysql -u your_username -p your_database_name < database/schema.sql
   ```

3. **Configuration**
   - Copy the example configuration file:
   ```bash
   cp config/db.example.php config/db.php
   ```
   - Edit `db.php` with your database credentials:
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Install Dependencies**
   ```bash
   composer install
   ```

5. **File Permissions**
   ```bash
   chmod 755 -R ./
   chmod 777 -R ./uploads
   ```

6. **Web Server Configuration**
   
   For Apache (.htaccess):
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php/$1 [L]
   ```

   For Nginx:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

### File Structure

```
job-management-system/
├── css/
│   ├── tailwind.min.css
│   ├── select2.min.css
│   └── ...
├── js/
│   ├── jquery-3.6.0.min.js
│   └── select2.min.js
├── config/
│   └── db.php
├── includes/
│   └── db_conn.php
├── home.php
├── index.php
├── view_jobs.php
└── README.md
```

## Usage Guide

1. **Accessing the System**
   - Navigate to `http://your-domain/index.php`
   - Log in using your admin credentials

2. **Managing Jobs**
   - Use the main dashboard to view all jobs
   - Click "Add New Job" to create entries
   - Use the search bar for quick lookups
   - Use filters for advanced searching

3. **Exporting Data**
   - Click "Export to Excel" button
   - Select desired columns
   - Choose export format

4. **Search Features**
   - Use the main search bar for general queries
   - Use dropdown filters for specific criteria
   - Combine multiple filters for precise results

## Security Considerations

- Always change default admin credentials
- Keep PHP and dependencies updated
- Use HTTPS in production
- Implement proper input validation
- Sanitize database queries

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and queries, please create an issue in the GitHub repository.
