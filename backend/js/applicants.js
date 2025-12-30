// --- Global Quill Instance Variables ---
let quillInstanceJobOffer = null;
let quillInstanceInitial = null;
let quillInstanceTechnical = null;

// The ID of the container for Quill and the hidden textarea for the form
const quillContainerIdJobOffer = "quill-editor-job-offer";
const hiddenInputIdJobOffer = "job-offer-message-hidden";

// --- Constants for new modals ---
const quillContainerIdInitial = "quill-editor-initial-interview";
const hiddenInputIdInitial = "initial-interview-message-hidden";
const quillContainerIdTechnical = "quill-editor-technical-interview";
const hiddenInputIdTechnical = "technical-interview-message-hidden";

/**
 * Generates initials from a full name (used for avatars without an image).
 * @param {string} fullName - The full name of the user.
 * @returns {string} The capital initials (e.g., 'JS' for 'John Smith').
 */
function generateInitials(fullName) {
  if (!fullName) return "";
  const parts = fullName.split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

/**
 * Toggles the action dropdown for a table row.
 * @param {HTMLElement} targetIcon - The icon that was clicked.
 */
function toggleActionDropdown(targetIcon) {
  const wrapper = targetIcon.closest(".action-dropdown-wrapper");
  if (!wrapper) return;

  const dropdown = wrapper.querySelector(".action-dropdown");
  if (!dropdown) return;

  // Close all other open dropdowns
  document.querySelectorAll(".action-dropdown").forEach((d) => {
    if (d !== dropdown) {
      d.style.display = "none";
    }
  });

  // Toggle the current dropdown
  const isVisible = dropdown.style.display === "block";
  dropdown.style.display = isVisible ? "none" : "block";

  if (!isVisible) {
    function closeOnOutsideClick(event) {
      if (!wrapper.contains(event.target)) {
        dropdown.style.display = "none";
        document.removeEventListener("click", closeOnOutsideClick);
      }
    }
    setTimeout(() => {
      document.addEventListener("click", closeOnOutsideClick);
    }, 0);
  }
}

/**
 * Helper function to calculate age.
 * @param {string} dateOfBirth - Date of birth in 'YYYY-MM-DD' format.
 * @returns {number|string} The calculated age or empty string if invalid/missing DOB.
 */
function calculateAge(dateOfBirth) {
  // Return empty string if DOB is null, empty, or '0000-00-00'
  if (!dateOfBirth || dateOfBirth === "0000-00-00") return "";
  const dob = new Date(dateOfBirth);
  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const m = today.getMonth() - dob.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
    age--;
  }
  return age;
}

// Global function for User Profile Dropdown Toggle
function toggleDropdown() {
  const dropdown = document.getElementById("user-dropdown");
  const isVisible = dropdown.style.display === "block";
  dropdown.style.display = isVisible ? "none" : "block";

  if (!isVisible) {
    document.addEventListener(
      "click",
      function closeOnClick(event) {
        // Check if the click is outside the dropdown and the profile icon
        if (
          !event.target.closest(".user-profile") &&
          !event.target.closest("#user-dropdown")
        ) {
          dropdown.style.display = "none";
          document.removeEventListener("click", closeOnClick);
        }
      },
      { once: false }
    );
  }
}

/**
 * Handles the AJAX call to update applicant status.
 * NOTE: This function is legacy. All email/status updates are
 * now handled by the modal form submit listeners.
 */
async function updateApplicantStatus(appId, newStatus, linkElement) {
  console.warn(
    "Legacy updateApplicantStatus called. This should be handled by modal forms."
  );
}

// --- Selectors for ALL status modals ---
const jobOfferModal = document.getElementById("jobOfferModal");
const initialInterviewModal = document.getElementById("initialInterviewModal");
const technicalInterviewModal = document.getElementById(
  "technicalInterviewModal"
);

// --- Close button selectors ---
const closeJobOfferModalBtn = document.getElementById("closeJobOfferModal");
const cancelJobOfferModalBtn = document.getElementById("cancelJobOfferModal");

// --- Form selectors ---
const jobOfferForm = document.getElementById("jobOfferForm");
const initialInterviewForm = document.getElementById("initialInterviewForm");
const technicalInterviewForm = document.getElementById(
  "technicalInterviewForm"
);

/**
 * Closes the Job Offer/Email modal and resets the button.
 */
function closeJobOfferModal() {
  if (jobOfferModal) jobOfferModal.style.display = "none";
  const confirmBtn = document.getElementById("confirmJobOffer");
  if (confirmBtn) {
    confirmBtn.textContent = "Send Email & Mark for Job Offer";
    confirmBtn.disabled = false;
  }
}

/**
 * --- Closes the Initial Interview modal and resets the button. ---
 */
function closeInitialInterviewModal() {
  if (initialInterviewModal) initialInterviewModal.style.display = "none";
  const confirmBtn = initialInterviewForm?.querySelector(
    'button[type="submit"]'
  );
  if (confirmBtn) {
    confirmBtn.textContent = "Send Email & Move to Initial Interview";
    confirmBtn.disabled = false;
  }
}

