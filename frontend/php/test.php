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
    <link rel="stylesheet" href="../css/test.css?v=3">
  </head>
  <body>
  <?php
  include 'check_maintenance.php';
  include 'database.php';
  ?>
  <?php include 'header.php'; ?>

  <div class="background-wrapper"></div>
  <section class="quiz-container">
      <div class="question-number">Question 21 of 50</div>
    <div class="progress">
    <div class="progress-bar" role="progressbar"></div>
  </div>

      <div class="question-box">
        <div class="question-text">
          You're working on a project with a tight deadline. You discover a bug that will take significant time to fix properly, but you could implement a quick workaround. What would you do?
        </div>
        <div class="option" onclick="selectOption(this)">A. Implement the quick workaround to meet the deadline and plan to fix it properly later</div>
        <div class="option" onclick="selectOption(this)">B. Explain the situation to your manager and request an extension to fix it properly</div>
        <div class="option" onclick="selectOption(this)">C. Work overtime to implement the proper fix before the deadline</div>
        <div class="option" onclick="selectOption(this)">D. Implement the workaround but clearly document the issue for transparency</div>
      </div>
      <div class="button-row">
        <button class="btnprev" onclick="prevQuestion()">&lt; Previous</button> 
        <button class="btnnext" onclick="nextQuestion()">Next &gt;</button>
        
      </div>
    </section>


  <script>
  const questions = [
    {
      text: "How would you rate your ability to write, debug, and optimize code in at least one programming language?",
      options: [
        "I understand basic syntax and can write simple scripts",
        "I can complete small coding tasks with frequent assistance",
        "I can build small applications and debug most errors independently",
        "I can design, develop, and optimize complex software systems",
        "I can architect large-scale systems, mentor others, and contribute to core language/technology"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How proficient are you in designing, querying, and managing databases?",
      options: [
        "I know what a database is and can write simple SELECT queries",
        "I can write basic JOIN queries and perform simple data updates",
        "I can design a normalized schema and write complex queries with subqueries and functions",
        "I can optimize query performance, manage indexes, and design for scalability",
        "I can architect enterprise-level data warehouses, ensure high availability, and set up complex replication"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How would you describe your understanding of computer networks and protocols?",
      options: [
        "I know basic terms like IP address and router but lack practical experience",
        "I can set up a home network and troubleshoot simple connectivity issues",
        "I understand core concepts like TCP/IP, DNS, DHCP, and can configure a small office/router",
        "I can manage and troubleshoot complex network configurations and services",
        "I can design, secure, and troubleshoot complex enterprise networks"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How would you rate your knowledge of securing systems and protecting data?",
      options: [
        "I know basic best practices like using strong passwords",
        "I understand common threats (phishing, malware) and use antivirus software",
        "I can configure firewalls, manage access controls, and understand encryption",
        "I can conduct vulnerability assessments and implement robust security architectures",
        "I can lead an organization's security strategy and respond to advanced persistent threats"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How skilled are you at analyzing data to find patterns and insights?",
      options: [
        "I can read basic charts and graphs",
        "I can use basic functions in a tool like Excel to sort, filter, and create simple charts",
        "I can perform data cleaning, exploratory analysis, and create meaningful dashboards",
        "I can use SQL/R/Python for complex analysis and create predictive models",
        "I can design and implement advanced statistical models and analytical frameworks for an organization"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How proficient are you in building and deploying machine learning models?",
      options: [
        "I understand basic ML concepts but have not built a model",
        "I have used pre-built AI services or libraries to complete a tutorial",
        "I can preprocess data, train, and evaluate standard models like regression or classification",
        "I can design and implement complex models (e.g., deep learning) and tune them for production",
        "I can lead ML research, develop new algorithms, and deploy large-scale AI systems"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How would you rate your ability to build and deploy websites or web applications?",
      options: [
        "I understand what HTML/CSS/JavaScript are but have little practical experience",
        "I can create a simple static webpage with HTML and CSS",
        "I can build dynamic, full-stack applications using a common framework",
        "I can build complex, full-stack applications with databases, APIs, and user authentication",
        "I can architect and lead the development of secure, scalable, and complex full-stack web applications"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How skilled are you at managing and maintaining computer systems and servers?",
      options: [
        "I can use an operating system but lack administrative skills",
        "I can perform basic updates and user support tasks",
        "I can manage user accounts, install software, and perform basic troubleshooting on servers",
        "I can automate server management, ensure high availability, and handle complex deployments",
        "I can design and manage entire enterprise IT infrastructures and disaster recovery plans"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How proficient are you in using cloud platforms (e.g., AWS, Azure, GCP)?",
      options: [
        "I have an account and have used basic console features",
        "I have completed introductory tutorials on core services",
        "I can deploy and manage virtual servers, storage, and databases in the cloud",
        "I can design and deploy complex, multi-service applications using infrastructure-as-code",
        "I can design, secure, and automate entire enterprise-scale cloud architectures"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How would you rate your ability to ensure software quality and find bugs?",
      options: [
        "I test my own code informally",
        "I can perform manual testing based on a predefined checklist",
        "I can write and run automated unit and integration tests",
        "I can write and maintain extensive automated test suites for complex applications",
        "I can design and implement a comprehensive QA strategy, including automation frameworks and CI/CD integration"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How well can you analyze information objectively and make a reasoned judgment?",
      options: [
        "I often accept information at face value and struggle to identify bias",
        "I can sometimes question information but lack a structured approach",
        "I can usually break down problems, identify assumptions, and evaluate evidence",
        "I consistently identify logical flaws, consider multiple perspectives, and make well-reasoned decisions under uncertainty",
        "I am sought out to solve ambiguous problems and can teach structured critical thinking techniques to others"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How effectively do you approach and resolve complex challenges?",
      options: [
        "I get easily stuck and rely heavily on others for solutions",
        "I can solve straightforward problems with a clear path",
        "I can break down moderately complex problems and develop effective solutions",
        "I can deconstruct highly complex problems, develop multiple solutions, and implement the optimal one",
        "I excel at solving novel, complex problems and innovating new solutions that become best practices"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How effectively can you convey information and ideas to others?",
      options: [
        "I often struggle to organize my thoughts and convey them clearly",
        "I can communicate simple ideas clearly, but complex topics are challenging",
        "My messages are usually clear and tailored to the audience, both in writing and speaking",
        "I can clearly articulate complex topics to technical and non-technical audiences and actively manage project communications",
        "I can expertly communicate complex topics to any audience and am highly skilled in facilitation and negotiation"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How well do you collaborate within a group to achieve a common goal?",
      options: [
        "I prefer to work alone and find group work challenging",
        "I complete my assigned tasks but rarely initiate collaboration",
        "I actively participate, share ideas, and support my teammates",
        "I proactively coordinate with others, resolve conflicts, and ensure the team stays aligned",
        "I actively foster a collaborative environment, mentor teammates, and successfully lead team initiatives"
      ],
      points: [1, 2, 3, 4, 5]
    },
    {
      text: "How well do you adjust to new situations, technologies, or changing requirements?",
      options: [
        "I strongly prefer routine and find change difficult",
        "I can adapt to minor changes but struggle with major shifts",
        "I generally handle change well and can adjust my approach as needed",
        "I actively seek out new challenges and quickly master new tools or processes",
        "I thrive in rapidly changing environments and often drive organizational change initiatives"
      ],
      points: [1, 2, 3, 4, 5]
    }
  ];
  const totalQuestions = questions.length;
  let currentQuestion = 1;
  const selectedAnswers = new Array(totalQuestions).fill(null);
  const selectedPoints = new Array(totalQuestions).fill(null);

  function updateQuestionUI() {
    const questionNumberElem = document.querySelector(".question-number");
    const progressElem = document.querySelector(".progress-bar");
    const questionTextElem = document.querySelector(".question-text");
    const optionsContainer = document.querySelector(".question-box");
    const btnNext = document.querySelector(".btnnext");
    const btnPrev = document.querySelector(".btnprev");

    questionNumberElem.textContent = `Question ${currentQuestion} of ${totalQuestions}`;
    const progressPercent = (currentQuestion / totalQuestions) * 100;
  progressElem.style.width = `${progressPercent}%`;
  progressElem.classList.remove("progress-low", "progress-mid", "progress-high");

  if (progressPercent <= 33) {
    progressElem.classList.add("progress-low");
  } else if (progressPercent <= 66) {
    progressElem.classList.add("progress-mid");
  } else {
    progressElem.classList.add("progress-high");
  }

    const q = questions[currentQuestion - 1];
    questionTextElem.textContent = q.text;

    const existingOptions = optionsContainer.querySelectorAll(".option");
    existingOptions.forEach(opt => opt.remove());

    q.options.forEach((optText, index) => {
      const div = document.createElement("div");
      div.className = "option";
      div.textContent = `${String.fromCharCode(65 + index)}. ${optText}`;
      div.addEventListener("click", () => selectOption(div, index));
      if (selectedAnswers[currentQuestion - 1] === index) div.classList.add("selected");
      optionsContainer.appendChild(div);
    });

    if (currentQuestion === totalQuestions) {
      btnNext.textContent = "Submit";
      btnNext.onclick = showSummary;
    } else {
      btnNext.textContent = "Next >";
      btnNext.onclick = nextQuestion;
    }
    btnPrev.style.visibility = currentQuestion === 1 ? "hidden" : "visible";
  }

  function nextQuestion() {
    const selected = document.querySelector('.option.selected');
    if (!selected) {
      alert('Please select an option first.');
      return;
    }

    if (currentQuestion < totalQuestions) {
      currentQuestion++;
      updateQuestionUI();
    }
  }

  function prevQuestion() {
    if (currentQuestion > 1) {
      currentQuestion--;
      updateQuestionUI();
    }
  }

  function selectOption(el, index) {
    const options = document.querySelectorAll('.option');
    options.forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    
    selectedAnswers[currentQuestion - 1] = index;
    selectedPoints[currentQuestion - 1] = questions[currentQuestion - 1].points[index];
  }

  function goToQuestion(index) {
    const modal = document.querySelector('.summary-modal');
    if (modal) modal.remove();
    if (index >= 0 && index < totalQuestions) {
      currentQuestion = index + 1;
      updateQuestionUI();
    }
  }


  function submitAnswers() {
    const unanswered = selectedAnswers.findIndex(ans => ans === null);
    if (unanswered !== -1) {
      alert(`Please answer all questions before submitting. Unanswered: Q${unanswered + 1}`);
      return;
    }
    const modal = document.querySelector('.summary-modal');
    if (modal) modal.remove();

    console.log('Submitted Answers:', selectedAnswers);
    console.log('Submitted Points:', selectedPoints);

    // Debug: Check if points are working correctly
    console.log('=== DEBUG POINTS CHECK ===');
    questions.forEach((q, index) => {
      const answerIndex = selectedAnswers[index];
      const points = selectedPoints[index];
      const answerText = q.options[answerIndex];
      console.log(`Q${index + 1}: Answer "${String.fromCharCode(65 + answerIndex)}" → ${points} points → "${answerText}"`);
    });
    console.log('Total Points Array:', selectedPoints);
    console.log('Array Length:', selectedPoints.length);
    console.log('All values are numbers:', selectedPoints.every(val => typeof val === 'number'));
    console.log('All values between 1-5:', selectedPoints.every(val => val >= 1 && val <= 5));
    console.log('=== END DEBUG ===');

    // Calculate total scores for each skill category
    const skillCategories = [
      { name: "Programming", scores: selectedPoints.slice(0, 1) },
      { name: "Database Management", scores: selectedPoints.slice(1, 2) },
      { name: "Networking", scores: selectedPoints.slice(2, 3) },
      { name: "Cybersecurity", scores: selectedPoints.slice(3, 4) },
      { name: "Data Analysis", scores: selectedPoints.slice(4, 5) },
      { name: "Machine Learning/AI", scores: selectedPoints.slice(5, 6) },
      { name: "Web Development", scores: selectedPoints.slice(6, 7) },
      { name: "System Administration", scores: selectedPoints.slice(7, 8) },
      { name: "Cloud Computing", scores: selectedPoints.slice(8, 9) },
      { name: "Software Testing/QA", scores: selectedPoints.slice(9, 10) },
      { name: "Critical Thinking", scores: selectedPoints.slice(10, 11) },
      { name: "Problem Solving", scores: selectedPoints.slice(11, 12) },
      { name: "Communication", scores: selectedPoints.slice(12, 13) },
      { name: "Teamwork", scores: selectedPoints.slice(13, 14) },
      { name: "Adaptability", scores: selectedPoints.slice(14, 15) }
    ];

    // Debug skill categories
    console.log('=== SKILL CATEGORIES DEBUG ===');
    skillCategories.forEach((category, index) => {
      console.log(`${category.name}:`, category.scores);
    });
    console.log('=== END SKILL CATEGORIES DEBUG ===');

    const totalScore = selectedPoints.reduce((sum, points) => sum + points, 0);
    const maxScore = totalQuestions * 5;

    // Store basic results in localStorage (existing)
    localStorage.setItem('skillAssessmentResults', JSON.stringify({
      answers: selectedAnswers,
      points: selectedPoints,
      skillCategories: skillCategories,
      totalScore: totalScore,
      maxScore: maxScore
    }));

    // Show preview of what will be sent to API
    const previewData = {
      skills: selectedPoints,
      skill_breakdown: skillCategories.map(cat => ({
        skill: cat.name,
        rating: cat.scores[0]
      }))
    };

    console.log('=== DATA BEING SENT TO API ===');
    console.log('Skills Array:', selectedPoints);
    console.log('Full Payload:', previewData);
    console.log('=== END API DATA ===');

  

    // Call the recommendation API
    fetch('http://localhost:5000/recommend', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        skills: selectedPoints
      })
    })
    .then(response => {
      console.log('API Response Status:', response.status);
      console.log('API Response Headers:', response.headers);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('API Response Data:', data);
      
      // Store API results in sessionStorage
      const sessionData = {
        api_results: data,
        user_skills: selectedPoints,
        skill_categories: skillCategories,
        timestamp: new Date().toISOString()
      };
      
      sessionStorage.setItem('jobRecommendationResults', JSON.stringify(sessionData));
      
     
      // Show the success modal
      const confirmModal = document.createElement("div");
      confirmModal.className = "summary-modal";
      confirmModal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-dialog" style="text-align:center; padding: 2rem 2.5rem;">
          <img src="../image/suctest.png" alt="Check" style="width: 200px; height="200px"; margin-bottom: 1rem;">
          <h2 style="font-size: 3rem; margin-bottom: .8rem; color: #002366; font-weight="bolder";">Submission Complete</h2>
          <p style="margin: 0.5rem 0; font-size: 1.4rem; color: #333;">Thank you for completing the assessment.</p>
          <p style="font-size: 1.3rem; margin-bottom: 3rem;">Results have been saved to your session.</p>
          <a href="../php/result.php" class="btn-view-result" style="padding: 0.75rem 2rem; background-color: #002366; color: #fff; text-decoration: none; border-radius: 5px; font-size: 1.4rem;">Check Your Result</a>
        </div>
      `;
      document.body.appendChild(confirmModal);
    })
    .catch(error => {
      console.error('API Error Details:', error);
      
      // Store empty results in sessionStorage even if API fails
      const sessionData = {
        api_results: null,
        user_skills: selectedPoints,
        skill_categories: skillCategories,
        timestamp: new Date().toISOString(),
        error: error.message
      };
      
      sessionStorage.setItem('jobRecommendationResults', JSON.stringify(sessionData));
      
      alert(`API Error: ${error.message}\n\nYour answers have been saved locally. You can try again later.`);
      
      // Show the success modal even if API fails
      const confirmModal = document.createElement("div");
      confirmModal.className = "summary-modal";
      confirmModal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-dialog" style="text-align:center; padding: 2rem 2.5rem;">
          <img src="../image/suctest.png" alt="Check" style="width: 200px; height="200px"; margin-bottom: 1rem;">
          <h2 style="font-size: 3rem; margin-bottom: .8rem; color: #002366; font-weight="bolder";">Submission Complete</h2>
          <p style="margin: 0.5rem 0; font-size: 1.4rem; color: #333;">Thank you for completing the assessment.</p>
          <p style="font-size: 1.3rem; margin-bottom: 3rem; color: #e74c3c;">Note: Job recommendations are temporarily unavailable.</p>
          <a href="../php/result.php" class="btn-view-result" style="padding: 0.75rem 2rem; background-color: #002366; color: #fff; text-decoration: none; border-radius: 5px; font-size: 1.4rem;">Check Your Result</a>
        </div>
      `;
      document.body.appendChild(confirmModal);
    });
  }


  function showSummary() {
    const selected = document.querySelector('.option.selected');
    if (!selected) {
      alert('Please select an option first.');
      return;
    }

    const index = Array.from(document.querySelectorAll('.option')).indexOf(selected);
    selectedAnswers[currentQuestion - 1] = index;
    selectedPoints[currentQuestion - 1] = questions[currentQuestion - 1].points[index];

    const modalContent = document.createElement("div");
    modalContent.className = "summary-modal";
    modalContent.innerHTML = `
      <div class="modal-overlay"></div>
      <div class="modal-dialog">
        <h2>Your Answers Summary</h2>
        <p style="margin-bottom: 10px; font-style: italic; font-size: 1.3rem; color: #444;">Please review your answers below before submitting:</p>
        <div class="summary-list">
          ${questions.map((q, i) => {
            const ans = selectedAnswers[i];
            const atext = ans !== null ? `${String.fromCharCode(65 + ans)}. ${q.options[ans]}` : "<span style='color:red'>No answer</span>";
            return `<p style="display:flex; justify-content:space-between; align-items:center;"><span><strong>Q${i + 1}:</strong> ${atext}</span><button onclick=\"goToQuestion(${i})\">Edit</button></p>`;
          }).join('')}
        </div>
      <div style="margin-top: 20px; display: flex; justify-content: space-between;">
    <button onclick="this.closest('.summary-modal').remove()">Close</button>
    <button onclick="submitAnswers()" style="margin-left: 10px;">Confirm Submit</button>
  </div>
      </div>
    `;
    document.body.appendChild(modalContent);

    document.querySelector('.summary-list').scrollTop = 0;
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateQuestionUI();
    document.querySelector('.btnprev').addEventListener('click', prevQuestion);
  });
  </script>

  </body>
  </html>
