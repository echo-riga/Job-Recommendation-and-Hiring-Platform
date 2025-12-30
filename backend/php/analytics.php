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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../css/analytics.css">
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
    // Generate initials from name
    $nameParts = explode(" ", trim($adminName));
    $avatarInitials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $avatarInitials .= strtoupper(substr($nameParts[1], 0, 1));
    }
    // Handle admin's own profile image
    if (!empty($adminData['adminimage'])) {
        $adminImageBase64 = 'data:image/jpeg;base64,' . base64_encode($adminData['adminimage']);
    }
}
$stmt->close();

// --- Fetch System Settings ---
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


$message = '';
$message_type = ''; // 'success' or 'error'

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
      <a href="dashboard.php">
        <i class="fas fa-home"></i> Dashboard
      </a>
      <a class="active" aria-current="page">
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
          <div class="admin-label">Homepage > <span style="font-weight: bold;">Test Analytics</span></div>
        </div>

        <div class="user-profile" onclick="toggleDropdown()">
            <?php if ($adminImageBase64): ?>
                <img src="<?php echo $adminImageBase64; ?>" alt="Admin Avatar" class="avatar-circle">
            <?php else: ?>
                <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
            <?php endif; ?>            <div class="user-info">
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
        <h2>Test Analytics</h2>
        <p>Display your tests analytics and its overall performance .</p>
      </div>

     <div class="overview-graphs">
  <div class="graph-card">
    <div class="graph-header">
      <h3>Most Recommended IT Roles</h3>
      <span>Last 30 days</span>
    </div>
    <canvas id="barChart"></canvas>
  </div>

  <div class="graph-card">
    <div class="graph-header">
      <h3>Score Distribution by Skill</h3>
      <span>Key skills assessment</span>
    </div>
    <canvas id="pieChart" class="pie-chart-canvas"></canvas>
  </div>
</div>


     
    </section>


      <section class="dashboard-overview">
        <div class="bordered-section">
            <div class="section-header">
                <div>
                    <h3>JobFit Test Takers</h3>
                    <p>A list of all individuals who have taken the JobFit test, including their results and contact information.</p>
                </div>
            </div>
            <div class="search-filter-controls">
                <div class="search-box">
                    <input type="text" class="search-input" id="testTakerSearch" placeholder="Search test takers...">
                </div>
                <div class="filters">
                    <select class="filter-select" id="jftResultFilter">
                        <option value="">Result Status</option>
                        <option value="Pass">Pass (Example)</option>
                        <option value="Fail">Fail (Example)</option>
                    </select>
                    <select class="filter-select" id="jftDateSortOrder">
                        <option value="default">Default Order</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table id="testTakerTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date Taken</th>
                            <th>Current JFT Result</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        </tbody>
                </table>
            </div>
            <div class="pagination-controls">
                <div class="pagination-info" id="testTakerPaginationInfo">Showing 0 to 0 of 0 test takers</div>

                <div class="pagination-buttons">
                    <button class="pagination-button" id="testTakerPrevPage"><i class="fas fa-chevron-left"></i></button>
                    <div class="page-numbers" id="testTakerPageNumbers">
                        </div>
                    <button class="pagination-button" id="testTakerNextPage"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="pagination-rows-per-page">
                    <select id="testTakerRowsPerPage" class="rows-per-page-select">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="20">20 per page</option>
                    </select>
                </div>
            </div>
        </div>


     <div class="completion-rate-section">
        <div class="graph-card small-chart">
          <div class="graph-header">
            <h3>Test Completion Rate</h3>
            <div class="tab-buttons">
              <button class="tab active" data-period="daily">Daily</button>
              <button class="tab" data-period="weekly">Weekly</button>
              <button class="tab" data-period="monthly">Monthly</button>
            </div>
          </div>
          <canvas id="lineChart"></canvas>
        </div>
      </div>

      </section>



