<?php
include 'database.php';
date_default_timezone_set('Asia/Manila');

$systemLogoBase64 = '../image/jftlogo.png';


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


$totalJobPosting = 0;
$openJobCount = 0;
$closedJobCount = 0;

$sql = "SELECT 
            COUNT(*) AS total_jobpost,
            SUM(CASE WHEN poststatus = 'Open' THEN 1 ELSE 0 END) AS total_openjob,
            SUM(CASE WHEN poststatus = 'Closed' THEN 1 ELSE 0 END) AS total_closejob
        FROM jobposting";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $totalJobPosting = $row['total_jobpost'];
    $openJobCount = $row['total_openjob'];
    $closedJobCount = $row['total_closejob'];
}


$query = "
    SELECT 
        jp.postid, 
        jp.posttype,
        jp.postjobrole, 
        DATE(jp.postdate) as post_date_only,
        jp.postdeadline, 
        jp.poststatus,
        COUNT(a.postid) as applicant_count
    FROM 
        jobposting jp
    LEFT JOIN 
        application a ON jp.postid = a.postid
    GROUP BY
        jp.postid
    ORDER BY
        jp.postid DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Job Posting Report</title>
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
  border-bottom: 3px solid #0056d2;
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
  color: #0056d2;
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
  gap: 30px;
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
  color: #0056d2;
  margin: 0;
  font-size: 20px;
}
.stats-box p {
  font-size: 24px;
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
  background-color: #0056d2;
  color: white;
  font-weight: 600;
}
tr:nth-child(even) {
  background-color: #f9fafb;
}
.status-open {
  background-color: #d1fae5;
  color: #16a34a;
  padding: 5px 10px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 13px;
}
.status-closed {
  background-color: #fee2e2;
  color: #dc2626;
  padding: 5px 10px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 13px;
}
.print-btn {
  display: block;
  margin: 30px 10rem 30px auto;
  padding: 10px 25px;
  background-color: #0056d2;
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
  body {
    background: white;
    margin: 0;
  }
  .report-wrapper {
    box-shadow: none;
    border: none;
    margin: 0;
  }
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
      <h2>Job Posting Report</h2>
    </div>
    <div class="report-date">
      <p>Generated on:<br><strong><?php echo date('F j, Y - g:i A'); ?></strong></p>
    </div>
  </div>

  <div class="stats-summary">
    <div class="stats-box">
      <h3>Total Job Postings</h3>
      <p><?php echo $totalJobPosting; ?></p>
    </div>
    <div class="stats-box">
      <h3>Open Jobs</h3>
      <p><?php echo $openJobCount; ?></p>
    </div>
    <div class="stats-box">
      <h3>Closed Jobs</h3>
      <p><?php echo $closedJobCount; ?></p>
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Job Position</th>
          <th>Employment Type</th>
          <th>Posting Date</th>
          <th>Deadline</th>
          <th>Applicants</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['postid'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['postjobrole']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['posttype']) . "</td>";
            echo "<td>" . date('F j, Y', strtotime($row['post_date_only'])) . "</td>";
            echo "<td>" . date('F j, Y', strtotime($row['postdeadline'])) . "</td>";
            echo "<td>" . $row['applicant_count'] . " applicant/s</td>";
            $status = strtolower($row['poststatus']) === 'open' 
                      ? '<span class="status-open">OPEN</span>' 
                      : '<span class="status-closed">CLOSED</span>';
            echo "<td>" . $status . "</td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='7'>No job postings found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>


</div>

</body>
</html>
