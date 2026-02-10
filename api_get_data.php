<?php
require_once 'config.php';

setCorsHeaders();

$db = Database::getInstance()->getConnection();

// Get request type
$type = isset($_GET['type']) ? $_GET['type'] : 'current';

switch ($type) {
    case 'current':
        getCurrentData($db);
        break;
    case 'history':
        $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;
        getHistoricalData($db, $hours);
        break;
    case 'alerts':
        getAlerts($db);
        break;
    case 'stats':
        getStats($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
}

function getCurrentData($db) {
    $result = $db->query("SELECT temperature, humidity, timestamp 
                          FROM sensor_data 
                          ORDER BY timestamp DESC 
                          LIMIT 1");
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => [
                'temperature' => floatval($row['temperature']),
                'humidity' => floatval($row['humidity']),
                'timestamp' => $row['timestamp']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No data available'
        ]);
    }
}

function getHistoricalData($db, $hours) {
    $stmt = $db->prepare("SELECT temperature, humidity, timestamp 
                          FROM sensor_data 
                          WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                          ORDER BY timestamp ASC");
    $stmt->bind_param("i", $hours);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'temperature' => floatval($row['temperature']),
            'humidity' => floatval($row['humidity']),
            'timestamp' => $row['timestamp']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
    
    $stmt->close();
}

function getAlerts($db) {
    $result = $db->query("SELECT * FROM alert_history 
                          WHERE acknowledged = 0 
                          ORDER BY timestamp DESC 
                          LIMIT 10");
    
    $alerts = [];
    while ($row = $result->fetch_assoc()) {
        $alerts[] = [
            'id' => intval($row['id']),
            'type' => $row['alert_type'],
            'message' => $row['message'],
            'value' => floatval($row['value']),
            'timestamp' => $row['timestamp']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'alerts' => $alerts,
        'count' => count($alerts)
    ]);
}

function getStats($db) {
    $hours = isset($_GET['hours']) ? intval($_GET['hours']) : 24;
    
    $stmt = $db->prepare("SELECT 
                          MIN(temperature) as temp_min,
                          MAX(temperature) as temp_max,
                          AVG(temperature) as temp_avg,
                          MIN(humidity) as humidity_min,
                          MAX(humidity) as humidity_max,
                          AVG(humidity) as humidity_avg,
                          COUNT(*) as reading_count
                          FROM sensor_data 
                          WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)");
    $stmt->bind_param("i", $hours);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'stats' => [
                'temperature' => [
                    'min' => round(floatval($row['temp_min']), 1),
                    'max' => round(floatval($row['temp_max']), 1),
                    'avg' => round(floatval($row['temp_avg']), 1)
                ],
                'humidity' => [
                    'min' => round(floatval($row['humidity_min']), 1),
                    'max' => round(floatval($row['humidity_max']), 1),
                    'avg' => round(floatval($row['humidity_avg']), 1)
                ],
                'reading_count' => intval($row['reading_count']),
                'period_hours' => $hours
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No data available'
        ]);
    }
    
    $stmt->close();
}
?>