<!-- MODAL STRUCTURE -->
<div class="modal takers-profile-modal" id="viewjobtakersModal">
    <div class="modal-content takers-profile-content">
        <div class="modal-header">
            <h2>JobFit Test Takers</h2>
            <span class="close-button" id="closejobtakersModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="avatar-circle-large" id="jobtakersProfileAvatar">AD</div>
                <div class="profile-name" id="jobtakersnProfileFullName">Admin Doe</div>
                <div class="profile-email" id="jobtakersnProfileEmailSummary">admin.doe@example.com</div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user"></i></div>
                <div class="section-title">Personal Information</div>
                <div class="section-fields">
                    <div class="form-field">
                         <label for="viewUserId">User ID</label>
                         <input type="text" id="viewUserId" readonly>
                    </div>
                    <div class="form-field">
                        <label>User Name</label>
                        <input type="text" id="viewUserFullName" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-envelope"></i></div>
                <div class="section-title">Contact Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Email Address</label>
                        <input type="email" id="viewUserEmail" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Date Taken</label>
                        <input type="text" id="viewdatetaken" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-chart-line"></i></div>
                <div class="section-title">JobFit Test Result</div>
                <div class="section-fields">
    <!-- Top: Current Job Role Match -->
    <div class="form-field">
      <label>Current Job Role Match</label>
      <input type="text" id="rolematch" readonly>
    </div>

    <!-- Second row: Percentage and Date Taken side by side -->
    <div class="form-field-row">
      <div class="form-field">
        <label>Percentage</label>
        <input type="text" id="rolepercentage" readonly>
      </div>
      <div class="form-field">
        <label>Date Taken</label>
        <input type="text" id="roledatetaken" readonly>
      </div>
    </div>
  </div>
</div>

                 <div class="profile-section">
                <div class="section-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="section-title">Test History</div>
                <div class="section-fields">
                    <div class="form-field">
                        <table class="history-table">
    <thead>
      <tr>
        <th>Suggested Job</th>
        <th>Date Taken</th>
        <th>Percentage</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><input type="text" value="System Admin" readonly></td>
        <td><input type="text" value="October 15, 2025" readonly></td>
        <td><input type="text" value="85%" readonly></td>
      </tr>
      <tr>
        <td><input type="text" value="Web developer" readonly></td>
        <td><input type="text" value="October 18, 2025" readonly></td>
        <td><input type="text" value="90%" readonly></td>
      </tr>
      <tr>
        <td><input type="text" value="Ui/UX" readonly></td>
        <td><input type="text" value="October 20, 2025" readonly></td>
        <td><input type="text" value="92%" readonly></td>
      </tr>
    </tbody>
  </table>
                    </div>
                </div>
            </div>

        <div class="modal-footer">
            <button class="btn-close" id="jobtakersModalCloseBtn">Close</button>
        </div>
    </div>
</div>
  </div>



  <div class="delete-user-modal" id="deleteUserModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-exclamation-triangle delete-warning-icon"></i>
        </div>
        <h2>Delete User</h2>
        <p class="delete-warning-text">This action cannot be undone.</p>
        <p class="delete-confirmation-text">Are you sure you want to delete <span id="deleteUserName"></span>? This will permanently remove their account and all associated data.</p>
        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelDeleteUser">Cancel</button>
            <button class="btn-delete" id="confirmDeleteUser">Delete User</button>
        </div>
    </div>
</div>


</main>




