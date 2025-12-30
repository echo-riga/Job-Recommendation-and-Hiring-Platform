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
  <link rel="stylesheet" href="../css/resumetips.css">
</head>
<body>
<?php
include 'check_maintenance.php';
include 'database.php';
?>
<?php include 'header.php'; ?>
  
  <!--Home-->
<section class="jthome" id="home">
  <div class="jt-content">
    <h3 style="text-align: center; font-weight: bold;">
  Get <span style="color: #0057d9;">Ready</span>, 
  Get <span style="color: #f4c10f;">Set</span>, 
  <br>
  Get <span style="color: #e60000;">Hired!</span>
</h3>
<p>This page is designed to guide you through every step of your job search journey. Whether you need help crafting a winning resume, preparing for interviews, or writing a professional job application letter, you’ll find practical tips and tools here to boost your confidence and increase your chances of success. Start exploring below to take the next step toward your career goals!</p>
   <button class="scroll-down-btn" onclick="document.querySelector('#apply').scrollIntoView({ behavior: 'smooth' });">
  <i class="fas fa-chevron-down"></i>
</button>
</div>
</section>

<section class="how-to-apply"  id="apply">
  <div class="apply-header">
    <h2>HOW TO APPLY</h2>
    <p>
      Ready to start your job search? This section will walk you through the step-by-step process of
      applying for jobs, so you can send your applications with confidence. These steps will guide
      you on how to apply with clarity and ease.
    </p>
  </div>
  <div class="timeline-wrapper">
    <div class="timeline">
      <div class="timeline-step right">
        <div class="dot blue">1</div>
        <div class="content">
          <h3 class="blue-box">Step 1: Find the right position for you</h3>
          <p>
            Start by taking the skills assessment test on our website to discover which positions best
            match your abilities and interests. Then, look for job openings that fit your recommended
            roles and align with your career goals.
          </p>
        </div>
      </div>

      <div class="timeline-step left">
        <div class="dot yellow">2</div>
        <div class="content">
          <h3 class="yellow-box">Step 2: Read the Job Opportunity Announcement</h3>
          <p>
            Carefully read the job posting. Pay attention to the responsibilities, required qualifications,
            and application instructions so you can tailor your resume and documents effectively.
          </p>
        </div>
      </div>

      <div class="timeline-step right">
        <div class="dot purple">3</div>
        <div class="content">
          <h3 class="purple-box">Step 3: Apply for Position</h3>
          <p>
            Prepare and submit your resume, application letter, and any other required documents.
            Double-check that everything is accurate, complete, and customized to the specific job.
          </p>
        </div>
      </div>

      <div class="timeline-step left">
        <div class="dot cyan">4</div>
        <div class="content">
          <h3 class="cyan-box">Step 4: Interview for the position</h3>
          <p>
            If you're shortlisted, you'll be invited to an interview. Be ready to talk about your experience,
            highlight your skills, and show why you're the perfect fit for the job.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>



<section class="interview-tips" id="intertips">
  <h2>INTERVIEW TIPS</h2>
  <div class="images-row">
    <img src="../image/INTER.jpg" alt="Interview Image 1" />
    <img src="../image/site.jpg" alt="Interview Image 2" />
    <img src="../image/INTERE2.png" alt="Interview Image 3" />
  </div>

  <p>Remember that hiring managers make the hiring decisions, not resumes or applications. The keys to success in an interview are proper preparation and a positive attitude.</p>
  <p>Interviewing is an important part of the selection process because it allows you to describe your previous experience, education, and training. It is also an opportunity for you to gain a better understanding of the organization and your role...</p>

  <div class="accordion">
    <div class="accordion-item">
      <div class="accordion-header" onclick="toggleAccordion(this)">
        Before the Interview <span class="toggle-icon">+</span>
      </div>
      <div class="accordion-content">
        <p>Research the position and organization (e.g., mission, goals, etc.) prior to the interview. Re-familiarize yourself with the job announcement details including the duties, requirements, and evaluation methods of the position. Do not assume you know everything about the organization even if you have experience with the organization. Always do your research.
