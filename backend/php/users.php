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
  <link rel="stylesheet" href="../css/users.css">

</head>
<?php
include 'database.php'; 
include 'phpusers.php';
include 'my_admin_profile_modal.php';
?>


<body>

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
    <a href="analytics.php">
      <i class="fas fa-chart-bar"></i> Test Analytics
    </a>
    <a class="active" aria-current="page">
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
        <div class="admin-label">Homepage > <span style="font-weight: bold;">Users</span></div>
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
  <div class="overview-header-left">
    <h2>User Management</h2>
    <p>Manage system users and their access permission</p>
  </div>
  <button class="report-button" id="generateReportBtn">
    <i class="fas fa-file-alt"></i> Generate Report
  </button>
</div>

    <div class="users-section">
      <div class="bordered-section">
        <div class="section-header">
          <div>
            <h3>Admins</h3>
            <p>A list of all Admins in the system including their name, email, role and status.</p>
          </div>
        <button class="add-button" id="addAdminBtn"><i class="fas fa-plus"></i> Add Admin</button>

        </div>
        <div class="search-filter-controls">
            <div class="search-box">
                <input type="text" class="search-input" id="adminSearch" placeholder="Search admin users...">
            </div>
            <div class="filters">
                <select class="filter-select" id="adminStatusFilter">
                    <option value="">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <select class="filter-select" id="adminSortOrder">
                    <option value="default">Default Order</option>
                    <option value="asc">Name (A-Z)</option>
                    <option value="desc">Name (Z-A)</option>
                </select>
            </div>
        </div>
        <div class="table-container">
          <table id="adminTable">
            <thead>
              <tr>
                <th>ID</th> <th>NAME</th>
                <th>EMAIL</th>
                <th>ROLE</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php

              $adminResult = mysqli_query($con, "SELECT adminid, adminname, adminemail, adminimage, adminstatus FROM admin");
              if ($adminResult && mysqli_num_rows($adminResult) > 0) {
                  while ($admin = mysqli_fetch_assoc($adminResult)) {
                      echo "<tr data-adminid='" . $admin['adminid'] . "' data-adminname='" . htmlspecialchars($admin['adminname']) . "' data-adminemail='" . htmlspecialchars($admin['adminemail']) . "' data-adminstatus='" . htmlspecialchars($admin['adminstatus']) . "'>";
                      echo "<td>" . htmlspecialchars($admin['adminid']) . "</td>"; 
                      echo "<td><div class='user-cell'>";
              
                      if (!empty($admin['adminimage'])) {
                          $adminProfileImage = 'data:image/jpeg;base64,' . base64_encode($admin['adminimage']);
                          echo "<img src='" . $adminProfileImage . "' alt='Admin Avatar' class='avatar-circle'>";
                      } else {
                          echo "<div class='avatar-circle' style='background-color: #6a1b9a;'>" . strtoupper(substr($admin['adminname'], 0, 1)) . "</div>";
                      }
                      echo htmlspecialchars($admin['adminname']);
                      echo "</div></td>";
                      echo "<td>" . htmlspecialchars($admin['adminemail']) . "</td>";
                      echo "<td><span class='role-tag admin'>Admin</span></td>";
                      echo "<td><span class='status-tag " . (strtolower($admin['adminstatus']) === 'active' ? 'active' : 'inactive') . "'>" . htmlspecialchars($admin['adminstatus']) . "</span></td>";
                      echo "<td class='actions-cell'>";
                      echo "<i class='fas fa-eye action-icon view-admin' data-id='" . $admin['adminid'] . "'></i>";
                      echo "<div class='action-dropdown-wrapper'>"; 
                      echo "<i class='fas fa-pen-to-square action-icon edit-admin' data-id='" . $admin['adminid'] . "'></i>";
                      echo "<div class='action-dropdown'>";
                      echo "<a href='#' class='dropdown-item edit-admin-info' data-id='" . $admin['adminid'] . "'>Edit Info</a>";
                      echo "<div class='dropdown-divider'></div>";
                      if (strtolower($admin['adminstatus']) === 'active') {
                          echo "<a href='#' class='dropdown-item deactivate-admin' data-id='" . $admin['adminid'] . "' data-status='Inactive' style='color: #ef4444;'>Deactivate</a>";
                      } else {
                          echo "<a href='#' class='dropdown-item activate-admin' data-id='" . $admin['adminid'] . "' data-status='Active' style='color: #22c55e;'>Activate</a>";
                      }
                      echo "</div>"; 
                      echo "</div>"; 
                      echo "<i class='fas fa-trash-alt action-icon delete-admin' data-id='" . $admin['adminid'] . "'></i>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='6' style='text-align: center;'>No Admin Found</td></tr>"; }
              ?>
            </tbody>
          </table>
        </div>
        <div class="pagination-controls">
            <div class="pagination-info" id="adminPaginationInfo">Showing 0 to 0 of 0 users</div>

            <div class="pagination-buttons">
                <button class="pagination-button" id="adminPrevPage"><i class="fas fa-chevron-left"></i></button>
                <div class="page-numbers" id="adminPageNumbers">
                    </div>
                <button class="pagination-button" id="adminNextPage"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="pagination-rows-per-page">
                <select id="adminRowsPerPage" class="rows-per-page-select">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                </select>
            </div>
        </div>
      </div>

      <div class="bordered-section">
        <div class="section-header">
          <div>
            <h3>System Users</h3>
            <p>A list of all registered users in the system including their name, email, role and status.</p>
          </div>
          
        </div>
        <div class="search-filter-controls">
            <div class="search-box">
                <input type="text" class="search-input" id="userSearch" placeholder="Search system users...">
            </div>
            <div class="filters">
                <select class="filter-select" id="userStatusFilter">
                    <option value="">Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <select class="filter-select" id="userSortOrder">
                    <option value="default">Default Order</option>
                    <option value="asc">Name (A-Z)</option>
                    <option value="desc">Name (Z-A)</option>
                </select>
            </div>
        </div>
        <div class="table-container">
          <table id="userTable">
            <thead>
              <tr>
                <th>ID</th> <th>NAME</th>
                <th>EMAIL</th>
                <th>ROLE</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
             
              $userResult = mysqli_query($con, "SELECT userid, firstname, lastname, email, image, status FROM users");
              if ($userResult && mysqli_num_rows($userResult) > 0) {
                  while ($user = mysqli_fetch_assoc($userResult)) {
                      echo "<tr data-userid='" . $user['userid'] . "' data-username='" . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . "' data-useremail='" . htmlspecialchars($user['email']) . "' data-userstatus='" . htmlspecialchars($user['status']) . "'>";
                      echo "<td>" . htmlspecialchars($user['userid']) . "</td>"; 
                      echo "<td><div class='user-cell'>";
                      if (!empty($user['image'])) {
                          $userProfileImage = 'data:image/jpeg;base64,' . base64_encode($user['image']);
                          echo "<img src='" . $userProfileImage . "' alt='User Avatar' class='avatar-circle-small'>";
                      } else {
                          echo "<div class='avatar-circle-small' style='background-color: #2f80ed;'>";
                          echo strtoupper(substr($user['firstname'], 0, 1));
                          echo "</div>";
                      }
                      echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']);
                      echo "</div></td>";
                      echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                      echo "<td><span class='role-tag user'>User</span></td>";
                      echo "<td><span class='status-tag " . (strtolower($user['status']) === 'active' ? 'active' : 'inactive') . "'>" . htmlspecialchars($user['status']) . "</span></td>";
                      echo "<td class='actions-cell'>";
                      echo "<i class='fas fa-eye action-icon view-user' data-id='" . $user['userid'] . "'></i>";
                      echo "<div class='action-dropdown-wrapper'>";
                      echo "<i class='fas fa-pen-to-square action-icon edit-user' data-id='" . $user['userid'] . "'></i>";
                      echo "<div class='action-dropdown'>";
                      if (strtolower($user['status']) === 'active') {
                          echo "<a href='#' class='dropdown-item deactivate-user' data-id='" . $user['userid'] . "' data-status='Inactive' style='color: #ef4444;'>Deactivate</a>";
                      } else {
                          echo "<a href='#' class='dropdown-item activate-user' data-id='" . $user['userid'] . "' data-status='Active' style='color: #22c55e;'>Activate</a>";
                      }
                      echo "</div>";
                      echo "</div>";
                      echo "<i class='fas fa-trash-alt action-icon delete-user' data-id='" . $user['userid'] . "'></i>";
                      echo "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='6' style='text-align: center;'>No User Found</td></tr>"; }
              ?>
            </tbody>
          </table>
        </div>
        <div class="pagination-controls">
            <div class="pagination-info" id="userPaginationInfo">Showing 0 to 0 of 0 users</div>

            <div class="pagination-buttons">
                <button class="pagination-button" id="userPrevPage"><i class="fas fa-chevron-left"></i></button>
                <div class="page-numbers" id="userPageNumbers">
                    </div>
                <button class="pagination-button" id="userNextPage"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="pagination-rows-per-page">
                <select id="userRowsPerPage" class="rows-per-page-select">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                </select>
            </div>
        </div>
      </div>
    </div>
  </section>
</main>


<div class="add-admin-modal modal" id="addAdminModal">
    <form class="add-admin-modal-content" id="addAdminForm"> <span class="close-btn" id="closeAddAdminModal">&times;</span>
        <h2>Add New Admin</h2>

        <div class="form-group">
            <label for="adminName">Name</label>
            <input type="text" name="adminName" id="adminName" required />
        </div>

        <div class="form-group">
            <label for="adminEmail">Email</label>
            <input type="email" name="adminEmail" id="adminEmail" required />
        </div>

        <div class="form-group">
            <label for="adminPassword">Password</label>
            <input type="password" id="signup-password" name="adminPassword" placeholder="Enter secure password" required />
            <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="signup-password">
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="defaultPasswordCheckbox">
            <label for="defaultPasswordCheckbox" class="default-password-label">Use Default Password</label>
        </div>

      <div class="strength-bar">
          <div class="strength-bar-inner" id="strength-bar"></div>
      </div>

      <div class="password-criteria">
          <div class="criteria-grid">
              <div class="criteria-item" id="length"><i class="fas fa-circle"></i> - 8+ characters</div>
              <div class="criteria-item" id="upper"><i class="fas fa-circle"></i> - Uppercase letter</div>
              <div class="criteria-item" id="lower"><i class="fas fa-circle"></i> - Lowercase letter</div>
              <div class="criteria-item" id="number"><i class="fas fa-circle"></i> - Number</div>
              <div class="criteria-item" id="special"><i class="fas fa-circle"></i> - Special character</div>
          </div><br>
      </div>


        <div class="form-group">
            <label for="adminRole">Role</label>
            <input type="text" name="adminRole" id="adminRole" value="Admin" readonly style="cursor: not-allowed; background-color: #f3f4f6;" />
        </div>

        <div class="form-actions">
            <button type="button" class="cancel-btn" id="cancelAddAdmin">Cancel</button>
            <button type="submit" name="submitAddAdmin" class="add-btn" id="submitAddAdminBtn">Add Admin</button>
        </div>
    </form>
</div>

</div>

<div class="modal add-admin-modal" id="editAdminModal">
    <form class="add-admin-modal-content" id="editAdminForm"> <span class="close-btn" id="closeEditAdminModal">&times;</span>
        <h2>Edit Admin Information</h2>
        <input type="hidden" name="editAdminId" id="editAdminId">

        <div class="form-group">
            <label for="editAdminName">Name</label>
            <input type="text" name="editAdminName" id="editAdminName" required />
        </div>

        <div class="form-group">
            <label for="editAdminEmail">Email (Cannot be changed)</label>
            <input type="email" name="editAdminEmail" id="editAdminEmail" readonly/>
        </div>

        <div class="form-group">
          <label for="editAdminPassword">New Password (Leave blank to keep current)</label>
          <input type="password" id="edit-admin-password" name="editAdminPassword" placeholder="Enter new password (optional)" />
          <img src="../image/eyeoff.png" alt="toggle" class="toggle-password" data-target="edit-admin-password" style="top: 53%">
        </div>

        <div class="strength-bar">
            <div class="strength-bar-inner" id="edit-strength-bar"></div>
        </div>

        <div class="password-criteria">
            <div class="criteria-grid">
                <div class="criteria-item" id="edit-length"><i class="fas fa-circle"></i> - 8+ characters</div>
                <div class="criteria-item" id="edit-upper"><i class="fas fa-circle"></i> - Uppercase letter</div>
                <div class="criteria-item" id="edit-lower"><i class="fas fa-circle"></i> - Lowercase letter</div>
                <div class="criteria-item" id="edit-number"><i class="fas fa-circle"></i> - Number</div>
                <div class="criteria-item" id="edit-special"><i class="fas fa-circle"></i> - Special character</div>
            </div><br>
        </div>


        <div class="form-actions">
            <button type="button" class="cancel-btn" id="cancelEditAdmin">Cancel</button>
            <button type="submit" name="updateAdminInfo" class="add-btn" id="submitEditAdminBtn">Save Changes</button>
        </div>
    </form>
</div>


<div class="delete-user-modal" id="deleteAdminModal">
    <div class="delete-user-modal-content">
        <div class="delete-icon-container">
            <i class="fas fa-exclamation-triangle delete-warning-icon"></i>
        </div>
        <h2>Delete Admin</h2>
        <p class="delete-warning-text">This action cannot be undone.</p>
        <p class="delete-confirmation-text">Are you sure you want to delete <span id="deleteAdminName"></span>? This will permanently remove their account and all associated data.</p>
        <div class="modal-buttons">
            <button class="btn-cancel" id="cancelDeleteAdmin">Cancel</button>
            <button class="btn-delete" id="confirmDeleteAdmin">Delete Admin</button>
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

<div class="modal user-profile-modal" id="viewUserProfileModal">
    <div class="modal-content user-profile-content">
        <div class="modal-header">
            <h2>User Profile</h2>
            <span class="close-button" id="closeUserProfileModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="avatar-circle-large" id="profileAvatar">JS</div>
                <div class="profile-name" id="profileFullName">John Smith</div>
                <div class="profile-email" id="profileEmailSummary">john.smith@example.com</div>
                <p class="profile-status" id="profileStatus"></p>
            </div>
            <p class="null-field-instruction" id="nullFieldInstruction">
        <i class="fas fa-exclamation-circle"></i> Red highlights indicate fields not yet set by the user.
    </p>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user"></i></div>
                <div class="section-title">Personal Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label for="viewUserId">User ID</label>
                        <input type="text" id="viewUserId" readonly>
                    </div>
                    <div class="form-field">
                        <label>Full Name</label>
                        <input type="text" id="viewFullName" readonly>
                    </div>
                    <div class="form-field-group">
                        <div class="form-field">
                            <label>Date of Birth</label>
                            <input type="text" id="viewDOB" readonly>
                        </div>
                        <div class="form-field">
                            <label>Age (Automated)</label>
                            <input type="text" id="viewAge" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-envelope"></i></div>
                <div class="section-title">Contact Information</div>
                <div class="section-fields">
                    <div class="form-field-group">
                        <div class="form-field">
                            <label>Email Address</label>
                            <input type="email" id="viewEmail" readonly>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Phone Number</label>
                        <input type="text" id="viewContact" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="section-title">Educational Background</div>
                <div class="section-fields">

                    <div class="form-field">
                        <label for="viewEduLvl">Educational Level</label>
                        <input type="text" id="viewEduLvl" class="form-control" readonly>
                    </div>

                    <div class="form-field">
                        <label for="viewCourse">Course</label>
                        <input type="text" id="viewCourse" class="form-control" readonly>
                    </div>

                    <div class="form-field">
                        <label for="viewSchool">School</label>
                        <input type="text" id="viewSchool" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Joined Date</label>
                        <input type="text" id="viewSignupDate" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="profileModalCloseBtn">Close</button>
        </div>
    </div>
</div>

<div class="modal admin-profile-modal" id="viewAdminProfileModal">
    <div class="modal-content admin-profile-content">
        <div class="modal-header">
            <h2>Admin Profile</h2>
            <span class="close-button" id="closeAdminProfileModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-summary">
                <div class="avatar-circle-large" id="adminProfileAvatar">AD</div>
                <div class="profile-name" id="adminProfileFullName">Admin Doe</div>
                <div class="profile-email" id="adminProfileEmailSummary">admin.doe@example.com</div>
                <p class="profile-status" id="adminProfileStatus"></p>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-user"></i></div>
                <div class="section-title">Personal Information</div>
                <div class="section-fields">
                    <div class="form-field">
                         <label for="viewAdminId">Admin ID</label>
                         <input type="text" id="viewAdminId" readonly>
                    </div>
                    <div class="form-field">
                        <label>Admin Name</label>
                        <input type="text" id="viewAdminFullName" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-envelope"></i></div>
                <div class="section-title">Contact Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Email Address</label>
                        <input type="email" id="viewAdminEmail" readonly>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-icon"><i class="fas fa-info-circle"></i></div>
                <div class="section-title">System Information</div>
                <div class="section-fields">
                    <div class="form-field">
                        <label>Joined Date</label>
                        <input type="text" id="viewAdminJoinedAt" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" id="adminProfileModalCloseBtn">Close</button>
        </div>
    </div>
</div>

<div class="modal confirm-modal" id="addAdminConfirmModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-question-circle confirm-icon"></i>
        </div>
        <h2>Confirm Add Admin</h2>
        <p>Are you sure you want to add this new administrator?</p>
        <div class="modal-buttons">
            <button class="btn-no" id="cancelAddAdminConfirm">No</button>
            <button class="btn-yes" id="confirmAddAdmin">Yes</button>
        </div>
    </div>
</div>

<div class="modal confirm-modal" id="updateStatusConfirmModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-question-circle confirm-icon"></i>
        </div>
        <h2>Confirm Status Update</h2>
        <p id="updateStatusConfirmMessage">Are you sure you want to change the status?</p>
        <div class="modal-buttons">
            <button class="btn-no" id="cancelUpdateStatusConfirm">No</button>
            <button class="btn-yes" id="confirmUpdateStatus">Yes</button>
        </div>
    </div>
</div>

<div class="modal confirm-modal" id="updateAdminInfoConfirmModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon-container">
            <i class="fas fa-question-circle confirm-icon"></i>
        </div>
        <h2>Confirm Admin Info Update</h2>
        <p>Are you sure you want to save these changes to the admin's information?</p>
        <div class="modal-buttons">
            <button class="btn-no" id="cancelUpdateAdminInfoConfirm">No</button>
            <button class="btn-yes" id="confirmUpdateAdminInfo">Yes</button>
        </div>
    </div>
</div>


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
  window.open('usersreport.php', '_blank');
});
</script>


<script src="../js/users.js"></script>
</body>
</html>