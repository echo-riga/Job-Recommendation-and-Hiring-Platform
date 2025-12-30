
/**
 * Generates initials from a full name string.
 * @param {string} fullName - The full name.
 * @returns {string} The uppercase initials.
 */
function generateInitials(fullName) {
    const nameParts = fullName.trim().split(/\s+/);
    let initials = nameParts[0].substring(0, 1).toUpperCase();
    if (nameParts.length > 1) {
        initials += nameParts[nameParts.length - 1].substring(0, 1).toUpperCase(); 
    }
    return initials;
}

/**
 * Dynamically resizes a textarea element to fit its content.
 * @param {HTMLTextAreaElement} element - The textarea element.
 */
function autoResizeTextarea(element) {
    if (element) {
        element.style.height = 'auto';
        element.style.height = (element.scrollHeight) + 'px';
    }
}

/**
 * Toggles the action dropdown for a table row.
 * @param {HTMLElement} targetIcon - The icon that was clicked.
 */
function toggleActionDropdown(targetIcon) {
    const wrapper = targetIcon.closest('.action-dropdown-wrapper');
    if (!wrapper) return;

    const dropdown = wrapper.querySelector('.action-dropdown');
    if (!dropdown) return;

    // Close all other open dropdowns
    document.querySelectorAll('.action-dropdown').forEach(d => {
      if (d !== dropdown) {
        d.style.display = 'none';
      }
    });

    // Toggle the current dropdown
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    if (!isVisible) {
      function closeOnOutsideClick(event) {
        if (!wrapper.contains(event.target)) {
          dropdown.style.display = 'none';
          document.removeEventListener('click', closeOnOutsideClick);
        }
      }
      setTimeout(() => {
        document.addEventListener('click', closeOnOutsideClick);
      }, 0);
    }
}

/**
 * Toggles the visibility of the user dropdown menu.
 */
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

/**
 * --- [NEW] Initializes a Quill editor instance. ---
 * @param {string} editorId - The ID of the div to become the editor.
 * @param {string} hiddenInputId - The ID of the hidden textarea to sync with.
 * @param {string} placeholder - The placeholder text for the editor.
 * @returns {Quill} The initialized Quill instance.
 */
function initializeQuillEditor(editorId, hiddenInputId, placeholder) {
    if (!document.querySelector(`#${editorId}`)) return null;
    
    const toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link'],
        ['clean']
    ];

    const quill = new Quill(`#${editorId}`, {
        modules: { toolbar: toolbarOptions },
        theme: 'snow',
        placeholder: placeholder || 'Enter details here...'
    });
    
    const hiddenInput = document.getElementById(hiddenInputId);
    if (!hiddenInput) {
        console.error("Quill hidden input not found:", hiddenInputId);
        return quill;
    }
    
    quill.on('text-change', function() {
        let html = quill.root.innerHTML;
        if (quill.getLength() === 1 || html === '<p><br></p>') {
            hiddenInput.value = ''; 
        } else {
            hiddenInput.value = html; 
        }
    });

    return quill;
}



let quillInstanceSummary = null;
let quillInstanceResponsibilities = null;
let quillInstanceSpecification = null;

let quillViewSummary = null;
let quillViewResponsibilities = null;
let quillViewSpecification = null;