Review your application (e.g., resume and assessment questionnaire responses) to assist preparing real examples of past accomplishments that directly supports what is required of the position and what you submitted with your application package. Be sure that highlight any paid and non-paid experiences. Non-paid experiences may include "lived experience" which is defined as direct, first-hand involvement in activities or events in a person's life that enable a deep understanding or knowledge of issues and their application in the work environment. Do not assume the interviewer knows everything about you.
Practice interviewing. Take the time to research and review typical interview questions to help give you a framework for your responses. Be sure to review the competencies outlined in the announcement as that may be a guide in determining what type of interview questions may be asked of you.
Be flexible with scheduling and allow sufficient time for the interview. Be sure to ask for specifics about the time, location, point of contact (POC) as well as any other logistical details.
Ask whether there will be one or multiple interviewers.
If you need accommodations for the interview makes sure to ask for them as early as possible.
Make sure you have a business professional outfit for the interview that fits well.
Print a few physical copies of your resume, if possible. Prepare a clean, neutral-colored folder that includes copies of your resume, a pen and notepad, and a printout of your interview logistics.</p>
      </div>
    </div>

    <div class="accordion-item">
      <div class="accordion-header blue" onclick="toggleAccordion(this)">
        During the Interview <span class="toggle-icon">+</span>
      </div>
      <div class="accordion-content">
        <p>Plan to arrive early. Keep in mind that security/access requirements and time to get on the site may vary by location. Check with the POC regarding proper arrival times, check-in procedures, and logistics. Remember you get one chance to make a first impression.
Be prepared to summarize your experience in about 30-60 seconds and describe what you bring to the position.
Listen carefully to each question asked. Answer questions as directly as possible. Focus on your achievements relevant to the position using examples of how your knowledge, skills and abilities fit the job. Be sure to ask the interviewer to restate a question if further clarification is needed.
Remain positive and avoid negative comments about past employers.
Be aware of your body language and tone of voice. Remain engaged by giving your full attention to the interviewer.
Take limited notes, if desired.
Be sure to ask any final questions about the organization or the position. Also, ask about the next steps in the selection process to include timeframes. Request POC information should you have any follow up questions.
Reinforce your interest in the position and thank the interviewer(s) for the opportunity to interview.
If you have a virtual interview, ensure your space is quiet and free of distractions. Use a headset if you have one available.
Note: Conversations about salary, benefits, and other human resources (HR) matters should be primarily addressed with the servicing HR POC listed on the job opportunity announcement.</p>
      </div>
    </div>

    <div class="accordion-item">
      <div class="accordion-header yellow" onclick="toggleAccordion(this)">
        After the Interview <span class="toggle-icon">+</span>
      </div>
      <div class="accordion-content">
        <p>Provide any other requested information as soon as possible. If professional references are requested, provide advance notice to each reference you list so they are not alarmed if contacted.
Send a thank you email to your contact or those you interview. Use this to highlight your strengths or areas from the interview you wish to provide more context.
Be patient. Remember the hiring process takes time. You can follow up with your POC if you have not been contacted within the established timeframe.
Notify your HR Specialist and POC if you choose to withdraw your application. This may occur as a result of you accepting another job in the time you are waiting for a response or change your mind about being open for consideration.
The hiring official is looking for the right person with the right skills to fill the vacant position. It is up to you to show that you are that person during the interview. If you have questions about these tips, please contact your servicing Component HR representative.</p>
      </div>
    </div>
  </div>
</section>

<section class="resume-guide" id="resume">
  <h2>RESUME BUILDING GUIDE</h2>
  <p>Your resume is often your first and best chance to show employers why you’re the right fit for the job. A strong resume doesn’t just list your experience—it tells your story, highlights your accomplishments, and sets you apart from other candidates. This guide will walk you through the key steps to create a professional, polished resume that effectively showcases your skills and achievements. Whether you’re starting from scratch or updating an existing resume, you’ll find tips and strategies to make your resume clear, compelling, and tailored to your goals. Let’s get started.</p>

  <h3>Choose the right resume format</h3>
  <div class="resume-image">
    <img src="../image/guideresume.png" alt="Resume Format Overview" />
  </div>

  <strong>There are three major options to consider.</strong>

  <p><strong>Chronological Format (Reverse Chronological Resume)</strong> - Most Popular: This is the most common format, in which your work experience and qualifications are listed chronologically, beginning with the most recent. It is generally recommended for most job applications.</p>

  <p><strong>Functional Format (Skills-Based Resume):</strong> This format focuses on your abilities and skills rather than your work experience. It's ideal for those with limited experience or looking to change careers.</p>

  <p><strong>Combination Format (Hybrid Resume):</strong> This format combines elements of both chronological and functional resumes, emphasizing your skills and work experience. Choosing the format that best suits your background will make your resume stand out.</p>
