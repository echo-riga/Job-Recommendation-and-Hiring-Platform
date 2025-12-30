<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ADMIN JOBFITSYSTEM</title>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <link rel="stylesheet" href="../css/dashboard.css?v=3">
</head>

<body>
<?php
session_start();
include 'database.php';
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header('Location: admin_login.php');
  exit();
}

include 'admin_profile_modal.php'; 
$adminName = "Administrator";
$avatarInitials = "AD";
$adminImageBase64 = null; 
$adminEmail = $_SESSION['admin'];
$stmt = $con->prepare("SELECT adminname, adminpassword, adminimage FROM admin WHERE adminemail = ?");
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$res = $stmt->get_result();
$adminData = null;
if ($res->num_rows === 1) {
    $adminData = $res->fetch_assoc();
    $adminName = $adminData['adminname'];
    $nameParts = explode(" ", trim($adminName));
    $avatarInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $avatarInitials .= strtoupper(substr($nameParts[1], 0, 1));
    }
    if (!empty($adminData['adminimage'])) {
        $adminImageBase64 = 'data:image/jpeg;base64,' . base64_encode($adminData['adminimage']);
    }
}
$stmt->close();

$systemName = "JOBFIT"; 
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
}
$stmtSys->close();


$totalUsers = 0;
$sql = "SELECT COUNT(userid) AS total_users FROM users";
$result = $con->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $totalUsers = $row['total_users'];
}
$totaljobposting = 0;
$sql = "SELECT COUNT(postid) AS total_jobpost FROM jobposting";
$result = $con->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $totaljobposting = $row['total_jobpost'];
}

$totalapplication = 0;
$sql = "SELECT COUNT(applicationid) AS total_appli FROM application";
$result = $con->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $totalapplication = $row['total_appli'];
}



$openJobCount = 0;
$sql = "SELECT COUNT(*) AS total_openjob FROM jobposting WHERE poststatus = 'Open'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $openJobCount = $row['total_openjob'];
}


$closedJobCount = 0;
$sql = "SELECT COUNT(*) AS total_closejob FROM jobposting WHERE poststatus = 'Closed'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $closedJobCount = $row['total_closejob'];
}

$activeUsers = 0;
$sql = "SELECT COUNT(*) AS active_user FROM users WHERE status = 'ACTIVE'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $activeUsers = $row['active_user'];
}

$inactiveUsers = 0;
$sql = "SELECT COUNT(*) AS inactive_user FROM users WHERE status = 'INACTIVE'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $inactiveUsers = $row['inactive_user'];
}


$pendingApplicants = 0;
$sql = "SELECT COUNT(*) AS pending_appli FROM application WHERE status = 'Pending'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $pendingApplicants = $row['pending_appli'];
}


$joboffer = 0;
$sql = "SELECT COUNT(*) AS offer_appli FROM application WHERE status = 'Job Offer'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $joboffer = $row['offer_appli'];
}

$initialInterviewApplicants = 0;
$sql = "SELECT COUNT(*) AS initialinter_appli FROM application WHERE status = 'Initial Interview'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $initialInterviewApplicants = $row['initialinter_appli'];
}


$technicalInterviewApplicants = 0;
$sql = "SELECT COUNT(*) AS tecnicalinter_appli FROM application WHERE status = 'Technical Interview'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $technicalInterviewApplicants = $row['tecnicalinter_appli'];
}

$acceptApplicants = 0;
$sql = "SELECT COUNT(*) AS accept_appli FROM application WHERE status = 'Job Offer Accepted'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $acceptApplicants = $row['accept_appli'];
}


$rejectedApplicants = 0;
$sql = "SELECT COUNT(*) AS reject_appli FROM application WHERE status = 'Job Offer Rejected'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $rejectedApplicants = $row['reject_appli'];
}


$failedApplicants = 0;
$sql = "SELECT COUNT(*) AS failed_appli FROM application WHERE status = 'Rejected'";
$result = $con->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $failedApplicants = $row['failed_appli'];
}


$message = '';
$message_type = ''; 

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

?>

<button class="burger" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
  <aside class="sidebar" aria-label="Sidebar navigation">
    <div class="logo">
        <img src="<?php echo $systemLogoBase64; ?>" alt="JOBFIT logo" class="logo-img">
    </div>
    <div class="title">
        <?php echo $systemName; ?>
    </div>
    <nav>
      <a  class="active" aria-current="page">
  <i class="fas fa-home"></i> Dashboard
