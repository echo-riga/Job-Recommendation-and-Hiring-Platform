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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../css/result.css?v=3">
</head>
<body>

<?php
include 'check_maintenance.php';
session_start();
include 'database.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

$userid = $_SESSION['userid'];
$query = "SELECT firstname, lastname, email, contact, bday, educlvl, course, school, image FROM users WHERE userid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email, $contact, $bday, $educlvl, $course, $school, $image);
$stmt->fetch();
$stmt->close();

$fullname = htmlspecialchars(trim("$firstname $lastname"));
$profileEmail = htmlspecialchars($email);
$contact = htmlspecialchars($contact ?? '');
$bday = htmlspecialchars($bday ?? '');
$educlvl = htmlspecialchars($educlvl ?? '');
$course = htmlspecialchars($course ?? '');
$school = htmlspecialchars($school ?? '');
$profileImage = !empty($image) ? 'data:image/jpeg;base64,' . base64_encode($image) : '';
?>
<?php include 'header.php'; ?>

<section class="career-section" id="captureArea1">
  <div class="career-container">
    <h2>Hi!, <?= $fullname ?></h2>
    <h3>YOUR IT CAREER MATCH RESULTS</h3>
     <div class="title-underline"></div> 
    <p class="career-subtitle">
      Based on your responses, we've identified your ideal IT career paths
    </p>

     <div class="career-card">
      <div class="career-header">
        <div class="career-title">
          <span class="career-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" fill="#e0f8e9" />
              <path d="M9 9L7 12L9 15" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M15 9L17 12L15 15" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <h4>Software Developer</h4>
        </div>
        <div class="career-match">87% Match</div>
      </div>

      <div class="career-strengths">
          <div class="strength">
          <span class="strength-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" fill="#e0f8e9" />
              <path d="M8 12L11 15L16 10" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          Logical Thinking
        </div>
        <div class="strength">
          <span class="strength-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" fill="#e0f8e9" />
              <path d="M8 12L11 15L16 10" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          Problem Solving
        </div>
        <div class="strength">
          <span class="strength-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" fill="#e0f8e9" />
              <path d="M8 12L11 15L16 10" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          Attention to Detail
        </div>
      </div>
    

      <div class="career-details">
        <div class="career-info">
          <h5>About This Career</h5>
          <p>
            Software Developers design, build, and maintain computer programs that power everything from mobile apps to enterprise systems. They collaborate with teams to create solutions that meet user needs and business requirements.
          </p>
        </div>

        <div class="career-columns">
          <div class="career-column">
            <h6>What You'll Do:</h6>
            <ul>
              <li>Write clean, efficient code based on specifications</li>
              <li>Test and debug software to ensure quality</li>
              <li>Collaborate with cross-functional teams</li>
              <li>Optimize applications for performance</li>
              <li>Implement security and data protection measures</li>
            </ul>
          </div>

          <div class="career-column">
            <h6>Career Outlook:</h6>
            <ul>
              <li>Projected 22% growth over the next decade</li>
              <li>Median salary range: $75,000 - $120,000</li>
              <li>Advancement paths to senior developer, architect, or technical lead roles</li>
              <li>Opportunities in virtually every industry</li>
              <li>Strong demand for specialized skills (AI, cloud, mobile)</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="career-tip">
        <strong>Pro Tip:</strong> Software development offers many specializations. Based on your assessment, you might excel in back-end development where logical thinking and problem-solving are particularly valuable.
      </div>
    </div>
  </div>
