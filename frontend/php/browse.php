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
  <link rel="stylesheet" href="../css/browse.css?v=3">
</head>
<style>
  .recommended-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
  }

  .recommended-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .recommended-button:active {
    transform: translateY(0);
  }
</style>
<body>
<?php

include 'check_maintenance.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once 'database.php';
include 'header.php'; 
$logoutSuccess = isset($_GET['logout']) && $_GET['logout'] === 'success';
$isLoggedIn = isset($_SESSION['userid']);
$userid = $isLoggedIn ? $_SESSION['userid'] : 0; 


$userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;

$query = "SELECT postid, postsummary, postjobrole, postspecification, postexperience,postworksetup, postresponsibilities, postapplicantlimit,
                 postsalary, posttype, postdate, postdeadline, poststatus, postaddress 
          FROM jobposting WHERE poststatus='Open' ORDER BY postdate DESC";
$result = mysqli_query($con, $query);

$jobs = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = [
            'id' => $row['postid'],
            'title' => $row['postjobrole'],
            'company' => 'Philkoei International, Inc.',
            'location' => $row['postaddress'],
            'type' => strtolower($row['posttype']),
            'salary' => '₱' . number_format($row['postsalary'], 0) . ' / month',
            'salaryMin' => (float)$row['postsalary'],
            'salaryMax' => (float)$row['postsalary'],
            'summary' => $row['postsummary']
        ];
    }
}
?>

 
<div class="overlay" id="registerOverlay" style="display:none;">
    <div class="register-modal">
      <div class="close-btn" id="closeRegisterModal">&times;</div>
      <div class="icon">
        <svg viewBox="0 0 24 24">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
      </div>
      <h2>Hold On! Log In to Access Your Test</h2>
      <p>Please sign in to start your personalized career test and unlock tailored results just for you.</p>
      <button class="login" onclick="location.href='../php/login.php'">🔒 Log In Now</button>
      <div class="create-account" onclick="location.href='../php/login.php'">Create Account</div>
      <div class="secure">
        <svg viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Secure & Private
      </div>
    </div>
  </div>

