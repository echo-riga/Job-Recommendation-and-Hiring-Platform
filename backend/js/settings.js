
document.addEventListener('DOMContentLoaded', function () {
    // --- Sidebar and Dropdown Toggles ---
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');
    const userDropdown = document.getElementById('user-dropdown');
    const userProfile = document.querySelector('.user-profile');

    // Toggle sidebar visibility
    burger.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        burger.classList.toggle('active');
    });

    // Toggle user dropdown visibility
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

    // --- Settings Menu Navigation Logic ---
    const menuItems = document.querySelectorAll('.settings-menu .menu-item');
    const contentSections = document.querySelectorAll('.settings-content .content-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target;

            // Remove 'active' from all menu items and add to the clicked one
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            // Hide all content sections and show the target one
            contentSections.forEach(section => section.classList.remove('active'));
            document.getElementById(targetId).classList.add('active');

            // Update URL hash without reloading
            history.pushState(null, '', `#${targetId}`);
        });
    });

    // Initialize the first section as active or based on URL hash
    const initialHash = window.location.hash.substring(1); // Remove '#'
    let initialSectionSet = false;

    if (initialHash) {
        const targetMenuItem = document.querySelector(`.settings-menu .menu-item[data-target="${initialHash}"]`);
        const targetContentSection = document.getElementById(initialHash);
        if (targetMenuItem && targetContentSection) {
            menuItems.forEach(i => i.classList.remove('active'));
            contentSections.forEach(section => section.classList.remove('active'));
            targetMenuItem.classList.add('active');
            targetContentSection.classList.add('active');
            initialSectionSet = true;
        }
    }
    
    if (!initialSectionSet && menuItems.length > 0 && contentSections.length > 0) {
        menuItems[0].classList.add('active');
        contentSections[0].classList.add('active');
    }

    // --- Password Toggle Visibility ---
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // --- Password Strength Checker ---
    const passwordInput = document.getElementById('new-password');
    const strengthBar = document.getElementById('strength-bar');
    const criteria = ["length", "upper", "lower", "number", "special"];

    if (passwordInput) { // Ensure passwordInput exists before adding event listener
        passwordInput.addEventListener('input', () => {
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
                if (item) { // Ensure item exists
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
    }

    
    const maintenanceToggle = document.getElementById('maintenance-toggle');
    const statusMessageDiv = document.getElementById('maintenance-status-message');

    function setInitialMaintenanceMessage() {
        if (!maintenanceToggle || !statusMessageDiv) return;

        if (maintenanceToggle.checked) {
            statusMessageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Maintenance mode is <strong>active</strong> - site is closed to visitors.';
            statusMessageDiv.className = 'status-message warning-message';
        } else {
            statusMessageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Site is currently <strong>accessible</strong> to all visitors.';
            statusMessageDiv.className = 'status-message success-message';
        }
    }

    // Set the initial message when the page loads
    setInitialMaintenanceMessage();

    maintenanceToggle?.addEventListener('change', function() {
        const isEnabled = this.checked;

        // Prepare data for the server
        const formData = new FormData();
        formData.append('status', isEnabled);

        // Show a temporary "updating" message
        statusMessageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating status...';
        statusMessageDiv.className = 'status-message info-message';

        // Send the request to the server
        fetch('update_maintenance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Show the success/error alert at the top of the page
            const alertBox = document.getElementById('customAlert');
            if (alertBox) {
                alertBox.textContent = data.message;
                alertBox.classList.add('show');
                setTimeout(() => {
                    alertBox.classList.remove('show');
                }, 5000);
            }

            // Update the permanent status message based on the toggle's state
            setInitialMaintenanceMessage();
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show an error in the alert box
            const alertBox = document.getElementById('customAlert');
            if (alertBox) {
                alertBox.textContent = 'An error occurred. Could not update maintenance status.';
                alertBox.classList.add('show', 'error');
                setTimeout(() => {
                    alertBox.classList.remove('show', 'error');
                }, 5000);
            }
            
            // Revert the toggle and show an error message
            maintenanceToggle.checked = !isEnabled;
            statusMessageDiv.innerHTML = '<i class="fas fa-times-circle"></i> Failed to update status. Please try again.';
            statusMessageDiv.className = 'status-message error-message';
        });
    });


    // --- Feedback Table Functionality (Search, Filter, Sort, Pagination) ---
    function setupFeedbackTable(tableId, searchInputId, statusFilterId, sortOrderSelectId, prevPageBtnId, nextPageBtnId, pageNumbersDivId, paginationInfoId, rowsPerPageSelectId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        // Store all original rows once when the table is set up
        const allOriginalRows = Array.from(table.tBodies[0].querySelectorAll('tr:not(.no-data-row)'));

        let currentPage = 0;
        let rowsPerPage = parseInt(document.getElementById(rowsPerPageSelectId).value);

        function displayFeedbackTable() {
            let processedRows = applyFeedbackFiltersAndSearch(allOriginalRows);
            processedRows = applyFeedbackSorting(processedRows);

            updateFeedbackPaginationInfo(processedRows.length);

            table.tBodies[0].innerHTML = ''; // Clear existing rows

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
                emptyCell.textContent = 'No Feedback Found';
                emptyRow.appendChild(emptyCell);
                table.tBodies[0].appendChild(emptyRow);
            }
            updateFeedbackPaginationControls(processedRows.length);
            // Re-attach event listeners for action icons after table refresh
            attachFeedbackActionListeners();
        }

        function applyFeedbackFiltersAndSearch(dataRows) {
            const searchTerm = document.getElementById(searchInputId).value.toLowerCase();
            const statusFilter = document.getElementById(statusFilterId).value.toLowerCase();

            return dataRows.filter(row => {
                const name = row.cells[2].textContent.toLowerCase();    // Column for Name
                const subject = row.cells[3].textContent.toLowerCase(); // Column for Subject
                const statusElement = row.cells[4].querySelector('.status-tag'); // Column for Status
                const status = statusElement ? statusElement.textContent.toLowerCase() : '';

                const matchesSearch = name.includes(searchTerm) || subject.includes(searchTerm);
                const matchesStatus = statusFilter === '' || status === statusFilter;

                return matchesSearch && matchesStatus;
            });
        }

        function applyFeedbackSorting(dataRows) {
            const sortOrder = document.getElementById(sortOrderSelectId).value;

            if (sortOrder === 'default') {
                // If default, maintain the original order (which is DESC by condate from PHP)
                return dataRows;
            }

            return [...dataRows].sort((a, b) => {
                const dateA = new Date(a.dataset.condate); // Use data attribute for proper date sorting
                const dateB = new Date(b.dataset.condate);

                if (sortOrder === 'newest') {
                    return dateB - dateA; // Newest first (descending date)
                } else if (sortOrder === 'oldest') {
                    return dateA - dateB; // Oldest first (ascending date)
                }
                return 0;
            });
        }

        function updateFeedbackPaginationInfo(totalRows) {
            const paginationInfo = document.getElementById(paginationInfoId);
            const start = totalRows > 0 ? (currentPage * rowsPerPage) + 1 : 0;
            const end = Math.min((currentPage + 1) * rowsPerPage, totalRows);
            paginationInfo.textContent = `Showing ${start} to ${end} of ${totalRows} feedback`;
        }

        function updateFeedbackPaginationControls(totalRows) {
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            const pageNumbersDiv = document.getElementById(pageNumbersDivId);
            pageNumbersDiv.innerHTML = '';

            for (let i = 0; i < totalPages; i++) {
                const pageSpan = document.createElement('span');
                pageSpan.textContent = i + 1;
                pageSpan.classList.add('page-number');
                if (i === currentPage) {
                    pageSpan.classList.add('active');
                }
                pageSpan.addEventListener('click', () => {
                    currentPage = i;
                    displayFeedbackTable();
                });
                pageNumbersDiv.appendChild(pageSpan);
            }

            document.getElementById(prevPageBtnId).disabled = currentPage === 0;
            document.getElementById(nextPageBtnId).disabled = currentPage >= totalPages - 1;
        }

        document.getElementById(searchInputId)?.addEventListener('input', () => {
            currentPage = 0; 
            displayFeedbackTable();
        });

        document.getElementById(statusFilterId)?.addEventListener('change', () => {
            currentPage = 0; 
            displayFeedbackTable();
        });

        document.getElementById(sortOrderSelectId)?.addEventListener('change', () => {
            currentPage = 0; 
            displayFeedbackTable();
        });

        document.getElementById(rowsPerPageSelectId)?.addEventListener('change', function () {
            rowsPerPage = parseInt(this.value);
            currentPage = 0; 
            displayFeedbackTable();
        });

        document.getElementById(prevPageBtnId)?.addEventListener('click', () => {
            if (currentPage > 0) {
                currentPage--;
                displayFeedbackTable();
            }
        });

        document.getElementById(nextPageBtnId)?.addEventListener('click', () => {
            const totalRows = applyFeedbackSorting(applyFeedbackFiltersAndSearch(allOriginalRows)).length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            if (currentPage < totalPages - 1) {
                currentPage++;
                displayFeedbackTable();
            }
        });

        displayFeedbackTable();
    }

    setupFeedbackTable(
        'feedbackTable',
        'feedbackSearch',
        'adminStatusFilter',
        'feedbackSortOrder',
        'feedbackPrevPage',
        'feedbackNextPage',
        'feedbackPageNumbers',
        'feedbackPaginationInfo',
        'feedbackRowsPerPage'
    );

    // --- Feedback Action Buttons (View, Edit Dropdown, Delete) ---
    const viewFeedbackModal = document.getElementById('viewFeedbackModal');
    const closeFeedbackModalBtn = document.getElementById('closeFeedbackModal');
    const feedbackModalCloseBtn = document.getElementById('feedbackModalCloseBtn');

    // Reply Modal
    const replyFeedbackModal = document.getElementById('replyFeedbackModal');
    const closeReplyModalBtn = document.getElementById('closeReplyModal');
    const cancelReplyModalBtn = document.getElementById('cancelReplyModal');
    const replyFeedbackForm = document.getElementById('replyFeedbackForm');

    // === NEW MODAL VARS ===
    const deleteFeedbackModal = document.getElementById('deleteFeedbackModal');
    const cancelDeleteFeedback = document.getElementById('cancelDeleteFeedback');
    const confirmDeleteFeedback = document.getElementById('confirmDeleteFeedback');
    const deleteFeedbackNameSpan = document.getElementById('deleteFeedbackName');
    const feedbackToDeleteIdInput = document.getElementById('feedbackToDeleteId');
    
    const resolveFeedbackConfirmModal = document.getElementById('resolveFeedbackConfirmModal');
    const cancelResolveFeedbackConfirm = document.getElementById('cancelResolveFeedbackConfirm');
    const confirmResolveFeedback = document.getElementById('confirmResolveFeedback');
    const resolveFeedbackNameSpan = document.getElementById('resolveFeedbackName');
    const feedbackToResolveIdInput = document.getElementById('feedbackToResolveId');
    // === END NEW MODAL VARS ===


    // Close modal on ESC key press
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if(logoutModal) logoutModal.style.display = 'none';
            if(userDropdown) userDropdown.style.display = 'none';
            if(viewFeedbackModal) viewFeedbackModal.style.display = 'none';
            if(replyFeedbackModal) replyFeedbackModal.style.display = 'none';
            if(deleteFeedbackModal) deleteFeedbackModal.style.display = 'none';
            if(resolveFeedbackConfirmModal) resolveFeedbackConfirmModal.style.display = 'none';
            
            // Check if myprofile modal function/var exists before calling
            if (typeof closeMyProfileModal === 'function' && typeof myprofileIsEditing !== 'undefined') {
                closeMyProfileModal(myprofileIsEditing);
            }
        }
    });

    // Close modals when clicking outside of them
    window.addEventListener('click', function(event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
        }
        if (event.target === viewFeedbackModal) {
            viewFeedbackModal.style.display = 'none';
        }
        if (event.target === replyFeedbackModal) {
            replyFeedbackModal.style.display = 'none';
        }
        if (event.target === deleteFeedbackModal) {
            deleteFeedbackModal.style.display = 'none';
        }
        if (event.target === resolveFeedbackConfirmModal) {
            resolveFeedbackConfirmModal.style.display = 'none';
        }
        // Check if myprofile modal function/var exists before calling
        if (event.target === myprofileAdminProfileModal && typeof closeMyProfileModal === 'function' && typeof myprofileIsEditing !== 'undefined') {
            closeMyProfileModal(myprofileIsEditing);
        }
    });


    function attachFeedbackActionListeners() {
        document.querySelectorAll('.view-feedback').forEach(icon => {
            icon.removeEventListener('click', handleViewFeedbackClick);
            icon.addEventListener('click', handleViewFeedbackClick);
        });

        document.querySelectorAll('.action-dropdown-wrapper').forEach(wrapper => {
            const editIcon = wrapper.querySelector('.edit-feedback');
            const dropdown = wrapper.querySelector('.action-dropdown');

            if (editIcon && dropdown) {
                editIcon.removeEventListener('click', handleEditFeedbackClick);
                editIcon.addEventListener('click', handleEditFeedbackClick);
            }

        
        });

        // Delete Feedback Confirmation
        document.querySelectorAll('.delete-feedback').forEach(icon => {
            icon.removeEventListener('click', handleDeleteFeedbackClick);
            icon.addEventListener('click', handleDeleteFeedbackClick);
        });

        // Reply Link in Feedback Actions
        document.querySelectorAll('.dropdown-item[href^="mailto:"]').forEach(link => {
            link.removeEventListener('click', handleReplyFeedbackClick);
            link.addEventListener('click', handleReplyFeedbackClick);
        });

        // === NEW LISTENER FOR RESOLVE BUTTON ===
        document.querySelectorAll('.resolve-feedback-btn').forEach(btn => {
            btn.removeEventListener('click', handleResolveFeedbackClick);
            btn.addEventListener('click', handleResolveFeedbackClick);
        });
    }

    function handleViewFeedbackClick(event) {
        const row = this.closest('tr');
        if (row && viewFeedbackModal) {
            document.getElementById('feedbackViewID').value = row.dataset.conid;
            document.getElementById('feedbackViewDate').value = row.dataset.condate;
            document.getElementById('feedbackViewName').textContent = row.dataset.conname;
            document.getElementById('feedbackViewEmailSummary').textContent = row.dataset.conemail;
            document.getElementById('feedbackViewPhone').value = row.dataset.conphone;
            document.getElementById('feedbackViewSubject').value = row.dataset.consubject;
            document.getElementById('feedbackViewMessage').value = row.dataset.conmessage;
            document.getElementById('feedbackViewStatus').textContent = `Status: ${row.dataset.constatus}`;
            document.getElementById('feedbackViewStatus').className = `profile-status status-${row.dataset.constatus.toLowerCase()}`;

            viewFeedbackModal.style.display = 'flex';
        }
    }

    function handleEditFeedbackClick(event) {
        event.stopPropagation(); 
        const dropdown = this.closest('.action-dropdown-wrapper')?.querySelector('.action-dropdown');

        document.querySelectorAll('.action-dropdown').forEach(openDropdown => {
            if (openDropdown !== dropdown) {
                openDropdown.style.display = 'none';
            }
        });
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    }

    // === REPLACED DELETE HANDLER ===
    function handleDeleteFeedbackClick(event) {
        const row = this.closest('tr');
        if (!row) return;
        
        const conid = this.dataset.id; // Get conid from button
        const conname = row.dataset.conname; // Get name from tr

        if (deleteFeedbackNameSpan) deleteFeedbackNameSpan.textContent = conname;
        if (feedbackToDeleteIdInput) feedbackToDeleteIdInput.value = conid;
        if (deleteFeedbackModal) deleteFeedbackModal.style.display = 'flex';
    }

    // === NEW RESOLVE HANDLER ===
    function handleResolveFeedbackClick(event) {
        event.preventDefault(); 
        if (this.classList.contains('disabled')) return; // Do nothing if disabled

        const conid = this.dataset.id;
        const conname = this.dataset.name;

        if (resolveFeedbackNameSpan) resolveFeedbackNameSpan.textContent = conname;
        if (feedbackToResolveIdInput) feedbackToResolveIdInput.value = conid;
        if (resolveFeedbackConfirmModal) resolveFeedbackConfirmModal.style.display = 'flex';
    }


    closeFeedbackModalBtn?.addEventListener('click', function() {
        viewFeedbackModal.style.display = 'none';
    });
    feedbackModalCloseBtn?.addEventListener('click', function() {
        viewFeedbackModal.style.display = 'none';
    });

    document.addEventListener('click', function(event) {
        document.querySelectorAll('.action-dropdown').forEach(dropdown => {
            const wrapper = dropdown.closest('.action-dropdown-wrapper');
            if (wrapper && !wrapper.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    });

    // === NEW MODAL BUTTON HANDLERS ===

    // Delete Feedback Modal
    cancelDeleteFeedback?.addEventListener('click', () => {
        if(deleteFeedbackModal) deleteFeedbackModal.style.display = 'none';
    });

    confirmDeleteFeedback?.addEventListener('click', () => {
        const conid = feedbackToDeleteIdInput.value;
        if (conid) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'settings.php'; 

            const inputConid = document.createElement('input');
            inputConid.type = 'hidden';
            inputConid.name = 'conid';
            inputConid.value = conid;
            form.appendChild(inputConid);

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'feedback_action';
            inputAction.value = 'delete';
            form.appendChild(inputAction);

            document.body.appendChild(form);
            form.submit();
        }
        if(deleteFeedbackModal) deleteFeedbackModal.style.display = 'none';
    });

    // Resolve Feedback Modal
    cancelResolveFeedbackConfirm?.addEventListener('click', () => {
        if(resolveFeedbackConfirmModal) resolveFeedbackConfirmModal.style.display = 'none';
    });

    confirmResolveFeedback?.addEventListener('click', () => {
        const conid = feedbackToResolveIdInput.value;
        // Find the button that opened the modal to find its form
        const resolveBtn = document.querySelector(`.resolve-feedback-btn[data-id='${conid}']`);
        if (resolveBtn) {
            const form = resolveBtn.closest('form.resolve-feedback-form');
            if(form) {
                form.submit();
            } else {
                console.error('Could not find resolve form for conid:', conid);
            }
        }
        if(resolveFeedbackConfirmModal) resolveFeedbackConfirmModal.style.display = 'none';
    });
    // === END NEW MODAL BUTTON HANDLERS ===


    attachFeedbackActionListeners();


    function handleReplyFeedbackClick(event) {
            event.preventDefault(); 
            event.stopPropagation();

            const row = this.closest('tr'); 
            if (row && replyFeedbackModal) {
                const recipientEmail = row.dataset.conemail;
                const subject = `Re: ${row.dataset.consubject}`; 
                const conid = row.dataset.conid; 

                document.getElementById('reply-recipient-email').value = recipientEmail;
                document.getElementById('reply-subject').value = subject;
                document.getElementById('reply-message').value = ''; 
                document.getElementById('conid-for-reply').value = conid;

                const dropdown = this.closest('.action-dropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }

                replyFeedbackModal.style.display = 'flex'; 
            }
        }

        closeReplyModalBtn?.addEventListener('click', function() {
            replyFeedbackModal.style.display = 'none';
        });
        cancelReplyModalBtn?.addEventListener('click', function() {
            replyFeedbackModal.style.display = 'none';
        });

        // Handle Reply Form Submission via AJAX
        replyFeedbackForm?.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            }

            const formData = new FormData(this);
            formData.append('send_reply_email', true); 

            fetch('phpsettings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    const alertBox = document.getElementById('customAlert');
                    alertBox.textContent = data.message;
                    alertBox.classList.add('show');
                    alertBox.style.backgroundColor = '#dc3545'; 
                    setTimeout(() => {
                        alertBox.classList.remove('show');
                        alertBox.style.backgroundColor = ''; 
                    }, 5000);

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Send Email';
                    }
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error); 

                let errorContent = "An unexpected error occurred during email submission.\n\n";
                errorContent += "Details:\n";
                errorContent += "Error Message: " + (error.message || "N/A") + "\n";
                errorContent += "Error String: " + error.toString() + "\n\n";
                errorContent += "Please check the browser's console (F12) for more technical details.";

                const errorWindow = window.open('', '_blank');
                if (errorWindow) {
                    errorWindow.document.write('<pre>' + errorContent + '</pre>');
                    errorWindow.document.title = 'Email Submission Error';
                    errorWindow.document.close();
                } else {
                    alert("An error occurred during email submission. Please check the console (F12) for details, or allow pop-ups for this site to see the full error message in a new window:\n\n" + (error.message || error.toString()));
                }
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Send Email';
                }
            });
        });

    // --- Admin Profile Modal Elements (Uniquely Named) ---
    const myprofileAdminProfileModal = document.getElementById('myprofile_adminUserProfileModal');
    const myprofileViewProfileModalBtn = document.getElementById('viewProfileModalBtn'); 
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

        // Check if buttons exist before manipulating display
        if(myprofileEditProfileBtn) myprofileEditProfileBtn.style.display = enable ? 'none' : 'inline-block';
        if(myprofileSaveProfileBtn) myprofileSaveProfileBtn.style.display = enable ? 'inline-block' : 'none';
        if(myprofileChangePhotoBtn) myprofileChangePhotoBtn.style.display = enable ? 'inline-block' : 'none';

        if(myprofileAdminProfileModalCloseBtn) myprofileAdminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

        const action = enable ? 'add' : 'remove';
        if(myprofileFields.adminName) myprofileFields.adminName.classList[action]('editable-field');
        if(myprofileFields.adminEmail) myprofileFields.adminEmail.classList[action]('editable-field');
    }

  
    function generateInitials(name) {
        if (!name) return '';
        const parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        if (parts.length >= 2) return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
        return '';
    }

    function restoreMyProfileOriginalImage() {
        const avatarElement = myprofileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

        const mainAvatarIsImg = mainAvatarElement?.tagName === 'IMG';
        const modalAvatarIsImg = avatarElement?.tagName === 'IMG';

        if (myprofileOriginalAdminImageBase64 && myprofileOriginalAdminImageBase64 !== '') {
            // Update modal avatar to image
            if (avatarElement && !modalAvatarIsImg) {
                const imgElement = document.createElement('img');
                imgElement.src = myprofileOriginalAdminImageBase64;
                imgElement.alt = "Admin Avatar";
                imgElement.classList.add('avatar-circle-large');
                avatarElement.replaceWith(imgElement);
                myprofileSummaryElements.profileAvatarModal = imgElement;
            } else if (avatarElement) {
                avatarElement.src = myprofileOriginalAdminImageBase64;
            }

            // Update main avatar to image
            if (mainAvatarElement && !mainAvatarIsImg) {
                const mainImgElement = document.createElement('img');
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
            if (avatarElement && modalAvatarIsImg) {
                const initialsDiv = document.createElement('div');
                initialsDiv.classList.add('avatar-circle-large');
                initialsDiv.textContent = initials;
                avatarElement.replaceWith(initialsDiv);
                myprofileSummaryElements.profileAvatarModal = initialsDiv;
            } else if (avatarElement) {
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

        // Must ensure these elements exist before accessing .value
        if (myprofileFields.adminName) myprofileOriginalAdminName = myprofileFields.adminName.value;
        if (myprofileFields.adminEmail) myprofileOriginalAdminEmail = myprofileFields.adminEmail.value;
        
        const base64Input = document.getElementById('myprofile_originalAdminImageBase64');
        if (base64Input) myprofileOriginalAdminImageBase64 = base64Input.value;
        
        myprofileTempAdminImageFile = null;

        restoreMyProfileOriginalImage();
        toggleMyProfileEditMode(false);
        if(myprofileAdminProfileModal) myprofileAdminProfileModal.style.display = 'flex';
    });

    // Logic to close/cancel the admin profile modal
    function closeMyProfileModal(isCancel = false) {
        if (isCancel || myprofileIsEditing) {
            // Revert changes if cancelled or in edit mode
            if(myprofileFields.adminName) myprofileFields.adminName.value = myprofileOriginalAdminName;
            if(myprofileFields.adminEmail) myprofileFields.adminEmail.value = myprofileOriginalAdminEmail;
            restoreMyProfileOriginalImage();
            myprofileTempAdminImageFile = null;
        }
        toggleMyProfileEditMode(false);
        if(myprofileAdminProfileModal) myprofileAdminProfileModal.style.display = 'none';
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
            formData.append('removeAdminImage', '1'); 
        }

        try {
            const response = await fetch('setting_update_admin.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                location.reload();

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
        if(myprofileAdminImageInput) myprofileAdminImageInput.click();
    });

    // Handle file input change (for image preview)
    myprofileAdminImageInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            myprofileTempAdminImageFile = this.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                const avatarElement = myprofileSummaryElements.profileAvatarModal;
                if (!avatarElement) return;

                if (avatarElement.tagName === 'DIV') {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    imgElement.alt = "Admin Avatar";
                    imgElement.classList.add('avatar-circle-large');
                    avatarElement.replaceWith(imgElement);
                    myprofileSummaryElements.profileAvatarModal = imgElement;
                } else { // It's an IMG tag
                    avatarElement.src = e.target.result;
                }
            };
            reader.readAsDataURL(myprofileTempAdminImageFile);
        } else {
            // Handle case where user opens file dialog but cancels
            // Revert to original image if they had one
            if (myprofileOriginalAdminImageBase64) {
                 const avatarElement = myprofileSummaryElements.profileAvatarModal;
                 if (avatarElement && avatarElement.tagName === 'IMG') {
                     avatarElement.src = myprofileOriginalAdminImageBase64;
                 }
            }
            myprofileTempAdminImageFile = null;
        }
    });

    // --- Website Content Image Previews ---
    
    /**
     * Sets up a live image preview for a file input.
     * @param {string} inputId The ID of the <input type="file">
     * @param {string} imgId The ID of the <img> tag for preview
     * @param {string} placeholderId The ID of the placeholder <div>
     */
    function setupImagePreview(inputId, imgId, placeholderId) {
        const input = document.getElementById(inputId);
        const img = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);

        if (input && img && placeholder) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        img.classList.add('has-image');
                        placeholder.classList.add('has-image');
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    }

    // Initialize the previews for the website content tab
    setupImagePreview('upload-banner', 'banner-preview-img', 'banner-placeholder');
    setupImagePreview('upload-group-photo', 'group-photo-preview-img', 'group-photo-placeholder');


    // --- Site Identity Logo Preview ---
    const siteLogoInput = document.getElementById('upload-logo');
    const siteLogoPreview = document.getElementById('current-logo-preview');
    const siteLogoIcon = document.getElementById('default-logo-icon');

    if (siteLogoInput && siteLogoPreview && siteLogoIcon) {
        siteLogoInput.addEventListener('change', function() {
            // Check if a file was selected
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                // When the file is loaded by the reader
                reader.onload = function(e) {
                    // Set the <img> tag's source to the new image
                    siteLogoPreview.src = e.target.result;
                    
                    // Force the <img> tag to be visible
                    siteLogoPreview.style.display = 'block';
                    
                    // Hide the default placeholder icon
                    siteLogoIcon.style.display = 'none';
                };
                
                // Read the selected file as a data URL (which sets off the 'onload' event)
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
});