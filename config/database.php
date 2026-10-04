<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vaccination_management_system');
define('DB_PORT', '3306');


date_default_timezone_set('Asia/Karachi');


try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
   
    $error_message = $e->getMessage();
    
    
    if (strpos($error_message, 'Unknown database') !== false) {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #e0e0e0; border-radius: 12px; background: #fff8f8; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <h2 style='color: #c53030; margin-top: 0;'>⚠️ Database Not Found</h2>
            <p style='color: #4a5568; line-height: 1.6;'>The database <strong>" . DB_NAME . "</strong> has not been created in phpMyAdmin / MySQL yet.</p>
            <div style='background: #edf2f7; padding: 15px; border-radius: 8px; font-size: 14px; margin: 15px 0;'>
                <strong>How to fix:</strong><br>
                1. Open <strong>phpMyAdmin</strong> in your browser (<code>http://localhost/phpmyadmin</code>)<br>
                2. Click on the <strong>Import</strong> tab<br>
                3. Choose the file <code>database/vaccination_system.sql</code> from this project folder<br>
                4. Click <strong>Go / Import</strong><br>
                5. Refresh this page!
            </div>
            <p style='color: #718096; font-size: 13px;'>Raw Error: " . htmlspecialchars($error_message) . "</p>
        </div>");
    } else {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #e0e0e0; border-radius: 12px; background: #fff8f8; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <h2 style='color: #c53030; margin-top: 0;'>⚠️ MySQL Connection Error</h2>
            <p style='color: #4a5568; line-height: 1.6;'>Could not connect to MySQL server. Please make sure <strong>MySQL</strong> is started in your <strong>XAMPP Control Panel</strong>.</p>
            <p style='color: #718096; font-size: 13px;'>Raw Error: " . htmlspecialchars($error_message) . "</p>
        </div>");
    }
}
?>

