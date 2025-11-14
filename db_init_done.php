<?php
// db_init.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_system";

// Step 1: Connect to MySQL (without selecting DB)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$dbname' created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Step 3: Select the database
$conn->select_db($dbname);

// Step 4: Create users table
$createUsersTable = "
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','hr','employee') NOT NULL DEFAULT 'employee',
  email VARCHAR(150) DEFAULT NULL,
  contact_no VARCHAR(15) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  department VARCHAR(100) DEFAULT NULL,
  designation VARCHAR(100) DEFAULT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

if ($conn->query($createUsersTable) === TRUE) {
    echo "✅ Table 'users' created or already exists.<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Step 5: Insert default users if table empty
$result = $conn->query("SELECT COUNT(*) AS count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $users = [
        ['Admin User', 'admin', password_hash('Admin@123', PASSWORD_BCRYPT), 'admin'],
        ['HR User', 'hr', password_hash('Hr@123', PASSWORD_BCRYPT), 'hr'],
        ['Employee User', 'employee', password_hash('Emp@123', PASSWORD_BCRYPT), 'employee']
    ];
    $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->bind_param("ssss", $u[0], $u[1], $u[2], $u[3]);
        $stmt->execute();
    }
    echo "✅ Default users inserted successfully.<br>";
} else {
    echo "ℹ️ Users table already has data, skipping default insert.<br>";
}

echo "<br>🎉 Database initialization complete!";
$conn->close();
?>
