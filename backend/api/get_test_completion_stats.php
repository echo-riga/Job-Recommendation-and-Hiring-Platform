<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../php/database.php';

try {
    $period = $_GET['period'] ?? 'daily';
    
    // Validate period
    $valid_periods = ['daily', 'weekly', 'monthly'];
    if (!in_array($period, $valid_periods)) {
        $period = 'daily';
    }
    
    if ($period === 'daily') {
        // Last 14 days completion stats
        $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as completed_tests,
                (SELECT COUNT(*) FROM users WHERE DATE(created_at) <= DATE(jr.created_at)) as total_users
            FROM job_recommendations jr
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";
    } elseif ($period === 'weekly') {
        // Last 8 weeks completion stats
        $query = "
            SELECT 
                YEARWEEK(created_at) as week,
                CONCAT('Week ', WEEK(created_at)) as week_label,
                COUNT(*) as completed_tests,
                (SELECT COUNT(*) FROM users WHERE YEARWEEK(created_at) <= YEARWEEK(jr.created_at)) as total_users
            FROM job_recommendations jr
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
            GROUP BY YEARWEEK(created_at)
            ORDER BY week ASC
        ";
    } else { // monthly
        // Last 6 months completion stats
        $query = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_label,
                COUNT(*) as completed_tests,
                (SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') <= DATE_FORMAT(jr.created_at, '%Y-%m')) as total_users
            FROM job_recommendations jr
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ";
    }
    
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    
    $completion_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total users for not started calculation
    $total_users_query = "SELECT COUNT(*) as total_users FROM users";
    $total_users_stmt = $dbh->prepare($total_users_query);
    $total_users_stmt->execute();
    $total_users = $total_users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Format response for charts
    $labels = [];
    $completed = [];
    $notStarted = [];
    
    foreach ($completion_data as $row) {
        if ($period === 'daily') {
            $labels[] = date('M j', strtotime($row['date']));
        } elseif ($period === 'weekly') {
            $labels[] = $row['week_label'];
        } else {
            $labels[] = $row['month_label'];
        }
        
        $completed[] = (int)$row['completed_tests'];
        $notStarted[] = max(0, (int)$total_users - (int)$row['total_users']);
    }
    
    // Get summary statistics
    $total_completed = array_sum($completed);
    $completion_rate = $total_users > 0 ? round(($total_completed / $total_users) * 100, 1) : 0;
    
    $response = [
        'success' => true,
        'data' => [
            'timeSeries' => [
                'labels' => $labels,
                'completed' => $completed,
                'notStarted' => $notStarted
            ],
            'summary' => [
                'total_users' => (int)$total_users,
                'total_completed' => $total_completed,
                'completion_rate' => $completion_rate,
                'period' => $period
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>