</section>




 <div class="container" id="captureArea">
    <section class="process-matches">
      <h2>Your Top IT Career Matches</h2>
      <div class="process-item" data-percent="78">
        <div class="process-header">
          <h3><img src="https://img.icons8.com/ios-filled/20/4392f1/data-configuration.png" alt=""/> Data Analyst</h3><span>78%</span>
        </div> 
        <div class="progress-bar"><div class="bar"></div></div>
        <p>Data Analysts collect, process, and analyze data to help organizations make better decisions...</p>
      </div>
      <div class="process-item" data-percent="72">
        <div class="process-header">
          <h3><img src="https://img.icons8.com/ios-filled/20/4784f2/lock.png" alt=""/> Cybersecurity Specialist</h3>
          <span>72%</span>
        </div>
        <div class="progress-bar"><div class="bar"></div></div>
        <p>Cybersecurity Specialists protect organizations from digital threats and vulnerabilities...</p>
      </div>
      <div class="process-item" data-percent="65">
        <div class="process-header">
          <h3><img src="https://img.icons8.com/ios-filled/20/b962dd/database.png" alt=""/> Database Administrator</h3>
          <span>65%</span>
        </div>
        <div class="progress-bar"><div class="bar"></div></div>
        <p>Database Administrators design, implement, and maintain database systems...</p>
      </div>
      <div class="process-item" data-percent="58">
        <div class="process-header">
          <h3><img src="https://img.icons8.com/ios-filled/20/f24f86/design.png" alt=""/> UX/UI Designer</h3>
          <span>58%</span>
        </div>
        <div class="progress-bar"><div class="bar"></div></div>
        <p>UX/UI Designers create intuitive, engaging digital experiences...</p>
      </div>
     
    </section>

    <section class="skills-breakdown">
      <h2>Skills Breakdown</h2>
      <canvas id="skillsChart"></canvas>
      <p>This shows how your abilities align with different IT career demands...</p>
    </section>

    <section class="actions">
    <button onclick="window.location.href='test.php'">⟲ Retake Test</button>
        <button class="primary" id="openModal">Save Result ➔</button>
    </section>
  </div>
<div id="successModal" class="custom-modal-overlay">
  <div class="custom-modal">
    <img src="https://cdn-icons-png.flaticon.com/512/2278/2278992.png" alt="Success" class="modal-image" />
    <h2>Congratulations!</h2>
    <p>You've successfully discovered your ideal job<br>position by completing our test!</p>
    <p>Come back anytime to reassess your skills and growth!</p>
    <div class="modal-buttons">
      <button class="btn-outline" onclick="closeModal()">Cancel</button>
      <button class="btn-filled" onclick="saveResultsToDatabase()">Save Result</button>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionResults = sessionStorage.getItem('jobRecommendationResults');
    
    if (sessionResults) {
        console.log('Using session storage results');
        processResults(JSON.parse(sessionResults));
    } else {
        console.log('No session results found, calling API');
        fetchLatestResults();
    }
});

function fetchLatestResults() {
    fetch('../api/getLatestResult.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Using fresh database results');
                const convertedData = convertApiResponseToSessionFormat(data);
                processResults(convertedData);
                
                sessionStorage.setItem('jobRecommendationResults', JSON.stringify(convertedData));
            } else {
                console.log('No database results found, checking session storage...');
                fallbackToSessionStorage(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching database results:', error);
            console.log('Falling back to session storage due to error...');
            fallbackToSessionStorage(error.message);
        });
}

function fallbackToSessionStorage(reason) {
    const sessionResults = sessionStorage.getItem('jobRecommendationResults');
    
    if (sessionResults) {
        console.log('Using session storage results because:', reason);
        processResults(JSON.parse(sessionResults));
    } else {
        console.log('No session results available either');
        showNoResultsMessage();
    }
}
function convertApiResponseToSessionFormat(apiData) {
    console.log('=== Converting API Response ===');
    console.log('API data received:', apiData);
    
    const convertedData = {
        api_results: {
            top_5_jobs: apiData.assessment.top_jobs.map(job => ({
                job: job.job,
                probability: job.probability,
                displayPercentage: job.displayPercentage
            })),
            soft_traits: apiData.assessment.soft_traits
        }
    };
    
    console.log('Converted data:', convertedData);
    return convertedData;
}

function processResults(data) {
    try {
        console.log('Processing results:', data);
        const convertedData = convertProbabilitiesToMatchPercentages(data);
        renderMainCareer(convertedData);
        renderOtherMatches(convertedData);
        renderSkillsBreakdown(convertedData);
        setTimeout(updateProgressBars, 100);

    } catch (error) {
        console.error('Error processing results:', error);
        showNoResultsMessage();
    }
}