</a>
<a href="analytics.php">
  <i class="fas fa-chart-bar"></i> Test Analytics
</a>

<a href="users.php">
  <i class="fas fa-users-cog"></i> Users  
</a>
<a href="posting.php">
 <i class="fas fa-briefcase"></i> Job Posting
</a>
<a href="settings.php">
  <i class="fas fa-cog"></i> Settings
</a>



    </nav>
    <a href="#" id="logoutBtn" class="logout">Logout</a>
  </aside>

<div class="logout-modal" id="logoutModal">
  <div class="logout-modal-content">
    <i class="fas fa-sign-out-alt"></i>
    <h2>LOG OUT</h2>
    <p>Are you sure you want to logout?</p>
    <div class="logout-modal-buttons">
      <button class="btn-no" id="cancelLogout">NO</button>
      <button class="btn-yes" onclick="window.location.href='?logout=true'">YES</button>
    </div>
  </div>
</div>

  <main class="content">
<section id="dashboard-header">
  <div class="top-header">
    <div class="header-left">
        <div class="admin-label">Homepage > <span style="font-weight: bold;">Dashboard</span></div>
    </div>

    <div class="user-profile" onclick="toggleDropdown()">
        <?php if ($adminImageBase64): ?>
                <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle">
            <?php else: ?>
                <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
            <?php endif; ?>
            <div class="user-info">
              <div class="user-name"><?php echo htmlspecialchars($adminName); ?></div>
              <div class="user-role">Administrator</div>
            </div>
      <div class="dropdown-icon">▼</div>

      <div class="user-dropdown" id="user-dropdown">
      <a href="#" id="viewProfileModalBtn">View Profile</a>
      <a href="settings.php">Settings</a>
      <a href="#" id="logoutDropdownBtn" class="logout">Logout</a>
      </div>
    </div>
  </div>
</section>

<section class="dashboard-overview">
  <div class="overview-header">
    <h2>Dashboard Overview</h2>
    <p>Welcome back! Here's what's happening with the System</p>
  </div>
<!-- First row -->
<div class="overview-cards">

  <div class="card">
    <div class="card-content">
      <div>
        <div class="card-title">Total Tests Taken</div>
        <div class="card-value" id="tests-taken">0</div>
        <div class="card-trend up" id="tests-taken-trend"></div>
      </div>
      <i class="fas fa-check-circle card-icon"></i>
    </div>
  </div>

  <div class="card">
    <div class="card-content">
      <div>
        <div class="card-title">Most Recommended IT Role</div>
        <div class="card-role" id="top-role"></div>
        <div class="card-sub" id="top-role-share"></div>
      </div>
      <i class="fas fa-user-tie card-icon"></i>
    </div>
  </div>

  <div class="card">
    <div class="card-content">
      <div>
        <div class="card-title">Average Test Score</div>
        <div class="card-value" id="avg-score">0%</div>
        <div class="card-trend down" id="score-trend"></div>
      </div>
      <i class="fas fa-chart-line card-icon"></i>
    </div>
  </div>
</div>

<!-- Second row (now properly aligned in the same row like the first) -->
<div class="overview-cards">
<!-- Job Posting Status card (keeps .card class) -->
<div class="card job-status-card">
  <div class="card-title">JOB POSTING STATUS</div>


  <div class="status-row">
    <div class="status-left">
      <div class="status-badge">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="status-label">TOTAL JOB POSTS:</div>
    </div>
    <div class="status-count"><?php echo $totaljobposting; ?></div>
  </div>

  <div class="status-row">
    <div class="status-left">
      <div class="status-badge">
        <i class="fa-solid fa-door-open"></i>
      </div>
      <div class="status-label">OPEN JOB POSTS:</div>
    </div>
    <div class="status-count"><?php echo $openJobCount; ?></div>
  </div>

  <div class="status-row">
    <div class="status-left">
      <div class="status-badge closed">
        <i class="fa-solid fa-lock"></i>
      </div>
      <div class="status-label">CLOSED JOB POSTS:</div>
    </div>
    <div class="status-count"><?php echo $closedJobCount; ?></div>
  </div>