<script>
  document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    
    initializeAllCharts();
    
    loadTestTakersTable();
    
    document.querySelectorAll('.tab').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            this.classList.add('active');
            const period = this.getAttribute('data-period');
            updateLineChartByPeriod(period);
        });
    });

    // --- START: Added Admin Profile Modal Logic ---

    // --- Admin Profile Modal Elements (from admin_profile_modal.php) ---
    const adminProfileModal = document.getElementById('adminUserProfileModal');
    const viewProfileModalBtn = document.getElementById('viewProfileModalBtn');
    const closeAdminUserProfileModal = document.getElementById('closeAdminUserProfileModal');
    const adminProfileModalCloseBtn = document.getElementById('adminProfileModalCloseBtn');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const adminImageInput = document.getElementById('adminImageInput');

    // Admin Profile Fields/Summary Elements
    const profileFields = {
        adminName: document.getElementById('viewAdminFullName'),
        adminEmail: document.getElementById('viewAdminEmail'),
    };
    const profileSummaryElements = {
        profileFullNameModal: document.getElementById('profileFullNameModal'),
        profileEmailSummaryModal: document.getElementById('profileEmailSummaryModal'),
        profileAvatarModal: document.getElementById('profileAvatarModal')
    };

    // Admin Profile State Variables
    let isEditing = false;
    let originalAdminName = profileFields.adminName?.value || '';
    let originalAdminEmail = profileFields.adminEmail?.value || '';
    let originalAdminImageBase64 = document.getElementById('originalAdminImageBase64')?.value || '';
    let tempAdminImageFile = null;

    
    // --- Core Utility Functions (Admin Profile & Modals) ---

    /**
     * Toggles the readOnly state of admin profile fields and button visibility.
     * @param {boolean} enable - True to enable editing, false to disable.
     */
    function toggleEditMode(enable) {
        isEditing = enable;
        if (profileFields.adminName) profileFields.adminName.readOnly = !enable;
        if (profileFields.adminEmail) profileFields.adminEmail.readOnly = !enable;

        if (editProfileBtn) editProfileBtn.style.display = enable ? 'none' : 'inline-block';
        if (saveProfileBtn) saveProfileBtn.style.display = enable ? 'inline-block' : 'none';
        if (changePhotoBtn) changePhotoBtn.style.display = enable ? 'inline-block' : 'none';

        if (adminProfileModalCloseBtn) adminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

        if (profileFields.adminName) profileFields.adminName.classList.toggle('editable-field', enable);
        if (profileFields.adminEmail) profileFields.adminEmail.classList.toggle('editable-field', enable);
    }

    /**
     * Restores the original image/initials in the modal and main profile area.
     */
    function restoreOriginalImageInModal() {
        const avatarElement = profileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

        const updateAvatar = (element, sizeClass) => {
            if (!element) return;
            // First, remove existing image/initials
            if (element.tagName === 'IMG') {
                const tempDiv = document.createElement('div');
                element.replaceWith(tempDiv);
                element = tempDiv;
            }
            element.innerHTML = '';
            element.className = sizeClass;

            if (originalAdminImageBase64) {
                const imgElement = document.createElement('img');
                imgElement.src = originalAdminImageBase64;
                imgElement.alt = "Admin Avatar";
                imgElement.classList.add(sizeClass);
                element.replaceWith(imgElement);
                return imgElement;
            } else {
                element.classList.add(sizeClass);
                element.textContent = generateInitials(originalAdminName);
                return element;
            }
        };

        if (avatarElement) {
            profileSummaryElements.profileAvatarModal = updateAvatar(avatarElement, 'avatar-circle-large');
        }
        if (mainAvatarElement) {
            updateAvatar(mainAvatarElement, 'avatar-circle');
        }
    }

    
    const closeAdminProfile = () => {
        if (adminProfileModal && adminProfileModal.style.display === 'flex' && isEditing) {
            if (profileFields.adminName) profileFields.adminName.value = originalAdminName;
            if (profileFields.adminEmail) profileFields.adminEmail.value = originalAdminEmail;
            restoreOriginalImageInModal();
            tempAdminImageFile = null;
        }
        if (adminProfileModal) adminProfileModal.style.display = 'none';
        toggleEditMode(false);
    };
    


    // --- Admin Profile Modal Handlers ---
    viewProfileModalBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('user-dropdown').style.display = 'none'; 
        originalAdminName = profileFields.adminName.value;
        originalAdminEmail = profileFields.adminEmail.value;
        originalAdminImageBase64 = document.getElementById('originalAdminImageBase64').value; 
        tempAdminImageFile = null; 
        restoreOriginalImageInModal();
        toggleEditMode(false); 
        if (adminProfileModal) adminProfileModal.style.display = 'flex';
    });

    closeAdminUserProfileModal?.addEventListener('click', closeAdminProfile);
    adminProfileModalCloseBtn?.addEventListener('click', closeAdminProfile);

    editProfileBtn?.addEventListener('click', function () {
        toggleEditMode(true);
    });

    saveProfileBtn?.addEventListener('click', async function () {
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
                let avatarElement = profileSummaryElements.profileAvatarModal;
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

    // --- END: Added Admin Profile Modal Logic ---


    // --- Job Taker Modal Listeners (Moved from second DOMContentLoaded) ---
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-result')) {
            const userId = e.target.getAttribute('data-userid');
            if (userId) {
                loadUserTestDetails(userId);
                document.getElementById('viewjobtakersModal').style.display = 'flex';
            }
        }
    });

    // Close modal when clicking the X or the Close button
    document.getElementById('closejobtakersModal').addEventListener('click', () => {
        document.getElementById('viewjobtakersModal').style.display = 'none';
    });

    document.getElementById('jobtakersModalCloseBtn').addEventListener('click', () => {
        document.getElementById('viewjobtakersModal').style.display = 'none';
    });

}); // <-- END OF DOMContentLoaded



// Combined function to initialize all charts
function initializeAllCharts() {
    renderBarChart();
    initializePieChart();
    initializeLineChart();
}

// Bar Chart - Most Recommended IT Roles
async function renderBarChart() {
    try {
        const response = await fetch('../api/job_counts_api.php');
        const data = await response.json();
        
        if (data.success) {
            createBarChartWithData(data.data);
        } else {
            console.error('API error:', data.message);
            createBarChartWithStaticData();
        }
    } catch (error) {
        console.error('Error loading recommended roles:', error);
        createBarChartWithStaticData();
    }
}

