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

$totalFeedback = 0;
$totalResolved = 0;
$totalReplied = 0;
$totalPending = 0;

$sql = "SELECT 
            COUNT(*) AS total_feedback,
            SUM(CASE WHEN constatus = 'resolved' THEN 1 ELSE 0 END) AS total_resolved,
            SUM(CASE WHEN constatus = 'replied' THEN 1 ELSE 0 END) AS total_replied,
            SUM(CASE WHEN constatus = 'pending' THEN 1 ELSE 0 END) AS total_pending
        FROM contactus";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $totalFeedback = $row['total_feedback'];
    $totalResolved = $row['total_resolved'];
    $totalReplied = $row['total_replied'];
    $totalPending = $row['total_pending'];
}

$query = "
    SELECT conid, condate, conname, consubject, conemail, conphone, constatus
    FROM contactus
    ORDER BY condate DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Feedback Report</title>
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
  flex-wrap: wrap;
}
.stats-box {
  background-color: #eef2ff;
  padding: 15px 25px;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  min-width: 150px;
}
.stats-box h3 {
  color: #0056d2;
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
  background-color: #0056d2;
  color: white;
  font-weight: 600;
}
tr:nth-child(even) {
  background-color: #f9fafb;
}
.status-tag {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 13px;
}
.status-resolved {
  background-color: #d1fae5;
  color: #16a34a;
}
.status-replied {
  background-color: #e0f2fe;
  color: #0284c7;
}
.status-pending {
  background-color: #fef9c3;
  color: #ca8a04;
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
      <h2>Feedback Report</h2>
    </div>
    <div class="report-date">
      <p>Generated on:<br><strong><?php echo date('F j, Y - g:i A'); ?></strong></p>
    </div>
  </div>

  <div class="stats-summary">
    <div class="stats-box">
      <h3>Total Feedback</h3>
      <p><?php echo $totalFeedback; ?></p>
    </div>
    <div class="stats-box">
      <h3>Pending</h3>
      <p><?php echo $totalPending; ?></p>
    </div>
    <div class="stats-box">
      <h3>Replied</h3>
      <p><?php echo $totalReplied; ?></p>
    </div>
    <div class="stats-box">
      <h3>Resolved</h3>
      <p><?php echo $totalResolved; ?></p>
    </div>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Name</th>
          <th>Subject</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $statusClass = 'status-pending';
            if (strtolower($row['constatus']) === 'resolved') $statusClass = 'status-resolved';
            elseif (strtolower($row['constatus']) === 'replied') $statusClass = 'status-replied';

            echo "<tr>";
            echo "<td>" . $row['conid'] . "</td>";
            echo "<td>" . date('F j, Y', strtotime($row['condate'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['conname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['consubject']) . "</td>";
            echo "<td>" . htmlspecialchars($row['conemail']) . "</td>";
            echo "<td>" . htmlspecialchars($row['conphone']) . "</td>";
            echo "<td><span class='status-tag $statusClass'>" . strtoupper(htmlspecialchars($row['constatus'])) . "</span></td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='7'>No feedback records found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>



</div>

</body>
</html>
