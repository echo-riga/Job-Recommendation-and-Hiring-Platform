<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBFITSYSTEM</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/editprofile.css">
</head>
<body>
<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

$userid = $_SESSION['userid'];

$query = "SELECT firstname, lastname, email, bday, contact, educlvl, course, school, image FROM users WHERE userid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email, $bday, $contact, $educlvl, $course, $school, $image);
$stmt->fetch();
$stmt->close();

$profileImage = !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : '';
$fullname = trim($firstname . ' ' . $lastname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstname = $_POST['firstname'] ?? '';
  $lastname = $_POST['lastname'] ?? '';
  $email = $_POST['email'] ?? '';
  $bday = $_POST['bday'] ?? null;
  $educlvl = $_POST['educlvl'] ?? '';
  $course = $_POST['course'] ?? '';
  $school = $_POST['school'] ?? '';
  $contact = $_POST['contact'] ?? '';


  if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
    $stmt = $con->prepare("UPDATE users SET firstname=?, lastname=?, email=?, bday=?, contact=?, educlvl=?, course=?, school=?, image=NULL WHERE userid=?");
    $stmt->bind_param("ssssssssi", $firstname, $lastname, $email, $bday, $contact, $educlvl, $course, $school, $userid);
  } elseif (!empty($_FILES['photo']['tmp_name'])) {
    $imageData = file_get_contents($_FILES['photo']['tmp_name']);
    $stmt = $con->prepare("UPDATE users SET firstname=?, lastname=?, email=?, bday=?, contact=?, educlvl=?, course=?, school=?, image=? WHERE userid=?");
    $stmt->bind_param("sssssssssi", $firstname, $lastname, $email, $bday, $contact, $educlvl, $course, $school, $imageData, $userid);
  } else {
    $stmt = $con->prepare("UPDATE users SET firstname=?, lastname=?, email=?, bday=?, contact=?, educlvl=?, course=?, school=? WHERE userid=?");
    $stmt->bind_param("ssssssssi", $firstname, $lastname, $email, $bday, $contact, $educlvl, $course, $school, $userid);
  }

if ($stmt->execute()) {
  $updateStmt = $con->prepare("UPDATE users SET profile_completed = 1 WHERE userid = ?");
  $updateStmt->bind_param("i", $userid);
  $updateStmt->execute();
  $updateStmt->close();

  $_SESSION['profile_show_full'] = true;
  header("Location: profile.php");
  exit();
}


  $stmt->close();
}
?>

<?php include 'header.php'; ?>
 <div class="background-wrapper"></div>
   <div style="max-width: 720px; width: 100%; margin-top: 10rem; height:10rem;">
    <a href="../php/profile.php">&larr; Back to Profile</a>
    <h1>Edit Profile</h1>
    <p class="subtitle">Update your personal information</p>
  </div>
 <main>

  <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
  <div class="profile-pic-edit">
    <input type="file" name="photo" id="photoUpload" accept="image/*" hidden />
    <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0" />
    <label for="photoUpload" class="avatar" id="avatarPreview">
      <?php if (!empty($profileImage)): ?>
        <img src="<?= $profileImage ?>" alt="Profile Image" style="width: 100%; height: 100%; object-fit: cover;" />
      <?php else: ?>
        <span style="font-size: 1.2rem;">JS</span>
      <?php endif; ?>
    </label>
    <div style="margin-top: 10px;">
      <button onclick="document.getElementById('photoUpload').click(); return false;">Change Photo</button>
      <button type="button" onclick="removePhoto()">Remove Photo</button>
    </div>
  </div>

    <section>
      <div class="section-title"><i class="fa-solid fa-user"></i> Personal Information</div>
      <div class="form-row">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required />
        </div>
        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required />
        </div>
      </div>
      <div class="form-group">
        <label>Full Name (Auto-generated)</label>
         <input type="text" id="fullName" value="<?= htmlspecialchars($fullname) ?>" readonly />
      </div>
    </section>

    <section>
      <div class="section-title"><i class="fa-solid fa-envelope"></i> Contact Information</div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required />
      </div>
      <div class="form-group">
        <label for="contact">Contact Number</label>
    <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($contact) ?>" required />
     </div>
    </section>

    <section>
      <div class="section-title"><i class="fa-solid fa-cake-candles"></i> Date of Birth</div>
      <div class="form-row">
        <div class="form-group">
          <label for="dob">Birthday</label>
          <input type="date" id="dob" name="bday" value="<?= $bday ?>" required />
        </div>
        <div class="form-group">
          <label>Age (Auto-calculated)</label>
          <input type="text" id="age" name="age" readonly />
        </div>
      </div>
    </section>

    <section>
      <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> Educational Background</div>
      <div class="form-row">
        <div class="form-group">
          <label for="education">Educational Level</label>
          <select id="education" name="educlvl">
            <option value="High School" <?= $educlvl === 'High School' ? 'selected' : '' ?>>High School</option>
          <option value="College/University" <?= $educlvl === 'College/University' ? 'selected' : '' ?>>College/University</option>
          <option value="Postgraduate" <?= $educlvl === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
          </select>
        </div>
        <div class="form-group">
          <label for="course">Course/Strand</label>
           <input type="text" id="course" name="course" value="<?= htmlspecialchars($course) ?>" required />
        </div>
      </div>
      <div class="form-group">
        <label for="school">Institution/School Name</label>
         <input type="text" id="school" name="school" value="<?= htmlspecialchars($school) ?>" required />
      </div>
    </section>


    <div class="buttons">
      <button class="cancel">Cancel</button>
   <button class="btn-open-modal-success">Save Changes</button>
    </div>
    </section>

  </main>
      <div class="modal-success-backdrop" id="modal-success">
    <div class="modal-success">
      <div class="modal-success-icon">💾</div>
      <h2>Save Changes</h2>
      <p>Do you want to save this?</p>
      <div class="modal-success-buttons">
        <button class="modal-success-btn modal-success-btn-cancel" id="modal-success-cancel">Cancel</button>
         <button class="modal-success-btn modal-success-btn-save" id="modal-success-confirm">✔ Save</button>
      </div>
    </div>
  </div>
  <div class="modal-age-backdrop" id="modal-age-warning" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:white; padding:2rem; border-radius:10px; max-width:400px; text-align:center;">
    <h4>Invalid Age</h4>
    <p>Age must be between 5 and 120 years old.</p>
    <div style="margin-top:1rem;">
      <button id="modal-age-ok" class="btn btn-danger">Log Out</button>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const btn = document.querySelector(".dropdown-btn");
  const dropdown = document.querySelector(".dropdown-content");
  btn?.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    dropdown?.classList.toggle("show");
  });
  document.addEventListener("click", function (e) {
    if (!btn?.contains(e.target) && !dropdown?.contains(e.target)) {
      dropdown?.classList.remove("show");
    }
  });

  const allLinks = document.querySelectorAll(".navbar a, .dropdown-content a");
  function setActive(link) {
    allLinks.forEach((l) => l.classList.remove("active-link"));
    link.classList.add("active-link");
  }
  allLinks.forEach((link) =>
    link.addEventListener("click", () => setActive(link))
  );
  const currentHash = window.location.hash;
  allLinks.forEach((link) => {
    if (link.getAttribute("href") === currentHash) setActive(link);
  });

  window.toggleProfileDropdown = function () {
    document.getElementById("profileMenu")?.classList.toggle("show");
  };
  window.addEventListener("click", function (event) {
    if (!event.target.closest(".profile-dropdown")) {
      document.getElementById("profileMenu")?.classList.remove("show");
    }
  });

  const burger = document.querySelector(".burger");
  const sidebar = document.querySelector(".sidebar");
  burger?.addEventListener("click", () => {
    sidebar?.classList.toggle("active");
    burger.classList.toggle("active");
  });

  const logoutModal = document.getElementById("logoutModal");
  const cancelLogout = document.getElementById("cancelLogout");
  const logoutBtnSidebar = document.getElementById("logoutBtn");
  const logoutBtnDropdown = document.getElementById("logoutDropdownBtn");
  function openLogoutModal(e) {
    e.preventDefault();
    logoutModal.style.display = "flex";
  }
  logoutBtnSidebar?.addEventListener("click", openLogoutModal);
  logoutBtnDropdown?.addEventListener("click", openLogoutModal);
  cancelLogout?.addEventListener("click", () => {
    logoutModal.style.display = "none";
  });

  const firstName = document.getElementById("firstName");
  const lastName = document.getElementById("lastName");
  const fullName = document.getElementById("fullName");
  function updateFullName() {
    fullName.value = `${firstName.value} ${lastName.value}`.trim();
  }
  updateFullName();
  firstName.addEventListener("input", updateFullName);
  lastName.addEventListener("input", updateFullName);

 const dob = document.getElementById("dob");