</div>


<div class="card users-status-card">
  <div class="card-title">USERS STATUS</div>

  <!-- Total Users -->
  <div class="status-row">
    <div class="status-left">
      <div class="status-badge total">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="status-label">TOTAL USERS:</div>
    </div>
    <div class="status-count"><?php echo $totalUsers; ?></div>
  </div>

  <!-- Active Users -->
  <div class="status-row">
    <div class="status-left">
      <div class="status-badge active">
        <i class="fa-solid fa-user-check"></i>
      </div>
      <div class="status-label">ACTIVE USERS:</div>
    </div>
    <div class="status-count"><?php echo $activeUsers; ?></div>
  </div>

  <!-- Inactive Users -->
  <div class="status-row">
    <div class="status-left">
      <div class="status-badge inactive">
        <i class="fa-solid fa-user-xmark"></i>
      </div>
      <div class="status-label">INACTIVE USERS:</div>
    </div>
    <div class="status-count"><?php echo $inactiveUsers; ?></div>
  </div>
</div>
</div>
<div class="overview-cards">
<div class="card applicants-card">
  <div class="card-title">APPLICATIONS</div>

  <div class="status-grid">
    <!-- total application -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge pending">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="status-label">TOTAL APPLICANTS:</div>
      </div>
      <div class="status-count"><?php echo $totalapplication; ?></div>
    </div>

    <!-- Pending -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge pending">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="status-label">PENDING APPLICANTS:</div>
      </div>
      <div class="status-count"><?php echo $pendingApplicants; ?></div>
    </div>


    <!-- Initial Interview -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge initial">
          <i class="fa-regular fa-user"></i>
        </div>
        <div class="status-label">INITIAL INTERVIEW:</div>
      </div>
      <div class="status-count"><?php echo $initialInterviewApplicants; ?></div>
    </div>

    <!-- Technical Interview -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge final">
          <i class="fa-solid fa-code"></i>
        </div>
        <div class="status-label">TECHNICAL INTERVIEW:</div>
      </div>
      <div class="status-count"><?php echo $technicalInterviewApplicants; ?></div>
    </div>

    <!-- Job Offer -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge approved">
          <i class="fa-solid fa-handshake"></i>
        </div>
        <div class="status-label">JOB OFFER:</div>
      </div>
      <div class="status-count"><?php echo $joboffer; ?></div>
    </div>

    
    <!-- Accepted -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge approved">
          <i class="fa-solid fa-check"></i>
        </div>
        <div class="status-label">ACCEPTED JOB OFFER:</div>
      </div>
      <div class="status-count"><?php echo $acceptApplicants; ?></div>
    </div>

    <!-- Rejected Job Offer -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge rejected">
          <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="status-label">REJECTED JOB OFFER:</div>
      </div>
      <div class="status-count"><?php echo $rejectedApplicants; ?></div>
    </div>

    <!-- Failed -->
    <div class="status-row">
      <div class="status-left">
        <div class="status-badge rejected">
          <i class="fa-solid fa-xmark"></i>
        </div>
        <div class="status-label">FAILED APPLICANTS:</div>
      </div>
      <div class="status-count"><?php echo $failedApplicants; ?></div>
    </div>
  </div>
</div>



</div>



  <section class="audit-trail-section">
    <div class="table-card">
        <div class="table-header">
            <div>
              <h3>Audit Trail</h3>
              <p>Recent system activities</p>
            </div>
            <div class="button-group">
                <button class="report-button" id="generateReportBtn">
                  <i class="fas fa-file-alt"></i> Generate Report
                </button>
            </div>
        </div>
        
       <div class="search-filter-controls">
  <div class="search-box">
    <input type="text" class="search-input" id="auditSearch" placeholder="Search logs (User, Action, Details)...">
  </div>

  <div class="filters">
    <input type="text" class="filter-select date-range-input" id="auditDateRange" placeholder="Select Date Range" readonly>
    <select class="filter-select" id="auditUserFilter">
      <option value="">All Users/Admins</option>
      <option value="Admin">Admin</option>
      <option value="User">System User</option>
    </select>
  </div>