function createBarChartWithData(rolesData) {
    const barCtx = document.getElementById('barChart').getContext('2d');
    
    if (window.barChart instanceof Chart) {
        window.barChart.destroy();
    }
    
    const sortedData = rolesData.sort((a, b) => b.job_count - a.job_count);
    
    const labels = sortedData.map(role => role.job_title);
    const counts = sortedData.map(role => role.job_count);
    const confidences = sortedData.map(role => (role.average_confidence * 100).toFixed(1));
    
    const backgroundColors = generateColors(sortedData.length);
    
    window.barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Recommendations',
                data: counts,
                backgroundColor: backgroundColors,
                borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Recommendations'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Job Titles'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                       
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
}

function createBarChartWithStaticData() {
    const barCtx = document.getElementById('barChart').getContext('2d');
    
    if (window.barChart instanceof Chart) {
        window.barChart.destroy();
    }
    
    // Static fallback data
    const staticData = {
        labels: ['Software Developer', 'Data Scientist', 'Network Engineer', 'Cybersecurity Analyst', 'UI/UX Designer', 'DevOps Engineer', 'Cloud Architect', 'System Administrator'],
        datasets: [{
            label: 'Number of Recommendations',
            data: [30, 25, 20, 15, 12, 10, 8, 7],
            backgroundColor: ['#3b82f6', '#60a5fa', '#818cf8', '#a78bfa', '#f472b6', '#fb7185', '#fca5a5', '#f87171']
        }]
    };
    
    window.barChart = new Chart(barCtx, {
        type: 'bar',
        data: staticData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 35
                }
            }
        }
    });
}
// Pie Chart - Score Distribution by Skill (REAL DATA)
async function initializePieChart() {
    try {
        const response = await fetch('../api/get_skill_distribution.php');
        const data = await response.json();
        
        if (data.success) {
            createPieChartWithData(data.data);
        } else {
            console.error('API error:', data.message);
            createPieChartWithStaticData();
        }
    } catch (error) {
        console.error('Error loading skill distribution:', error);
        createPieChartWithStaticData();
    }
}