const age = document.getElementById("age");

function calculateAndSetAge() {
  if (!dob.value) return;
  const birthday = new Date(dob.value);
  const today = new Date();
  let userAge = today.getFullYear() - birthday.getFullYear();
  const m = today.getMonth() - birthday.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthday.getDate())) {
    userAge--;
  }
  age.value = userAge; 
}

dob.addEventListener("change", calculateAndSetAge);

if (dob.value) calculateAndSetAge();
  const avatarPreview = document.getElementById("avatarPreview");
  const fileInput = document.getElementById("photoUpload");
  const removePhotoFlag = document.getElementById("removePhotoFlag");
  fileInput.addEventListener("change", function () {
    const file = this.files[0];
    if (file && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = function (e) {
        avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />`;
        removePhotoFlag.value = "0";
      };
      reader.readAsDataURL(file);
    }
  });


  const modalSuccess = document.getElementById("modal-success");
  const openModalBtn = document.querySelector(".btn-open-modal-success");
  const closeBtn = document.getElementById("modal-success-cancel");
  const saveBtn = document.getElementById("modal-success-confirm");
  const form = document.querySelector("form");

  openModalBtn.addEventListener("click", function (e) {
    e.preventDefault();
    if (validateForm()) {
      modalSuccess.style.display = "flex";
    }
  });

  closeBtn.addEventListener("click", () => {
    modalSuccess.style.display = "none";
  });

  saveBtn.addEventListener("click", () => {
    modalSuccess.style.display = "none";
    form.submit(); 
  });
});

function validateForm() {
  const email = document.getElementById("email").value;
  const contact = document.getElementById("contact").value;
  const ageInput = document.getElementById("age"); 
  const ageValue = parseInt(ageInput.value, 10); 

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const contactRegex = /^\d{10,15}$/;

  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    return false;
  }

  if (!contactRegex.test(contact)) {
    alert("Please enter a valid contact number (10–15 digits).");
    return false;
  }

  if (isNaN(ageValue) || ageValue < 5 || ageValue > 120) {
    document.getElementById("modal-age-warning").style.display = "flex";
    return false;
  }

  return true;
}
document.getElementById("modal-age-ok").addEventListener("click", function () {
  document.getElementById("modal-age-warning").style.display = "none";
});

function removePhoto() {
  const avatarPreview = document.getElementById("avatarPreview");
  const removePhotoFlag = document.getElementById("removePhotoFlag");
  avatarPreview.innerHTML = '<span style="font-size: 1.2rem;">JS</span>';
  removePhotoFlag.value = "1";
  document.getElementById("photoUpload").value = "";
}
</script>

</body>
</html>