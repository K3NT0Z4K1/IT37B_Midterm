<?php
require_once 'config.php';

setCorsHeaders();

$db = Database::getInstance()->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getAlertSettings($db);
        break;
    case 'POST':
        updateAlertSettings($db);
        break;
    case 'PUT':
        acknowledgeAlert($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function getAlertSettings($db) {
    $result = $db->query("SELECT * FROM alert_settings ORDER BY alert_type");
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[] = [
            'id' => intval($row['id']),
            'type' => $row['alert_type'],
            'threshold' => floatval($row['threshold']),
            'enabled' => boolval($row['enabled'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
}

function updateAlertSettings($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['type']) || !isset($input['threshold'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $type = $input['type'];
    $threshold = floatval($input['threshold']);
    $enabled = isset($input['enabled']) ? boolval($input['enabled']) : true;
    
    $stmt = $db->prepare("UPDATE alert_settings SET threshold = ?, enabled = ? WHERE alert_type = ?");
    $stmt->bind_param("dis", $threshold, $enabled, $type);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Alert settings updated'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update settings'
        ]);
    }
    
    $stmt->close();
}

function acknowledgeAlert($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['alert_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing alert_id']);
        return;
    }
    
    $alert_id = intval($input['alert_id']);
    
    $stmt = $db->prepare("UPDATE alert_history SET acknowledged = 1 WHERE id = ?");
    $stmt->bind_param("i", $alert_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Alert acknowledged'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to acknowledge alert'
        ]);
    }
    
    $stmt->close();
}
?>