function createPieChartWithData(skillData) {
    const pieCtx = document.getElementById('pieChart');
    if (!pieCtx) return;
    
    // Destroy existing chart if it exists
    if (window.pieChart instanceof Chart) {
        window.pieChart.destroy();
    }
    
    // Prepare data for chart
    const labels = skillData.map(skill => `${skill.skill} (${skill.percentage}%)`);
    const scores = skillData.map(skill => skill.average_score);
    const backgroundColors = [
        '#3b82f6', '#10b981', '#6366f1', '#f97316', '#e879f9', '#facc15',
        '#84cc16', '#ef4444', '#8b5cf6', '#06b6d4'
    ];
    
    window.pieChart = new Chart(pieCtx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: scores,
                backgroundColor: backgroundColors.slice(0, skillData.length),
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    align: 'center',
                    labels: {
                        boxWidth: 14,
                        padding: 10,
                        font: { 
                            size: 12,
                            family: 'Inter, sans-serif'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const skill = skillData[context.dataIndex];
                            return [
                                `Skill: ${skill.skill}`,
                                `Average Score: ${skill.average_score}%`,
                                `Based on ${skill.test_count} tests`
                            ];
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
}

// Keep static fallback for errors
function createPieChartWithStaticData() {
    const pieCtx = document.getElementById('pieChart');
    if (!pieCtx) return;
    
    if (window.pieChart instanceof Chart) {
        window.pieChart.destroy();
    }
    
    window.pieChart = new Chart(pieCtx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: [
                'Logical Reasoning (22%)',
                'Problem Solving (20%)',
                'Technical Aptitude (18%)',
                'Communication (15%)',
                'Creativity (12%)',
                'Attention to Detail (13%)'
            ],
            datasets: [{
                data: [22, 20, 18, 15, 12, 13],
                backgroundColor: [
                    '#3b82f6', '#10b981', '#6366f1',
                    '#f97316', '#e879f9', '#facc15'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    align: 'center',
                    labels: {
                        boxWidth: 14,
                        padding: 10,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}
// Line Chart - Test Completion Rate
async function initializeLineChart() {
    try {
        const response = await fetch('../api/get_test_completion_stats.php?period=daily');
        const data = await response.json();
        
        if (data.success) {
            createLineChartWithData(data.data);
        } else {
            console.error('API error:', data.message);
            createLineChartWithStaticData('daily');
        }
    } catch (error) {
        console.error('Error loading completion stats:', error);
        createLineChartWithStaticData('daily');
    }
}

function createLineChartWithData(chartData) {
    const lineCtx = document.getElementById('lineChart');
    if (!lineCtx) return;
    
    if (window.lineChart instanceof Chart) {
        window.lineChart.destroy();
    }
    
    window.lineChart = new Chart(lineCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: chartData.timeSeries.labels,
            datasets: [
                {
                    label: 'Completed Tests',
                    data: chartData.timeSeries.completed,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Not Yet Started',
                    data: chartData.timeSeries.notStarted,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.2)',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Users'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const datasetIndex = context.datasetIndex;
                            const dataIndex = context.dataIndex;
                            if (datasetIndex === 0) {
                                return `Completed tests on this date`;
                            } else {
                                return `Users who haven't taken the test yet`;
                            }
                        }
                    }
                }
            }
        }
    });
}

async function updateLineChartByPeriod(period) {
    try {
        const response = await fetch(`../api/get_test_completion_stats.php?period=${period}`);
        const data = await response.json();
        
        if (data.success) {
            if (window.lineChart) {
                window.lineChart.data.labels = data.data.timeSeries.labels;
                window.lineChart.data.datasets[0].data = data.data.timeSeries.completed;
                window.lineChart.data.datasets[1].data = data.data.timeSeries.notStarted;
                window.lineChart.update();
            } else {
                createLineChartWithData(data.data);
            }
        } else {
            console.error('API error:', data.message);
            createLineChartWithStaticData(period);
        }
    } catch (error) {
        console.error('Error updating completion stats:', error);
        createLineChartWithStaticData(period);
    }
}

// Keep your static fallback data function
function createLineChartWithStaticData(period) {
    const lineCtx = document.getElementById('lineChart');
    if (!lineCtx) return;
    
    if (window.lineChart instanceof Chart) {
        window.lineChart.destroy();
    }
    
    const completionData = {
        daily: {
            labels: ['May 1', 'May 2', 'May 3', 'May 4', 'May 5', 'May 6', 'May 7', 'May 8', 'May 9', 'May 10', 'May 11', 'May 12', 'May 13', 'May 14'],
            completed: [110, 100, 120, 115, 80, 60, 100, 130, 135, 120, 110, 120, 130, 135],
            notStarted: [50, 60, 40, 45, 90, 110, 80, 35, 30, 45, 55, 45, 35, 30]
        },
        weekly: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            completed: [350, 420, 470, 510],
            notStarted: [100, 90, 80, 70]
        },
        monthly: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            completed: [1000, 1200, 1300, 1450, 1550],
            notStarted: [300, 250, 200, 150, 120]
        }
    };

    const data = completionData[period] || completionData.daily;
    
    window.lineChart = new Chart(lineCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Completed Tests',
                    data: data.completed,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Not Yet Started',
                    data: data.notStarted,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.2)',
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function generateColors(count) {
    const baseColors = [
        'rgba(59, 130, 246, 0.8)',  
        'rgba(16, 185, 129, 0.8)', 
        'rgba(139, 92, 246, 0.8)',  
        'rgba(245, 158, 11, 0.8)',   
        'rgba(239, 68, 68, 0.8)',   
        'rgba(14, 165, 233, 0.8)',  
        'rgba(20, 184, 166, 0.8)',   
        'rgba(168, 85, 247, 0.8)',  
        'rgba(249, 115, 22, 0.8)',  
        'rgba(236, 72, 153, 0.8)'    
    ];
    
    // If we need more colors than available, cycle through them
    const colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
    }
    
    return colors;
}

// --- START: Added generateInitials Helper ---
/**
 * Generates 1-2 letter initials from a full name.
 * @param {string} name - The full name.
 * @returns {string} The uppercase initials.
 */
function generateInitials(name) {
    if (!name) return '??';
    const nameParts = name.trim().split(' ');
    let initials = nameParts[0] ? nameParts[0].charAt(0) : '';
    if (nameParts.length > 1) {
        initials += nameParts[nameParts.length - 1] ? nameParts[nameParts.length - 1].charAt(0) : '';
    }
    return initials.toUpperCase();
}
// --- END: Added generateInitials Helper ---


// SIDEBAR & LOGOUT FUNCTIONALITY
function initializeSidebar() {
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');

    // Sidebar toggle
    burger.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        burger.classList.toggle('active');
    });

    // Universal logout modal open handler
    function openLogoutModal(e) {
        e.preventDefault();
        logoutModal.style.display = 'flex';
    }

    // Support both logout buttons
    const logoutBtnSidebar = document.getElementById('logoutBtn');
    const logoutBtnDropdown = document.getElementById('logoutDropdownBtn');

    logoutBtnSidebar?.addEventListener('click', openLogoutModal);
    logoutBtnDropdown?.addEventListener('click', openLogoutModal);

    // Cancel logout
    cancelLogout.addEventListener('click', function () {
        logoutModal.style.display = 'none';
    });

    // Close modal on ESC
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            logoutModal.style.display = 'none';
            document.getElementById('user-dropdown').style.display = 'none';
        }
    });
}