</section>



 <section class="resume-step">
      <h2>How to make a resume: <br> step-by-step guide</h2>
    <div class="step-row">
      <div class="step-box">
        <h2><span class="step-number">01</span> Step #1: Contact information</h2>
        <ul>
          <li><strong>Full name & title.</strong> List your first and last name. Plus the role for which you are applying.</li>
          <li><strong>Professional email address.</strong> Use an appropriate email with your name.</li>
          <li><strong>Phone number.</strong> Use one that you check regularly and has a professional voicemail.</li>
          <li><strong>Location.</strong> City and state is enough. Add 'Willing to Relocate' if applicable.</li>
          <li><strong>LinkedIn.</strong> Include if relevant and up to date.</li>
          <li><strong>Portfolio.</strong> GitHub or other relevant work.</li>
        </ul>
      </div>
      <div class="step-box">
        <h2><span class="step-number">02</span> Step #2: Professional summary or objective</h2>
        <ul>
          <li><strong>Resume Objective</strong> highlights future goals and qualifications.</li>
          <li><strong>Summary</strong>: For experienced professionals – key skills and achievements.</li>
          <li><strong>Objective</strong>: For recent grads or career changers – career goals and value.</li>
        </ul>
      </div>
    </div>

    <div class="step-row" id="more-steps">
      <div class="step-box">
        <h2><span class="step-number">03</span> Step #3: Work experience</h2>
        <ul>
          <li>List your most recent work first. Include company, role, and dates.</li>
          <li>Use bullet points to describe achievements, not duties.</li>
          <li>Quantify impact with numbers where possible.</li>
        </ul>
      </div>
      <div class="step-box">
        <h2><span class="step-number">04</span> Step #4: Education</h2>
        <ul>
          <li>Include degree, major, school, and graduation date.</li>
          <li>Add honors, GPA (if high), and relevant coursework.</li>
        </ul>
      </div>
      <div class="step-box">
        <h2><span class="step-number">05</span> Step #5: Skills</h2>
        <ul>
          <li>Tailor to job description. Include both hard and soft skills.</li>
          <li>Organize with bullets or categories if needed.</li>
        </ul>
      </div>
      <div class="step-box">
        <h2><span class="step-number">06</span> Step #6: Additional sections</h2>
        <ul>
          <li>Certifications, languages, awards, volunteer work, etc.</li>
          <li>Only include if relevant and impressive.</li>
        </ul>
      </div>
    </div>

    <button class="read-more-btn" id="read-more-btn" onclick="toggleSteps(true)">Read More</button>
    <button class="read-less-btn" id="read-less-btn" onclick="toggleSteps(false)" style="display:none;">Read Less</button>
  </section>

    <section class="resutips">
    <div class="resutips-container">
      <h2>Resume writing tips</h2>
      <p class="description">
        Before you start working on your resume, take a look at some of<br>
        the ways that you could boost its potential. Here are some simple<br>
        resume writing tips.
      </p>

      <div class="tips-box">
        <ul>
          <li><b>Cut out any jargon.</b> Use simple language that the hiring manager can understand.</li>
          <li><b>Incorporate keywords.</b> Pepper your resume with keywords and phrases.</li>
          <li><b>Quantify your results.</b> Always provide evidence for the achievements you’ve made.</li>
          <li><b>Add creative language.</b> Include some action verbs and creative adjectives.</li>
          <li><b>Create a matching cover letter.</b> Write a cover letter that suits your resume perfectly.</li>
          <li><b>Avoid silly mistakes.</b> Always proofread your resume before you submit it.</li>
          <li><b>Update your resume.</b> Regularly review and update your resume content.</li>
          <li><b>Consider a resume writer.</b> Get a professional resume writer to review or help write your resume.</li>
        </ul>
      </div>
    </div>
  </section>

