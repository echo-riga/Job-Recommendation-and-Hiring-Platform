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

// ===== Count total users =====
$totalUsers = 0;
$sqlUsers = "SELECT COUNT(userid) AS total_users FROM users";
$resultUsers = $con->query($sqlUsers);
if ($resultUsers && $row = $resultUsers->fetch_assoc()) {
    $totalUsers = $row['total_users'];
}

// ===== Count total admins =====
$totalAdmins = 0;
$sqlAdmins = "SELECT COUNT(adminid) AS total_admins FROM admin";
$resultAdmins = $con->query($sqlAdmins);
if ($resultAdmins && $row = $resultAdmins->fetch_assoc()) {
    $totalAdmins = $row['total_admins'];
}

// ===== Fetch audit trail (Admin) =====
$auditAdminQuery = "
    SELECT adminid, username, action, details, time 
    FROM audit 
    WHERE adminid IS NOT NULL 
    ORDER BY time DESC";
$auditAdminResult = $con->query($auditAdminQuery);

// ===== Fetch audit trail (User) =====
$auditUserQuery = "
    SELECT userid, username, action, details, time 
    FROM audit 
    WHERE userid IS NOT NULL 
    ORDER BY time DESC";
$auditUserResult = $con->query($auditUserQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title>Audit Trail Report</title>
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
  margin-bottom: 40px;
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
.table-title {
  font-size: 20px;
  font-weight: bold;
  color: #0056d2;
  margin-bottom: 15px;
  text-align: center;
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
      <h2>Audit Trail Report</h2>
    </div>
    <div class="report-date">
      <p>Generated on:<br><strong><?php echo date('F j, Y - g:i A'); ?></strong></p>
    </div>
  </div>

  <div class="stats-summary">
    <div class="stats-box"><h3>Total Users</h3><p><?php echo $totalUsers; ?></p></div>
    <div class="stats-box"><h3>Total Admins</h3><p><?php echo $totalAdmins; ?></p></div>
  </div>

  <!-- Admin Audit Table -->
  <div class="table-container">
    <div class="table-title">Admin Audit Trail</div>
    <table>
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>Admin</th>
          <th>Action</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($auditAdminResult && $auditAdminResult->num_rows > 0) {
          while ($row = $auditAdminResult->fetch_assoc()) {
            $dateTime = new DateTime($row['time']);
            $formattedDate = $dateTime->format('F j, Y - h:i A');
            echo "<tr>
                    <td>{$formattedDate}</td>
                    <td>" . htmlspecialchars($row['username']) . "</td>
                    <td>" . htmlspecialchars($row['action']) . "</td>
                    <td>" . htmlspecialchars($row['details']) . "</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='4'>No admin audit records found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- User Audit Table -->
  <div class="table-container">
    <div class="table-title">User Audit Trail</div>
    <table>
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>User</th>
          <th>Action</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($auditUserResult && $auditUserResult->num_rows > 0) {
          while ($row = $auditUserResult->fetch_assoc()) {
            $dateTime = new DateTime($row['time']);
            $formattedDate = $dateTime->format('F j, Y - h:i A');
            echo "<tr>
                    <td>{$formattedDate}</td>
                    <td>" . htmlspecialchars($row['username']) . "</td>
                    <td>" . htmlspecialchars($row['action']) . "</td>
                    <td>" . htmlspecialchars($row['details']) . "</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='4'>No user audit records found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>


</div>

</body>
</html>