function showNoResultsMessage() {
    const captureArea = document.getElementById('captureArea');
    if (captureArea) {
        captureArea.innerHTML = `
            <div class="no-results-message">
                <h2>No Assessment Results Found</h2>
                <p>It looks like you haven't completed an assessment yet, or no results are available.</p>
                <button onclick="window.location.href='test.php'" class="primary">
                    Take Assessment Now
                </button>
            </div>
        `;
    }

    const careerSection = document.querySelector('.career-section');
    if (careerSection) {
        careerSection.innerHTML = `
            <div class="career-container">
                <h2>Hi!, <?= $fullname ?></h2>
                <h3>NO ASSESSMENT RESULTS FOUND</h3>
                <div class="title-underline"></div>
                <p class="career-subtitle">
                    Please complete the career assessment to see your personalized results.
                </p>
                <div style="text-align: center; margin: 30px 0;">
                    <button onclick="window.location.href='test.php'" class="primary" style="font-size: 18px; padding: 12px 30px;">
                        Start Assessment
                    </button>
                </div>
            </div>
        `;
    }
}
function convertProbabilitiesToMatchPercentages(data) {
    if (!data.api_results || !data.api_results.top_5_jobs) return data;
    
    const jobs = data.api_results.top_5_jobs;
    
    console.log('=== DEBUG convertProbabilitiesToMatchPercentages ===');
    jobs.forEach((job, index) => {
        console.log(`Job ${index} before conversion:`, job);
        console.log(`  - job.job: "${job.job}"`, typeof job.job);
        console.log(`  - job.probability: ${job.probability}`, typeof job.probability);
    });
    
    const convertedJobs = jobs.map((job, index) => {
        const matchPercentage = calculateMatchPercentage(job.probability, index);
        return {
            job: String(job.job || `Job ${index + 1}`).trim(), 
            probability: Number(job.probability || 0.1), 
            displayPercentage: matchPercentage,
            originalProbability: job.probability
        };
    });
    
    console.log('Jobs after conversion:', convertedJobs);
    return {
        ...data,
        api_results: {
            ...data.api_results,
            top_5_jobs: convertedJobs
        }
    };
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
function renderMainCareer(data) {
    if (!data.api_results || !data.api_results.top_5_jobs || data.api_results.top_5_jobs.length === 0) {
        console.log('No API results available');
        return;
    }

    const topJob = data.api_results.top_5_jobs[0]; // Highest match
    const matchPercentage = topJob.displayPercentage;
    
    // Update main career section
    const careerTitle = document.querySelector('.career-title h4');
    const careerMatch = document.querySelector('.career-match');
    const careerInfo = document.querySelector('.career-info p');
    const careerColumns = document.querySelector('.career-columns');
    const careerTip = document.querySelector('.career-tip');
    
    if (careerTitle) careerTitle.textContent = topJob.job;
    if (careerMatch) careerMatch.textContent = `${matchPercentage}% Match`;
    
    // Update all career details based on job type
    if (careerInfo) {
        careerInfo.textContent = getCareerDescription(topJob.job);
    }
    
    if (careerColumns) {
        careerColumns.innerHTML = getCareerColumns(topJob.job);
    }
    
    if (careerTip) {
        careerTip.innerHTML = `<strong>Pro Tip:</strong> ${getCareerTip(topJob.job)}`;
    }
    
    // Update strengths based on soft traits
    updateStrengths(data.api_results.soft_traits);
}

function renderOtherMatches(data) {
    if (!data.api_results || !data.api_results.top_5_jobs) return;
    
    const otherJobs = data.api_results.top_5_jobs.slice(1); // Exclude the top one
    const processItems = document.querySelectorAll('.process-item');
    
    otherJobs.forEach((job, index) => {
        if (index < processItems.length) {
            const item = processItems[index];
            const matchPercentage = job.displayPercentage;
            
            // Update job title and percentage
            const header = item.querySelector('.process-header h3');
            const percentageSpan = item.querySelector('.process-header span');
            const progressBar = item.querySelector('.bar');
            const description = item.querySelector('p');
            
            if (header) {
                // Update icon based on job type
                const icon = getJobIcon(job.job);
                header.innerHTML = `${icon} ${job.job}`;
            }
            if (percentageSpan) percentageSpan.textContent = `${matchPercentage}%`;
            if (progressBar) {
                progressBar.style.width = `${matchPercentage}%`;
                progressBar.style.backgroundColor = getColorForPercentage(matchPercentage);
            }
            if (description) {
                description.textContent = getJobDescription(job.job);
            }
            
            // Update data attribute
            item.setAttribute('data-percent', matchPercentage);
        }
    });
}

function renderSkillsBreakdown(data) {
    if (!data.api_results || !data.api_results.soft_traits) return;
    
    const softTraits = data.api_results.soft_traits;
    
    // Convert soft traits to radar chart data (scale 1-5 to 0-100)
    const radarLabels = Object.keys(softTraits);
    const radarData = Object.values(softTraits).map(rating => (rating / 5) * 100);
    
    // Update the skills breakdown section with a new design
    const skillsSection = document.querySelector('.skills-breakdown');
    if (!skillsSection) return;
    
    // Replace the entire skills breakdown section
    skillsSection.innerHTML = `
        <h2>Your Soft Skills Profile</h2>
        <div class="skills-breakdown-container">
            <div class="radar-chart-container">
                <canvas id="skillsChart"></canvas>
            </div>
            <div class="skills-details">
                <div class="skills-summary">
                    <h3>Skills Analysis</h3>
                    <p>Your soft skills assessment shows strengths in key areas that are crucial for IT professionals.</p>
                </div>
                <div class="skills-list">
                    ${Object.entries(softTraits).map(([skill, rating]) => `
                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">${skill}</span>
                                <span class="skill-rating">${rating}/5</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-progress" style="width: ${(rating/5)*100}%"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        <p class="skills-note">This analysis helps identify areas where you naturally excel in teamwork and problem-solving.</p>
    `;
    
    // Now render the radar chart
    renderRadarChart(radarLabels, radarData);
}

function renderRadarChart(labels, data) {
    const ctx = document.getElementById('skillsChart');
    if (!ctx) {
        console.log('Skills chart canvas not found');
        return;
    }
    
    // Destroy existing chart if it exists
    if (ctx.chart) {
        ctx.chart.destroy();
    }
    
    ctx.chart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Skill Level',
                data: data,
                backgroundColor: 'rgba(74, 107, 255, 0.3)',
                borderColor: '#4a6bff',
                borderWidth: 3,
                pointBackgroundColor: '#4a6bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#4a6bff',
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                r: {
                    beginAtZero: true,
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        backdropColor: 'transparent',
                        color: '#666',
                        font: {
                            size: 10,
                            weight: 'bold'
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    angleLines: {
                        color: 'rgba(0, 0, 0, 0.15)',
                        lineWidth: 1.5
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        circular: true
                    },
                    pointLabels: {
                        font: {
                            size: 11,
                            weight: '600',
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                        },
                        color: '#2c3e50',
                        padding: 12
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 62, 80, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#4a6bff',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const trait = labels[context.dataIndex];
                            const originalRating = Math.round((value / 100) * 5);
                            return `${trait}: ${originalRating}/5 (${Math.round(value)}%)`;
                        }
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.2
                }
            }
        }
    });
}

function updateStrengths(softTraits) {
    const strengthsContainer = document.querySelector('.career-strengths');
    if (!strengthsContainer) return;
    
    // Sort traits by rating (highest first) and take top 3
    const topTraits = Object.entries(softTraits)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 3);
    
    strengthsContainer.innerHTML = '';
    
    topTraits.forEach(([trait, rating]) => {
        const strengthDiv = document.createElement('div');
        strengthDiv.className = 'strength';
        strengthDiv.innerHTML = `
            <span class="strength-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" fill="#e0f8e9" />
                    <path d="M8 12L11 15L16 10" stroke="#34c759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            ${trait}
        `;
        strengthsContainer.appendChild(strengthDiv);
    });
}

// Helper functions for job details
function getCareerDescription(jobTitle) {
    const descriptions = {
        'AI/Machine Learning Engineer': 'AI/Machine Learning Engineers design and develop intelligent systems that can learn from data and make predictions. They work on cutting-edge technologies like neural networks, natural language processing, and computer vision to create solutions that automate tasks and provide insights.',
        'Cybersecurity Specialist': 'Cybersecurity Specialists protect organizations from digital threats by implementing security measures, monitoring systems for vulnerabilities, and responding to security incidents. They ensure data privacy and system integrity in an increasingly connected world.',
        'Web Developer': 'Web Developers create and maintain websites and web applications, focusing on both front-end user interfaces and back-end server functionality. They work with various programming languages and frameworks to build responsive, secure, and user-friendly digital experiences.',
        'Data Analyst': 'Data Analysts transform raw data into meaningful insights that drive business decisions. They use statistical analysis, data visualization, and reporting tools to identify trends, patterns, and opportunities for optimization across organizations.',
        'Network Engineer': 'Network Engineers design, implement, and maintain computer networks that enable communication between devices and systems. They ensure network reliability, security, and performance while troubleshooting connectivity issues and optimizing infrastructure.',
        'Software Developer': 'Software Developers design, build, and maintain computer programs and applications that solve specific problems or meet user needs. They work across the entire development lifecycle, from planning and coding to testing and deployment.',
        'IT Support Specialist': 'IT Support Specialists provide technical assistance to users, troubleshoot hardware and software issues, and maintain computer systems. They are the frontline support ensuring technology functions smoothly for end-users.',
        'Database Administrator': 'Database Administrators design, implement, and maintain database systems to ensure data integrity, security, and availability. They optimize database performance, manage backups, and ensure efficient data storage and retrieval.',
        'System Administrator': 'System Administrators maintain and troubleshoot computer systems, servers, and IT infrastructure. They ensure system reliability, perform updates, manage user accounts, and implement security measures.',
        'Data Scientist': 'Data Scientists extract insights from complex datasets using advanced statistical analysis, machine learning, and programming skills. They build predictive models and develop algorithms to solve complex business problems and drive innovation.'
    };
    
    return descriptions[jobTitle] || `${jobTitle} professionals work in the technology field, utilizing their specialized skills to solve complex problems and drive innovation.`;
}

function getCareerColumns(jobTitle) {
    const columns = {
        'AI/Machine Learning Engineer': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Design and implement machine learning models</li>
                    <li>Preprocess and analyze large datasets</li>
                    <li>Develop neural networks and AI algorithms</li>
                    <li>Optimize model performance and accuracy</li>
                    <li>Collaborate with data engineers and scientists</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 32% growth over the next decade</li>
                    <li>Median salary range: $110,000 - $160,000</li>
                    <li>High demand across tech, healthcare, and finance</li>
                    <li>Opportunities in research and development</li>
                    <li>Continuous learning and skill advancement</li>
                </ul>
            </div>
        `,
        'Cybersecurity Specialist': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Monitor systems for security breaches</li>
                    <li>Implement security protocols and firewalls</li>
                    <li>Conduct vulnerability assessments</li>
                    <li>Respond to and investigate security incidents</li>
                    <li>Educate staff on security best practices</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 35% growth over the next decade</li>
                    <li>Median salary range: $90,000 - $130,000</li>
                    <li>Critical role in all industries</li>
                    <li>Multiple certification paths available</li>
                    <li>High job security and demand</li>
                </ul>
            </div>
        `,
        'Web Developer': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Write clean, efficient code for websites</li>
                    <li>Create responsive web designs</li>
                    <li>Develop both front-end and back-end systems</li>
                    <li>Test and debug web applications</li>
                    <li>Collaborate with designers and stakeholders</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 16% growth over the next decade</li>
                    <li>Median salary range: $70,000 - $110,000</li>
                    <li>Opportunities in agencies, corporations, and freelance</li>
                    <li>Continuous evolution with new technologies</li>
                    <li>Strong remote work opportunities</li>
                </ul>
            </div>
        `,
        'Data Analyst': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Collect and process raw data</li>
                    <li>Create visualizations and reports</li>
                    <li>Identify trends and patterns</li>
                    <li>Provide data-driven recommendations</li>
                    <li>Collaborate with business teams</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 25% growth over the next decade</li>
                    <li>Median salary range: $65,000 - $95,000</li>
                    <li>Essential role across all industries</li>
                    <li>Pathway to data science roles</li>
                    <li>High demand for analytical skills</li>
                </ul>
            </div>
        `,
        'Network Engineer': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Design and implement network infrastructure</li>
                    <li>Configure routers, switches, and firewalls</li>
                    <li>Monitor network performance and security</li>
                    <li>Troubleshoot connectivity issues</li>
                    <li>Plan network upgrades and expansions</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 8% growth over the next decade</li>
                    <li>Median salary range: $75,000 - $115,000</li>
                    <li>Critical for cloud and IoT expansion</li>
                    <li>Multiple certification paths (Cisco, Juniper)</li>
                    <li>Opportunities in telecom and enterprise</li>
                </ul>
            </div>
        `,
        'Software Developer': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Design and develop software applications</li>
                    <li>Write and test code across multiple platforms</li>
                    <li>Collaborate with cross-functional teams</li>
                    <li>Debug and optimize software performance</li>
                    <li>Maintain and update existing systems</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 25% growth over the next decade</li>
                    <li>Median salary range: $85,000 - $130,000</li>
                    <li>Opportunities in virtually every industry</li>
                    <li>Multiple specializations available</li>
                    <li>Strong remote work possibilities</li>
                </ul>
            </div>
        `,
        'IT Support Specialist': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Provide technical support to end-users</li>
                    <li>Troubleshoot hardware and software issues</li>
                    <li>Install and configure computer systems</li>
                    <li>Maintain IT documentation and procedures</li>
                    <li>Train users on new technologies</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 11% growth over the next decade</li>
                    <li>Median salary range: $45,000 - $70,000</li>
                    <li>Entry point to IT career paths</li>
                    <li>Opportunities in all industries</li>
                    <li>Pathway to specialized IT roles</li>
                </ul>
            </div>
        `,
        'Database Administrator': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Design and implement database structures</li>
                    <li>Optimize database performance</li>
                    <li>Ensure data security and integrity</li>
                    <li>Perform backups and recovery operations</li>
                    <li>Monitor database systems and troubleshoot issues</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 12% growth over the next decade</li>
                    <li>Median salary range: $80,000 - $120,000</li>
                    <li>Critical for data-driven organizations</li>
                    <li>Opportunities in cloud database management</li>
                    <li>High responsibility and impact</li>
                </ul>
            </div>
        `,
        'System Administrator': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Maintain server infrastructure</li>
                    <li>Install and configure operating systems</li>
                    <li>Monitor system performance and security</li>
                    <li>Manage user accounts and permissions</li>
                    <li>Implement system updates and patches</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 7% growth over the next decade</li>
                    <li>Median salary range: $70,000 - $100,000</li>
                    <li>Foundation of IT infrastructure</li>
                    <li>Opportunities in cloud and on-premise</li>
                    <li>Pathway to senior infrastructure roles</li>
                </ul>
            </div>
        `,
        'Data Scientist': `
            <div class="career-column">
                <h6>What You'll Do:</h6>
                <ul>
                    <li>Develop predictive models and algorithms</li>
                    <li>Analyze complex datasets for insights</li>
                    <li>Build machine learning systems</li>
                    <li>Communicate findings to stakeholders</li>
                    <li>Collaborate with engineering teams</li>
                </ul>
            </div>
            <div class="career-column">
                <h6>Career Outlook:</h6>
                <ul>
                    <li>Projected 36% growth over the next decade</li>
                    <li>Median salary range: $100,000 - $150,000</li>
                    <li>High demand across all sectors</li>
                    <li>Cutting-edge technology focus</li>
                    <li>Research and development opportunities</li>
                </ul>
            </div>
        `
    };
    
    return columns[jobTitle] || `
        <div class="career-column">
            <h6>What You'll Do:</h6>
            <ul>
                <li>Apply technical skills to solve problems</li>
                <li>Collaborate with team members</li>
                <li>Stay updated with industry trends</li>
                <li>Deliver quality solutions</li>
                <li>Continuous learning and improvement</li>
            </ul>
        </div>
        <div class="career-column">
            <h6>Career Outlook:</h6>
            <ul>
                <li>Strong growth in technology sector</li>
                <li>Competitive salary opportunities</li>
                <li>Diverse industry applications</li>
                <li>Continuous skill development</li>
                <li>Global career opportunities</li>
            </ul>
        </div>
    `;
}

