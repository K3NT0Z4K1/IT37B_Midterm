<?php
require_once 'config.php';

setCorsHeaders();

$db = Database::getInstance()->getConnection();

// Get JSON input or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate input
if (!isset($input['temperature']) || !isset($input['humidity'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields: temperature and humidity'
    ]);
    exit();
}

$temperature = floatval($input['temperature']);
$humidity = floatval($input['humidity']);

// Validate ranges
if ($temperature < -50 || $temperature > 100) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Temperature out of valid range (-50 to 100°C)'
    ]);
    exit();
}

if ($humidity < 0 || $humidity > 100) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Humidity out of valid range (0 to 100%)'
    ]);
    exit();
}

// Insert sensor data
$stmt = $db->prepare("INSERT INTO sensor_data (temperature, humidity) VALUES (?, ?)");
$stmt->bind_param("dd", $temperature, $humidity);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    
    // Check for alerts
    checkAlerts($db, $temperature, $humidity);
    
    echo json_encode([
        'success' => true,
        'message' => 'Data saved successfully',
        'id' => $insert_id,
        'temperature' => $temperature,
        'humidity' => $humidity
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save data: ' . $stmt->error
    ]);
}

$stmt->close();

// Function to check and create alerts
function checkAlerts($db, $temperature, $humidity) {
    // Get alert settings
    $result = $db->query("SELECT * FROM alert_settings WHERE enabled = 1");
    
    while ($setting = $result->fetch_assoc()) {
        $triggered = false;
        $message = '';
        $value = 0;
        
        switch ($setting['alert_type']) {
            case 'temp_high':
                if ($temperature > $setting['threshold']) {
                    $triggered = true;
                    $message = "High temperature alert: {$temperature}°C (threshold: {$setting['threshold']}°C)";
                    $value = $temperature;
                }
                break;
            case 'temp_low':
                if ($temperature < $setting['threshold']) {
                    $triggered = true;
                    $message = "Low temperature alert: {$temperature}°C (threshold: {$setting['threshold']}°C)";
                    $value = $temperature;
                }
                break;
            case 'humidity_high':
                if ($humidity > $setting['threshold']) {
                    $triggered = true;
                    $message = "High humidity alert: {$humidity}% (threshold: {$setting['threshold']}%)";
                    $value = $humidity;
                }
                break;
            case 'humidity_low':
                if ($humidity < $setting['threshold']) {
                    $triggered = true;
                    $message = "Low humidity alert: {$humidity}% (threshold: {$setting['threshold']}%)";
                    $value = $humidity;
                }
                break;
        }
        
        if ($triggered) {
            $stmt = $db->prepare("INSERT INTO alert_history (alert_type, message, value) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $setting['alert_type'], $message, $value);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