</div>

        <div class="table-container"> <table id="auditTrailTable">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User/Admin</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $auditQuery = "SELECT adminid, userid, username, action, details, time FROM audit ORDER BY time DESC"; 
                    $auditResult = mysqli_query($con, $auditQuery);

                    if ($auditResult && mysqli_num_rows($auditResult) > 0) {
                        while ($row = mysqli_fetch_assoc($auditResult)) {
                            $dateTime = new DateTime($row['time']);
                            $auditDateTime = $dateTime->format('F j, Y - h:i A');
                            $auditDateOnly = $dateTime->format('Y-m-d'); 
                            
                            $userName = htmlspecialchars($row['username']);
                            $action = htmlspecialchars($row['action']);
                            $details = htmlspecialchars($row['details']);
                            
                            $userType = '';
                            if (!empty($row['adminid'])) {
                                $userType = 'Admin';
                            } else if (!empty($row['userid'])) {
                                $userType = 'User';
                            }

                            echo "<tr data-date='" . $auditDateOnly . "' data-usertype='" . $userType . "' data-search-terms='" . strtolower($userName . ' ' . $action . ' ' . $details) . "'>";
                            echo "<td>" . $auditDateTime . "</td>";
                            echo "<td>" . $userName . "</td>";
                            echo "<td>" . $action . "</td>";
                            echo "<td>" . $details . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr class='no-results-row'><td colspan='4' style='text-align: center;'>No audit trail entries found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div> <div class="pagination-controls">
            <div class="pagination-info" id="auditPaginationInfo">Showing 0 to 0 of 0 logs</div>

            <div class="pagination-buttons">
                <button class="pagination-button" id="auditPrevPage" disabled><i class="fas fa-chevron-left"></i></button>
                <div class="page-numbers" id="auditPageNumbers">
                </div>
                <button class="pagination-button" id="auditNextPage" disabled><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="pagination-rows-per-page">
                <select id="auditRowsPerPage" class="rows-per-page-select">
                    <option value="5" selected>5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>
        </div>
</section>
</section>

</main>


<?php if ($message): ?>
  <div id="message" class="custom-alert <?= $message_type ?> show">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<script>
  if ("<?= $message ?>") {
    setTimeout(() => {
      document.getElementById('message')?.classList.remove('show');
    }, 3000);
  }
document.getElementById('generateReportBtn').addEventListener('click', function() {
  window.open('postingreport.php', '_blank');
});
</script>


<script>

  // Function to fetch and render test statistics
async function loadTestStatistics() {
    try {
        const response = await fetch('../api/get_test_statistics.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            renderTestStatistics(data.data);
        } else {
            throw new Error(data.message || 'Failed to load test statistics');
        }
    } catch (error) {
        console.error('Error loading test statistics:', error);
        renderTestStatisticsError();
    }
}

// Function to render the statistics
function renderTestStatistics(stats) {
    // Total Tests Taken
    const testsTakenElement = document.getElementById('tests-taken');
    const testsTrendElement = document.getElementById('tests-taken-trend');
    
    if (testsTakenElement) {
        testsTakenElement.textContent = stats.total_tests.toLocaleString();
    }
    
    if (testsTrendElement) {
        testsTrendElement.className = `card-trend ${stats.trends.tests.direction}`;
        
        let trendText = '';
        if (stats.trends.tests.value === 'N/A') {
            trendText = '<i class="fas fa-minus"></i> No previous data';
        } else if (stats.trends.tests.value === 'New data') {
            trendText = '<i class="fas fa-arrow-up"></i> New data';
        } else {
            const directionText = stats.trends.tests.direction === 'up' ? 'increase' : 
                                stats.trends.tests.direction === 'down' ? 'decrease' : 'no change';
            trendText = `<i class="fas fa-${stats.trends.tests.direction === 'up' ? 'arrow-up' : stats.trends.tests.direction === 'down' ? 'arrow-down' : 'minus'}"></i>
                        ${stats.trends.tests.value} ${directionText}`;
        }
        
        testsTrendElement.innerHTML = trendText;
    }
    
    // Most Recommended IT Role
    const topRoleElement = document.getElementById('top-role');
    const topRoleShareElement = document.getElementById('top-role-share');
    
    if (topRoleElement) {
        topRoleElement.textContent = stats.most_recommended_role;
    }
    
    if (topRoleShareElement) {
        topRoleShareElement.textContent = `${stats.role_share}% of total recommendations`;
    }
    
    // Average Test Score
    const avgScoreElement = document.getElementById('avg-score');
    const scoreTrendElement = document.getElementById('score-trend');
    
    if (avgScoreElement) {
        avgScoreElement.textContent = `${stats.average_score}%`;
    }
    
    if (scoreTrendElement) {
        scoreTrendElement.className = `card-trend ${stats.trends.score.direction}`;
        
        let trendText = '';
        if (stats.trends.score.value === 'N/A') {
            trendText = '<i class="fas fa-minus"></i> No previous data';
        } else if (stats.trends.score.value === 'New data') {
            trendText = '<i class="fas fa-arrow-up"></i> New data';
        } else {
            const directionText = stats.trends.score.direction === 'up' ? 'increase' : 
                                stats.trends.score.direction === 'down' ? 'decrease' : 'no change';
            trendText = `<i class="fas fa-${stats.trends.score.direction === 'up' ? 'arrow-up' : stats.trends.score.direction === 'down' ? 'arrow-down' : 'minus'}"></i>
                        ${stats.trends.score.value} ${directionText}`;
        }
        
        scoreTrendElement.innerHTML = trendText;
    }
}

// Function to handle errors
function renderTestStatisticsError() {
    const elements = {
        'tests-taken': '0',
        'top-role': 'No data available',
        'top-role-share': 'Unable to load data',
        'avg-score': '0%'
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
    
    // Reset trends
    const trendElements = ['tests-taken-trend', 'score-trend'];
    trendElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.className = 'card-trend neutral';
            element.innerHTML = '<i class="fas fa-minus"></i> Unable to load';
        }
    });
}


// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadTestStatistics();
    
    setInterval(loadTestStatistics, 300000);
});


function generateInitials(fullName) {
    const nameParts = fullName.trim().split(/\s+/); 
    let initials = nameParts[0].substring(0, 1).toUpperCase();
    if (nameParts.length > 1) {
        initials += nameParts[nameParts.length - 1].substring(0, 1).toUpperCase(); 
    }
    return initials;
}

function toggleDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    if (!isVisible) {
        document.addEventListener('click', function closeOnClick(event) {
          if (
            !event.target.closest('.user-profile') &&
            !event.target.closest('#user-dropdown')
          ) {
            dropdown.style.display = 'none';
            document.removeEventListener('click', closeOnClick); 
          }
        }, { once: false }); 
    }
}


class TablePaginator {
    constructor(tableId, controlsPrefix) {
        this.table = document.getElementById(tableId);
        this.tbody = this.table ? this.table.querySelector('tbody') : null;
        if (!this.tbody) {
            return;
        }

        this.rows = Array.from(this.tbody.querySelectorAll('tr:not(.no-results-row)'));
        this.noResultsRow = this.tbody.querySelector('.no-results-row');
        this.currentPage = 1;

        this.search = document.getElementById(controlsPrefix + 'Search');
        this.rowsPerPageSelect = document.getElementById(controlsPrefix + 'RowsPerPage');
        this.infoDiv = document.getElementById(controlsPrefix + 'PaginationInfo');
        this.prevButton = document.getElementById(controlsPrefix + 'PrevPage');
        this.nextButton = document.getElementById(controlsPrefix + 'NextPage');
        this.pageNumbersDiv = document.getElementById(controlsPrefix + 'PageNumbers');
        this.dateRangeInput = document.getElementById(controlsPrefix + 'DateRange');
        this.userFilter = document.getElementById(controlsPrefix + 'UserFilter');
        this.rowsPerPage = parseInt(this.rowsPerPageSelect ? this.rowsPerPageSelect.value : 10);
        this.filteredRows = this.rows;
        this.startDate = null;
        this.endDate = null;

        this.init();
    }

