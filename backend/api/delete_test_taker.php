<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../php/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST' || $method === 'DELETE') {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id']) || empty($input['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'User ID is required'
            ]);
            exit;
        }
        
        $user_id = $input['user_id'];
        
        // Start transaction to ensure data consistency
        $dbh->beginTransaction();
        
        try {
            // 1. First, delete from job_recommendations table (child records)
            $delete_recommendations_sql = "DELETE FROM job_recommendations WHERE user_id = ?";
            $stmt_recommendations = $dbh->prepare($delete_recommendations_sql);
            $stmt_recommendations->execute([$user_id]);
            $recommendations_deleted = $stmt_recommendations->rowCount();
            
            // 2. Then delete from users table (parent record)
            $delete_user_sql = "DELETE FROM users WHERE userid = ?";
            $stmt_user = $dbh->prepare($delete_user_sql);
            $stmt_user->execute([$user_id]);
            $user_deleted = $stmt_user->rowCount();
            
            // Commit transaction
            $dbh->commit();
            
            if ($user_deleted > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Test taker deleted successfully',
                    'data' => [
                        'user_records_deleted' => $user_deleted,
                        'recommendation_records_deleted' => $recommendations_deleted
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not found or already deleted'
                ]);
            }
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $dbh->rollBack();
            throw $e;
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed. Use POST or DELETE.'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>