// TEST TAKERS TABLE FUNCTIONALITY 


let allRows = [];
let filteredRows = [];
let currentPage = 1;
let rowsPerPage = 5;

// Initialize table functionality
function initializeTable() {
    const searchInput = document.getElementById('testTakerSearch');
    const resultFilter = document.getElementById('jftResultFilter');
    const dateSortOrder = document.getElementById('jftDateSortOrder');
    const rowsPerPageSelect = document.getElementById('testTakerRowsPerPage');
    
    // Set initial rows per page
    rowsPerPage = parseInt(rowsPerPageSelect.value);
    
    // Event listeners
    searchInput.addEventListener('input', applyFilters);
    resultFilter.addEventListener('change', applyFilters);
    dateSortOrder.addEventListener('change', applyFilters);
    
    rowsPerPageSelect.addEventListener('change', () => {
        rowsPerPage = parseInt(rowsPerPageSelect.value);
        currentPage = 1;
        applyFilters();
    });
    
    document.getElementById('testTakerPrevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('testTakerNextPage').addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
}

// Function to load test takers table with real data
async function loadTestTakersTable() {
    try {
        const response = await fetch('../api/get_test_takers.php');
        const data = await response.json();
        
        if (data.success) {
            populateTestTakersTable(data.data);
            allRows = Array.from(document.querySelectorAll('#testTakerTable tbody tr'));
            filteredRows = [...allRows];
            applyFilters();
            initializeTable(); 
        } else {
            console.error('API error:', data.message);
            // Show empty state
            const tbody = document.querySelector('#testTakerTable tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                        No test takers found
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading test takers:', error);
        // Show error state
        const tbody = document.querySelector('#testTakerTable tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                    Error loading test takers. Please try again.
                </td>
            </tr>
        `;
    }
}

// Function to populate test takers table 
function populateTestTakersTable(testTakers) {
    const tbody = document.querySelector('#testTakerTable tbody');
    tbody.innerHTML = '';
    
    if (!testTakers || testTakers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                    No test takers found
                </td>
            </tr>
        `;
        return;
    }
    
    testTakers.forEach(taker => {
        const nameParts = taker.name.split(' ');
        const initials = nameParts.map(n => n.charAt(0)).join('').toUpperCase();
        const row = document.createElement('tr');
        
        // Set data attributes for filtering
        row.setAttribute('data-userid', taker.userid);
        row.setAttribute('data-username', taker.name.toLowerCase());
        row.setAttribute('data-useremail', taker.email.toLowerCase());
        row.setAttribute('data-jftresult', taker.match_percentage);
        row.setAttribute('data-testdate', taker.test_date);
        
        row.innerHTML = `
            <td>${taker.userid}</td>
            <td class="user-cell">
                <div class='avatar-circle-small' style='background-color: #2f80ed;'>${initials}</div>
                <span>${taker.name}</span>
            </td>
            <td>${taker.email}</td>
            <td>${formatDate(taker.test_date)}</td>
            <td>${taker.match_percentage}% ${taker.recommended_role || 'No role'}</td>
            <td class="actions-cell">
                <i class="fas fa-eye action-icon view-result" data-userid="${taker.userid}" title="View Details"></i>
                <i class="fas fa-trash-alt action-icon delete-result" data-userid="${taker.userid}" title="Delete Test Taker"></i>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function applyFilters() {
    const searchTerm = document.getElementById('testTakerSearch').value.toLowerCase();
    const resultValue = document.getElementById('jftResultFilter').value;
    const sortOrder = document.getElementById('jftDateSortOrder').value;

    console.log('Applying filters:', { searchTerm, resultValue, sortOrder, allRowsCount: allRows.length });

    // If no rows are loaded yet, try to get them
    if (allRows.length === 0) {
        allRows = Array.from(document.querySelectorAll('#testTakerTable tbody tr'));
        filteredRows = [...allRows];
    }

    filteredRows = allRows.filter(row => {
        const userName = row.dataset.username || '';
        const userEmail = row.dataset.useremail || '';
        const jftResult = parseFloat(row.dataset.jftresult) || 0;

        console.log('Filtering row:', { userName, userEmail, jftResult });

        // Search by Name & Email
        const matchesSearch = searchTerm === '' || 
            userName.includes(searchTerm) ||
            userEmail.includes(searchTerm);

        // Filter by Result Status
        let matchesResult = true;
        if (resultValue === 'Pass') {
            matchesResult = jftResult >= 75;
        } else if (resultValue === 'Fail') {
            matchesResult = jftResult < 75;
        }

        return matchesSearch && matchesResult;
    });

    console.log('After filtering:', filteredRows.length, 'rows');

    // Sort by Date if selected
    if (sortOrder !== 'default') {
        filteredRows.sort((a, b) => {
            const dateA = new Date(a.dataset.testdate);
            const dateB = new Date(b.dataset.testdate);
            if (sortOrder === 'newest') return dateB - dateA;
            if (sortOrder === 'oldest') return dateA - dateB;
            return 0;
        });
    }

    currentPage = 1;
    renderTable();
}

function renderTable() {
    const tbody = document.querySelector('#testTakerTable tbody');
    const paginationInfo = document.getElementById('testTakerPaginationInfo');
    const pageNumbersContainer = document.getElementById('testTakerPageNumbers');
    const prevPageBtn = document.getElementById('testTakerPrevPage');
    const nextPageBtn = document.getElementById('testTakerNextPage');

    // Clear existing rows
    tbody.innerHTML = '';

    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    console.log('Rendering table:', { totalRows, totalPages, currentPage, rowsPerPage });

    if (totalRows === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                    No test takers found matching your criteria
                </td>
            </tr>
        `;
        paginationInfo.textContent = `Showing 0 to 0 of 0 test takers`;
        pageNumbersContainer.innerHTML = '';
        prevPageBtn.disabled = true;
        nextPageBtn.disabled = true;
        return;
    }

    // Calculate pagination
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
    const rowsToShow = filteredRows.slice(startIndex, endIndex);

    console.log('Showing rows:', startIndex + 1, 'to', endIndex);

    // Add rows to table (clone them to avoid reference issues)
    rowsToShow.forEach(row => {
        tbody.appendChild(row.cloneNode(true));
    });

    // Update pagination info
    paginationInfo.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} test takers`;

    // Update page numbers
    pageNumbersContainer.innerHTML = '';
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    // Adjust if we're near the end
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    // Previous page button for multiple pages
    if (startPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '&laquo;';
        prevBtn.classList.add('page-number');
        prevBtn.addEventListener('click', () => {
            currentPage = startPage - 1;
            renderTable();
        });
        pageNumbersContainer.appendChild(prevBtn);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.classList.add('page-number');
        if (i === currentPage) btn.classList.add('active');
        btn.addEventListener('click', () => {
            currentPage = i;
            renderTable();
        });
        pageNumbersContainer.appendChild(btn);
    }

    // Next page button for multiple pages
    if (endPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '&raquo;';
        nextBtn.classList.add('page-number');
        nextBtn.addEventListener('click', () => {
            currentPage = endPage + 1;
            renderTable();
        });
        pageNumbersContainer.appendChild(nextBtn);
    }

    // Update button states
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages;
}

function toggleDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    // Click outside to close
    document.addEventListener('click', function (event) {
        if (
            !event.target.closest('.user-profile') &&
            !event.target.closest('#user-dropdown')
        ) {
            dropdown.style.display = 'none';
        }
    }, { once: true });
}

// Function to fetch and display user test details
async function loadUserTestDetails(userId) {
    try {
        const response = await fetch(`../api/get_user_test_details.php?user_id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            populateUserModal(data.data);
        } else {
            alert('Error loading user details: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading user test details:', error);
        alert('Error loading user details');
    }
}

// Function to populate modal with real data
function populateUserModal(userData) {
    const userInfo = userData.user_info;
    const latestTest = userData.latest_test;
    const testHistory = userData.test_history;
    
    // Generate avatar initials
    const initials = (userInfo.firstname.charAt(0) + userInfo.lastname.charAt(0)).toUpperCase();
    document.getElementById('jobtakersProfileAvatar').textContent = initials;
    
    // Populate basic information
    document.getElementById('jobtakersnProfileFullName').textContent = `${userInfo.firstname} ${userInfo.lastname}`;
    document.getElementById('jobtakersnProfileEmailSummary').textContent = userInfo.email;
    document.getElementById('viewUserId').value = userInfo.userid;
    document.getElementById('viewUserFullName').value = `${userInfo.firstname} ${userInfo.lastname}`;
    document.getElementById('viewUserEmail').value = userInfo.email;
    document.getElementById('viewdatetaken').value = userInfo.registered_date;
    
    // Populate latest test results
    if (latestTest.recommended_role) {
        document.getElementById('rolematch').value = latestTest.recommended_role;
        document.getElementById('rolepercentage').value = `${latestTest.match_percentage}%`;
        document.getElementById('roledatetaken').value = formatDate(latestTest.test_date);
    } else {
        document.getElementById('rolematch').value = 'No test taken';
        document.getElementById('rolepercentage').value = 'N/A';
        document.getElementById('roledatetaken').value = 'N/A';
    }
    
    // Populate test history table
    populateTestHistoryTable(testHistory);
}

// Function to populate test history table with only top recommended job for each test
function populateTestHistoryTable(testHistory) {
    const tbody = document.querySelector('.history-table tbody');
    tbody.innerHTML = ''; 
    
    if (!testHistory || testHistory.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 15px; color: #666;">
                    No test history available
                </td>
            </tr>
        `;
        return;
    }
    
    testHistory.forEach(test => {
        // Check if recommended_roles exists and is an array
        if (!test.recommended_roles || !Array.isArray(test.recommended_roles)) {
            console.warn('Invalid recommended_roles for test:', test);
            return;
        }
        
        // Find the top recommended role (first one with valid role data)
        const topRole = test.recommended_roles.find(roleData => 
            roleData && roleData.role && roleData.role !== '0'
        );
        
        if (topRole) {
            try {
                const matchPercentage = calculateMatchPercentage(topRole.confidence, 0); 
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td><input type="text" value="${topRole.role}" readonly></td>
                    <td><input type="text" value="${formatDate(test.test_date)}" readonly></td>
                    <td><input type="text" value="${matchPercentage}%" readonly></td>
                `;
                
                tbody.appendChild(row);
            } catch (error) {
                console.error('Error calculating match percentage:', error, topRole);
            }
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function calculateMatchPercentage(probability, rank) {
    const baseScore = probability * 100;
    
    let matchPercentage;
    
    if (rank === 0) {
        matchPercentage = 60 + (baseScore * 0.6);
    } else if (rank === 1) {
        matchPercentage = 55 + (baseScore * 0.5);
    } else if (rank === 2) {
        matchPercentage = 50 + (baseScore * 0.45);
    } else if (rank === 3) {
        matchPercentage = 45 + (baseScore * 0.4);
    } else {
        matchPercentage = 40 + (baseScore * 0.35);
    }
    
    matchPercentage = Math.max(40, Math.min(95, matchPercentage));
    return Math.round(matchPercentage);
}

// Delete user modal functionality
const deleteModal = document.getElementById('deleteUserModal');
const cancelDeleteBtn = document.getElementById('cancelDeleteUser');
const confirmDeleteBtn = document.getElementById('confirmDeleteUser');
const deleteUserNameSpan = document.getElementById('deleteUserName');

let selectedUserId = null;
let selectedUserRow = null;

// Open modal when clicking delete icon
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-result')) {
        const row = e.target.closest('tr');
        selectedUserId = row.dataset.userid;
        selectedUserRow = row;
        const userName = row.dataset.username || 'this user';
        
        deleteUserNameSpan.textContent = userName;
        deleteModal.style.display = 'flex';
    }
});

// Close modal on cancel
cancelDeleteBtn.addEventListener('click', function () {
    deleteModal.style.display = 'none';
    selectedUserId = null;
    selectedUserRow = null;
});

// Handle delete confirmation
confirmDeleteBtn.addEventListener('click', async function () {
    if (selectedUserId) {
        try {
            // Show loading state
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            const response = await fetch('../api/delete_test_taker.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: selectedUserId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove the row from the table
                if (selectedUserRow) {
                    selectedUserRow.remove();
                }
                
                // Show success message
                showNotification('Test taker deleted successfully', 'success');
                
                // Update the table display
                applyFilters();
                
                // Refresh charts to update counts
                renderBarChart();
                initializeLineChart();
                
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
            
        } catch (error) {
            console.error('Error deleting user:', error);
            showNotification('Error deleting test taker. Please try again.', 'error');
        } finally {
            // Reset button state
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = 'Delete User';
            
            // Close modal
            deleteModal.style.display = 'none';
            selectedUserId = null;
            selectedUserRow = null;
        }
    }
});

// Helper function to show notifications
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles for notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }
`;
document.head.appendChild(style);
</script>


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

</script>

</body>
</html>