/**
 * --- Closes the Technical Interview modal and resets the button. ---
 */
function closeTechnicalInterviewModal() {
  if (technicalInterviewModal) technicalInterviewModal.style.display = "none";
  const confirmBtn = technicalInterviewForm?.querySelector(
    'button[type="submit"]'
  );
  if (confirmBtn) {
    confirmBtn.textContent = "Send Email & Move to Technical Interview";
    confirmBtn.disabled = false;
  }
}

/**
 * Opens the Job Offer/Email modal with pre-populated data and template.
 * (This is for the 'Job Offer' / 'Hired' status)
 * @param {string} appId - Application ID.
 * @param {string} email - Applicant Email.
 * @param {string} fullName - Applicant Full Name.
 * @param {string} jobRole - Job Role.
 */
function openJobOfferModal(appId, email, fullName, jobRole) {
  const defaultSubject = `Good News! Job Offer for ${jobRole} Position in Philkoei International, Inc.`;

  // Set modal fields
  document.getElementById("appToJobOfferId").value = appId;
  document.getElementById("jobRoleToJobOffer").value = jobRole;
  document.getElementById("job-offer-recipient-email").value = email;
  document.getElementById("jobOfferApplicantFullName").textContent = fullName;
  document.getElementById("job-offer-subject").value = defaultSubject;

  // Email Template
  const emailTemplate = `
    <p>Dear ${fullName},</p>
    <p>Good day!</p>
    <p>Congratulations! We're happy to share that you've successfully passed the technical interview for the <strong>${jobRole}</strong> position.</p>
    <p>We'd love to invite you to a <strong>Job Offer Discussion</strong> via Zoom on <strong>[Date]</strong> at <strong>[Time]</strong>. This will be a great opportunity for us to talk about the next steps and what's ahead for you at Philkoei.</p>
    <p>Please confirm your availability for this schedule by replying to this email. Once confirmed, we'll send you the Zoom meeting credentials and a few details to help you prepare.</p>
    <p>We're excited to speak with you soon!</p>
    <p>Best regards,</p>
`;

  // Set Quill content
  if (quillInstanceJobOffer) {
    quillInstanceJobOffer.setContents([]);
    quillInstanceJobOffer.clipboard.dangerouslyPasteHTML(
      0,
      emailTemplate,
      "silent"
    );
    document.getElementById(hiddenInputIdJobOffer).value =
      quillInstanceJobOffer.root.innerHTML;
  }

  jobOfferModal.style.display = "flex";
}

/**
 * --- Opens the Initial Interview modal. ---
 * @param {string} appId - Application ID.
 * @param {string} email - Applicant Email.
 * @param {string} fullName - Applicant Full Name.
 * @param {string} jobRole - Job Role.
 */
function openInitialInterviewModal(appId, email, fullName, jobRole) {
  const defaultSubject = `Good News! Next Step (Initial Interview) for Your Application for ${jobRole} Position.`;

  // Set modal fields
  document.getElementById("appToInitialInterviewId").value = appId;
  document.getElementById("jobRoleToInitialInterview").value = jobRole;
  document.getElementById("initial-interview-recipient-email").value = email;
  document.querySelector(
    'strong[data-full-name="initialInterview"]'
  ).textContent = fullName;
  document.getElementById("initial-interview-subject").value = defaultSubject;

  // Email Template
  const emailTemplate = `
        <p>Dear ${fullName},</p>
        <p>Good day!</p>
        <p>Thank you for your interest in applying for the <strong>${jobRole}</strong> position at Philkoei International. After reviewing your CV, we're pleased to invite you to a screening interview.</p>
        <p>To help us learn more about your background and assess your competencies, kindly choose your preferred interview schedule via the link below:</p>
        <p><a href="https://calendly.com/careers-philkoei/screening-interview" target="_blank">https://calendly.com/careers-philkoei/screening-interview</a></p>
        <p>You will receive a confirmation email containing the interview details after scheduling.</p>
        <p>Thank you, and we look forward to meeting you soon!</p>
        <p>Best regards,</p>
    `;

  // Set Quill content
  if (quillInstanceInitial) {
    quillInstanceInitial.setContents([]);
    quillInstanceInitial.clipboard.dangerouslyPasteHTML(
      0,
      emailTemplate,
      "silent"
    );
    document.getElementById(hiddenInputIdInitial).value =
      quillInstanceInitial.root.innerHTML;
  }

  initialInterviewModal.style.display = "flex";
}

/**
 * --- Opens the Technical Interview modal. ---
 * @param {string} appId - Application ID.
 * @param {string} email - Applicant Email.
 * @param {string} fullName - Applicant Full Name.
 * @param {string} jobRole - Job Role.
 */
