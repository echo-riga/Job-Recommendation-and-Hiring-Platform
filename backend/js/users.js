document.addEventListener('DOMContentLoaded', function () {
    // --- Sidebar and Dropdown Toggles ---
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');
    const userDropdown = document.getElementById('user-dropdown');
    const userProfile = document.querySelector('.user-profile');

    burger.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        burger.classList.toggle('active');
    });

    userProfile.addEventListener('click', function (event) {
        event.stopPropagation(); // Prevent click from immediately closing the dropdown
        userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!userProfile.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.style.display = 'none';
        }
    });

    // --- Logout Modal Management ---
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogout');
    const logoutBtnSidebar = document.getElementById('logoutBtn');
    const logoutBtnDropdown = document.getElementById('logoutDropdownBtn');

    function openLogoutModal(e) {
        e.preventDefault();
        logoutModal.style.display = 'flex';
    }
    logoutBtnSidebar?.addEventListener('click', openLogoutModal);
    logoutBtnDropdown?.addEventListener('click', openLogoutModal);

    cancelLogoutBtn.addEventListener('click', function () {
        logoutModal.style.display = 'none';
    });

    // --- Add Admin Modal Management ---
    const addAdminModal = document.getElementById('addAdminModal');
    const addAdminBtn = document.getElementById('addAdminBtn');
    const closeAddAdminModal = document.getElementById('closeAddAdminModal');
    const cancelAddAdminBtn = document.getElementById('cancelAddAdmin');
    const addAdminForm = document.getElementById('addAdminForm'); // Get the form
    const submitAddAdminBtn = document.getElementById('submitAddAdminBtn'); // Get the submit button

    // Get the default password checkbox and the password input
    const defaultPasswordCheckbox = document.getElementById('defaultPasswordCheckbox');
    const addAdminPasswordInput = document.getElementById('signup-password');

    addAdminBtn.addEventListener('click', function () {
        addAdminModal.style.display = 'flex';
        document.getElementById('adminName').value = '';
        document.getElementById('adminEmail').value = '';
        addAdminPasswordInput.value = ''; // Ensure password field is cleared
        resetPasswordStrength();
        // Reset default password checkbox and enable password input on modal open
        defaultPasswordCheckbox.checked = false;
        addAdminPasswordInput.disabled = false;
        addAdminPasswordInput.required = true; // Ensure it's required by default
    });

    closeAddAdminModal.addEventListener('click', function () {
        addAdminModal.style.display = 'none';
    });
    cancelAddAdminBtn.addEventListener('click', function () {
        addAdminModal.style.display = 'none';
    });

    // Add Admin Confirmation Modal
    const addAdminConfirmModal = document.getElementById('addAdminConfirmModal');
    const cancelAddAdminConfirmBtn = document.getElementById('cancelAddAdminConfirm');
    const confirmAddAdminBtn = document.getElementById('confirmAddAdmin');

    submitAddAdminBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default form submission
        // Validate form fields first
        // If the default password is used, the password input itself is disabled,
        // so we only check validity if it's NOT disabled.
        if (addAdminForm.checkValidity() && (!addAdminPasswordInput.disabled || defaultPasswordCheckbox.checked)) {
            addAdminConfirmModal.style.display = 'flex';
            addAdminModal.style.display = 'none'; // Hide the add admin form temporarily
        } else {
            addAdminForm.reportValidity(); // Show browser's validation messages
        }
    });

    cancelAddAdminConfirmBtn.addEventListener('click', function() {
        addAdminConfirmModal.style.display = 'none';
        addAdminModal.style.display = 'flex';
    });

    confirmAddAdminBtn.addEventListener('click', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_admin.php';
        form.style.display = 'none';

        const formData = new FormData(addAdminForm);
        // Important: If password input is disabled, its value won't be in formData.
        // Manually add it if the checkbox is checked.
        if (defaultPasswordCheckbox.checked) {
            formData.set('adminPassword', addAdminPasswordInput.value);
        }

        for (let pair of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            form.appendChild(input);
        }

        // Add a hidden input to signify confirmation
        const confirmedInput = document.createElement('input');
        confirmedInput.type = 'hidden';
        confirmedInput.name = 'submitAddAdmin';
        confirmedInput.value = '1';
        form.appendChild(confirmedInput);

        document.body.appendChild(form);
        form.submit();
    });

    // --- Password Strength Checker (for Add Admin Modal) ---
    const passwordInput = document.getElementById('signup-password');
    const strengthBar = document.getElementById('strength-bar');
    const criteria = ["length", "upper", "lower", "number", "special"];

    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const targetId = icon.getAttribute('data-target');
            if (!targetId) return;
            const target = document.getElementById(targetId);
            const isHidden = target.type === 'password';

            target.type = isHidden ? 'text' : 'password';
            icon.src = isHidden ? '../image/eyeon.png' : '../image/eyeoff.png';
        });
    });

    passwordInput.addEventListener('input', () => {
        // Only run strength check if the default password checkbox is NOT checked
        if (defaultPasswordCheckbox.checked) {
            resetPasswordStrength(); // Ensure strength bar is reset if user types after checking
            return;
        }

        const val = passwordInput.value;
        let passed = 0;

        const checks = {
            length: val.length >= 8,
            upper: /[A-Z]/.test(val),
            lower: /[a-z]/.test(val),
            number: /[0-9]/.test(val),
            special: /[^A-Za-z0-9]/.test(val)
        };

        criteria.forEach(key => {
            const item = document.getElementById(key);
            const icon = item.querySelector('i');
            if (checks[key]) {
                item.classList.add('valid');
                item.classList.remove('invalid');
                icon.className = 'fas fa-check-circle';
                passed++;
            } else {
                item.classList.remove('valid');
                item.classList.add('invalid');
                icon.className = 'fas fa-times-circle';
            }
        });

        const percent = (passed / 5) * 100;
        strengthBar.style.width = percent + '%';

        if (percent <= 40) {
            strengthBar.style.backgroundColor = '#ef4444'; // red
        } else if (percent <= 80) {
            strengthBar.style.backgroundColor = '#f97316'; // orange
        } else {
            strengthBar.style.backgroundColor = '#22c55e'; // green
        }
    });

    function resetPasswordStrength() {
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = 'transparent'; // Or initial color
        criteria.forEach(key => {
            const item = document.getElementById(key);
            const icon = item.querySelector('i');
            item.classList.remove('valid', 'invalid');
            icon.className = 'fas fa-circle'; // Reset to initial icon
        });
    }

    // Event listener for the default password checkbox
    defaultPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            addAdminPasswordInput.value = 'Jobfit@2025';
            addAdminPasswordInput.disabled = true; // Disable input when default is used
            addAdminPasswordInput.required = false; // Not required when disabled
            resetPasswordStrength(); // Clear strength indicator
        } else {
            addAdminPasswordInput.value = '';
            addAdminPasswordInput.disabled = false; // Enable input
            addAdminPasswordInput.required = true; // Make it required again
            // Password strength will update on next input or when it's re-enabled and has content
        }
    });

    // --- Delete Admin Functionality ---
    const deleteAdminModal = document.getElementById('deleteAdminModal');
    const cancelDeleteAdminBtn = document.getElementById('cancelDeleteAdmin');
    const confirmDeleteAdminBtn = document.getElementById('confirmDeleteAdmin');
    const deleteAdminNameSpan = document.getElementById('deleteAdminName');
    let adminToDeleteId = null;

    document.querySelectorAll('.action-icon.delete-admin').forEach(icon => {
        icon.addEventListener('click', function () {
            const row = this.closest('tr');
            const adminName = row.querySelector('.user-cell').textContent.trim().substring(1).trim();
            adminToDeleteId = this.dataset.id;
            deleteAdminNameSpan.textContent = adminName;
            deleteAdminModal.style.display = 'flex';
        });
    });

    cancelDeleteAdminBtn.addEventListener('click', function () {
        deleteAdminModal.style.display = 'none';
        adminToDeleteId = null;
    });

    confirmDeleteAdminBtn.addEventListener('click', function () {
        if (adminToDeleteId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete_user_admin.php'; // Point to the new delete handler
            form.style.display = 'none';

            const inputId = document.createElement('input');
            inputId.name = 'adminId';
            inputId.value = adminToDeleteId;
            form.appendChild(inputId);

            const inputAction = document.createElement('input');
            inputAction.name = 'deleteAdmin'; // Specific action for admin
            inputAction.value = '1';
            form.appendChild(inputAction);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // --- Delete User Functionality ---
    const deleteUserModal = document.getElementById('deleteUserModal');
    const cancelDeleteUserBtn = document.getElementById('cancelDeleteUser');
    const confirmDeleteUserBtn = document.getElementById('confirmDeleteUser');
    const deleteUserNameSpan = document.getElementById('deleteUserName');
    let userToDeleteId = null;

    document.querySelectorAll('.action-icon.delete-user').forEach(icon => {
        icon.addEventListener('click', function () {
            const row = this.closest('tr');
            const userName = row.querySelector('.user-cell').textContent.trim().substring(1).trim();
            userToDeleteId = this.dataset.id;
            deleteUserNameSpan.textContent = userName;
            deleteUserModal.style.display = 'flex';
        });
    });

    cancelDeleteUserBtn.addEventListener('click', function () {
        deleteUserModal.style.display = 'none';
        userToDeleteId = null;
    });

    confirmDeleteUserBtn.addEventListener('click', function () {
        if (userToDeleteId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete_user_admin.php'; // Point to the new delete handler
            form.style.display = 'none';

            const inputId = document.createElement('input');
            inputId.name = 'userId';
            inputId.value = userToDeleteId;
            form.appendChild(inputId);

            const inputAction = document.createElement('input');
            inputAction.name = 'deleteUser'; // Specific action for user
            inputAction.value = '1';
            form.appendChild(inputAction);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // --- View Admin Profile Functionality ---
    const viewAdminProfileModal = document.getElementById('viewAdminProfileModal');
    const closeAdminProfileModalBtn = document.getElementById('closeAdminProfileModal');
    const adminProfileModalCloseBtn = document.getElementById('adminProfileModalCloseBtn');

    document.querySelectorAll('.action-icon.view-admin').forEach(icon => {
        icon.addEventListener('click', function () {
            const adminId = this.dataset.id;
            fetch(`fetch_admin_profile.php?adminid=${adminId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching admin data:', data.error);
                        alert('Could not load admin profile: ' + data.error);
                        return;
                    }
                    const adminProfileAvatarDiv = document.getElementById('adminProfileAvatar');
                    adminProfileAvatarDiv.innerHTML = ''; // Clear previous content

                    if (data.adminimage_base64) {
                        const img = document.createElement('img');
                        img.src = data.adminimage_base64;
                        img.alt = "Admin Avatar";
                        img.classList.add('avatar-circle-large'); // Apply existing avatar styling
                        adminProfileAvatarDiv.appendChild(img);
                    } else {
                        const initials = (data.adminname ? data.adminname.charAt(0) : '');
                        adminProfileAvatarDiv.textContent = initials.toUpperCase();
                        adminProfileAvatarDiv.style.backgroundColor = '#6a1b9a'; // Fallback color
                    }

                    document.getElementById('adminProfileFullName').textContent = data.adminname;
                    document.getElementById('adminProfileEmailSummary').textContent = data.adminemail;

                    // Set Admin Status
                    const adminStatusElement = document.getElementById('adminProfileStatus');
                    if (data.adminstatus) {
                        adminStatusElement.textContent = data.adminstatus;
                        adminStatusElement.classList.remove('active-status', 'inactive-status'); // Clear previous classes
                        if (data.adminstatus.toLowerCase() === 'active') {
                            adminStatusElement.classList.add('active-status');
                        } else if (data.adminstatus.toLowerCase() === 'inactive') {
                            adminStatusElement.classList.add('inactive-status');
                        }
                        adminStatusElement.style.display = 'block'; // Show status
                    } else {
                        adminStatusElement.style.display = 'none'; // Hide if no status
                    }

                    // Add this line to display the Admin ID
                    document.getElementById('viewAdminId').value = data.adminid; // New line

                    document.getElementById('viewAdminFullName').value = data.adminname;
                    document.getElementById('viewAdminEmail').value = data.adminemail;
                    document.getElementById('viewAdminJoinedAt').value = data.joined_date_formatted;

                    viewAdminProfileModal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('An error occurred while fetching admin data.');
                });
        });
    });

    closeAdminProfileModalBtn.addEventListener('click', function () {
        viewAdminProfileModal.style.display = 'none';
    });
    adminProfileModalCloseBtn.addEventListener('click', function () {
        viewAdminProfileModal.style.display = 'none';
    });

    // --- View User Profile Functionality ---
    const viewUserProfileModal = document.getElementById('viewUserProfileModal');
    const closeUserProfileModalBtn = document.getElementById('closeUserProfileModal');
    const profileModalCloseBtn = document.getElementById('profileModalCloseBtn');
    const nullFieldInstruction = document.getElementById('nullFieldInstruction');

    // Function to calculate age
    function calculateAge(birthDateString) {
        if (!birthDateString || birthDateString === '0000-00-00') {
            return ''; // Return empty if birthdate is not set
        }
        const birthDate = new Date(birthDateString);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age > 0 ? age : 0; // Ensure age is not negative
    }

    document.querySelectorAll('.action-icon.view-user').forEach(icon => {
        icon.addEventListener('click', function () {
            const userId = this.dataset.id;
            fetch(`fetch_user_profile.php?userid=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching user data:', data.error);
                        alert('Could not load user profile: ' + data.error);
                        return;
                    }

                    // Reset previous highlights
                    document.querySelectorAll('.form-field.highlight-null').forEach(el => {
                        el.classList.remove('highlight-null');
                    });
                    nullFieldInstruction.style.display = 'none';
                    let hasNullFields = false;

                    const profileAvatarDiv = document.getElementById('profileAvatar');
                    profileAvatarDiv.innerHTML = ''; // Clear previous content

                    if (data.image_base64) {
                        const img = document.createElement('img');
                        img.src = data.image_base64;
                        img.alt = "User Avatar";
                        img.classList.add('avatar-circle-large'); // Apply existing avatar styling
                        profileAvatarDiv.appendChild(img);
                    } else {
                        const initials = (data.firstname ? data.firstname.charAt(0) : '');
                        profileAvatarDiv.textContent = initials.toUpperCase();
                        profileAvatarDiv.style.backgroundColor = '#2f80ed'; // Fallback color
                    }

                    document.getElementById('profileFullName').textContent = `${data.firstname || ''} ${data.lastname || ''}`.trim();
                    document.getElementById('profileEmailSummary').textContent = data.email;

                    // Set User Status
                    const userStatusElement = document.getElementById('profileStatus');
                    if (data.status) {
                        userStatusElement.textContent = data.status;
                        userStatusElement.classList.remove('active-status', 'inactive-status');
                        if (data.status.toLowerCase() === 'active') {
                            userStatusElement.classList.add('active-status');
                        } else if (data.status.toLowerCase() === 'inactive') {
                            userStatusElement.classList.add('inactive-status');
                        }
                        userStatusElement.style.display = 'block';
                    } else {
                        userStatusElement.style.display = 'none';
                    }

                    // Calculate age
                    const age = calculateAge(data.bday);
                    document.getElementById('viewAge').value = age !== '' ? age + ' years old' : ''; // Display age with 'years old'

                    // Add this to your fieldsToCheck object
                    const fieldsToCheck = {
                        viewFullName: `${data.firstname || ''} ${data.lastname || ''}`.trim(),
                        viewDOB: data.bday,
                        viewEmail: data.email,
                        viewContact: data.contact,
                        viewEduLvl: data.educlvl,
                        viewCourse: data.course,
                        viewSchool: data.school,
                        viewSignupDate: data.joined_date_formatted,
                        viewUserId: data.userid // New: Add the user ID here
                    };

                    for (const [id, value] of Object.entries(fieldsToCheck)) {
                        const inputElement = document.getElementById(id);
                        if (inputElement) {
                            inputElement.value = value || '';
                            if (value === null || value === '' || value === '0000-00-00') {
                                inputElement.closest('.form-field').classList.add('highlight-null');
                                hasNullFields = true;
                            }
                        }
                    }

                    // Special check for viewAge if the birthdate was not set
                    if (data.bday === null || data.bday === '' || data.bday === '0000-00-00') {
                        const ageInputField = document.getElementById('viewAge');
                        if (ageInputField) {
                            ageInputField.closest('.form-field').classList.add('highlight-null');
                            hasNullFields = true;
                        }
                    }

                    if (hasNullFields) {
                        nullFieldInstruction.style.display = 'block';
                    }
                    
                    viewUserProfileModal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('An error occurred while fetching user data.');
                });
        });
    });

    closeUserProfileModalBtn.addEventListener('click', function () {
        viewUserProfileModal.style.display = 'none';
    });
    profileModalCloseBtn.addEventListener('click', function () {
        viewUserProfileModal.style.display = 'none';
    });

    // Handle Escape key to close modals
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            logoutModal.style.display = 'none';
            addAdminModal.style.display = 'none';
            deleteAdminModal.style.display = 'none'; // Close admin delete modal
            deleteUserModal.style.display = 'none';   // Close user delete modal
            userDropdown.style.display = 'none';
            viewUserProfileModal.style.display = 'none';
            viewAdminProfileModal.style.display = 'none'; // Close admin profile modal
            editAdminModal.style.display = 'none'; // Close edit admin modal

            // Close new confirmation modals
            addAdminConfirmModal.style.display = 'none';
            updateStatusConfirmModal.style.display = 'none';
            updateAdminInfoConfirmModal.style.display = 'none';
        }
    });

    // --- Dropdown Menu Functionality (for Edit Icons) ---
    document.querySelectorAll('.action-dropdown-wrapper').forEach(wrapper => {
        const editIcon = wrapper.querySelector('.edit-admin, .edit-user');
        const dropdown = wrapper.querySelector('.action-dropdown');

        if (editIcon && dropdown) {
            editIcon.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevent clicks from bubbling up and closing other dropdowns
                // Close other open dropdowns
                document.querySelectorAll('.action-dropdown').forEach(openDropdown => {
                    if (openDropdown !== dropdown) {
                        openDropdown.style.display = 'none';
                    }
                });
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });
        }
    });

    // Close dropdowns when clicking anywhere else on the document
    document.addEventListener('click', function(event) {
        document.querySelectorAll('.action-dropdown').forEach(dropdown => {
            const wrapper = dropdown.closest('.action-dropdown-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    });

    // New: Update Status Confirmation Modals
    const updateStatusConfirmModal = document.getElementById('updateStatusConfirmModal');
    const updateStatusConfirmMessage = document.getElementById('updateStatusConfirmMessage');
    const cancelUpdateStatusConfirmBtn = document.getElementById('cancelUpdateStatusConfirm');
    const confirmUpdateStatusBtn = document.getElementById('confirmUpdateStatus');

    let pendingStatusUpdate = { type: null, id: null, newStatus: null }; // To store pending update details

    document.querySelectorAll('.deactivate-admin, .activate-admin').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const adminId = this.dataset.id;
            const newStatus = this.dataset.status;
            const row = this.closest('tr');
            const adminName = row.dataset.adminname || row.querySelector('.user-cell').textContent.trim(); // Get name from data-attribute or cell
            
            pendingStatusUpdate = { type: 'admin', id: adminId, newStatus: newStatus };
            updateStatusConfirmMessage.textContent = `Are you sure you want to change the status of ${adminName} to "${newStatus}"?`;
            updateStatusConfirmModal.style.display = 'flex';
        });
    });

    document.querySelectorAll('.deactivate-user, .activate-user').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const userId = this.dataset.id;
            const newStatus = this.dataset.status;
            const row = this.closest('tr');
            const userName = row.dataset.username || row.querySelector('.user-cell').textContent.trim(); // Get name from data-attribute or cell

            pendingStatusUpdate = { type: 'user', id: userId, newStatus: newStatus };
            updateStatusConfirmMessage.textContent = `Are you sure you want to change the status of ${userName} to "${newStatus}"?`;
            updateStatusConfirmModal.style.display = 'flex';
        });
    });

    cancelUpdateStatusConfirmBtn.addEventListener('click', function() {
        updateStatusConfirmModal.style.display = 'none';
        pendingStatusUpdate = { type: null, id: null, newStatus: null }; // Clear pending data
    });

    confirmUpdateStatusBtn.addEventListener('click', function() {
        if (pendingStatusUpdate.type && pendingStatusUpdate.id && pendingStatusUpdate.newStatus) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'update_user_admin.php';
            form.style.display = 'none';

            if (pendingStatusUpdate.type === 'admin') {
                const inputId = document.createElement('input');
                inputId.name = 'adminId';
                inputId.value = pendingStatusUpdate.id;
                form.appendChild(inputId);

                const inputStatus = document.createElement('input');
                inputStatus.name = 'newStatus';
                inputStatus.value = pendingStatusUpdate.newStatus;
                form.appendChild(inputStatus);

                const inputAction = document.createElement('input');
                inputAction.name = 'updateAdminStatusConfirmed'; // Signify confirmed action
                inputAction.value = '1';
                form.appendChild(inputAction);
            } else if (pendingStatusUpdate.type === 'user') {
                const inputId = document.createElement('input');
                inputId.name = 'userId';
                inputId.value = pendingStatusUpdate.id;
                form.appendChild(inputId);

                const inputStatus = document.createElement('input');
                inputStatus.name = 'newStatus';
                inputStatus.value = pendingStatusUpdate.newStatus;
                form.appendChild(inputStatus);

                const inputAction = document.createElement('input');
                inputAction.name = 'updateUserStatusConfirmed'; // Signify confirmed action
                inputAction.value = '1';
                form.appendChild(inputAction);
            }

            document.body.appendChild(form);
            form.submit();
        }
        updateStatusConfirmModal.style.display = 'none';
        pendingStatusUpdate = { type: null, id: null, newStatus: null }; // Clear pending data
    });

    // --- Edit Admin Info Modal Management ---
    const editAdminModal = document.getElementById('editAdminModal');
    const closeEditAdminModal = document.getElementById('closeEditAdminModal');
    const cancelEditAdminBtn = document.getElementById('cancelEditAdmin');
    const editAdminForm = document.getElementById('editAdminForm'); // Get the form
    const submitEditAdminBtn = document.getElementById('submitEditAdminBtn'); // Get the submit button

    // New: Update Admin Info Confirmation Modal
    const updateAdminInfoConfirmModal = document.getElementById('updateAdminInfoConfirmModal');
    const cancelUpdateAdminInfoConfirmBtn = document.getElementById('cancelUpdateAdminInfoConfirm');
    const confirmUpdateAdminInfoBtn = document.getElementById('confirmUpdateAdminInfo');

    document.querySelectorAll('.dropdown-item.edit-admin-info').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const adminId = this.dataset.id;
            const row = this.closest('tr');
            const adminName = row.dataset.adminname;
            const adminEmail = row.dataset.adminemail;

            document.getElementById('editAdminId').value = adminId;
            document.getElementById('editAdminName').value = adminName;
            document.getElementById('editAdminEmail').value = adminEmail;
            document.getElementById('edit-admin-password').value = ''; // Clear password field on open
            resetEditPasswordStrength(); // Reset strength for edit modal

            editAdminModal.style.display = 'flex';
        });
    });

    // Handle submission of the edit admin info form
    submitEditAdminBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default form submission
        if (editAdminForm.checkValidity()) {
            updateAdminInfoConfirmModal.style.display = 'flex';
            editAdminModal.style.display = 'none'; // Hide edit admin form temporarily
        } else {
            editAdminForm.reportValidity(); // Show browser's validation messages
        }
    });

    closeEditAdminModal.addEventListener('click', function() {
        editAdminModal.style.display = 'none';
    });
    cancelEditAdminBtn.addEventListener('click', function() {
        editAdminModal.style.display = 'none';
    });

    cancelUpdateAdminInfoConfirmBtn.addEventListener('click', function() {
        updateAdminInfoConfirmModal.style.display = 'none';
        editAdminModal.style.display = 'flex'; // Show edit admin form again
    });

    confirmUpdateAdminInfoBtn.addEventListener('click', function() {
        // Create a hidden form and submit the data to update_user_admin.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'update_user_admin.php';
        form.style.display = 'none';

        // Append all form data from editAdminForm
        const formData = new FormData(editAdminForm);
        for (let pair of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            form.appendChild(input);
        }

        // Add a hidden input to signify confirmation
        const confirmedInput = document.createElement('input');
        confirmedInput.type = 'hidden';
        confirmedInput.name = 'updateAdminInfoConfirmed'; // Match the name expected by update_user_admin.php
        confirmedInput.value = '1';
        form.appendChild(confirmedInput);

        document.body.appendChild(form);
        form.submit();
    });

    // Password Strength Checker for Edit Admin Modal
    const editPasswordInput = document.getElementById('edit-admin-password');
    const editStrengthBar = document.getElementById('edit-strength-bar');
    const editCriteria = ["edit-length", "edit-upper", "edit-lower", "edit-number", "edit-special"];

    editPasswordInput.addEventListener('input', () => {
        const val = editPasswordInput.value;
        let passed = 0;

        const checks = {
            'edit-length': val.length >= 8,
            'edit-upper': /[A-Z]/.test(val),
            'edit-lower': /[a-z]/.test(val),
            'edit-number': /[0-9]/.test(val),
            'edit-special': /[^A-Za-z0-9]/.test(val)
        };

        editCriteria.forEach(key => {
            const item = document.getElementById(key);
            const icon = item.querySelector('i');
            if (checks[key]) {
                item.classList.add('valid');
                item.classList.remove('invalid');
                icon.className = 'fas fa-check-circle';
                passed++;
            } else {
                item.classList.remove('valid');
                item.classList.add('invalid');
                icon.className = 'fas fa-times-circle';
            }
        });

        const percent = (passed / 5) * 100;
        editStrengthBar.style.width = percent + '%';

        if (percent <= 40) {
            editStrengthBar.style.backgroundColor = '#ef4444'; // red
        } else if (percent <= 80) {
            editStrengthBar.style.backgroundColor = '#f97316'; // orange
        } else {
            editStrengthBar.style.backgroundColor = '#22c55e'; // green
        }
    });

    function resetEditPasswordStrength() {
        editStrengthBar.style.width = '0%';
        editStrengthBar.style.backgroundColor = 'transparent';
        editCriteria.forEach(key => {
            const item = document.getElementById(key);
            const icon = item.querySelector('i');
            item.classList.remove('valid', 'invalid');
            icon.className = 'fas fa-circle';
        });
    }

    // Generic table management function (RETAINS SEARCH/FILTER/SORT/PAGINATION)
    function setupTable(tableId, searchInputId, statusFilterId, sortOrderSelectId, prevPageBtnId, nextPageBtnId, pageNumbersDivId, paginationInfoId, rowsPerPageSelectId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const allOriginalRows = Array.from(table.tBodies[0].querySelectorAll('tr:not(.no-data-row)'));

        let currentPage = 0; 
        let rowsPerPage = parseInt(document.getElementById(rowsPerPageSelectId).value);

        function displayTable() {
            let processedRows = applyFiltersAndSearch(allOriginalRows);
            processedRows = applySorting(processedRows);

            updatePaginationInfo(processedRows.length);

            table.tBodies[0].innerHTML = '';

            const start = currentPage * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedRows = processedRows.slice(start, end);

            if (paginatedRows.length > 0) {
                paginatedRows.forEach(row => table.tBodies[0].appendChild(row));
            } else {
                const colspan = table.tHead.rows[0].cells.length;
                const emptyRow = document.createElement('tr');
                emptyRow.classList.add('no-data-row'); 
                const emptyCell = document.createElement('td');
                emptyCell.colSpan = colspan;
                emptyCell.style.textAlign = 'center';
                emptyCell.textContent = tableId === 'adminTable' ? 'No Admin Found' : 'No User Found';
                emptyRow.appendChild(emptyCell);
                table.tBodies[0].appendChild(emptyRow);
            }
            updatePaginationControls(processedRows.length);
        }

        function applyFiltersAndSearch(dataRows) {
            const searchTerm = document.getElementById(searchInputId).value.toLowerCase();
            const statusFilter = document.getElementById(statusFilterId).value.toLowerCase();

            return dataRows.filter(row => {
                const name = (row.dataset.adminname || row.dataset.username || '').toLowerCase(); // Handles both admin and user tables
                const email = (row.dataset.adminemail || row.dataset.useremail || '').toLowerCase(); // Handles both admin and user tables
                const status = (row.dataset.adminstatus || row.dataset.userstatus || '').toLowerCase(); // Handles both admin and user tables

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus = statusFilter === '' || status === statusFilter;

                return matchesSearch && matchesStatus;
            });
        }

        function applySorting(dataRows) {
            const sortOrder = document.getElementById(sortOrderSelectId).value;

            if (sortOrder === 'default') {
                return dataRows;
            }

            return [...dataRows].sort((a, b) => {
                const nameA = (a.dataset.adminname || a.dataset.username || '').toLowerCase();
                const nameB = (b.dataset.adminname || b.dataset.username || '').toLowerCase();

                if (sortOrder === 'asc') {
                    return nameA.localeCompare(nameB);
                } else {
                    return nameB.localeCompare(nameA);
                }
            });
        }

        function updatePaginationInfo(totalRows) {
            const paginationInfo = document.getElementById(paginationInfoId);
            const start = totalRows > 0 ? (currentPage * rowsPerPage) + 1 : 0;
            const end = Math.min((currentPage + 1) * rowsPerPage, totalRows);
            paginationInfo.textContent = `Showing ${start} to ${end} of ${totalRows} records`;
        }

        function updatePaginationControls(totalRows) {
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            const pageNumbersDiv = document.getElementById(pageNumbersDivId);
            pageNumbersDiv.innerHTML = '';

            if (totalPages <= 1) {
                document.getElementById(prevPageBtnId).disabled = true;
                document.getElementById(nextPageBtnId).disabled = true;
                return;
            }

            // --- NEW PAGINATION LOGIC START ---
            const maxButtons = 5;
            const currentOneBasedPage = currentPage + 1; 

            let startPage = Math.max(1, currentOneBasedPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);

            if (endPage - startPage + 1 < maxButtons) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageSpan = document.createElement('span');
                pageSpan.textContent = i;
                pageSpan.classList.add('page-number');
                if (i === currentOneBasedPage) {
                    pageSpan.classList.add('active');
                }
                pageSpan.addEventListener('click', () => {
                    currentPage = i - 1; 
                    displayTable();
                });
                pageNumbersDiv.appendChild(pageSpan);
            }
            document.getElementById(prevPageBtnId).disabled = currentPage === 0;
            document.getElementById(nextPageBtnId).disabled = currentPage >= totalPages - 1;
        }

        document.getElementById(searchInputId).addEventListener('input', () => {
            currentPage = 0; 
            displayTable();
        });

        document.getElementById(statusFilterId).addEventListener('change', () => {
            currentPage = 0; 
            displayTable();
        });

        document.getElementById(sortOrderSelectId).addEventListener('change', () => {
            currentPage = 0; 
            displayTable();
        });

        document.getElementById(rowsPerPageSelectId).addEventListener('change', function () {
            rowsPerPage = parseInt(this.value);
            currentPage = 0; 
            displayTable();
        });

        document.getElementById(prevPageBtnId).addEventListener('click', () => {
            if (currentPage > 0) {
                currentPage--;
                displayTable();
            }
        });

        document.getElementById(nextPageBtnId).addEventListener('click', () => {
            const totalRows = applySorting(applyFiltersAndSearch(allOriginalRows)).length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            if (currentPage < totalPages - 1) {
                currentPage++;
                displayTable();
            }
        });

        displayTable();
    }

    // Initialize both tables
    setupTable(
        'adminTable',
        'adminSearch',
        'adminStatusFilter',
        'adminSortOrder',
        'adminPrevPage',
        'adminNextPage',
        'adminPageNumbers',
        'adminPaginationInfo',
        'adminRowsPerPage'
    );

    setupTable(
        'userTable',
        'userSearch',
        'userStatusFilter',
        'userSortOrder',
        'userPrevPage',
        'userNextPage',
        'userPageNumbers',
        'userPaginationInfo',
        'userRowsPerPage'
    );

    // ======================================================================
    // 2. Admin Profile Modal Logic (FOR USERS.PHP) - Renamed for Uniqueness
    // ======================================================================

    // --- Admin Profile Modal Elements (Uniquely Named) ---
    const myprofileAdminProfileModal = document.getElementById('myprofile_adminUserProfileModal');
    const myprofileViewProfileModalBtn = document.getElementById('viewProfileModalBtn'); // This button is outside the modal, keep its original ID
    const myprofileCloseAdminUserProfileModal = document.getElementById('myprofile_closeAdminUserProfileModal');
    const myprofileEditProfileBtn = document.getElementById('myprofile_editProfileBtn');
    const myprofileSaveProfileBtn = document.getElementById('myprofile_saveProfileBtn');
    const myprofileChangePhotoBtn = document.getElementById('myprofile_changePhotoBtn');
    const myprofileAdminImageInput = document.getElementById('myprofile_adminImageInput');
    const myprofileImageUploadForm = document.getElementById('myprofile_imageUploadForm');
    const myprofileAdminProfileModalCloseBtn = document.getElementById('myprofile_adminProfileModalCloseBtn');

    const myprofileFields = {
        adminName: document.getElementById('myprofile_viewAdminFullName'),
        adminEmail: document.getElementById('myprofile_viewAdminEmail'),
    };

    const myprofileSummaryElements = {
        profileFullNameModal: document.getElementById('myprofile_profileFullNameModal'),
        profileEmailSummaryModal: document.getElementById('myprofile_profileEmailSummaryModal'),
        profileAvatarModal: document.getElementById('myprofile_profileAvatarModal')
    };

    let myprofileIsEditing = false;
    let myprofileOriginalAdminName = myprofileFields.adminName?.value || '';
    let myprofileOriginalAdminEmail = myprofileFields.adminEmail?.value || '';
    let myprofileOriginalAdminImageBase64 = document.getElementById('myprofile_originalAdminImageBase64')?.value || '';
    let myprofileTempAdminImageFile = null;

    /** Toggles the edit mode for the admin profile modal. */
    function toggleMyProfileEditMode(enable) {
        myprofileIsEditing = enable;

        myprofileFields.adminName.readOnly = !enable;
        myprofileFields.adminEmail.readOnly = !enable;

        myprofileEditProfileBtn.style.display = enable ? 'none' : 'inline-block';
        myprofileSaveProfileBtn.style.display = enable ? 'inline-block' : 'none';
        myprofileChangePhotoBtn.style.display = enable ? 'inline-block' : 'none';

        myprofileAdminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

        const action = enable ? 'add' : 'remove';
        myprofileFields.adminName.classList[action]('editable-field');
        myprofileFields.adminEmail.classList[action]('editable-field');
    }

    /** Restores the user's original profile image in the modal and header. */
    function restoreMyProfileOriginalImage() {
        const avatarElement = myprofileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

        const mainAvatarIsImg = mainAvatarElement?.tagName === 'IMG';
        const modalAvatarIsImg = avatarElement.tagName === 'IMG';

        if (myprofileOriginalAdminImageBase64 && myprofileOriginalAdminImageBase64 !== '') {
            // Update modal avatar to image
            if (!modalAvatarIsImg) {
                const imgElement = document.createElement('img');
                imgElement.src = myprofileOriginalAdminImageBase64;
                imgElement.alt = "Admin Avatar";
                imgElement.classList.add('avatar-circle-large');
                avatarElement.replaceWith(imgElement);
                myprofileSummaryElements.profileAvatarModal = imgElement;
            } else {
                avatarElement.src = myprofileOriginalAdminImageBase64;
            }

            // Update main avatar to image
            if (mainAvatarElement && !mainAvatarIsImg) {
                const mainImgElement = document.createElement('img');
                // FIX: Corrected redundant image path here
                mainImgElement.src = myprofileOriginalAdminImageBase64;
                mainImgElement.alt = "Admin Avatar";
                mainImgElement.classList.add('avatar-circle');
                mainAvatarElement.replaceWith(mainImgElement);
            } else if (mainAvatarElement) {
                mainAvatarElement.src = myprofileOriginalAdminImageBase64;
            }

        } else {
            // Update modal avatar to initials
            const initials = generateInitials(myprofileOriginalAdminName);
            if (modalAvatarIsImg) {
                const initialsDiv = document.createElement('div');
                initialsDiv.classList.add('avatar-circle-large');
                initialsDiv.textContent = initials;
                avatarElement.replaceWith(initialsDiv);
                myprofileSummaryElements.profileAvatarModal = initialsDiv;
            } else {
                avatarElement.textContent = initials;
            }

            // Update main avatar to initials
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

    // Open the admin profile modal
    myprofileViewProfileModalBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('user-dropdown').style.display = 'none';
        myprofileOriginalAdminName = myprofileFields.adminName.value;
        myprofileOriginalAdminEmail = myprofileFields.adminEmail.value;
        myprofileOriginalAdminImageBase64 = document.getElementById('myprofile_originalAdminImageBase64').value;
        myprofileTempAdminImageFile = null;

        restoreMyProfileOriginalImage();
        toggleMyProfileEditMode(false);
        myprofileAdminProfileModal.style.display = 'flex';
    });

    // Logic to close/cancel the admin profile modal
    function closeMyProfileModal(isCancel = false) {
        if (isCancel || myprofileIsEditing) {
            // Revert changes if cancelled or in edit mode
            myprofileFields.adminName.value = myprofileOriginalAdminName;
            myprofileFields.adminEmail.value = myprofileOriginalAdminEmail;
            restoreMyProfileOriginalImage();
            myprofileTempAdminImageFile = null;
        }
        toggleMyProfileEditMode(false);
        myprofileAdminProfileModal.style.display = 'none';
    }

    myprofileCloseAdminUserProfileModal?.addEventListener('click', () => closeMyProfileModal(true));
    myprofileAdminProfileModalCloseBtn?.addEventListener('click', () => closeMyProfileModal(myprofileIsEditing));

    // Handle Edit Profile button click
    myprofileEditProfileBtn?.addEventListener('click', function () {
        toggleMyProfileEditMode(true);
    });

    // Handle Save Changes button click
    myprofileSaveProfileBtn?.addEventListener('click', async function () {
        const newAdminName = myprofileFields.adminName.value.trim();
        const newAdminEmail = myprofileFields.adminEmail.value.trim();

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

        if (myprofileTempAdminImageFile) {
            formData.append('adminImage', myprofileTempAdminImageFile);
        } else if (myprofileOriginalAdminImageBase64 === '') {
            formData.append('adminImage', '');
        }

        try {
            const response = await fetch('update_admin_profile.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                myprofileOriginalAdminName = newAdminName;
                myprofileOriginalAdminEmail = newAdminEmail;
                myprofileOriginalAdminImageBase64 = data.imageUrl || '';
                myprofileTempAdminImageFile = null;

                myprofileSummaryElements.profileFullNameModal.textContent = newAdminName;
                myprofileSummaryElements.profileEmailSummaryModal.textContent = newAdminEmail;
                document.querySelector('.user-name').textContent = newAdminName;

                // Update the main dashboard avatar
                const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');
                const mainAvatarIsImg = mainAvatarElement?.tagName === 'IMG';

                if (data.imageUrl) {
                    if (!mainAvatarIsImg) {
                        const imgElement = document.createElement('img');
                        imgElement.src = data.imageUrl;
                        imgElement.alt = "Admin Avatar";
                        imgElement.classList.add('avatar-circle');
                        mainAvatarElement.replaceWith(imgElement);
                    } else {
                        mainAvatarElement.src = data.imageUrl;
                    }
                } else {
                    const initials = generateInitials(newAdminName);
                    if (mainAvatarIsImg) {
                        const initialsDiv = document.createElement('div');
                        initialsDiv.classList.add('avatar-circle');
                        initialsDiv.textContent = initials;
                        mainAvatarElement.replaceWith(initialsDiv);
                    } else {
                        mainAvatarElement.textContent = initials;
                    }
                }

                toggleMyProfileEditMode(false);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('An error occurred while updating profile.');
        }
    });

    // Handle Change Photo button click
    myprofileChangePhotoBtn?.addEventListener('click', function () {
        myprofileAdminImageInput.click();
    });

    // Handle file input change (for image preview)
    myprofileAdminImageInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            myprofileTempAdminImageFile = this.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                const avatarElement = myprofileSummaryElements.profileAvatarModal;
                if (avatarElement.tagName === 'DIV') {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    imgElement.alt = "Admin Avatar";
                    imgElement.classList.add('avatar-circle-large');
                    avatarElement.replaceWith(imgElement);
                    myprofileSummaryElements.profileAvatarModal = imgElement;
                } else {
                    avatarElement.src = e.target.result;
                }
            };
            reader.readAsDataURL(myprofileTempAdminImageFile);
        }
    });
});