function getCareerTip(jobTitle) {
    const tips = {
        'AI/Machine Learning Engineer': 'Focus on building strong foundations in mathematics and programming. Specialize in either computer vision, natural language processing, or reinforcement learning to stand out in this competitive field.',
        'Cybersecurity Specialist': 'Start with foundational certifications like Security+ and gain hands-on experience through labs and capture-the-flag competitions. Stay updated with the latest security threats and defense mechanisms.',
        'Web Developer': 'Master both front-end and back-end technologies to become a full-stack developer. Build a strong portfolio with diverse projects and stay current with modern frameworks and best practices.',
        'Data Analyst': 'Develop strong storytelling skills to effectively communicate insights from data. Master tools like SQL, Excel, and visualization platforms while building domain knowledge in your industry of interest.',
        'Network Engineer': 'Pursue vendor certifications like CCNA and gain practical experience with network simulation tools. Specialize in areas like cloud networking or security to advance your career.',
        'Software Developer': 'Contribute to open-source projects and build a diverse portfolio. Focus on writing clean, maintainable code and learn multiple programming languages to increase your versatility.',
        'IT Support Specialist': 'Develop excellent communication skills and patience for helping users. Use this role as a stepping stone to specialize in areas like networking, security, or systems administration.',
        'Database Administrator': 'Gain expertise in both SQL and NoSQL databases. Focus on performance tuning and learn cloud database services to stay relevant in evolving infrastructure environments.',
        'System Administrator': 'Automate repetitive tasks using scripting languages. Specialize in either Windows or Linux systems and gain cloud administration skills for career advancement.',
        'Data Scientist': 'Build strong statistical foundations and practical programming skills. Focus on business problem-solving and develop domain expertise to provide valuable insights to organizations.'
    };
    
    return tips[jobTitle] || 'Continue developing both technical and soft skills. Stay curious about new technologies and seek opportunities for practical application of your knowledge.';
}

