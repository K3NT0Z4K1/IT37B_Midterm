<?php
/**
 * System Test Script
 * Run this file to verify your setup is working correctly
 * Access: http://your-server/test.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Weather Dashboard - System Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0e1a;
            color: #e4e9f2;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .test-box {
            background: #1a2332;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        h1 {
            color: #ff6b6b;
            margin-bottom: 2rem;
        }
        h2 {
            color: #4ecdc4;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .success {
            color: #6bcf7f;
            font-weight: bold;
        }
        .error {
            color: #ff6b6b;
            font-weight: bold;
        }
        .info {
            color: #94a3b8;
        }
        table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
        }
        td {
            padding: 0.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }
        td:first-child {
            font-weight: 600;
            width: 200px;
        }
        pre {
            background: #0a0e1a;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.85rem;
        }
        .btn {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <h1>🔧 Weather Dashboard System Test</h1>";

// Test 1: PHP Version
echo "<div class='test-box'>";
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4', '>=')) {
    echo "<p class='success'>✓ PHP $phpVersion (OK)</p>";
} else {
    echo "<p class='error'>✗ PHP $phpVersion (Requires 7.4+)</p>";
}
echo "</div>";

// Test 2: Required Extensions
echo "<div class='test-box'>";
echo "<h2>2. Required PHP Extensions</h2>";
$extensions = ['mysqli', 'json', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ $ext extension loaded</p>";
    } else {
        echo "<p class='error'>✗ $ext extension missing</p>";
    }
}
echo "</div>";

// Test 3: Database Connection
echo "<div class='test-box'>";
echo "<h2>3. Database Connection</h2>";

require_once 'config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($db->connect_error) {
        throw new Exception($db->connect_error);
    }
    
    echo "<p class='success'>✓ Successfully connected to MySQL</p>";
    echo "<table>";
    echo "<tr><td>Host:</td><td>" . DB_HOST . "</td></tr>";
    echo "<tr><td>Database:</td><td>" . DB_NAME . "</td></tr>";
    echo "<tr><td>MySQL Version:</td><td>" . $db->server_info . "</td></tr>";
    echo "</table>";
    
    // Test 4: Database Tables
    echo "</div><div class='test-box'>";
    echo "<h2>4. Database Tables</h2>";
    
    $tables = ['sensor_data', 'alert_settings', 'alert_history'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
            
            // Show row count
            $countResult = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $countResult->fetch_assoc()['count'];
            echo "<p class='info'>  → Contains $count rows</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' missing</p>";
            $allTablesExist = false;
        }
    }
    
    if (!$allTablesExist) {
        echo "<p class='error'>Please run database.sql to create missing tables</p>";
    }
    
    // Test 5: Sample Data
    echo "</div><div class='test-box'>";
    echo "<h2>5. Recent Sensor Data</h2>";
    
    $result = $db->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ Found " . $result->num_rows . " recent readings</p>";
        echo "<table>";
        echo "<tr><td><strong>Temperature</strong></td><td><strong>Humidity</strong></td><td><strong>Timestamp</strong></td></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['temperature'] . "°C</td>";
            echo "<td>" . $row['humidity'] . "%</td>";
            echo "<td>" . $row['timestamp'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ No sensor data yet. ESP32 hasn't sent any data.</p>";
    }
    
    // Test 6: Alert Settings
    echo "</div><div class='test-box'>";
    echo "<h2>6. Alert Configuration</h2>";
    
    $result = $db->query("SELECT * FROM alert_settings");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><td><strong>Alert Type</strong></td><td><strong>Threshold</strong></td><td><strong>Status</strong></td></tr>";
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['enabled'] ? "<span class='success'>Enabled</span>" : "<span class='error'>Disabled</span>";
            echo "<tr>";
            echo "<td>" . ucwords(str_replace('_', ' ', $row['alert_type'])) . "</td>";
            echo "<td>" . $row['threshold'] . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Please check your database credentials in config.php</p>";
}

// Test 7: File Permissions
echo "</div><div class='test-box'>";
echo "<h2>7. File Permissions</h2>";

$files = ['index.html', 'style.css', 'script.js', 'api_send_data.php', 'api_get_data.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "<p class='success'>✓ $file ($perms)</p>";
    } else {
        echo "<p class='error'>✗ $file not found</p>";
    }
}

// Test 8: API Endpoints
echo "</div><div class='test-box'>";
echo "<h2>8. API Test</h2>";
echo "<p class='info'>Test API by sending sample data:</p>";
echo "<pre>curl -X POST http://YOUR_SERVER/api_send_data.php \\
  -H 'Content-Type: application/json' \\
  -d '{\"temperature\": 25.5, \"humidity\": 65.0}'</pre>";

// Summary
echo "</div><div class='test-box'>";
echo "<h2>✅ Test Summary</h2>";
echo "<p>If all tests passed, your system is ready!</p>";
echo "<a href='index.html' class='btn'>Open Dashboard</a>";
echo "</div>";

echo "</body></html>";

// Delete this test file for security
echo "
<div class='test-box'>
    <h2>⚠️ Security Notice</h2>
    <p class='error'>Delete this test.php file after testing for security!</p>
</div>
";
?>
