<?php
session_start();
header('Content-Type: application/json');
include '../php/database.php';

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

// Function to calculate match percentage (same as your other file)
function calculateMatchPercentage($probability, $rank) {
    $baseScore = $probability * 100;
    
    $matchPercentage = 0;
    
    if ($rank === 0) {
        $matchPercentage = 60 + ($baseScore * 0.6);
    } else if ($rank === 1) {
        $matchPercentage = 55 + ($baseScore * 0.5);
    } else if ($rank === 2) {
        $matchPercentage = 50 + ($baseScore * 0.45);
    } else if ($rank === 3) {
        $matchPercentage = 45 + ($baseScore * 0.4);
    } else {
        $matchPercentage = 40 + ($baseScore * 0.35);
    }
    
    $matchPercentage = max(40, min(95, $matchPercentage));
    return round($matchPercentage);
}

// Function to convert blob image to base64 for JSON response
function imageToBase64($imageBlob) {
    if (!empty($imageBlob)) {
        // Get image info and encode to base64
        $imageData = base64_encode($imageBlob);
        
        // Try to detect image type, default to jpeg if unknown
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageBlob);
        
        // Validate it's actually an image
        if (strpos($mimeType, 'image/') === 0) {
            return 'data:' . $mimeType . ';base64,' . $imageData;
        } else {
            // If not a valid image, return null
            return null;
        }
    }
    return null;
}

// Get job name from query parameters
$jobName = isset($_GET['job_name']) ? trim($_GET['job_name']) : '';
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if (empty($jobName) && $postId === 0) {
    echo json_encode(['success' => false, 'error' => 'Job name or post ID is required']);
    exit();
}