function getJobDescription(jobTitle) {
    const descriptions = {
        'AI/Machine Learning Engineer': 'Builds intelligent systems and algorithms that can learn from data and make predictions...',
        'Cybersecurity Specialist': 'Protects digital assets and systems from cyber threats and security breaches...',
        'Web Developer': 'Creates and maintains websites and web applications using various technologies...',
        'Data Analyst': 'Transforms data into actionable insights through analysis and visualization...',
        'Network Engineer': 'Designs and maintains computer networks for optimal performance and security...',
        'Software Developer': 'Designs, codes, and maintains software applications and systems...',
        'IT Support Specialist': 'Provides technical assistance and troubleshooting for computer systems...',
        'Database Administrator': 'Manages and optimizes database systems for performance and security...',
        'System Administrator': 'Maintains and troubleshoots computer systems and server infrastructure...',
        'Data Scientist': 'Extracts insights from complex data using statistical and machine learning methods...'
    };
    
    return descriptions[jobTitle] || `Works in ${jobTitle} role, applying technical expertise to solve problems and create solutions...`;
}

function getJobIcon(jobTitle) {
    const icons = {
        'AI/Machine Learning Engineer': '<img src="https://img.icons8.com/ios-filled/20/4392f1/artificial-intelligence.png" alt="AI"/>',
        'Cybersecurity Specialist': '<img src="https://img.icons8.com/ios-filled/20/4784f2/lock.png" alt="Security"/>',
        'Web Developer': '<img src="https://img.icons8.com/ios-filled/20/4392f1/code.png" alt="Web"/>',
        'Data Analyst': '<img src="https://img.icons8.com/ios-filled/20/4392f1/data-configuration.png" alt="Data"/>',
        'Network Engineer': '<img src="https://img.icons8.com/ios-filled/20/4392f1/network.png" alt="Network"/>',
        'Software Developer': '<img src="https://img.icons8.com/ios-filled/20/4392f1/code.png" alt="Software"/>',
        'IT Support Specialist': '<img src="https://img.icons8.com/ios-filled/20/4392f1/support.png" alt="Support"/>',
        'Database Administrator': '<img src="https://img.icons8.com/ios-filled/20/b962dd/database.png" alt="Database"/>',
        'System Administrator': '<img src="https://img.icons8.com/ios-filled/20/4392f1/server.png" alt="Server"/>',
        'Data Scientist': '<img src="https://img.icons8.com/ios-filled/20/4392f1/statistics.png" alt="Data Science"/>'
    };
    
    return icons[jobTitle] || '<img src="https://img.icons8.com/ios-filled/20/a5a5a5/project.png" alt="IT"/>';
}