function openTechnicalInterviewModal(appId, email, fullName, jobRole) {
  const defaultSubject = `Good News! Next Step (Technical Interview) for Your Application for ${jobRole} Position.`;

  // Set modal fields
  document.getElementById("appToTechnicalInterviewId").value = appId;
  document.getElementById("jobRoleToTechnicalInterview").value = jobRole;
  document.getElementById("technical-interview-recipient-email").value = email;
  document.querySelector(
    'strong[data-full-name="technicalInterview"]'
  ).textContent = fullName;
  document.getElementById("technical-interview-subject").value = defaultSubject;

  // Email Template
  const emailTemplate = `
        <p>Dear ${fullName},</p>
        <p>Good day!</p>
        <p>Great news! You've successfully passed the initial screening for the <strong>${jobRole}</strong> position at Philkoei International.</p>
        <p>We'd love to invite you to the next step of the process: a <strong>Technical Interview</strong>, which will be held onsite on <strong>[Date]</strong> at <strong>[Time]</strong>. This will be a great opportunity for us to get to know you better and learn more about your skills and experiences.</p>
        <p>Kindly confirm your availability for the schedule by replying to this email. Once we receive your confirmation, we'll send another email with the reminders and details.</p>
        <p>Thank you, and we look forward to seeing you soon!</p>
        <p>Best regards,</p>
    `;

  // Set Quill content
  if (quillInstanceTechnical) {
    quillInstanceTechnical.setContents([]);
    quillInstanceTechnical.clipboard.dangerouslyPasteHTML(
      0,
      emailTemplate,
      "silent"
    );
    document.getElementById(hiddenInputIdTechnical).value =
      quillInstanceTechnical.root.innerHTML;
  }

  technicalInterviewModal.style.display = "flex";
}

/**
 * Initializes the Quill editor for the Job Offer modal.
 */
function initializeQuillEditorJobOffer() {
  if (!document.querySelector(`#${quillContainerIdJobOffer}`)) return;

  const toolbarOptions = [
    ["bold", "italic", "underline"],
    [{ list: "ordered" }, { list: "bullet" }],
    ["link"],
    ["clean"],
  ];

  quillInstanceJobOffer = new Quill(`#${quillContainerIdJobOffer}`, {
    modules: { toolbar: toolbarOptions },
    theme: "snow",
  });

  quillInstanceJobOffer.on("text-change", function () {
    document.getElementById(hiddenInputIdJobOffer).value =
      quillInstanceJobOffer.root.innerHTML;
  });
}

/**
 * --- Initializes the Quill editor for the Initial Interview modal. ---
 */
function initializeQuillEditorInitial() {
  if (!document.querySelector(`#${quillContainerIdInitial}`)) return;

  const toolbarOptions = [
    ["bold", "italic", "underline"],
    [{ list: "ordered" }, { list: "bullet" }],
    ["link"],
    ["clean"],
  ];

  quillInstanceInitial = new Quill(`#${quillContainerIdInitial}`, {
    modules: { toolbar: toolbarOptions },
    theme: "snow",
  });

  quillInstanceInitial.on("text-change", function () {
    document.getElementById(hiddenInputIdInitial).value =
      quillInstanceInitial.root.innerHTML;
  });
}

/**
 * --- Initializes the Quill editor for the Technical Interview modal. ---
 */
function initializeQuillEditorTechnical() {
  if (!document.querySelector(`#${quillContainerIdTechnical}`)) return;

  const toolbarOptions = [
    ["bold", "italic", "underline"],
    [{ list: "ordered" }, { list: "bullet" }],
    ["link"],
    ["clean"],
  ];

  quillInstanceTechnical = new Quill(`#${quillContainerIdTechnical}`, {
    modules: { toolbar: toolbarOptions },
    theme: "snow",
  });

  quillInstanceTechnical.on("text-change", function () {
    document.getElementById(hiddenInputIdTechnical).value =
      quillInstanceTechnical.root.innerHTML;
  });
}

/**
 * --- Generic AJAX handler for all status email forms ---
 * (Job Offer, Initial Interview, Technical Interview)
 */
async function handleStatusEmailFormSubmit(e) {
  e.preventDefault();

  const form = e.target;
  const newStatus = form.querySelector('input[name="newStatus"]').value;
  const confirmBtn = form.querySelector('button[type="submit"]');
  if (!confirmBtn) return;

  let originalButtonText = confirmBtn.textContent;

  // Get data from form
  const appId = form.querySelector('input[name="appid"]').value;
  const subject = form.querySelector('input[name="subject"]').value.trim();
  const customMessage = form.querySelector(
    'textarea[name="customMessage"]'
  ).value;

  if (
    !subject ||
    !customMessage ||
    customMessage.trim() === "<p><br></p>" ||
    customMessage.trim() === ""
  ) {
    alert("Subject and Email Message cannot be empty.");
    return;
  }

  confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
  confirmBtn.disabled = true;

  const formData = new FormData(form);

  try {
    const response = await fetch("update_applicant_status.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      window.location.reload();
    } else {
      alert("Failed to update status: " + result.message);
      confirmBtn.textContent = originalButtonText;
      confirmBtn.disabled = false;
    }
  } catch (error) {
    console.error("AJAX Error:", error);
    alert(
      "An unexpected error occurred while sending the email and updating the status."
    );
    confirmBtn.textContent = originalButtonText;
    confirmBtn.disabled = false;
  }
}

