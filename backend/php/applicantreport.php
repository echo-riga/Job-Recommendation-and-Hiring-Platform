<?php
include 'database.php';
date_default_timezone_set('Asia/Manila');

$systemLogoBase64 = '../image/jftlogo.png';

// ===== Fetch system name and logo =====
$stmtSys = $con->prepare("SELECT sysname, sysimage FROM systemname WHERE sysid = 1");
$stmtSys->execute();
$resSys = $stmtSys->get_result();
if ($resSys->num_rows === 1) {
    $sysData = $resSys->fetch_assoc();
    $systemName = htmlspecialchars($sysData['sysname']);
    if (!empty($sysData['sysimage'])) {
        $systemLogoBase64 = 'data:image/jpeg;base64,' . base64_encode($sysData['sysimage']);
    }
} else {
    $systemName = "Company System";
}

// ===== Get postid =====
$postID = isset($_GET['postid']) ? (int)$_GET['postid'] : 0;
$jobRole = "No Job Post Selected";

if ($postID > 0) {
    $titleStmt = $con->prepare("SELECT postjobrole FROM jobposting WHERE postid = ?");
    $titleStmt->bind_param("i", $postID);
    $titleStmt->execute();
    $titleResult = $titleStmt->get_result();
    if ($titleResult->num_rows > 0) {
        $jobData = $titleResult->fetch_assoc();
        $jobRole = htmlspecialchars($jobData['postjobrole']);
    }
    $titleStmt->close();
}

// ===== Applicant stats =====
$totalapplication = $pendingApplicants = $initialInterviewApplicants = $finalInterviewApplicants = $approvedApplicants = $failedApplicants = 0;

if ($postID > 0) {
    $sql = "SELECT 
                COUNT(*) AS total_appli,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_appli,
                SUM(CASE WHEN status = 'Initial Interview' THEN 1 ELSE 0 END) AS initialinter_appli,
                SUM(CASE WHEN status = 'Final Interview' THEN 1 ELSE 0 END) AS finalinter_appli,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_appli,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS failed_appli
            FROM application
            WHERE postid = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $statsResult = $stmt->get_result();
    if ($statsResult && $row = $statsResult->fetch_assoc()) {
        $totalapplication = $row['total_appli'];
        $pendingApplicants = $row['pending_appli'];
        $initialInterviewApplicants = $row['initialinter_appli'];
        $finalInterviewApplicants = $row['finalinter_appli'];
        $approvedApplicants = $row['approved_appli'];
        $failedApplicants = $row['failed_appli'];
    }
    $stmt->close();
}

// ===== Applicant details =====
$applicantQuery = "
    SELECT 
        a.applicationid,
        a.userid,
        DATE(a.date_applied) as date_applied_only,
        a.status,
        u.firstname,
        u.lastname,
        u.email
    FROM 
        application a
    INNER JOIN 
        users u ON a.userid = u.userid
    WHERE 
        a.postid = ?
    ORDER BY 
        a.date_applied DESC";
$stmt = $con->prepare($applicantQuery);
$stmt->bind_param("i", $postID);
$stmt->execute();
$applicantResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Applicant Report</title>
<style>
body {
  font-family: 'Segoe UI', Roboto, Arial, sans-serif;
  background-color: #f7f9fb;
  color: #333;
  margin: 40px;
}
.report-wrapper {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(0,0,0,0.08);
  padding: 40px;
  max-width: 1100px;
  margin: auto;
}
.report-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 3px solid #1D10B2;
  padding-bottom: 15px;
  margin-bottom: 30px;
}
.report-header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}
.report-header img {
  width: 80px;
  height: auto;
}
.report-header h2 {
  color: #1D10B2;
  margin: 0;
  font-size: 26px;
  font-weight: 700;
}
.report-date {
  text-align: right;
  font-size: 14px;
  color: #666;
}
.stats-summary {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  margin: 20px 0 40px;
  text-align: center;
}
.stats-box {
  background-color: #eef2ff;
  padding: 15px 25px;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.stats-box h3 {
  color: #1D10B2;
  margin: 0;
  font-size: 18px;
}
.stats-box p {
  font-size: 22px;
  font-weight: bold;
  color: #111;
  margin: 5px 0 0;
}
.table-container {
  width: 100%;
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 10px;
  overflow: hidden;
  font-size: 14px;
}
th, td {
  padding: 12px 10px;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
}
th {
  background-color: #1D10B2;
  color: white;
  font-weight: 600;
}
tr:nth-child(even) {
  background-color: #f9fafb;
}
.status-tag {
  padding: 5px 10px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 13px;
}
.status-pending { background-color: #fef9c3; color: #a16207; }
.status-approved { background-color: #d1fae5; color: #16a34a; }
.status-rejected { background-color: #fee2e2; color: #dc2626; }
.status-interview { background-color: #bfdbfe; color: #1d4ed8; }
.print-btn {
  display: block;
  margin: 30px 10rem 30px auto;
  padding: 10px 25px;
  background-color: #1D10B2;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  cursor: pointer;
  transition: 0.3s ease;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
.print-btn:hover {
  background-color: #003f91;
  transform: translateY(-1px);
}
@media print {
  body { background: white; margin: 0; }
  .report-wrapper { box-shadow: none; border: none; margin: 0; }
  .print-btn { display: none; }
}
</style>
</head>
<body>
  <button class="print-btn" onclick="window.print()">
    <i class="fa-solid fa-print"></i> Print / Download PDF
  </button>
<div class="report-wrapper">
  <div class="report-header">
    <div class="report-header-left">
      <img src="<?php echo $systemLogoBase64; ?>" alt="Company Logo">
      <h2>Applicant Report — <?php echo $jobRole; ?></h2>
    </div>
    <div class="report-date">
      <p>Generated on:<br><strong><?php echo date('F j, Y - g:i A'); ?></strong></p>
    </div>
    
  </div>

  <div class="stats-summary">
    <div class="stats-box"><h3>Total Applicants</h3><p><?php echo $totalapplication; ?></p></div>
    <div class="stats-box"><h3>Pending</h3><p><?php echo $pendingApplicants; ?></p></div>
    <div class="stats-box"><h3>Initial Interview</h3><p><?php echo $initialInterviewApplicants; ?></p></div>
    <div class="stats-box"><h3>Final Interview</h3><p><?php echo $finalInterviewApplicants; ?></p></div>
    <div class="stats-box"><h3>Approved</h3><p><?php echo $approvedApplicants; ?></p></div>
    <div class="stats-box"><h3>Rejected</h3><p><?php echo $failedApplicants; ?></p></div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>App ID</th>
          <th>User ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Date Applied</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($applicantResult && $applicantResult->num_rows > 0) {
          while ($row = $applicantResult->fetch_assoc()) {
            $status = strtolower($row['status']);
            $statusClass = ($status === 'approved') ? 'status-approved' :
                           (($status === 'rejected') ? 'status-rejected' :
                           (($status === 'pending') ? 'status-pending' : 'status-interview'));
            echo "<tr>
                    <td>{$row['applicationid']}</td>
                    <td>{$row['userid']}</td>
                    <td>{$row['firstname']} {$row['lastname']}</td>
                    <td>{$row['email']}</td>
                    <td>" . date('F j, Y', strtotime($row['date_applied_only'])) . "</td>
                    <td><span class='status-tag {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='6'>No applicants found for this job post.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>


</div>

</body>
</html>