function getColorForPercentage(percent) {
    if (percent >= 85) return '#4392f1';
    if (percent >= 75) return '#4784f2';
    if (percent >= 65) return '#b962dd';
    if (percent >= 55) return '#f24f86';
    return '#a5a5a5';
}

// Update progress bars for other matches
function updateProgressBars() {
    const colorMap = [
        { min: 85, color: '#4392f1' },
        { min: 75, color: '#4784f2' },
        { min: 65, color: '#b962dd' },
        { min: 55, color: '#f24f86' },
        { min: 0,  color: '#a5a5a5' }
    ];

    document.querySelectorAll('.process-item').forEach(item => {
        const percent = parseInt(item.getAttribute('data-percent'));
        const bar = item.querySelector('.bar');
        const matched = colorMap.find(c => percent >= c.min);
        if (bar && matched) {
            bar.style.width = percent + '%';
            bar.style.backgroundColor = matched.color;
        }
    });
}

// Save Results Functionality
document.getElementById('openModal')?.addEventListener('click', function() {
    document.getElementById('successModal').style.display = 'flex';
});
function saveResultsToDatabase() {
    const sessionResults = sessionStorage.getItem('jobRecommendationResults');
    
    if (!sessionResults) {
        alert('No results found to save. Please complete the assessment first.');
        return;
    }

    try {
        const data = JSON.parse(sessionResults);
        
        // DEBUG: Check what we're about to send
        console.log('Original session data:', data);
        
        // Ensure the data structure is correct
        const apiResults = data.api_results;
        
        // Validate and clean the top_5_jobs array
        if (apiResults.top_5_jobs && Array.isArray(apiResults.top_5_jobs)) {
            // Make sure all items have the correct structure
            apiResults.top_5_jobs = apiResults.top_5_jobs.map((job, index) => {
                if (!job || typeof job !== 'object') {
                    console.error('Invalid job object at index', index, ':', job);
                    // Create a fallback job object
                    const fallbackJobs = [
                        'Data Analyst',
                        'Cybersecurity Specialist', 
                        'Database Administrator',
                        'Network Engineer',
                        'IT Support Specialist'
                    ];
                    return {
                        job: fallbackJobs[index] || 'IT Professional',
                        probability: 0.1
                    };
                }
                
                // Ensure job has both properties
                return {
                    job: job.job || `Job ${index + 1}`,
                    probability: typeof job.probability === 'number' ? job.probability : 0.1
                };
            });
            
            console.log('Cleaned top_5_jobs:', apiResults.top_5_jobs);
        }
        
        // Validate soft_traits
        if (apiResults.soft_traits && typeof apiResults.soft_traits === 'object') {
            const requiredTraits = ['Critical Thinking', 'Problem Solving', 'Communication', 'Teamwork', 'Adaptability'];
            requiredTraits.forEach(trait => {
                if (typeof apiResults.soft_traits[trait] !== 'number') {
                    console.warn(`Missing or invalid trait: ${trait}, setting to 3`);
                    apiResults.soft_traits[trait] = 3.0;
                }
            });
        }
        
        console.log('Sending data to API:', {
            api_results: apiResults
        });
        
        // Send data to PHP API
        fetch('../api/save_recommendation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                api_results: apiResults
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('API Response:', result);
            if (result.success) {
                // Clear sessionStorage after successful save
                clearSessionStorage();
                
            
                closeModal();
                
                // Optional: Refresh the page to show the latest saved results
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Failed to save results: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error saving results:', error);
            alert('Error saving results. Please try again. Check console for details.');
        });
        
    } catch (error) {
        console.error('Error parsing session data:', error);
        alert('Error processing results. Please retake the assessment.');
    }
}

function clearSessionStorage() {
    sessionStorage.removeItem('jobRecommendationResults');
    localStorage.removeItem('skillAssessmentResults');
    console.log('Session storage cleared');
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}
</script>

</body>
</html>