    init() {
        if (!this.tbody) return;

        this.search?.addEventListener('input', this.filterAndPaginate.bind(this));
        this.rowsPerPageSelect?.addEventListener('change', this.handleRowsPerPageChange.bind(this));
        this.prevButton?.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        this.nextButton?.addEventListener('click', () => this.goToPage(this.currentPage + 1));
        this.userFilter?.addEventListener('change', this.filterAndPaginate.bind(this));

        if (this.dateRangeInput) {
            $(this.dateRangeInput).datepicker({
                dateFormat: 'yy-mm-dd',
                onSelect: (dateText, inst) => {
                    if (!this.startDate || (this.startDate && this.endDate)) {
                        this.startDate = dateText;
                        this.endDate = null;
                        $(this.dateRangeInput).val(dateText + ' to ');
                    } 
                    else if (!this.endDate && this.startDate && dateText >= this.startDate) {
                        this.endDate = dateText;
                        $(this.dateRangeInput).val(this.startDate + ' to ' + this.endDate);
                        this.filterAndPaginate();
                    } 
                    else if (dateText < this.startDate) {
                         this.startDate = dateText;
                         this.endDate = null;
                         $(this.dateRangeInput).val(dateText + ' to ');
                    }
                }
            });

            $(this.dateRangeInput).on('click', () => {
                 if (this.startDate && this.endDate) {
                    this.startDate = null;
                    this.endDate = null;
                    $(this.dateRangeInput).val('');
                    this.filterAndPaginate();
                }
            });
        }
        
        this.filterAndPaginate(); 
    }

    handleRowsPerPageChange(event) {
        this.rowsPerPage = parseInt(event.target.value);
        this.currentPage = 1;
        this.paginate();
    }

    filterAndPaginate() {
        const searchTerm = this.search ? this.search.value.toLowerCase().trim() : '';
        const userTypeFilter = this.userFilter ? this.userFilter.value : '';
        const startDate = this.startDate; 
        const endDate = this.endDate;     

        this.filteredRows = this.rows.filter(row => {
            const rowSearchTerms = row.dataset.searchTerms || '';
            const searchMatch = !searchTerm || rowSearchTerms.includes(searchTerm);

            const rowUserType = row.dataset.usertype || '';
            const userTypeMatch = !userTypeFilter || rowUserType === userTypeFilter;

            const rowDate = row.dataset.date; 
            let dateMatch = true;

            if (startDate && rowDate) {
                if (endDate) {
                    dateMatch = (rowDate >= startDate && rowDate <= endDate);
                } else {
                    dateMatch = (rowDate === startDate);
                }
            }

            return searchMatch && userTypeMatch && dateMatch;
        });

        this.currentPage = 1; 
        this.paginate();
    }

    paginate() {
        const totalRows = this.filteredRows.length;
        const totalPages = Math.ceil(totalRows / this.rowsPerPage);
        
        this.currentPage = Math.max(1, Math.min(this.currentPage, totalPages || 1));

        const start = (this.currentPage - 1) * this.rowsPerPage;
        const end = start + this.rowsPerPage;

        this.rows.forEach(row => row.style.display = 'none');
        
        if (totalRows > 0) {
            this.filteredRows.slice(start, end).forEach(row => row.style.display = '');
             if (this.noResultsRow) this.noResultsRow.style.display = 'none';
        } else {
             if (this.noResultsRow) this.noResultsRow.style.display = '';
        }

        this.updateControls(totalRows, totalPages, start, end);
    }

    updateControls(totalRows, totalPages, start, end) {
        const displayStart = totalRows === 0 ? 0 : start + 1;
        const displayEnd = Math.min(end, totalRows);
        if (this.infoDiv) {
            this.infoDiv.textContent = `Showing ${displayStart} to ${displayEnd} of ${totalRows} logs`;
        }

        if (this.prevButton) this.prevButton.disabled = this.currentPage === 1 || totalRows === 0;
        if (this.nextButton) this.nextButton.disabled = this.currentPage === totalPages || totalRows === 0;

        if (this.pageNumbersDiv) {
            this.pageNumbersDiv.innerHTML = '';
            const maxButtons = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);

            if (endPage - startPage + 1 < maxButtons) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('button');
                pageButton.classList.add('page-number');
                if (i === this.currentPage) {
                    pageButton.classList.add('active');
                }
                pageButton.textContent = i;
                pageButton.addEventListener('click', () => this.goToPage(i));
                this.pageNumbersDiv.appendChild(pageButton);
            }
        }
    }

    goToPage(page) {
        if (page >= 1 && page <= Math.ceil(this.filteredRows.length / this.rowsPerPage)) {
            this.currentPage = page;
            this.paginate();
        }
    }
}


