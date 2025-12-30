<?php
session_start();

if (!isset($_SESSION['adminid'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$maintenanceFlagFile = __DIR__ . '/../../.maintenance';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status']; 

    if ($status === 'true') {
        // Turn maintenance mode ON
        if (!file_exists($maintenanceFlagFile)) {
            // Create the flag file
            if (touch($maintenanceFlagFile)) {
                echo json_encode(['success' => true, 'message' => 'Maintenance mode has been enabled.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: Could not create the maintenance flag file. Check folder permissions.']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Maintenance mode is already enabled.']);
        }
    } elseif ($status === 'false') {
        // Turn maintenance mode OFF
        if (file_exists($maintenanceFlagFile)) {
            // Delete the flag file
            if (unlink($maintenanceFlagFile)) {
                echo json_encode(['success' => true, 'message' => 'Maintenance mode has been disabled. The site is now live.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: Could not remove the maintenance flag file. Please remove it manually.']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Maintenance mode is already disabled.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid status provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}