// ======================================================================
// 3. MAIN DOM CONTENT LOADED INITIALIZATION 🚀
// ======================================================================

document.addEventListener("DOMContentLoaded", function () {
  // --- 3.1. DOM Element Selectors ---

  // Modals
  const logoutModal = document.getElementById("logoutModal");
  const viewUserProfileModal = document.getElementById("viewUserProfileModal");
  const adminProfileModal = document.getElementById("adminUserProfileModal");
  const nullFieldInstruction = document.getElementById("nullFieldInstruction");
  const archiveApplicantModal = document.getElementById(
    "archiveApplicantModal"
  );
  const rejectApplicantModal = document.getElementById("rejectApplicantModal");

  // Buttons/Toggles
  const cancelLogout = document.getElementById("cancelLogout");
  const logoutBtnSidebar = document.getElementById("logoutBtn");
  const logoutBtnDropdown = document.getElementById("logoutDropdownBtn");
  const burger = document.querySelector(".burger");
  const sidebar = document.querySelector(".sidebar");
  const closeUserProfileModalBtn = document.getElementById(
    "closeUserProfileModal"
  );
  const profileModalCloseBtn = document.getElementById("profileModalCloseBtn");
  const viewProfileModalBtn = document.getElementById("viewProfileModalBtn");
  const closeAdminUserProfileModal = document.getElementById(
    "closeAdminUserProfileModal"
  );
  const adminProfileModalCloseBtn = document.getElementById(
    "adminProfileModalCloseBtn"
  );
  const editProfileBtn = document.getElementById("editProfileBtn");
  const saveProfileBtn = document.getElementById("saveProfileBtn");
  const changePhotoBtn = document.getElementById("changePhotoBtn");
  const adminImageInput = document.getElementById("adminImageInput");
  const applicantsTable = document.getElementById("applicantsTable");
  const cancelArchiveApplicantBtn = document.getElementById(
    "cancelArchiveApplicant"
  );
  const confirmArchiveApplicantBtn = document.getElementById(
    "confirmArchiveApplicant"
  );
  const archiveApplicantFullNameSpan = document.getElementById(
    "archiveApplicantFullName"
  );
  const archiveApplicantAppIdSpan = document.getElementById(
    "archiveApplicantAppId"
  );
  const appToArchiveIdInput = document.getElementById("appToArchiveId");
  const cancelRejectApplicantBtn = document.getElementById(
    "cancelRejectApplicant"
  );
  const confirmRejectApplicantBtn = document.getElementById(
    "confirmRejectApplicant"
  );
  const rejectionReasonSelect = document.getElementById(
    "rejectionReasonSelect"
  );
  const otherReasonField = document.getElementById("otherReasonField");
  const otherReasonText = document.getElementById("otherReasonText");
  const appToRejectIdInput = document.getElementById("appToRejectId");
  const userToRejectEmailInput = document.getElementById("userToRejectEmail");
  const userToRejectNameInput = document.getElementById("userToRejectName");
  const rejectApplicantFullNameSpan = document.getElementById(
    "rejectApplicantFullName"
  );
  const rejectApplicantAppIdSpan = document.getElementById(
    "rejectApplicantAppId"
  );
  const currentViewingJobRoleInput = document.getElementById(
    "currentViewingJobRole"
  );

  // Admin Profile Fields/Summary Elements
  const profileFields = {
    adminName: document.getElementById("viewAdminFullName"),
    adminEmail: document.getElementById("viewAdminEmail"),
  };
  const profileSummaryElements = {
    profileFullNameModal: document.getElementById("profileFullNameModal"),
    profileEmailSummaryModal: document.getElementById(
      "profileEmailSummaryModal"
    ),
    profileAvatarModal: document.getElementById("profileAvatarModal"),
  };

  // Admin Profile State Variables
  let isEditing = false;
  let originalAdminName = profileFields.adminName?.value || "";
  let originalAdminEmail = profileFields.adminEmail?.value || "";
  let originalAdminImageBase64 =
    document.getElementById("originalAdminImageBase64")?.value || "";
  let tempAdminImageFile = null;

  // --- 3.2. Core Utility Functions (Admin Profile & Modals) ---

  /**
   * Toggles the readOnly state of admin profile fields and button visibility.
   * @param {boolean} enable - True to enable editing, false to disable.
   */
  function toggleEditMode(enable) {
    isEditing = enable;
    if (profileFields.adminName) profileFields.adminName.readOnly = !enable;
    if (profileFields.adminEmail) profileFields.adminEmail.readOnly = !enable;

    if (editProfileBtn)
      editProfileBtn.style.display = enable ? "none" : "inline-block";
    if (saveProfileBtn)
      saveProfileBtn.style.display = enable ? "inline-block" : "none";
    if (changePhotoBtn)
      changePhotoBtn.style.display = enable ? "inline-block" : "none";

    if (adminProfileModalCloseBtn)
      adminProfileModalCloseBtn.textContent = enable ? "Cancel" : "Close";

    if (profileFields.adminName)
      profileFields.adminName.classList.toggle("editable-field", enable);
    if (profileFields.adminEmail)
      profileFields.adminEmail.classList.toggle("editable-field", enable);
  }

  /**
   * Restores the original image/initials in the modal and main profile area.
   */
  function restoreOriginalImageInModal() {
    const avatarElement = profileSummaryElements.profileAvatarModal;
    const mainAvatarElement = document.querySelector(
      ".user-profile .avatar-circle"
    );

    const updateAvatar = (element, sizeClass) => {
      if (!element) return;
      // First, remove existing image/initials
      if (element.tagName === "IMG") {
        const tempDiv = document.createElement("div");
        element.replaceWith(tempDiv);
        element = tempDiv;
      }
      element.innerHTML = "";
      element.className = sizeClass;

      if (originalAdminImageBase64) {
        const imgElement = document.createElement("img");
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
      profileSummaryElements.profileAvatarModal = updateAvatar(
        avatarElement,
        "avatar-circle-large"
      );
    }
    if (mainAvatarElement) {
      updateAvatar(mainAvatarElement, "avatar-circle");
    }
  }

  /**
   * Common function to close the Admin Profile modal, handling edit state cleanup.
   */
  const closeAdminProfile = () => {
    if (adminProfileModal.style.display === "flex" && isEditing) {
      if (profileFields.adminName)
        profileFields.adminName.value = originalAdminName;
      if (profileFields.adminEmail)
        profileFields.adminEmail.value = originalAdminEmail;
      restoreOriginalImageInModal();
      tempAdminImageFile = null;
    }
    if (adminProfileModal) adminProfileModal.style.display = "none";
    toggleEditMode(false);
  };

  function closeRejectModal() {
    if (rejectApplicantModal) rejectApplicantModal.style.display = "none";
    if (rejectionReasonSelect) rejectionReasonSelect.value = "";
    if (otherReasonText) otherReasonText.value = "";
    if (otherReasonField) otherReasonField.style.display = "none";
    if (confirmRejectApplicantBtn) {
      confirmRejectApplicantBtn.textContent = "Save";
      confirmRejectApplicantBtn.disabled = false;
    }
  }

  function openRejectModal(appId, name, email) {
    if (rejectApplicantModal) rejectApplicantModal.style.display = "flex";
    if (rejectApplicantFullNameSpan)
      rejectApplicantFullNameSpan.textContent = name;
    if (rejectApplicantAppIdSpan) rejectApplicantAppIdSpan.textContent = appId;
    if (appToRejectIdInput) appToRejectIdInput.value = appId;
    if (userToRejectEmailInput) userToRejectEmailInput.value = email;
    if (userToRejectNameInput) userToRejectNameInput.value = name;
  }

  // --- 3.3. Initializers and Core Logic ---

  // Initialize ALL Quill Editors
  initializeQuillEditorJobOffer();
  initializeQuillEditorInitial();
  initializeQuillEditorTechnical();

  // --- 3.4. Event Listeners ---

  // --- Logout Modal Handlers ---
  const openLogoutModal = (e) => {
    e.preventDefault();
    if (logoutModal) logoutModal.style.display = "flex";
  };

  logoutBtnSidebar?.addEventListener("click", openLogoutModal);
  logoutBtnDropdown?.addEventListener("click", openLogoutModal);

  cancelLogout?.addEventListener("click", function () {
    if (logoutModal) logoutModal.style.display = "none";
  });

  // --- Sidebar Toggle ---
  burger?.addEventListener("click", () => {
    sidebar?.classList.toggle("active");
    burger.classList.toggle("active");
  });

  // --- View Applicant Modal Closing Logic ---
  const closeApplicantModal = () => {
    if (viewUserProfileModal) viewUserProfileModal.style.display = "none";
  };

  closeUserProfileModalBtn?.addEventListener("click", closeApplicantModal);
  profileModalCloseBtn?.addEventListener("click", closeApplicantModal);

  // Close when clicking outside the applicant modal content
  window.addEventListener("click", function (event) {
    if (event.target == viewUserProfileModal) {
      closeApplicantModal();
    }
  });

  // --- Admin Profile Modal Handlers ---
  viewProfileModalBtn?.addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("user-dropdown").style.display = "none";
    originalAdminName = profileFields.adminName.value;
    originalAdminEmail = profileFields.adminEmail.value;
    originalAdminImageBase64 = document.getElementById(
      "originalAdminImageBase64"
    ).value;
    tempAdminImageFile = null;
    restoreOriginalImageInModal();
    toggleEditMode(false);
    if (adminProfileModal) adminProfileModal.style.display = "flex";
  });

  closeAdminUserProfileModal?.addEventListener("click", closeAdminProfile);
  adminProfileModalCloseBtn?.addEventListener("click", closeAdminProfile);

  editProfileBtn?.addEventListener("click", function () {
    toggleEditMode(true);
  });

  saveProfileBtn?.addEventListener("click", async function () {
    const newAdminName = profileFields.adminName.value.trim();
    const newAdminEmail = profileFields.adminEmail.value.trim();

    if (!newAdminName || !newAdminEmail) {
      alert("Full Name and Email Address cannot be empty.");
      return;
    }
    if (!/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/.test(newAdminEmail)) {
      alert("Please enter a valid email address.");
      return;
    }

    const formData = new FormData();
    formData.append("adminname", newAdminName);
    formData.append("adminemail", newAdminEmail);

    if (tempAdminImageFile) {
      formData.append("adminImage", tempAdminImageFile);
    } else if (originalAdminImageBase64 === "") {
      formData.append("adminImage", "");
    }

    try {
      const response = await fetch("update_admin_profile.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        window.location.reload();
      } else {
        alert("Error: " + data.message);
      }
    } catch (error) {
      console.error("Error updating profile:", error);
      alert("An error occurred while updating profile.");
    }
  });

  changePhotoBtn?.addEventListener("click", function () {
    if (adminImageInput) adminImageInput.click();
  });

  adminImageInput?.addEventListener("change", function () {
    if (this.files.length > 0) {
      tempAdminImageFile = this.files[0];
      const reader = new FileReader();
      reader.onload = function (e) {
        let avatarElement = profileSummaryElements.profileAvatarModal;
        if (!avatarElement) return;
        if (avatarElement.tagName === "DIV") {
          const imgElement = document.createElement("img");
          imgElement.src = e.target.result;
          imgElement.alt = "Admin Avatar";
          imgElement.classList.add("avatar-circle-large");
          avatarElement.replaceWith(imgElement);
          profileSummaryElements.profileAvatarModal = imgElement;
        } else {
          avatarElement.src = e.target.result;
        }
      };
      reader.readAsDataURL(tempAdminImageFile);
    }
  });

  // --- Applicants Table Universal Listener ---
  applicantsTable?.addEventListener("click", function (e) {
    // Listener for the Status Dropdown Icon
    const dropdownToggle = e.target.closest(".status-dropdown-toggle");
    if (dropdownToggle) {
      e.preventDefault();
      e.stopPropagation();
      toggleActionDropdown(dropdownToggle);
    }

    // Listener for the View Applicant Icon
    const viewApplicantIcon = e.target.closest(".view-applicant");
    if (viewApplicantIcon) {
      e.preventDefault();
      const userId = viewApplicantIcon.getAttribute("data-userid");
      const row = viewApplicantIcon.closest("tr");
      const statusTag = row.querySelector(".status-tag");
      const currentStatus = statusTag
        ? statusTag.textContent.trim()
        : "Pending";

      if (!userId) {
        alert("User ID not found for this applicant.");
        return;
      }

      fetch(`fetch_user_profile.php?userid=${userId}&postid=${currentPostId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            console.error("Error fetching user data:", data.error);
            alert("Could not load user profile: " + data.error);
            return;
          }

          document.querySelectorAll(".form-field").forEach((el) => {
            el.classList.remove("highlight-null");
          });
          if (nullFieldInstruction) nullFieldInstruction.style.display = "none";
          let hasNullFields = false;

          const profileAvatarDiv = document.getElementById("profileAvatar");
          profileAvatarDiv.innerHTML = "";
          if (data.image_base64) {
            const img = document.createElement("img");
            img.src = data.image_base64;
            img.alt = "User Avatar";
            img.classList.add("avatar-circle-large");
            profileAvatarDiv.appendChild(img);
          } else {
            const initials = data.firstname ? data.firstname.charAt(0) : "";
            profileAvatarDiv.textContent = initials.toUpperCase();
            profileAvatarDiv.style.backgroundColor = "#2f80ed";
          }

          const profileFullNameElement =
            document.getElementById("profileFullName");
          const profileEmailSummaryElement = document.getElementById(
            "profileEmailSummary"
          );
          const statusTagElement = document.getElementById("profileStatus");

          if (profileFullNameElement)
            profileFullNameElement.textContent = `${data.firstname || ""} ${
              data.lastname || ""
            }`.trim();
          if (profileEmailSummaryElement)
            profileEmailSummaryElement.textContent = data.email;

          if (statusTagElement) {
            const statusLower = currentStatus.toLowerCase();
            statusTagElement.textContent = `Application Status: ${currentStatus}`;
            statusTagElement.className = "profile-status";

            if (statusLower === "job offer") {
              statusTagElement.classList.add("status-job-offer");
            } else if (statusLower === "failed") {
              statusTagElement.classList.add("status-rejected");
            } else if (statusLower === "initial interview") {
              statusTagElement.classList.add("status-initial-interview");
            } else if (statusLower === "technical interview") {
              statusTagElement.classList.add("status-technical-interview");
            } else if (statusLower === "job offer accepted") {
              statusTagElement.classList.add("status-job-accepted");
            } else if (statusLower === "job offer rejected") {
              statusTagElement.classList.add("status-job-rejected");
            } else {
              statusTagElement.classList.add("status-pending");
            }
          }

          const calculatedAge = calculateAge(data.bday);
          const fieldsToCheck = {
            viewUserId: data.userid,
            viewFullName: `${data.firstname || ""} ${
              data.lastname || ""
            }`.trim(),
            viewDOB: data.bday,
            viewAge: calculatedAge,
            viewEmail: data.email,
            viewContact: data.contact,
            viewEduLvl: data.educlvl,
            viewCourse: data.course,
            viewSchool: data.school,
            viewSignupDate: data.joined_date_formatted,
            rolematch: data.rolematch || "",
            rolepercentage: data.rolepercentage || "",
          };

          for (const [id, value] of Object.entries(fieldsToCheck)) {
            const inputElement = document.getElementById(id);
            if (inputElement) {
              const displayValue = value || "N/A";
              inputElement.value = displayValue;
              const isMissing =
                value === null || value === "" || value === "0000-00-00";
              if (isMissing) {
                const parentFormField = inputElement.closest(".form-field");
                if (parentFormField) {
                  parentFormField.classList.add("highlight-null");
                  hasNullFields = true;
                }
              }
            }
          }

          if (hasNullFields && nullFieldInstruction) {
            nullFieldInstruction.style.display = "block";
          }
          if (viewUserProfileModal) viewUserProfileModal.style.display = "flex";
        })
        .catch((error) => {
          console.error("Fetch error:", error);
          alert("An error occurred while fetching applicant data.");
        });
    }

    // --- Handler for 'Job Offer' and 'Failed' ---
    if (e.target.classList.contains("update-applicant-status")) {
      e.preventDefault();
      const linkElement = e.target;
      const appId = linkElement.getAttribute("data-appid");
      const newStatus = linkElement.getAttribute("data-new-status");

      const dropdown = linkElement.closest(".action-dropdown");
      if (dropdown) dropdown.style.display = "none";

      const row = linkElement.closest("tr");
      const name = row.cells[2].textContent.trim();
      const email = row.cells[3].textContent.trim();
      const jobRole = currentViewingJobRoleInput.value;

      if (newStatus === "Job Offer") {
        openJobOfferModal(appId, email, name, jobRole);
      } else if (newStatus === "Failed") {
        openRejectModal(appId, name, email);
      }
    }

    // --- Handler for 'Initial Interview' and 'Technical Interview' ---
    const statusModalLink = e.target.closest(".open-status-modal");
    if (statusModalLink) {
      e.preventDefault();
      const linkElement = statusModalLink;
      const appId = linkElement.getAttribute("data-appid");
      const newStatus = linkElement.getAttribute("data-new-status");
      const name = linkElement.getAttribute("data-name");
      const email = linkElement.getAttribute("data-email");

      const dropdown = linkElement.closest(".action-dropdown");
      if (dropdown) dropdown.style.display = "none";

      const jobRole = currentViewingJobRoleInput.value;

      if (newStatus === "Initial Interview") {
        openInitialInterviewModal(appId, email, name, jobRole);
      } else if (newStatus === "Technical Interview") {
        openTechnicalInterviewModal(appId, email, name, jobRole);
      }
    }

    // Listener for the Archive Applicant Icon
    const archiveApplicantIcon = e.target.closest(".archive-applicant");
    if (archiveApplicantIcon) {
      e.preventDefault();
      const appId = archiveApplicantIcon.getAttribute("data-appid");
      const row = archiveApplicantIcon.closest("tr");
      if (!appId || !row) {
        alert("Error: Application ID or table row not found.");
        return;
      }
      const nameCell = row.querySelector("td:nth-child(3)");
      const fullName = nameCell ? nameCell.textContent.trim() : "N/A";
      if (archiveApplicantFullNameSpan)
        archiveApplicantFullNameSpan.textContent = fullName;
      if (archiveApplicantAppIdSpan)
        archiveApplicantAppIdSpan.textContent = appId;
      if (appToArchiveIdInput) appToArchiveIdInput.value = appId;
      if (archiveApplicantModal) archiveApplicantModal.style.display = "flex";
    }
  });

  // --- Archive Applicant Modal Handlers ---
  const closeArchiveModal = () => {
    if (archiveApplicantModal) archiveApplicantModal.style.display = "none";
  };

  cancelArchiveApplicantBtn?.addEventListener("click", closeArchiveModal);

  window.addEventListener("click", function (event) {
    if (event.target == archiveApplicantModal) {
      closeArchiveModal();
    }
  });

  confirmArchiveApplicantBtn?.addEventListener("click", async function () {
    const appId = appToArchiveIdInput.value;
    if (!appId) {
      alert("Error: Application ID not found.");
      return;
    }
    const formData = new FormData();
    formData.append("appid", appId);

    try {
      const response = await fetch("archive_applicant.php", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();
      if (result.success) {
        window.location.reload();
      } else {
        alert("Failed to archive application: " + result.message);
      }
    } catch (error) {
      console.error("AJAX Error:", error);
      alert("An unexpected error occurred during the archive process.");
    }
  });

  // --- Universal Keydown and Click Outside Handlers ---

  window.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      if (logoutModal) logoutModal.style.display = "none";
      closeApplicantModal();
      closeAdminProfile();
      closeRejectModal();
      closeJobOfferModal();
      closeInitialInterviewModal();
      closeTechnicalInterviewModal();
      const userDropdown = document.getElementById("user-dropdown");
      if (userDropdown) userDropdown.style.display = "none";
    }
  });

  window.addEventListener("click", function (event) {
    if (event.target == adminProfileModal) {
      closeAdminProfile();
    }
    if (event.target == rejectApplicantModal) {
      closeRejectModal();
    }
    if (event.target == jobOfferModal) {
      closeJobOfferModal();
    }
    if (event.target == initialInterviewModal) {
      closeInitialInterviewModal();
    }
    if (event.target == technicalInterviewModal) {
      closeTechnicalInterviewModal();
    }
  });

  // --- Event Listeners for Rejection Modal ---
  rejectionReasonSelect?.addEventListener("change", function () {
    if (this.value === "Other reason") {
      otherReasonField.style.display = "block";
      otherReasonText.setAttribute("required", "required");
    } else {
      otherReasonField.style.display = "none";
      otherReasonText.removeAttribute("required");
    }
  });

  cancelRejectApplicantBtn?.addEventListener("click", closeRejectModal);

  confirmRejectApplicantBtn?.addEventListener("click", async function () {
    const appId = appToRejectIdInput.value;
    const newStatus = "Failed";
    const rejectionReason = rejectionReasonSelect.value;
    let otherReasonDetail = otherReasonText.value.trim();

    if (!rejectionReason || rejectionReason === "") {
      alert("Please select a rejection reason.");
      return;
    }
    if (rejectionReason === "Other reason" && !otherReasonDetail) {
      alert('Please provide details for the "Other reason".');
      return;
    }

    const finalReason =
      rejectionReason === "Other reason"
        ? `Other reason: ${otherReasonDetail}`
        : rejectionReason;

    confirmRejectApplicantBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Saving...';
    confirmRejectApplicantBtn.disabled = true;

    const formData = new FormData();
    formData.append("appid", appId);
    formData.append("newStatus", newStatus);
    formData.append("rejectionReason", finalReason);

    try {
      const response = await fetch("update_applicant_status.php", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      if (result.success) {
        window.location.reload();
      } else {
        alert("Failed to update status: " + result.message);
      }
    } catch (error) {
      console.error("AJAX Error:", error);
      alert(
        "An unexpected error occurred while updating the status. \n\nREASON: " +
          error.message
      );
    } finally {
      confirmRejectApplicantBtn.textContent = "Save";
      confirmRejectApplicantBtn.disabled = false;
    }
  });

  // --- Event Listeners for ALL Email Status Modals ---

  // Close/Cancel buttons
  closeJobOfferModalBtn?.addEventListener("click", closeJobOfferModal);
  cancelJobOfferModalBtn?.addEventListener("click", closeJobOfferModal);

  // Generic listener for modal close/cancel buttons
  document
    .querySelectorAll(
      ".btn-cancel[data-modal-target], .close-button[data-modal-target]"
    )
    .forEach((btn) => {
      btn.addEventListener("click", function () {
        const targetId = this.getAttribute("data-modal-target");
        if (targetId === "initialInterviewModal") {
          closeInitialInterviewModal();
        } else if (targetId === "technicalInterviewModal") {
          closeTechnicalInterviewModal();
        }
      });
    });

  // Form submission handlers
  jobOfferForm?.addEventListener("submit", handleStatusEmailFormSubmit);
  initialInterviewForm?.addEventListener("submit", handleStatusEmailFormSubmit);
  technicalInterviewForm?.addEventListener(
    "submit",
    handleStatusEmailFormSubmit
  );

  // --- Generate Report Button ---
  document.querySelectorAll(".generateApplicantReportBtn").forEach((button) => {
    button.addEventListener("click", function () {
      const postId = this.getAttribute("data-postid");
      if (postId) {
        window.open("applicantreport.php?postid=" + postId, "_blank");
      } else {
        alert("Error: No Job Post ID found.");
      }
    });
  });
});
