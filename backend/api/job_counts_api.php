<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");


include_once '../php/database.php';


try {
    // Get job counts with highest confidence per test
    $query = "
        WITH RankedJobs AS (
            SELECT 
                user_id,
                CASE 
                    WHEN job1_confidence >= COALESCE(job2_confidence, 0) 
                     AND job1_confidence >= COALESCE(job3_confidence, 0)
                     AND job1_confidence >= COALESCE(job4_confidence, 0)
                     AND job1_confidence >= COALESCE(job5_confidence, 0)
                    THEN job1
                    WHEN job2_confidence >= COALESCE(job1_confidence, 0)
                     AND job2_confidence >= COALESCE(job3_confidence, 0)
                     AND job2_confidence >= COALESCE(job4_confidence, 0)
                     AND job2_confidence >= COALESCE(job5_confidence, 0)
                    THEN job2
                    WHEN job3_confidence >= COALESCE(job1_confidence, 0)
                     AND job3_confidence >= COALESCE(job2_confidence, 0)
                     AND job3_confidence >= COALESCE(job4_confidence, 0)
                     AND job3_confidence >= COALESCE(job5_confidence, 0)
                    THEN job3
                    WHEN job4_confidence >= COALESCE(job1_confidence, 0)
                     AND job4_confidence >= COALESCE(job2_confidence, 0)
                     AND job4_confidence >= COALESCE(job3_confidence, 0)
                     AND job4_confidence >= COALESCE(job5_confidence, 0)
                    THEN job4
                    ELSE job5
                END as highest_confidence_job,
                GREATEST(
                    COALESCE(job1_confidence, 0),
                    COALESCE(job2_confidence, 0),
                    COALESCE(job3_confidence, 0),
                    COALESCE(job4_confidence, 0),
                    COALESCE(job5_confidence, 0)
                ) as highest_confidence
            FROM job_recommendations
            WHERE job1 IS NOT NULL AND job1 != ''
        )
        SELECT 
            highest_confidence_job as job_title,
            COUNT(*) as job_count,
            AVG(highest_confidence) as average_confidence
        FROM RankedJobs
        WHERE highest_confidence_job IS NOT NULL AND highest_confidence_job != ''
        GROUP BY highest_confidence_job
        ORDER BY job_count DESC, average_confidence DESC
    ";
    
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    
    $job_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'data' => $job_counts,
        'total_tests' => array_sum(array_column($job_counts, 'job_count')),
        'unique_jobs' => count($job_counts)
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>