document.addEventListener('DOMContentLoaded', function () {

    // --- Core UI Elements ---
    const burger = document.querySelector('.burger');
    const sidebar = document.querySelector('.sidebar');
    
    // --- Universal Modals ---
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    
    // --- Admin Profile Modal Elements ---
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

    // --- Job Post Modal Elements (View/Edit) ---
    const viewJobPostModal = document.getElementById('viewJobPostModal');
    const closeJobPostModal = document.getElementById('closeJobPostModal');
    const postModalCloseBtn = document.getElementById('postModalCloseBtn');
    const jobPostTable = document.getElementById('jobPostTable');
    const editJobPostBtn = document.getElementById('editJobPostBtn');
    const saveJobPostBtn = document.getElementById('saveJobPostBtn');
    const editJobPostForm = document.getElementById('editJobPostForm');

    // Updated Job Post Modal Content Fields
    const postModalJobRole = document.getElementById('postModalJobRole'); 
    const postModalPostType = document.getElementById('postModalPostType');
    const postModalStatus = document.getElementById('postModalStatus');
    const viewPostId = document.getElementById('viewPostId'); 
    const viewPostJobRole = document.getElementById('viewPostJobRole');
    const viewPostType = document.getElementById('viewPostType');
    const viewPostExperience = document.getElementById('viewPostExperience');
    const viewPostSalary = document.getElementById('viewPostSalary');
    const viewPostAddress = document.getElementById('viewPostAddress');
    const viewPostDate = document.getElementById('viewPostDate'); 
    const displayPostDeadline = document.getElementById('displayPostDeadline'); 
    const viewPostDeadline = document.getElementById('viewPostDeadline'); 

    // --- NEW FIELDS ---
    const viewPostWorkSetup = document.getElementById('viewPostWorkSetup');
    const viewPostApplicantLimit = document.getElementById('viewPostApplicantLimit');
    
    // ---  Get textareas AND their display divs ---
    const viewPostSummary = document.getElementById('viewPostSummary');
    const viewPostResponsibilities = document.getElementById('viewPostResponsibilities');
    const viewPostSpecification = document.getElementById('viewPostSpecification');
    
    const displayPostSummary = document.getElementById('displayPostSummary');
    const displayPostResponsibilities = document.getElementById('displayPostResponsibilities');
    const displayPostSpecification = document.getElementById('displayPostSpecification');
    
    let originalPostData = {};
    let isJobPostEditing = false;


    // --- Add Job Post Modal Elements ---
    const addJobPostModal = document.getElementById('addJobPostModal');
    const closeAddJobPostModal = document.getElementById('closeAddJobPostModal');
    const postCancelBtn = document.getElementById('postCancelBtn');
    const addJobPostForm = document.getElementById('addJobPostForm');
    const openAddJobPostModalBtn = document.getElementById('openAddJobPostModalBtn'); 


    // --- Status/Deadline Action Modals ---
    const setDeadlineModal = document.getElementById('setDeadlineModal');
    const cancelSetDeadline = document.getElementById('cancelSetDeadline');
    const updateDeadlineForm = document.getElementById('updateDeadlineForm');
    const deadlinePostIdInput = document.getElementById('deadlinePostId');
    const newPostDeadlineInput = document.getElementById('newPostDeadline');
    const closeStatusConfirmModal = document.getElementById('closeStatusConfirmModal');
    const cancelCloseStatusButton = document.getElementById('cancelCloseStatus');
    const confirmCloseStatusButton = document.getElementById('confirmCloseStatus');
    let postIdToClose = null;

    // --- Delete Posting Modal Elements ---
    const deletePostingModal = document.getElementById('deletePostingModal');
    const cancelDeletePostingBtn = document.getElementById('cancelDeletePosting');
    const confirmDeletePostingBtn = document.getElementById('confirmDeletePosting');
    const deletePostingJobRoleSpan = document.getElementById('deletePostingJobRole');
    const postToDeleteIdInput = document.getElementById('postToDeleteId'); 

    // --- Table Interaction State ---
    let allTableRows = [];
    let currentPage = 1;
    let rowsPerPage = 5;

    // --- Table Control Elements ---
    const jobPostSearch = document.getElementById('jobPostSearch');
    const jobPostStatusFilter = document.getElementById('jobPostStatusFilter');
    const jobPostSortOrder = document.getElementById('jobPostSortOrder');
    const jobPostRowsPerPage = document.getElementById('jobPostRowsPerPage');
    const jobPostTableBody = jobPostTable?.querySelector('tbody');
    const jobPostPaginationInfo = document.getElementById('jobPostPaginationInfo');
    const jobPostPrevPage = document.getElementById('jobPostPrevPage');
    const jobPostNextPage = document.getElementById('jobPostNextPage');
    const jobPostPageNumbers = document.getElementById('jobPostPageNumbers');


    // --- [NEW] Initialize Quill Editors for Add Job Post Modal ---
    quillInstanceSummary = initializeQuillEditor(
        'quill-editor-summary', 
        'postSummary', 
        'Enter a brief summary of the job...'
    );
    quillInstanceResponsibilities = initializeQuillEditor(
        'quill-editor-responsibilities', 
        'postResponsibilities', 
        'Enter the job responsibilities (e.g., as a bulleted list)...'
    );
    quillInstanceSpecification = initializeQuillEditor(
        'quill-editor-specification', 
        'postSpecification', 
        'Enter the required specifications or qualifications...'
    );


    // Core UI Listeners 

    // Sidebar toggle
    burger?.addEventListener('click', () => {
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
    cancelLogout?.addEventListener('click', function () {
      logoutModal.style.display = 'none';
    });

    // Smooth scroll for hash navs
    document.querySelectorAll('nav a').forEach(link => {
      link.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
          e.preventDefault();
          document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
          this.classList.add('active');
          const target = document.querySelector(href);
          if (target) {
            window.scrollTo({
              top: target.offsetTop,
              behavior: 'smooth'
            });
          }
        }
      });
    });



    // Admin Profile Modal Logic

    function toggleEditMode(enable) {
      isEditing = enable;

      profileFields.adminName.readOnly = !enable;
      profileFields.adminEmail.readOnly = !enable;

      editProfileBtn.style.display = enable ? 'none' : 'inline-block';
      saveProfileBtn.style.display = enable ? 'inline-block' : 'none';
      changePhotoBtn.style.display = enable ? 'inline-block' : 'none';

      adminProfileModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

      const action = enable ? 'add' : 'remove';
      profileFields.adminName.classList[action]('editable-field');
      profileFields.adminEmail.classList[action]('editable-field');
    }

    function restoreOriginalImageInModal() {
        const avatarElement = profileSummaryElements.profileAvatarModal;
        const mainAvatarElement = document.querySelector('.user-profile .avatar-circle');

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
            // Update modal avatar to initials
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
    viewProfileModalBtn?.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('user-dropdown').style.display = 'none';
      originalAdminName = profileFields.adminName.value;
      originalAdminEmail = profileFields.adminEmail.value;
      originalAdminImageBase64 = document.getElementById('originalAdminImageBase64').value;
      tempAdminImageFile = null;

      restoreOriginalImageInModal();
      toggleEditMode(false);
      adminProfileModal.style.display = 'flex';
    });

    // Logic to close/cancel the admin profile modal
    function closeAdminProfileModal(isCancel = false) {
        if (isCancel || isEditing) {
            profileFields.adminName.value = originalAdminName;
            profileFields.adminEmail.value = originalAdminEmail;
            restoreOriginalImageInModal();
            tempAdminImageFile = null;
        }
        toggleEditMode(false);
        adminProfileModal.style.display = 'none';
    }

    closeAdminUserProfileModal?.addEventListener('click', () => closeAdminProfileModal(true));
    adminProfileModalCloseBtn?.addEventListener('click', () => closeAdminProfileModal(isEditing));

    // Handle Edit Profile button click
    editProfileBtn?.addEventListener('click', function () {
      toggleEditMode(true);
    });

    // Handle Save Changes button click
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

    // Handle Change Photo button click
    changePhotoBtn?.addEventListener('click', function () {
        adminImageInput.click();
    });

    // Handle file input change (for image preview)
    adminImageInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            tempAdminImageFile = this.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                const avatarElement = profileSummaryElements.profileAvatarModal;
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


    // Job Post View/Edit Modal Logic


    function toggleEditModeJobPost(enable) {
        isJobPostEditing = enable;

        const editableFields = [
            viewPostJobRole, viewPostExperience, viewPostAddress,
            viewPostApplicantLimit,
            viewPostType, viewPostWorkSetup
        ];

        editableFields.forEach(field => {
            if (field.tagName === 'SELECT') field.disabled = !enable;
            else field.readOnly = !enable;
            field.classList.toggle('editable-field', enable);
        });
        
        const fieldsToQuill = [
            { 
                display: displayPostSummary, 
                wrapper: document.getElementById('viewQuillWrapperSummary'), 
                editorDivId: 'quill-editor-viewSummary', 
                textareaId: 'viewPostSummary',
                placeholder: 'Enter summary...'
            },
            { 
                display: displayPostResponsibilities, 
                wrapper: document.getElementById('viewQuillWrapperResponsibilities'),
                editorDivId: 'quill-editor-viewResponsibilities', 
                textareaId: 'viewPostResponsibilities',
                placeholder: 'Enter responsibilities...'
            },
            { 
                display: displayPostSpecification, 
                wrapper: document.getElementById('viewQuillWrapperSpecification'),
                editorDivId: 'quill-editor-viewSpecification', 
                textareaId: 'viewPostSpecification',
                placeholder: 'Enter specifications...'
            }
        ];

        fieldsToQuill.forEach((field, index) => {
            if (enable) {
                field.display.style.display = 'none';
                field.wrapper.style.display = 'block';

                let currentInstance = [quillViewSummary, quillViewResponsibilities, quillViewSpecification][index];
                
                if (!currentInstance) {
                    currentInstance = initializeQuillEditor(field.editorDivId, field.textareaId, field.placeholder);
                    if (index === 0) quillViewSummary = currentInstance;
                    else if (index === 1) quillViewResponsibilities = currentInstance;
                    else if (index === 2) quillViewSpecification = currentInstance;
                }
                
                const textareaValue = document.getElementById(field.textareaId).value;
                if (currentInstance) {
                   currentInstance.root.innerHTML = textareaValue;
                }

            } else {
                field.display.style.display = 'block';
                field.wrapper.style.display = 'none';
            }
        });
        

        viewPostSalary.readOnly = !enable;
        viewPostSalary.classList.toggle('editable-field', enable);

        if (enable) {
            displayPostDeadline.style.display = 'none';
            viewPostDeadline.style.display = 'block';
            viewPostDeadline.disabled = false;
            viewPostDeadline.classList.add('editable-field');

            if (!viewPostDeadline.value || viewPostDeadline.value === 'Invalid Date') {
                const rawDate = originalPostData.postdeadline;
                if (rawDate) {
                    viewPostDeadline.value = rawDate;
                }
            }

            // Initialize jQuery UI Datepicker
            $(viewPostDeadline).datepicker("destroy");
            $(viewPostDeadline).datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                showAnim: "fadeIn"
            }).datepicker("setDate", viewPostDeadline.value);

        } else {
            $(viewPostDeadline).datepicker("destroy");
            viewPostDeadline.style.display = 'none';
            viewPostDeadline.disabled = true;
            viewPostDeadline.classList.remove('editable-field');
            displayPostDeadline.style.display = 'block';
            displayPostDeadline.value = originalPostData.formatted_postdeadline;
        }

        viewPostId.readOnly = true;
        viewPostDate.readOnly = true;

        editJobPostBtn.style.display = enable ? 'none' : 'inline-block';
        saveJobPostBtn.style.display = enable ? 'inline-block' : 'none';
        postModalCloseBtn.textContent = enable ? 'Cancel' : 'Close';

        if (enable) {
        }
    }

    // Updated 'hideJobPostModal' for new fields
    function hideJobPostModal() {
        if (isJobPostEditing) {
            viewPostJobRole.value = originalPostData.postjobrole;
            viewPostType.value = originalPostData.posttype;
            viewPostExperience.value = originalPostData.postexperience;
            viewPostSalary.value = originalPostData.postsalary;
            viewPostAddress.value = originalPostData.postaddress;
            viewPostDeadline.value = originalPostData.postdeadline;
            displayPostDeadline.value = originalPostData.formatted_postdeadline;
            
            viewPostWorkSetup.value = originalPostData.postworksetup;
            viewPostApplicantLimit.value = originalPostData.postapplicantlimit;

            const placeholder = '<p><i>No details provided.</i></p>';
            
            viewPostSummary.value = originalPostData.postsummary;
            displayPostSummary.innerHTML = originalPostData.postsummary || placeholder;

            viewPostResponsibilities.value = originalPostData.postresponsibilities;
            displayPostResponsibilities.innerHTML = originalPostData.postresponsibilities || placeholder;

            viewPostSpecification.value = originalPostData.postspecification;
            displayPostSpecification.innerHTML = originalPostData.postspecification || placeholder;

            toggleEditModeJobPost(false);

        } else {
            viewJobPostModal.style.display = 'none';
        }
    }

    // MODIFICATION: Updated 'viewJobPost' for new fields
    async function viewJobPost(postId) {
        try {
            const formData = new URLSearchParams();
            formData.append('postid', postId);

            const response = await fetch('fetch_job_post_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });

            const result = await response.json();

            if (result.success) {
                const data = result.data;

                // Store all data for potential revert
                originalPostData = {
                    postjobrole: data.postjobrole,
                    posttype: data.posttype,
                    postexperience: data.postexperience,
                    postsalary: data.postsalary,
                    postaddress: data.postaddress,
                    postdeadline: data.postdeadline_raw,
                    formatted_postdeadline: data.formatted_postdeadline,
                    postworksetup: data.postworksetup,
                    postapplicantlimit: data.postapplicantlimit,
                    postsummary: data.postsummary,
                    postresponsibilities: data.postresponsibilities,
                    postspecification: data.postspecification
                };

                // Populate summary header
                postModalJobRole.textContent = data.postjobrole;
                postModalPostType.textContent = data.posttype;
                postModalStatus.textContent = `Status: ${data.poststatus}`;

                postModalStatus.className = 'profile-status';
                const statusClass = data.poststatus.toLowerCase() === 'open' ? 'active' : 'inactive'; 
                postModalStatus.classList.add(statusClass);

                // Populate form fields
                viewPostId.value = data.postid;
                viewPostJobRole.value = data.postjobrole;
                viewPostType.value = data.posttype;
                viewPostExperience.value = data.postexperience;
                viewPostSalary.value = data.postsalary;
                viewPostAddress.value = data.postaddress;
                viewPostDate.value = data.formatted_postdate;
                displayPostDeadline.value = data.formatted_postdeadline;
                viewPostDeadline.value = data.postdeadline_raw;

                // Populate new fields
                viewPostWorkSetup.value = data.postworksetup;
                viewPostApplicantLimit.value = data.postapplicantlimit;
                
                // --- Populate both display div and hidden textarea ---
                const placeholder = '<p><i>No details provided.</i></p>';
                
                viewPostSummary.value = data.postsummary;
                displayPostSummary.innerHTML = data.postsummary || placeholder;

                viewPostResponsibilities.value = data.postresponsibilities;
                displayPostResponsibilities.innerHTML = data.postresponsibilities || placeholder;

                viewPostSpecification.value = data.postspecification;
                displayPostSpecification.innerHTML = data.postspecification || placeholder;


                toggleEditModeJobPost(false);
                viewJobPostModal.style.display = 'flex';

            } else {
                alert('Error fetching post details: ' + result.message);
            }

        } catch (error) {
            console.error('Fetch error:', error);
            alert('An error occurred while connecting to the server.');
        }
    }

//editpost
    async function editJobPost(postId) {
        try {
            const formData = new URLSearchParams();
            formData.append('postid', postId);

            const response = await fetch('fetch_job_post_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });

            const result = await response.json();

            if (result.success) {
                const data = result.data;

                 originalPostData = {
                    postjobrole: data.postjobrole,
                    posttype: data.posttype,
                    postexperience: data.postexperience,
                    postsalary: data.postsalary,
                    postaddress: data.postaddress,
                    postdeadline: data.postdeadline_raw,
                    formatted_postdeadline: data.formatted_postdeadline,
                    postworksetup: data.postworksetup,
                    postapplicantlimit: data.postapplicantlimit,
                    postsummary: data.postsummary,
                    postresponsibilities: data.postresponsibilities,
                    postspecification: data.postspecification
                };

                postModalJobRole.textContent = data.postjobrole;
                postModalPostType.textContent = data.posttype;
                postModalStatus.textContent = `Status: ${data.poststatus}`;

                postModalStatus.className = 'profile-status';
                const statusClass = data.poststatus.toLowerCase() === 'open' ? 'active' : 'inactive'; // Matched CSS classes
                postModalStatus.classList.add(statusClass);

                viewPostId.value = data.postid;
                viewPostJobRole.value = data.postjobrole;
                viewPostType.value = data.posttype;
                viewPostExperience.value = data.postexperience;
                viewPostSalary.value = data.postsalary;
                viewPostAddress.value = data.postaddress;
                viewPostDate.value = data.formatted_postdate;
                displayPostDeadline.value = data.formatted_postdeadline;
                viewPostDeadline.value = data.postdeadline_raw;
                viewPostWorkSetup.value = data.postworksetup;
                viewPostApplicantLimit.value = data.postapplicantlimit;
                
                const placeholder = '<p><i>No details provided.</i></p>';
                
                viewPostSummary.value = data.postsummary;
                displayPostSummary.innerHTML = data.postsummary || placeholder;

                viewPostResponsibilities.value = data.postresponsibilities;
                displayPostResponsibilities.innerHTML = data.postresponsibilities || placeholder;

                viewPostSpecification.value = data.postspecification;
                displayPostSpecification.innerHTML = data.postspecification || placeholder;


                toggleEditModeJobPost(true);
                viewJobPostModal.style.display = 'flex';
            } else {
                alert('Error fetching post details: ' + result.message);
            }

        } catch (error) {
            console.error('Fetch error:', error);
            alert('An error occurred while connecting to the server.');
        }
    }

    // Event delegation for the view icon (eye icon)
    jobPostTable?.addEventListener('click', (e) => {
        if (e.target.classList.contains('view-post')) {
            const postId = e.target.getAttribute('data-id');
            viewJobPost(postId);
        }
    });

    // Event listener for EDIT POST button
    editJobPostBtn?.addEventListener('click', function () {
        toggleEditModeJobPost(true);
    });

  
    saveJobPostBtn?.addEventListener('click', async function () {

        const quillInstances = [
            { instance: quillViewSummary, textareaId: 'viewPostSummary' },
            { instance: quillViewResponsibilities, textareaId: 'viewPostResponsibilities' },
            { instance: quillViewSpecification, textareaId: 'viewPostSpecification' }
        ];

        quillInstances.forEach(item => {
            if (item.instance) {
                let html = item.instance.root.innerHTML;
                let textarea = document.getElementById(item.textareaId);
                if (textarea) {
                    textarea.value = (item.instance.getLength() === 1 || html === '<p><br></p>') ? '' : html;
                }
            }
        });

        const formData = new FormData(editJobPostForm);

        // Validation logic
        const requiredFields = [
            { name: 'postjobrole', label: 'Job Position' },
            { name: 'posttype', label: 'Employment Type' },
            { name: 'postworksetup', label: 'Work Setup' },
            { name: 'postexperience', label: 'Experience Level' },
            { name: 'postaddress', label: 'Location/Address' },
            { name: 'postsummary', label: 'Job Summary' },
            { name: 'postresponsibilities', label: 'Responsibilities' },
            { name: 'postspecification', label: 'Specifications' },
            { name: 'postdeadline', label: 'Application Deadline' }
        ];

        let missingField = null;

        for (const field of requiredFields) {
            const value = formData.get(field.name);
            if (!value || String(value).trim() === '') {
                missingField = field.label;
                break;
            }
        }

        if (missingField) {
            alert(`${missingField} cannot be empty.`);
            return;
        }

        saveJobPostBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveJobPostBtn.disabled = true;

        try {
            const response = await fetch('update_job_post.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
               window.location.reload();

            } else {
                alert('Error updating job post: ' + data.message);
            }

        } catch (error) {
            console.error('Error updating job post:', error);
            alert('An error occurred while connecting to the server.');
        } finally {
            saveJobPostBtn.textContent = 'Save Changes';
            saveJobPostBtn.disabled = false;
        }
    });

    // Close listeners for Job Post Modal
    closeJobPostModal?.addEventListener('click', hideJobPostModal);
    postModalCloseBtn?.addEventListener('click', hideJobPostModal);
    window.addEventListener('click', (event) => {
        if (event.target === viewJobPostModal) {
            hideJobPostModal();
        }
    });


    //  Add Job Post Modal Logic

    function showAddJobPostModal() {
        addJobPostModal.style.display = 'flex';

        // Initialize datepicker on the correct field
        $('#postDeadline').datepicker("destroy");
        $('#postDeadline').datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            showAnim: "fadeIn"
        });
    }

    function hideAddJobPostModal() {
        addJobPostModal.style.display = 'none';
        addJobPostForm.reset();

        // --- NEW: Reset Quill editors and their hidden inputs ---
        if (quillInstanceSummary) {
            quillInstanceSummary.setContents([]); 
            document.getElementById('postSummary').value = ''; 
        }
        if (quillInstanceResponsibilities) {
            quillInstanceResponsibilities.setContents([]);
            document.getElementById('postResponsibilities').value = '';
        }
        if (quillInstanceSpecification) {
            quillInstanceSpecification.setContents([]);
            document.getElementById('postSpecification').value = '';
        }
        // --- END NEW ---
    }

    openAddJobPostModalBtn?.addEventListener('click', showAddJobPostModal);
    closeAddJobPostModal?.addEventListener('click', hideAddJobPostModal);
    postCancelBtn?.addEventListener('click', hideAddJobPostModal);

    window.addEventListener('click', (event) => {
        if (event.target === addJobPostModal) {
            hideAddJobPostModal();
        }
    });

    // Form Submission Logic (AJAX)
    addJobPostForm?.addEventListener('submit', function(e) {
        e.preventDefault();

        // --- Manually update hidden textareas just before submit ---
        if (quillInstanceSummary) {
            let html = quillInstanceSummary.root.innerHTML;
            document.getElementById('postSummary').value = (quillInstanceSummary.getLength() === 1 || html === '<p><br></p>') ? '' : html;
        }
        if (quillInstanceResponsibilities) {
            let html = quillInstanceResponsibilities.root.innerHTML;
            document.getElementById('postResponsibilities').value = (quillInstanceResponsibilities.getLength() === 1 || html === '<p><br></p>') ? '' : html;
        }
        if (quillInstanceSpecification) {
            let html = quillInstanceSpecification.root.innerHTML;
            document.getElementById('postSpecification').value = (quillInstanceSpecification.getLength() === 1 || html === '<p><br></p>') ? '' : html;
        }


        const postJobBtn = document.getElementById('postJobBtn');
        postJobBtn.disabled = true;
        postJobBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

        const formData = new FormData(this);
        formData.append('action', 'add_job_post');

        fetch('add_job_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok. Status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                hideAddJobPostModal();
                window.location.reload(); 
            } else {
                alert('Error adding job post: ' + (data.message || 'An unknown server error occurred.'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred during submission. Check console for details.');
        })
        .finally(() => {
            postJobBtn.disabled = false;
            postJobBtn.textContent = 'Post Job';
        });
    });


    // 5. Job Post Status/Deadline/Delete Logic (Table Actions)

    function changePostStatus(postId, action, deadline = null) {
        const formData = new FormData();
        formData.append('postid', postId);
        formData.append('action', action);
        if (deadline) {
          formData.append('postdeadline', deadline);
        }

        fetch('update_job_status.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
          if (data.success) {
            window.location.reload(); 
          } else {
            alert('Error: ' + (data.message || 'An unknown error occurred.'));
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          alert('An error occurred during status update. Check console for details.');
        });
      }


    function closeDeadlineModal() {
      setDeadlineModal.style.display = 'none';
      updateDeadlineForm.reset();
    }

    cancelSetDeadline?.addEventListener('click', closeDeadlineModal);
    window.addEventListener('click', (event) => {
      if (event.target === setDeadlineModal) {
        closeDeadlineModal();
      }
    });

    updateDeadlineForm?.addEventListener('submit', function (e) {
      e.preventDefault();
      const postId = deadlinePostIdInput.value;
      const newDeadline = newPostDeadlineInput.value;

      changePostStatus(postId, 'open_post_with_deadline', newDeadline);
      closeDeadlineModal();
    });


    function closeCloseStatusModal() {
        closeStatusConfirmModal.style.display = 'none';
        postIdToClose = null;
    }

    cancelCloseStatusButton?.addEventListener('click', closeCloseStatusModal);
    window.addEventListener('click', (event) => {
      if (event.target === closeStatusConfirmModal) {
        closeCloseStatusModal();
      }
    });

    confirmCloseStatusButton?.addEventListener('click', function() {
        if (postIdToClose) {
            changePostStatus(postIdToClose, 'close_post', null);
            closeCloseStatusModal();
        }
    });


    // --- Universal Action Dropdown Click Handler ---
    document.addEventListener('click', function (e) {
        if (e.target.matches('.action-icon.edit-post')) {
          e.preventDefault();
          toggleActionDropdown(e.target);
          return;
        }

        if (e.target.matches('.dropdown-item')) {
          e.preventDefault();
          const link = e.target;
          const postId = link.getAttribute('data-id');

          link.closest('.action-dropdown').style.display = 'none';

          if (link.classList.contains('post-status-action')) {
              if (link.classList.contains('action-close-post')) {
                postIdToClose = postId;
                closeStatusConfirmModal.style.display = 'flex';

              } else if (link.classList.contains('action-open-post')) {
                deadlinePostIdInput.value = postId;
                newPostDeadlineInput.min = new Date().toISOString().split('T')[0];
                // Destroy and re-init datepicker for the modal input
                $('#newPostDeadline').datepicker("destroy");
                $('#newPostDeadline').datepicker({
                    dateFormat: "yy-mm-dd",
                    minDate: new Date(),
                    changeMonth: true,
                    changeYear: true,
                    showAnim: "fadeIn"
                });
                setDeadlineModal.style.display = 'flex';
              }
          }

          if (link.classList.contains('edit-post-info')) {
            editJobPost(postId);
          }
        }
    });


    // Event Listener for Delete Icons
    document.querySelectorAll('.action-icon.delete-post').forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.preventDefault();

            const postId = this.dataset.id;
            const jobRole = this.dataset.jobRole;

            postToDeleteIdInput.value = postId;
            deletePostingJobRoleSpan.textContent = jobRole;

            deletePostingModal.style.display = 'flex';
        });
    });

    // Cancel Delete
    cancelDeletePostingBtn?.addEventListener('click', function () {
        deletePostingModal.style.display = 'none';
        postToDeleteIdInput.value = '';
        deletePostingJobRoleSpan.textContent = '';
    });

    // Confirm Delete
    confirmDeletePostingBtn?.addEventListener('click', async function () {
        const finalPostId = postToDeleteIdInput.value;

        if (!finalPostId) {
            alert("Error: Cannot find Post ID for deletion.");
            deletePostingModal.style.display = 'none';
            return;
        }

        confirmDeletePostingBtn.disabled = true;
        confirmDeletePostingBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        const formData = new FormData();
        formData.append('postid', finalPostId);

        try {
            const response = await fetch('delete_posting.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                window.location.reload();

            } else {
                alert('Deletion failed: ' + (data.message || 'An unknown server error occurred.'));
                confirmDeletePostingBtn.disabled = false;
                confirmDeletePostingBtn.textContent = 'Delete Posting';
            }
        } catch (error) {
            console.error('Deletion Error:', error);
            alert('An error occurred while communicating with the server. Check the console for details.');
            confirmDeletePostingBtn.disabled = false;
            confirmDeletePostingBtn.textContent = 'Delete Posting';
        }

        deletePostingModal.style.display = 'none';
        postToDeleteIdInput.value = '';
    });


    // Universal ESC and Outside Click Listeners

    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (logoutModal.style.display === 'flex') {
            logoutModal.style.display = 'none';
            return;
        }
        if (viewJobPostModal.style.display === 'flex') {
            hideJobPostModal();
            return;
        }
        if (addJobPostModal.style.display === 'flex') {
            hideAddJobPostModal();
            return;
        }
        if (adminProfileModal.style.display === 'flex') {
            closeAdminProfileModal(isEditing);
            return;
        }
        if (setDeadlineModal.style.display === 'flex') {
            closeDeadlineModal();
            return;
        }
        if (closeStatusConfirmModal.style.display === 'flex') {
            closeCloseStatusModal();
            return;
        }
        if (deletePostingModal.style.display === 'flex') {
            cancelDeletePostingBtn.click();
            return;
        }
        document.getElementById('user-dropdown').style.display = 'none';
      }
    });

    window.addEventListener('click', function(event) {
        if (event.target == adminProfileModal) {
            closeAdminProfileModal(isEditing);
        }
    });

    // 7. Table Interaction Logic (Search, Filter, Sort, Pagination)
    

    function initializeTableLogic() {
        if (!jobPostTableBody) return;

        allTableRows = Array.from(jobPostTableBody.querySelectorAll('tr'));
        
        if (allTableRows.length === 1 && allTableRows[0].cells.length === 1) {
            allTableRows = [];
        }

        rowsPerPage = parseInt(jobPostRowsPerPage?.value || '5', 10);
        renderJobPostTable();
    }


    function renderJobPostTable() {
        if (!jobPostTableBody) return;

        // --- 1. Filter Logic ---
        const searchTerm = jobPostSearch.value.toLowerCase().trim();
        const statusFilter = jobPostStatusFilter.value.toLowerCase();
        
        let filteredRows = allTableRows.filter(row => {
            const jobRole = row.getAttribute('data-jobrole').toLowerCase();
            const postType = row.getAttribute('data-posttype').toLowerCase();
            const rowStatus = row.getAttribute('data-poststatus').toLowerCase();

            const matchesSearch = jobRole.includes(searchTerm) || postType.includes(searchTerm);
            const matchesStatus = !statusFilter || rowStatus === statusFilter;

            return matchesSearch && matchesStatus;
        });

        // --- 2. Sort Logic ---
        const sortOrder = jobPostSortOrder.value;
        
        filteredRows.sort((a, b) => {
            const aJobRole = a.getAttribute('data-jobrole');
            const bJobRole = b.getAttribute('data-jobrole');
            const aPostDate = new Date(a.cells[3].textContent); 
            const bPostDate = new Date(b.cells[3].textContent);

            if (sortOrder === 'asc') {
                return aJobRole.localeCompare(bJobRole);
            } else if (sortOrder === 'desc') {
                return bJobRole.localeCompare(aJobRole);
            } else if (sortOrder === 'newest') {
                return bPostDate.getTime() - aPostDate.getTime();
            } else if (sortOrder === 'oldest') {
                return aPostDate.getTime() - bPostDate.getTime();
            }
            return 0; 
        });
        
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        } else if (currentPage === 0 && totalPages > 0) {
            currentPage = 1;
        } else if (totalPages === 0) {
            currentPage = 0;
        }


        // --- 3. Pagination Logic (Slicing) ---
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedRows = filteredRows.slice(start, end);

        // --- 4. Render Table Body ---
        jobPostTableBody.innerHTML = ''; 
        
        if (paginatedRows.length === 0) {
            jobPostTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No Job Posts Found Matching Your Criteria</td></tr>';
        } else {
            paginatedRows.forEach(row => jobPostTableBody.appendChild(row));
        }

        // --- 5. Update Pagination Info and Controls ---
        const startCount = totalRows === 0 ? 0 : start + 1;
        const endCount = totalRows === 0 ? 0 : Math.min(end, totalRows);
        
        if (jobPostPaginationInfo) {
            jobPostPaginationInfo.textContent = `Showing ${startCount} to ${endCount} of ${totalRows} job posts`;
        }
        
        if (jobPostPrevPage) jobPostPrevPage.disabled = currentPage <= 1;
        if (jobPostNextPage) jobPostNextPage.disabled = currentPage >= totalPages;

        if (jobPostPageNumbers) {
            jobPostPageNumbers.innerHTML = '';

            if (totalPages > 0) {
                const maxButtons = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);

                if (endPage - startPage + 1 < maxButtons) {
                    startPage = Math.max(1, endPage - maxButtons + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.classList.add('page-number');
                    if (i === currentPage) {
                        pageBtn.classList.add('active');
                    }
                    pageBtn.textContent = i;
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        renderJobPostTable();
                    });
                    jobPostPageNumbers.appendChild(pageBtn);
                }
            }
        }
    }


    // --- Event Listeners for Controls ---
    jobPostSearch?.addEventListener('input', () => {
        currentPage = 1; 
        renderJobPostTable();
    });

    jobPostStatusFilter?.addEventListener('change', () => {
        currentPage = 1;
        renderJobPostTable();
    });

    jobPostSortOrder?.addEventListener('change', () => {
        currentPage = 1;
        renderJobPostTable();
    });

    jobPostRowsPerPage?.addEventListener('change', (e) => {
        rowsPerPage = parseInt(e.target.value, 10);
        currentPage = 1;
        renderJobPostTable();
    });

    jobPostPrevPage?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderJobPostTable();
        }
    });

    jobPostNextPage?.addEventListener('click', () => {
        const searchTerm = jobPostSearch.value.toLowerCase().trim();
        const statusFilter = jobPostStatusFilter.value.toLowerCase();
        let filteredRows = allTableRows.filter(row => {
            const jobRole = row.getAttribute('data-jobrole').toLowerCase();
            const postType = row.getAttribute('data-posttype').toLowerCase();
            const rowStatus = row.getAttribute('data-poststatus').toLowerCase();
            const matchesSearch = jobRole.includes(searchTerm) || postType.includes(searchTerm);
            const matchesStatus = !statusFilter || rowStatus === statusFilter;
            return matchesSearch && matchesStatus;
        });
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        
        if (currentPage < totalPages) {
            currentPage++;
            renderJobPostTable();
        }
    });

    initializeTableLogic();
});