try {
    // If postId is provided, get the job name from the posting
    if ($postId > 0) {
        $postStmt = $con->prepare("SELECT postjobrole FROM jobposting WHERE postid = ?");
        $postStmt->bind_param("i", $postId);
        $postStmt->execute();
        $postResult = $postStmt->get_result();
        
        if ($postResult->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Job post not found']);
            exit();
        }
        
        $postData = $postResult->fetch_assoc();
        $jobName = $postData['postjobrole'];
        $postStmt->close();
    }

    // First, get all applicants for this job post
    $applicantsQuery = "
        SELECT 
            a.applicationid,
            a.userid,
            a.postid,
            DATE(a.date_applied) as date_applied,
            a.status,
            u.firstname,
            u.lastname,
            u.email,
            u.image,
            u.contact,
            u.bday,
            u.educlvl,
            u.course,
            u.school,
            u.created_at as user_created
        FROM 
            application a
        INNER JOIN 
            users u ON a.userid = u.userid
        WHERE 
            a.postid = ?
        ORDER BY 
            a.date_applied DESC";

    $applicantsStmt = $con->prepare($applicantsQuery);
    $applicantsStmt->bind_param("i", $postId);
    $applicantsStmt->execute();
    $applicantsResult = $applicantsStmt->get_result();
    
    $applicants = [];
    $userIds = [];
    
    // Collect all user IDs for batch recommendation lookup
    while ($row = $applicantsResult->fetch_assoc()) {
        $userIds[] = $row['userid'];
        $applicants[$row['userid']] = $row;
    }
    $applicantsStmt->close();

    // If we have users, get their latest job recommendations
    $recommendations = [];
    if (!empty($userIds)) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        
        // Get the latest recommendation for each user using a subquery approach
        $recQuery = "
            SELECT jr.* 
            FROM job_recommendations jr
            INNER JOIN (
                SELECT user_id, MAX(created_at) as latest_date
                FROM job_recommendations 
                WHERE user_id IN ($placeholders)
                GROUP BY user_id
            ) latest ON jr.user_id = latest.user_id AND jr.created_at = latest.latest_date
        ";
        
        $recStmt = $con->prepare($recQuery);
        $recStmt->bind_param(str_repeat('i', count($userIds)), ...$userIds);
        $recStmt->execute();
        $recResult = $recStmt->get_result();
        
        while ($row = $recResult->fetch_assoc()) {
            $recommendations[$row['user_id']] = $row;
        }
        $recStmt->close();
    }

    // Process and combine the data
    $processedApplicants = [];
    $totalApplicants = 0;
    $matchedApplicants = 0;
    
    foreach ($applicants as $userId => $applicantData) {
        $totalApplicants++;
        
        $recommendation = isset($recommendations[$userId]) ? $recommendations[$userId] : null;
        
        // Calculate match confidence
        $matchConfidence = 0;
        $matchPosition = 0;
        
        if ($recommendation) {
            // Check each job position for a match
            $jobs = [
                'job1' => $recommendation['job1'],
                'job2' => $recommendation['job2'], 
                'job3' => $recommendation['job3'],
                'job4' => $recommendation['job4'],
                'job5' => $recommendation['job5']
            ];
            
            $confidences = [
                'job1' => $recommendation['job1_confidence'],
                'job2' => $recommendation['job2_confidence'],
                'job3' => $recommendation['job3_confidence'],
                'job4' => $recommendation['job4_confidence'],
                'job5' => $recommendation['job5_confidence']
            ];
            
            $position = 1;
            foreach ($jobs as $jobKey => $jobNameValue) {
                if ($jobNameValue === $jobName) {
                    $matchConfidence = (float)$confidences[$jobKey];
                    $matchPosition = $position;
                    break;
                }
                $position++;
            }
        }
        
        // Calculate age from birthday
        $age = '';
        if (!empty($applicantData['bday']) && $applicantData['bday'] != '0000-00-00') {
            $birthday = new DateTime($applicantData['bday']);
            $today = new DateTime();
            $age = $today->diff($birthday)->y;
        }
        
        // Calculate match percentage using your custom function
        $calculatedMatchPercentage = 0;
        if ($matchConfidence > 0) {
            // Convert position to rank (0-based index)
            $rank = $matchPosition - 1;
            $calculatedMatchPercentage = calculateMatchPercentage($matchConfidence, $rank);
        }
        
        // Process image
        $avatar = null;
        if (!empty($applicantData['image'])) {
            $avatar = imageToBase64($applicantData['image']);
        }
        
        // Prepare final applicant data
        $applicant = [
            'application_id' => $applicantData['applicationid'],
            'user_id' => $applicantData['userid'],
            'post_id' => $applicantData['postid'],
            'name' => $applicantData['firstname'] . ' ' . $applicantData['lastname'],
            'first_name' => $applicantData['firstname'],
            'last_name' => $applicantData['lastname'],
            'email' => $applicantData['email'],
            'contact' => $applicantData['contact'],
            'date_applied' => $applicantData['date_applied'],
            'status' => $applicantData['status'],
            'age' => $age,
            'birthday' => $applicantData['bday'],
            'education_level' => $applicantData['educlvl'],
            'course' => $applicantData['course'],
            'school' => $applicantData['school'],
            'avatar' => $avatar, // Base64 encoded image or null
            'has_image' => !empty($applicantData['image']),
            'has_recommendation' => !empty($recommendation),
            'match_confidence' => $matchConfidence,
            'match_percentage' => $calculatedMatchPercentage, // Use calculated percentage
            'raw_percentage' => round($matchConfidence * 100, 1), // Keep raw for reference
            'match_position' => $matchPosition,
            'is_match' => $matchConfidence > 0,
            'recommendation_date' => $recommendation ? $recommendation['created_at'] : null,
            'skills' => $recommendation ? [
                'critical_thinking' => (float)$recommendation['critical_thinking'],
                'problem_solving' => (float)$recommendation['problem_solving'],
                'communication' => (float)$recommendation['communication'],
                'teamwork' => (float)$recommendation['teamwork'],
                'adaptability' => (float)$recommendation['adaptability']
            ] : null,
            'job_recommendations' => $recommendation ? [
                'job1' => [
                    'name' => $recommendation['job1'],
                    'confidence' => (float)$recommendation['job1_confidence'],
                    'calculated_percentage' => calculateMatchPercentage((float)$recommendation['job1_confidence'], 0)
                ],
                'job2' => [
                    'name' => $recommendation['job2'],
                    'confidence' => (float)$recommendation['job2_confidence'],
                    'calculated_percentage' => calculateMatchPercentage((float)$recommendation['job2_confidence'], 1)
                ],
                'job3' => [
                    'name' => $recommendation['job3'],
                    'confidence' => (float)$recommendation['job3_confidence'],
                    'calculated_percentage' => calculateMatchPercentage((float)$recommendation['job3_confidence'], 2)
                ],
                'job4' => [
                    'name' => $recommendation['job4'],
                    'confidence' => (float)$recommendation['job4_confidence'],
                    'calculated_percentage' => calculateMatchPercentage((float)$recommendation['job4_confidence'], 3)
                ],
                'job5' => [
                    'name' => $recommendation['job5'],
                    'confidence' => (float)$recommendation['job5_confidence'],
                    'calculated_percentage' => calculateMatchPercentage((float)$recommendation['job5_confidence'], 4)
                ]
            ] : null
        ];
        
        if ($applicant['is_match']) {
            $matchedApplicants++;
        }
        
        $processedApplicants[] = $applicant;
    }
    
    // Sort applicants by calculated match percentage (highest first), then by application date
    usort($processedApplicants, function($a, $b) {
        if ($b['match_percentage'] != $a['match_percentage']) {
            return $b['match_percentage'] <=> $a['match_percentage'];
        }
        return strtotime($b['date_applied']) <=> strtotime($a['date_applied']);
    });
    
    echo json_encode([
        'success' => true,
        'job_name' => $jobName,
        'post_id' => $postId,
        'total_applicants' => $totalApplicants,
        'matched_applicants' => $matchedApplicants,
        'match_rate' => $totalApplicants > 0 ? round(($matchedApplicants / $totalApplicants) * 100, 1) : 0,
        'applicants' => $processedApplicants,
        'calculation_info' => [
            'algorithm_used' => 'calculateMatchPercentage',
            'description' => 'Uses position-based weighting with base scores',
            'example' => '0.25 confidence in position 1 becomes ' . calculateMatchPercentage(0.25, 0) . '%'
        ]
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>