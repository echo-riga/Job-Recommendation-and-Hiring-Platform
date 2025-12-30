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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/viewjob.css?v=7">
  </head>
  <body>
  <?php
  include 'check_maintenance.php';
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  include_once 'database.php';

  $isLoggedIn = isset($_SESSION['userid']);
  $user_id = $_GET['user_id'] ?? 0;
  $postid = $_GET['postid'] ?? 0;

  $job = null;

  if ($postid !== null && $postid !== '') {
    $stmt = $con->prepare("SELECT * FROM jobposting WHERE postid = ?");
    $stmt->bind_param("i", $postid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $job = $result->fetch_assoc();
    }
    $stmt->close();
  }

  include 'header.php';
  ?>

  <section class="viewjob">
    <div class="jobview-container">
      <?php if ($job): ?>
        <div class="view-header">
          <h1 class="job-title"><?= htmlspecialchars($job['postjobrole']); ?></h1>
          <div class="job-company">Philkoei International, Inc.</div>
          <div class="job-location"><?= htmlspecialchars($job['postaddress']); ?></div>
          <div class="job-type"><strong>Type:</strong> <?= htmlspecialchars($job['posttype']); ?></div>
          <div class="job-work"><strong>Work Setup:</strong> <?= htmlspecialchars($job['postworksetup']); ?></div>
            <div class="job-limit"><strong>Hiring Slots:</strong> <?= htmlspecialchars($job['postapplicantlimit']); ?></div>
        </div>

        <div class="job-section">
          <h2 class="section-title">Job Summary</h2>
          <div class="section-content">
            <p><?= $job['postsummary']; ?></p>
          </div>
        </div>

        <div class="job-section">
          <h2 class="section-title">Specific Duties and Responsibilities:</h2>
          <div class="section-content"><?= $job['postresponsibilities']; ?></div>


        <div class="job-section">
          <h2 class="section-title">Job Specifications</h2>
          <div class="section-content"><?= $job['postspecification']; ?></div>

        <div class="job-section">
          <h2 class="section-title">Experience</h2>
          <div class="section-content"><?= nl2br(htmlspecialchars($job['postexperience'])); ?></div>
        </div>

        <div class="salary-benefits-grid">
          <div class="salary-section">
            <h2 class="section-title">Salary</h2>
            <div class="salary-amount">₱<?= number_format($job['postsalary'], 2); ?></div>
          </div>

          <div class="benefits-section">
            <h2 class="section-title">Job Dates</h2>
            <ul>
              <li><strong>Posted:</strong> <?= htmlspecialchars($job['postdate']); ?></li>
              <li><strong>Deadline:</strong> <?= htmlspecialchars($job['postdeadline']); ?></li>
            </ul>
          </div>
        </div>
      <?php else: ?>
        <div class="no-job-found">
          <h2>No Job Found</h2>
          <p>The job you’re looking for doesn’t exist or has been removed.</p>
        </div>
      <?php endif; ?>

      <div class="backbutton">
        <button class="back-btn" onclick="goBack()">← Back</button>
        <?php if ($job): ?>
        <button type="button" class="submit-cv-btn" onclick="openCVModal(<?php echo $job['postid']; ?>)">
          Submit Your CV
        </button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- CV Modal -->
  <div id="cvModal" class="modal-overlay" data-postid="">
    <div class="modal-container">
      <h2 class="modal-title">Submit Your CV</h2>
      <div class="upload-area" onclick="document.getElementById('fileInput').click()">
        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <div class="upload-text">Click to upload or drag and drop</div>
        <div class="upload-subtext">PDF, DOC, DOCX (max 5MB)</div>
      </div>
      <input type="file" id="fileInput" class="file-input" accept=".pdf,.doc,.docx" onchange="handleFileSelect(event)">
      <div id="uploadedFile" class="uploaded-file">
        <i class="fas fa-file-alt"></i>
        <span class="file-name" id="fileName"></span>
        <button type="button" class="remove-file" onclick="removeFile()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="error-message" id="fileError"></div>
      <div class="modal-buttons">
        <button type="button" class="btn-cancel" onclick="closeCVModal()">Cancel</button>
        <button type="button" class="btn-submit" onclick="submitApplication()">Submit Application</button>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div id="successModal" class="modal-overlay success-modal">
    <div class="modal-container">
      <div class="success-icon">✅</div>
      <h2 class="success-title">Application Submitted!</h2>
      <p class="success-message">Thank you for applying. We will review your CV and get back to you soon.</p>
      <button class="btn-submit" onclick="closeSuccessModal()">OK</button>
    </div>
  </div>

  <!-- Already Applied Modal -->
  <div id="alreadyAppliedModal" class="appliedmodal-overlay">
    <div class="appliedmodal-container">
      <h2>You have already applied for this job.</h2>
      <div class="appliedmodal-buttons">
        <button type="button" class="btn-cancel" onclick="closeAlreadyAppliedModal()">OK</button>
      </div>
    </div>
  </div>

  <script>
  let uploadedFile = null;


  function openCVModal(postId) {
    console.log('Opening CV modal for postId:', postId); 
    
    fetch('check_application.php?postid=' + postId)
      .then(res => res.json())
      .then(data => {
        if (data.alreadyApplied) {
          document.getElementById('alreadyAppliedModal').classList.add('active');
          document.body.style.overflow = 'hidden';
        } else {
          const modal = document.getElementById('cvModal');
          modal.setAttribute('data-postid', postId);
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      })
      .catch(err => {
        console.error('Check application error:', err);
        const modal = document.getElementById('cvModal');
        modal.setAttribute('data-postid', postId);
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
}

  function closeCVModal() {
    document.getElementById('cvModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    resetForm();
  }
  function closeAlreadyAppliedModal() {
    document.getElementById('alreadyAppliedModal').classList.remove('active');
    document.body.style.overflow = 'auto';
  }
  function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
      ];
      if (!allowedTypes.includes(file.type)) {
        showError('Please upload a PDF, DOC, or DOCX file.');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showError('File size must be less than 5MB.');
        return;
      }
      uploadedFile = file;
      document.getElementById('fileName').textContent = file.name;
      document.getElementById('uploadedFile').classList.add('show');
      hideError();
    }
  }
  function removeFile() {
    uploadedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadedFile').classList.remove('show');
  }

function submitApplication() {
    if (!uploadedFile) {
        showError('Please upload your CV to continue.');
        return;
    }

    const modal = document.getElementById('cvModal');
    const postId = modal.getAttribute('data-postid');

    console.log('Submitting application for postId:', postId);

    const formData = new FormData();
    formData.append('resume', uploadedFile);
    formData.append('postid', postId);

    fetch('submit_application.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log('submit_application.php response:', data);
        if (data.success) {
            closeCVModal();
            document.getElementById('successModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            showError(data.message || 'Something went wrong.');
        }
    })
    .catch(err => {
        console.error('Submit application error:', err);
        showError('An error occurred. Please try again.');
    });
}
  function showError(msg) {
    const el = document.getElementById('fileError');
    el.textContent = msg;
    el.classList.add('show');
  }
  function hideError() {
    document.getElementById('fileError').classList.remove('show');
  }

  function resetForm() {
    uploadedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadedFile').classList.remove('show');
    hideError();
  }

  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
      if (e.target.id === 'cvModal') closeCVModal();
      if (e.target.id === 'alreadyAppliedModal') closeAlreadyAppliedModal();
      if (e.target.id === 'successModal') closeSuccessModal();
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (document.getElementById('cvModal').classList.contains('active')) closeCVModal();
      else if (document.getElementById('alreadyAppliedModal').classList.contains('active')) closeAlreadyAppliedModal();
      else if (document.getElementById('successModal').classList.contains('active')) closeSuccessModal();
    }
  });

  function goBack() {
    window.history.back();
  }
  </script>
  </body>
  </html>