<!-- FOOTER -->
 <section class="footer">
  <div class="footer-content">
    <div class="footer-logo">
        <img src="<?php echo $systemLogoBase64; ?>" alt="JOBFIT logo" class="footer-logo-image">
      <h1 class="footer-title"><?php echo $systemName; ?></h1>
    </div>
    
    <div class="quick-links">
  <h3>Quick Links</h3>
  <ul>
     <li><a href="../php/home.php"><span class="icon"><i class="fas fa-home"></i></span> Home</a></li>
    <li><a href="../php/browse.php"><span class="icon"><i class="fas fa-box"></i></span> Browse Jobs</a></li>
    <li><a href="../php/about.php"><span class="icon"><i class="fas fa-brain"></i></span> About us</a></li>
    <li><a href="../php/yourtest.php"><span class="icon"><i class="fas fa-envelope"></i></span> Your Result</a></li>
  </ul>
</div>


   <div class="contact-info">
      <h3>Contact Info</h3>
      <p>Email: mails@philkoei.com.ph</p>
      <p>Phone: 283968882</p>
    </div>
    <div class="social-media">
      <h3>Follow Us</h3>
      <a href="https://www.facebook.com/PKII1989"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.philkoei.com.ph/"><i class="fas fa-globe"></i></a>
      <a href="https://www.instagram.com/philkoei_1989/?utm_source=ig_web_button_share_sheet"><i class="fab fa-instagram"></i></a>
    <a href="https://www.linkedin.com/company/philkoei-international-inc/?viewAsMember=true" target="_blank">
  <i class="fab fa-linkedin-in"></i>
</a>

      </div>
  </div>
  <hr>
  <div class="footer-credits">
       <p>© 2025 <?php echo $systemName; ?> IT by Philkoei International, Inc. All rights reserved.</p>
  </div>
</section>

<script> 
 document.addEventListener('DOMContentLoaded', function() {
  const btn = document.querySelector('.dropdown-btn');
  const dropdown = document.querySelector('.dropdown-content');

  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropdown.classList.toggle('show');
  });

  document.addEventListener('click', function(e) {
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  });
});
 const allLinks = document.querySelectorAll(".navbar a, .dropdown-content a");

  function setActive(link) {
    allLinks.forEach(l => l.classList.remove("active-link"));
    link.classList.add("active-link");
  }

  allLinks.forEach(link => {
    link.addEventListener("click", () => setActive(link));
  });

  window.addEventListener("DOMContentLoaded", () => {
    const currentHash = window.location.hash;
    allLinks.forEach(link => {
      if (link.getAttribute("href") === currentHash) {
        setActive(link);
      }
    });
  });


    function toggleAccordion(header) {
      const item = header.parentElement;
      const isActive = item.classList.contains('active');
      document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
      if (!isActive) item.classList.add('active');
    }

       function toggleSteps(showMore) {
      const moreSteps = document.getElementById('more-steps');
      const moreBtn = document.getElementById('read-more-btn');
      const lessBtn = document.getElementById('read-less-btn');
      if (showMore) {
        moreSteps.style.display = 'flex';
        moreBtn.style.display = 'none';
        lessBtn.style.display = 'inline-block';
      } else {
        moreSteps.style.display = 'none';
        moreBtn.style.display = 'inline-block';
        lessBtn.style.display = 'none';
      }
    }
     document.addEventListener('DOMContentLoaded', function () {
    const loginTrigger = document.getElementById('showLoginModal');
    const loginModal = document.getElementById('registerOverlay');
    const closeBtn = document.getElementById('closeRegisterModal');

    loginTrigger?.addEventListener('click', function (e) {
      e.preventDefault();
      loginModal.style.display = 'flex';
    });

    closeBtn?.addEventListener('click', function () {
      loginModal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
      if (event.target === loginModal) {
        loginModal.style.display = 'none';
      }
    });
  });
</script>
</body>
</html>