<section class="viewjobmatch">
    <div class="job-match-container">
        <div class="job-match-header">
            <h1 class="job-match-title">ALL AVAILABLE IT JOB POSITIONS</h1>
            <h2 class="job-match-subtitle">COMPLETE LISTING FROM Philkoei International, Inc.</h2>
            <div class="title-divider"></div>
        </div>
  
        <div class="search-section">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search jobs by title, company, or location...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div class="controls-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Jobs</button>
                <button class="filter-btn" data-filter="full-time">Full-Time</button>
                <button class="filter-btn" data-filter="part-time">Part-Time</button>
                <button class="filter-btn" data-filter="internship">Internship</button>
                <button class="recommended-button" onclick="loadRecommendedJobs()">
                  <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Recommended for You
                </button>
            </div>
            
            <div class="sort-container">
                <select id="sortSelect" class="sort-select">
                    <option value="">Sort by Salary</option>
                    <option value="salary-asc">Salary: Low to High</option>
                    <option value="salary-desc">Salary: High to Low</option>
                </select>
            </div>
        </div>
        
        <div class="job-grid" id="jobGrid">
            <!-- Job cards will be populated by JavaScript -->
        </div>
        
        <div class="pagination-section" id="paginationSection">
            <button id="prevBtn" class="pagination-btn" onclick="changePage(-1)">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <div class="page-numbers" id="pageNumbers"></div>
            <button id="nextBtn" class="pagination-btn" onclick="changePage(1)">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<script>
   const jobsData = <?php echo json_encode($jobs, JSON_PRETTY_PRINT); ?>;

  let filteredJobs = [...jobsData];
  let currentPage = 1;
  const jobsPerPage = 6;
  let currentFilter = 'all';
  let currentSort = '';

  function createJobCard(job) {
    const typeClasses = {
      'full time': 'badge-full-time',
      'part time': 'badge-part-time',
      'internship': 'badge-part-time'
    };

    const typeLabels = {
      'full time': 'Full-Time',
      'part time': 'Part-Time',
      'internship': 'Internship'
    };

    const jobType = job.type?.toLowerCase().trim();

    return `
      <div class="job-card">
        <h3 class="job-title">${job.title}</h3>
        <div class="job-company">${job.company}</div>
        <div class="job-location">${job.location}</div>
        <span class="job-type-badge ${typeClasses[jobType] || ''}">
          ${typeLabels[jobType] || job.type}
        </span>
        <div class="job-salary">${job.salary}</div>
        <p class="job-summary">${job.summary}</p>
        <button class="view-details-btn" 
          onclick="window.location.href='viewjob.php?user_id=<?php echo $userid; ?>&postid=${job.id}'">
          View Details
        </button>
      </div>
    `;
  }

  function filterJobs(searchTerm = '', filterType = currentFilter, sortType = currentSort) {
    const normalize = str => str.toLowerCase().trim().replace(/\s+/g, '-'); // normalize spaces/dashes

    let filtered = jobsData.filter(job => {
      const matchesSearch =
        !searchTerm ||
        job.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        job.company.toLowerCase().includes(searchTerm.toLowerCase()) ||
        job.location.toLowerCase().includes(searchTerm.toLowerCase());

      const jobTypeNormalized = normalize(job.type);

      const matchesFilter =
        filterType === 'all' ||
        jobTypeNormalized === normalize(filterType);

      return matchesSearch && matchesFilter;
    });


    if (sortType === 'salary-asc') {
      filtered.sort((a, b) => a.salaryMin - b.salaryMin);
    } else if (sortType === 'salary-desc') {
      filtered.sort((a, b) => b.salaryMax - a.salaryMax);
    }

    filteredJobs = filtered;
    currentPage = 1;
    renderJobs();
    updatePagination();
  }

  function renderJobs() {
    const jobGrid = document.getElementById('jobGrid');
    const startIndex = (currentPage - 1) * jobsPerPage;
    const endIndex = startIndex + jobsPerPage;
    const currentJobs = filteredJobs.slice(startIndex, endIndex);

    if (currentJobs.length === 0) {
      jobGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #64748b;">
          <h3>No jobs found</h3>
          <p>Try adjusting your search or filter criteria.</p>
        </div>`;
    } else {
      jobGrid.innerHTML = currentJobs.map(job => createJobCard(job)).join('');
    }
  }

  function updatePagination() {
    const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
    const paginationSection = document.getElementById('paginationSection');
    const pageNumbers = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (totalPages <= 1) {
      paginationSection.style.display = 'none';
      return;
    }

    paginationSection.style.display = 'flex';
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;

    pageNumbers.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `page-number ${i === currentPage ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.onclick = () => goToPage(i);
      pageNumbers.appendChild(pageBtn);
    }
  }

  function changePage(direction) {
    const totalPages = Math.ceil(filteredJobs.length / jobsPerPage);
    const newPage = currentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
      currentPage = newPage;
      renderJobs();
      updatePagination();
    }
  }

  function goToPage(page) {
    currentPage = page;
    renderJobs();
    updatePagination();
  }

  async function loadRecommendedJobs() {
    try {
      const response = await fetch('../api/filterJobs.php');
      const data = await response.json();
      
      const jobGrid = document.getElementById('jobGrid');
      const paginationSection = document.getElementById('paginationSection');
      
      if (data.success && data.jobs && data.jobs.length > 0) {
        jobGrid.innerHTML = '';
        
        data.jobs.forEach(job => {
          const jobCard = document.createElement('div');
          jobCard.className = 'job-card';
          
          const formattedSalary = job.postsalary.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
          
          jobCard.innerHTML = `
            <h3 class="job-title">${job.postjobrole}</h3>
            <div class="job-company">Philkoei International, Inc.</div>
            <div class="job-location">${job.postaddress}</div>
            <span class="job-type-badge badge-full-time">
              ${job.posttype}
            </span>
            <div class="job-salary">₱${formattedSalary} / month</div>
            <p class="job-summary">${job.postsummary}</p>
            <button class="view-details-btn" 
              onclick="window.location.href='viewjob.php?user_id=<?php echo $userid; ?>&postid=${job.postid}'">
              View Details
            </button>
          `;
          
          jobGrid.appendChild(jobCard);
        });
        
     
        paginationSection.style.display = 'none';
        
      } else {
    
        jobGrid.innerHTML = `
          <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #64748b;">
            <h3>No recommended jobs found</h3>
            <p>Complete your career assessment to get personalized job recommendations</p>
            <a href="yourtest.php" class="assessment-btn">Take Assessment</a>
          </div>`;
        paginationSection.style.display = 'none';
      }
    } catch (error) {
      console.error('Error fetching recommended jobs:', error);
      const jobGrid = document.getElementById('jobGrid');
      jobGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #64748b;">
          <h3>Unable to load recommendations</h3>
          <p>Please try again later or complete your assessment</p>
          <a href="assessment.php" class="assessment-btn">Take Assessment</a>
        </div>`;
      const paginationSection = document.getElementById('paginationSection');
      paginationSection.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    renderJobs();
    updatePagination();

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function () {
      filterJobs(this.value, currentFilter, currentSort);
    });

    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        filterButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentFilter = this.dataset.filter;
        filterJobs(searchInput.value, currentFilter, currentSort);
      });
    });

    const sortSelect = document.getElementById('sortSelect');
    sortSelect.addEventListener('change', function () {
      currentSort = this.value;
      filterJobs(searchInput.value, currentFilter, currentSort);
    });
  });
</script>
</body>
</html>