document.addEventListener('DOMContentLoaded', function () {
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');
    burger.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        burger.classList.toggle('active');
    });

    function openLogoutModal(e) {
        e.preventDefault();
        logoutModal.style.display = 'flex';
    }

    const logoutBtnSidebar = document.getElementById('logoutBtn');
    const logoutBtnDropdown = document.getElementById('logoutDropdownBtn'); 
    logoutBtnSidebar?.addEventListener('click', openLogoutModal);
    logoutBtnDropdown?.addEventListener('click', openLogoutModal);

    cancelLogout.addEventListener('click', function () {
        logoutModal.style.display = 'none';
    });

    document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(href);
                if (target) {
                    window.scrollTo({ top: target.offsetTop, behavior: 'smooth' });
                }
            }
        });
    });

   
    const adminProfileModal = document.getElementById('adminUserProfileModal');
    const viewProfileModalBtn = document.getElementById('viewProfileModalBtn');
    const closeAdminUserProfileModal = document.getElementById('closeAdminUserProfileModal');
    const adminProfileModalCloseBtn = document.getElementById('adminProfileModalCloseBtn');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const adminImageInput = document.getElementById('adminImageInput');
    const imageUploadForm = document.getElementById('imageUploadForm');

    const profileFields = {
      adminName: document.getElementById('viewAdminFullName'),
      adminEmail: document.getElementById('viewAdminEmail'),
    };

    const profileSummaryElements = {
        profileFullNameModal: document.getElementById('profileFullNameModal'),
        profileEmailSummaryModal: document.getElementById('profileEmailSummaryModal'),
        profileAvatarModal: document.getElementById('profileAvatarModal')
    };

    let isEditing = false;
    let originalAdminName = profileFields.adminName?.value || '';
    let originalAdminEmail = profileFields.adminEmail?.value || '';
    let originalAdminImageBase64 = document.getElementById('originalAdminImageBase64')?.value || '';
    let tempAdminImageFile = null;

    function toggleEditMode(enable) {
      isEditing = enable;

      if (profileFields.adminName) {
        profileFields.adminName.readOnly = !enable;
        profileFields.adminName.classList.toggle('editable-field', enable);
      }
      if (profileFields.adminEmail) {
        profileFields.adminEmail.readOnly = !enable;
        profileFields.adminEmail.classList.toggle('editable-field', enable);
      }


      if(editProfileBtn) editProfileBtn.style.display = enable ? 'none' : 'inline-block';
      if(saveProfileBtn) saveProfileBtn.style.display = enable ? 'inline-block' : 'none';
      if(changePhotoBtn) changePhotoBtn.style.display = enable ? 'inline-block' : 'none';

      if(adminProfileModalCloseBtn) adminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';
    }

    function restoreOriginalImageInModal() {
        const avatarElement = profileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

        if (!avatarElement) return; 

        const mainAvatarIsImg = mainAvatarElement?.tagName === 'IMG';
        const modalAvatarIsImg = avatarElement.tagName === 'IMG';

        if (originalAdminImageBase64 && originalAdminImageBase64 !== '') {
            if (!modalAvatarIsImg) { 
                const imgElement = document.createElement('img');
                imgElement.src = originalAdminImageBase64;
                imgElement.alt = "Admin Avatar";
                imgElement.classList.add('avatar-circle-large');
                avatarElement.replaceWith(imgElement);
                profileSummaryElements.profileAvatarModal = imgElement;
            } else {
                avatarElement.src = originalAdminImageBase64;
            }

            if (mainAvatarElement && !mainAvatarIsImg) {
                const mainImgElement = document.createElement('img');
                mainImgElement.src = originalAdminImageBase64;
                mainImgElement.alt = "Admin Avatar";
                mainImgElement.classList.add('avatar-circle');
                mainAvatarElement.replaceWith(mainImgElement);
            } else if (mainAvatarElement) {
                mainAvatarElement.src = originalAdminImageBase64;
            }

        } else {
            const initials = generateInitials(originalAdminName);
            if (modalAvatarIsImg) {
                const initialsDiv = document.createElement('div');
                initialsDiv.classList.add('avatar-circle-large');
                initialsDiv.textContent = initials;
                avatarElement.replaceWith(initialsDiv);
                profileSummaryElements.profileAvatarModal = initialsDiv;
            } else {
                avatarElement.textContent = initials;
            }

            if (mainAvatarElement && mainAvatarIsImg) {
                const mainInitialsDiv = document.createElement('div');
                mainInitialsDiv.classList.add('avatar-circle');
                mainInitialsDiv.textContent = initials;
                mainAvatarElement.replaceWith(mainInitialsDiv);
            } else if (mainAvatarElement) {
                mainAvatarElement.textContent = initials;
            }
        }
    }
    
    viewProfileModalBtn?.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('user-dropdown').style.display = 'none';
      
      if (profileFields.adminName) originalAdminName = profileFields.adminName.value;
      if (profileFields.adminEmail) originalAdminEmail = profileFields.adminEmail.value;
      
      const originalImageEl = document.getElementById('originalAdminImageBase64');
      if (originalImageEl) originalAdminImageBase64 = originalImageEl.value;
      
      tempAdminImageFile = null;

      restoreOriginalImageInModal();
      toggleEditMode(false);
      if (adminProfileModal) adminProfileModal.style.display = 'flex';
    });

    function closeAdminProfileModal(isCancel = false) {
        if (isCancel || isEditing) {
            if (profileFields.adminName) profileFields.adminName.value = originalAdminName;
            if (profileFields.adminEmail) profileFields.adminEmail.value = originalAdminEmail;
            restoreOriginalImageInModal();
            tempAdminImageFile = null;
        }
        toggleEditMode(false);
        if (adminProfileModal) adminProfileModal.style.display = 'none';
    }

    closeAdminUserProfileModal?.addEventListener('click', () => closeAdminProfileModal(true));
    adminProfileModalCloseBtn?.addEventListener('click', () => closeAdminProfileModal(isEditing));

    editProfileBtn?.addEventListener('click', function () {
      toggleEditMode(true);
    });

    saveProfileBtn?.addEventListener('click', async function () {
        if (!profileFields.adminName || !profileFields.adminEmail) return;

        const newAdminName = profileFields.adminName.value.trim();
        const newAdminEmail = profileFields.adminEmail.value.trim();

        if (!newAdminName || !newAdminEmail) {
            alert('Full Name and Email Address cannot be empty.');
            return;
        }

        if (!/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/.test(newAdminEmail)) {
            alert('Please enter a valid email address.');
            return;
        }

        const formData = new FormData();
        formData.append('adminname', newAdminName);
        formData.append('adminemail', newAdminEmail);

        if (tempAdminImageFile) {
            formData.append('adminImage', tempAdminImageFile);
        } else if (originalAdminImageBase64 === '') {
             formData.append('adminImage', ''); 
        }

        try {
            const response = await fetch('update_admin_profile.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload(); 
            } else {
                alert('Error: ' + data.message); 
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('An error occurred while updating profile.');
        }
    });

    changePhotoBtn?.addEventListener('click', function () {
        if (adminImageInput) adminImageInput.click();
    });

    adminImageInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            tempAdminImageFile = this.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                const avatarElement = profileSummaryElements.profileAvatarModal;
                if (!avatarElement) return;

                if (avatarElement.tagName === 'DIV') {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    imgElement.alt = "Admin Avatar";
                    imgElement.classList.add('avatar-circle-large');
                    avatarElement.replaceWith(imgElement);
                    profileSummaryElements.profileAvatarModal = imgElement;
                } else {
                    avatarElement.src = e.target.result;
                }
            };
            reader.readAsDataURL(tempAdminImageFile);
        }
    });

    const auditPaginator = new TablePaginator('auditTrailTable', 'audit');


    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (logoutModal.style.display === 'flex') {
            logoutModal.style.display = 'none';
            return;
        }

        if (adminProfileModal.style.display === 'flex') {
            closeAdminProfileModal(isEditing);
            return;
        }

        const userDropdown = document.getElementById('user-dropdown');
        if (userDropdown) userDropdown.style.display = 'none';
      }
    });

    window.addEventListener('click', function(event) {
        if (event.target == adminProfileModal) {
            closeAdminProfileModal(isEditing);
        }
    });

   
    const generateReportBtn = document.getElementById('generateReportBtn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            window.open('auditreport.php', '_blank');
        });
    }

}); 

</script>

</body>
</html>