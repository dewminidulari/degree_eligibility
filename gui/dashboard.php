<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty of Science – Physical Science (PS) GPA Calculator</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
</head>

<body>
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="logo-icon"><span class="material-icons">school</span></div>
      <h2>Faculty of Science</h2>
      <p>University of Kelaniya</p>
    </div>
    <div class="sidebar-avatar">
      <div class="avatar-circle"><span class="material-icons">account_circle</span></div>
      <div class="avatar-info"><span>Admin User</span><small>Administrator</small></div>
    </div>
    <div class="nav-section-label">Navigation</div>
    <nav>
      <a class="nav-item active" onclick="showPage('home', this)"><span class="icon"><span class="material-icons">dashboard</span></span> Home</a>
      <a class="nav-item" onclick="showPage('students', this)"><span class="icon"><span class="material-icons">people</span></span> Student Records</a>
      <a class="nav-item" onclick="showPage('courses', this)"><span class="icon"><span class="material-icons">menu_book</span></span> Course Management</a>
      <a class="nav-item" onclick="showPage('gpa', this)"><span class="icon"><span class="material-icons">calculate</span></span> GPA Calculator</a>
      <a class="nav-item" onclick="showPage('eligibility-predictor', this)"><span class="icon"><span class="material-icons">check_circle_outline</span></span> Degree Eligibility & Class Predictor</a>
    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" onclick="confirmLogout()">
        <span class="material-icons">logout</span> &nbsp;Log Out
      </button>
    </div>

  </aside>

  <main class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h1 id="page-title">Dashboard Overview</h1>
        <span id="page-sub">Faculty of Science · Academic Portal</span>
      </div>
      <div class="topbar-right">
        <div style="font-size:13px;" id="current-time"></div>
      </div>
    </div>

    <!-- HOME PAGE (fully implemented) -->
    <div class="page active" id="page-home">
      <div class="filters-row">
        <div class="filter-group"><label>Academic Year</label><select id="home-year" onchange="updateDashboard()">
            <option value="all">All Years</option>
            <option value="2024-25">2024 / 2025</option>
            <option value="2023-24">2023 / 2024</option>
            <option value="2022-23">2022 / 2023</option>
            <option value="2021-22">2021 / 2022</option>
            <option value="2020-21">2020 / 2021</option>
            <option value="2019-20">2019 / 2020</option>
            <option value="2018-19">2018 / 2019</option>
          </select></div>
        <div class="filter-group"><label>Degree Program</label><select id="home-degree" onchange="updateDashboard()">
            <option value="all">All Programs</option>
            <option value="PS">Physical Science</option>
            <option value="BS">Bio Science</option>
            <option value="SS">Sport Science</option>
            <option value="EC">Electronic and Computer Science</option>
            <option value="SE">Software Engineering</option>
            <option value="PE">Physics and Electronic</option>
            <option value="EN">Environmental Conservation and Management</option>
            <option value="AC">Applied Chemistry</option>
          </select></div>
        <div class="filter-group"><label>Year of Study</label><select id="home-studyyear" onchange="updateDashboard()">
            <option value="all">All Years</option>
            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
            <option value="3">Year 3</option>
            <option value="4">Year 4</option>
          </select></div>
      </div>
      <div class="section-heading">Overview</div>
      <div class="stats-grid">
        <div class="stat-card teal">
          <div class="icon-wrap"><span class="material-icons">group</span></div>
          <div class="label">Total Students</div>
          <div class="value" id="stat-total">0</div>
          <div class="sublabel">Registered Students</div>
        </div>
        <div class="stat-card gold">
          <div class="icon-wrap"><span class="material-icons">school</span></div>
          <div class="label">Eligible for Graduation</div>
          <div class="value" id="stat-grad">0</div>
          <div class="sublabel">Meeting all requirements</div>
        </div>
        <div class="stat-card pass">
          <div class="icon-wrap"><span class="material-icons">check_circle</span></div>
          <div class="label">Overall Pass Rate</div>
          <div class="value" id="stat-passrate">0%</div>
          <div class="sublabel">Current academic year</div>
        </div>
        <div class="stat-card danger">
          <div class="icon-wrap"><span class="material-icons">warning</span></div>
          <div class="label">At Risk</div>
          <div class="value" id="stat-risk">0</div>
          <div class="sublabel">GPA below threshold</div>
        </div>
      </div>
      <div class="section-heading">Classification Distribution</div>
      <div class="chart-container">
        <div class="donut-wrapper"><canvas id="donutChart" class="donut-canvas" width="200" height="200"></canvas></div>
        <div class="donut-legend" id="donut-legend"></div>
      </div>
      <div class="section-heading">By Classification</div>
      <div class="class-grid" id="classification-grid">
        <div class="class-card c-first"><span class="class-label">First Class</span><span class="class-count" id="cls-first">0</span></div>
        <div class="class-card c-su"><span class="class-label">Second Upper</span><span class="class-count" id="cls-su">0</span></div>
        <div class="class-card c-sl"><span class="class-label">Second Lower</span><span class="class-count" id="cls-sl">0</span></div>
        <div class="class-card c-pass"><span class="class-label">Pass</span><span class="class-count" id="cls-pass">0</span></div>
        <div class="class-card c-fail"><span class="class-label">Fail</span><span class="class-count" id="cls-fail">0</span></div>
      </div>
    </div>

    <!-- STUDENT RECORDS PAGE (fully implemented) -->
    <div class="page" id="page-students">
      <div class="filters-row">
        <div class="filter-group"><label>Academic Year</label><select id="student-year" onchange="filterStudents()">
            <option value="all">All Years</option>
            <option value="2024-25">2024 / 2025</option>
            <option value="2023-24">2023 / 2024</option>
            <option value="2022-23">2022 / 2023</option>
            <option value="2021-22">2021 / 2022</option>
            <option value="2020-21">2020 / 2021</option>
            <option value="2019-20">2019 / 2020</option>
            <option value="2018-19">2018 / 2019</option>
          </select></div>
        <div class="filter-group"><label>Degree Program</label><select id="student-program" onchange="filterStudents()">
            <option value="all">All Programs</option>
            <option value="PS">Physical Science</option>
            <option value="BS">Bio Science</option>
            <option value="SS">Sport Science</option>
            <option value="EC">Electronic and Computer Science</option>
            <option value="SE">Software Engineering</option>
            <option value="PE">Physics and Electronic</option>
            <option value="EN">Environmental Conservation and Management</option>
            <option value="AC">Applied Chemistry</option>
          </select></div>
        <div class="filter-group"><label>Year of Study</label><select id="student-year-level" onchange="filterStudents()">
            <option value="all">All Years</option>
            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
            <option value="3">Year 3</option>
            <option value="4">Year 4</option>
          </select></div>
        <div class="filter-group"><label>Classification</label><select id="student-class" onchange="filterStudents()">
            <option value="all">All</option>
            <option value="First Class">First Class</option>
            <option value="Second Upper">Second Upper</option>
            <option value="Second Lower">Second Lower</option>
            <option value="Pass">Pass</option>
            <option value="Fail">Fail</option>
          </select></div>
      </div>
      <div class="table-wrap">
        <div class="table-header">
          <h3>Student Records</h3>
          <div style="display:flex; gap:10px; align-items:center;">
            <input class="search-box" type="text" placeholder="Search by ID, name, or program..." oninput="searchStudents(this.value)">
            <span class="student-count" id="student-count">0 of 0 students</span>
            <button class="add-btn" onclick="addStudent()"><span class="material-icons">person_add</span> Add New</button>
          </div>
        </div>
        <table id="student-table">
          <thead>
            <tr>
              <th>Student No</th>
              <th>Name with Initials</th>
              <th>Full Name</th>
              <th>Program</th>
              <th>Year</th>
              <th>Total Credits</th>
              <th>GPA</th>
              <th>Eligibility</th>
              <th>Classification</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="student-tbody"></tbody>
        </table>
      </div>
    </div>

    <!-- GPA CALCULATOR PAGE (with automatic cross-check) -->
    <div class="page" id="page-gpa">
      <div class="section-heading">GPA Calculator</div>
      <p class="section-sub">Select a student, load their courses, then calculate GPA.</p>

      <div class="gpa-card">
        <div class="gpa-row">
          <div class="gpa-field">
            <label>Student Number</label>
            <input type="text" id="gpa-student-id" placeholder="e.g. PS/2020/001" value="PS/2022/147">
          </div>
          <button class="calc-btn" onclick="loadStudentCourses()">Load Courses</button>
        </div>
      </div>

      <div class="gpa-card" id="gpa-courses-container" style="display: none;">
        <h3 style="margin-bottom:16px;">Student Course List</h3>
        <div class="gpa-calc-table">
          <table id="gpa-course-table">
            <thead>
              <tr>
                <th>Course Code</th>
                <th>Grade</th>
                <th>Grade Point</th>
                <th>Credits</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody id="gpa-course-tbody">
              <!-- populated by JavaScript -->
            </tbody>
          </table>
        </div>
        <div style="display: flex; justify-content: flex-end; margin: 20px 0 10px;">
          <button class="calc-btn" onclick="computeGPAFromTable()">Calculate GPA</button>
        </div>
        <div id="gpa-computed-result" class="gpa-result" style="display: none;">
          <!-- GPA result will be shown here -->
        </div>

        <!-- VERIFICATION SECTION (matches image) -->
        <div class="gpa-card" id="gpa-verify-section" style="display: none; margin-top: 20px; background: #F8F9FB;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0;">Verify GPA</h3>
            <span class="success-badge" id="verify-badge" style="display: none;">Verified ✓</span>
          </div>
          <div class="gpa-row">
            <div class="gpa-field">
              <label>Student Number</label>
              <input type="text" id="verify-student-id" readonly value="">
            </div>
            <div class="gpa-field">
              <label>Academic Year (cumulative)</label>
              <input type="text" id="verify-academic-year" readonly value="All Years">
            </div>
            <div class="gpa-field">
              <label>Official GPA (cumulative)</label>
              <span id="verify-official-gpa-display" style="font-size: 24px; font-family: 'DM Serif Display', serif; color: var(--pass);">—</span>
            </div>
          </div>
          <div id="verify-result" style="margin-top: 20px;"></div>
        </div>
      </div>

      <div id="gpa-notfound" class="gpa-card" style="display: none; text-align: center; color: var(--danger);">
        ❌ Student not found. Please check the number.
      </div>
    </div>

    <!-- DEGREE ELIGIBILITY & PREDICTOR PAGE (fully implemented) -->
    <div class="page" id="page-eligibility-predictor">
      <div class="section-heading">Degree Eligibility & Classification Predictor</div>
      <p class="section-sub">Enter a student number to check eligibility and predict degree classification</p>
      <div class="combined-container">
        <div class="gpa-card" style="padding: 24px;">
          <div class="gpa-row">
            <div class="gpa-field"><label>Student Number</label><input type="text" id="combined-student-id" placeholder="e.g. PS/2020/001"></div>
            <button class="calc-btn" onclick="loadCombinedData()">Check</button>
          </div>
        </div>
        <div id="combined-results" style="display: none;">
          <div class="student-details-card" id="student-details"></div>
          <div id="eligibility-badge-container" style="text-align: center;"></div>
          <div id="classification-result" class="result-class" style="display: none;">
            <div class="class-title" id="predicted-class-title"></div>
            <div class="class-gpa" id="predicted-gpa"></div>
          </div>
          <div id="not-eligible-message" class="not-eligible-message" style="display: none;">
            <strong>⚠️ Not Eligible for Graduation</strong>
            <p style="margin-top: 10px; color: var(--muted);">This student does not meet the requirements for graduation. Classification cannot be predicted.</p>
          </div>
          <div style="margin-top: 30px;">
            <h3 style="margin-bottom: 12px;">Degree Classification Standards</h3>
            <table class="standards-table">
              <thead>
                <tr>
                  <th>Classification</th>
                  <th>GPA Range</th>
                  <th>Description</th>
                  <th>Recognition</th>
                </tr>
              </thead>
              <tbody>
                <tr class="first-class-row">
                  <td>First Class</td>
                  <td>3.70 - 4.00</td>
                  <td>Outstanding academic achievement with exceptional performance</td>
                  <td>Honors</td>
                </tr>
                <tr class="second-upper-row">
                  <td>Second Upper Class</td>
                  <td>3.30 - 3.69</td>
                  <td>Very good academic performance demonstrating strong understanding</td>
                  <td>Merit</td>
                </tr>
                <tr class="second-lower-row">
                  <td>Second Lower Class</td>
                  <td>2.70 - 3.29</td>
                  <td>Good academic performance with solid grasp subject matter</td>
                  <td>Standard</td>
                </tr>
                <tr class="pass-row">
                  <td>Pass</td>
                  <td>2.00 - 2.69</td>
                  <td>Satisfactory performance meeting minimum degree requirements</td>
                  <td>Pass</td>
                </tr>
                <tr class="fail-row">
                  <td>Fail</td>
                  <td>Below 2.00</td>
                  <td>Does not meet the academic standards for graduation</td>
                  <td>Fail</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div id="combined-notfound" class="gpa-card" style="display: none; text-align: center; color: var(--danger);">❌ Student not found. Please check the number.</div>
      </div>
    </div>

    <!-- COURSE MANAGEMENT PAGE (fully implemented) -->
    <div class="page" id="page-courses">
    <div class="course-management-container">
      <div class="header-actions">
        <h2>Course Management</h2>
        <p class="subtitle">Manage and view all course details</p>

        <div class="search-add-container">
          <div class="search-box">
            <span class="material-icons">search</span>
            <input type="text"
              id="search-input"
              placeholder="Search by ID, name..."
              onkeyup="searchCourses(this.value)">
          </div>

          <div class="course-info">
            <span id="course-count">0 of 0 courses</span>

            <button class="btn-add" onclick="addNewCourse()">
              <span class="material-icons">add</span>
              Add New Course
            </button>

          </div>
        </div>
      </div>

      <table class="course-table">
        <thead>
          <tr>
            <th>Module Code & Name</th>
            <th>Credits</th>
            <th>GPA Status</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="course-tbody">
          <tr>
            <td colspan="5" style="text-align: center;">Loading courses...</td>
          </tr>
        </tbody>
      </table>

      <!-- Add Course Modal (Popup) -->

      <div id="addCourseModal" class="modal" style="display: none;">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New Course</h3>
            <span class="close" onclick="hideAddModal()">&times;</span>
          </div>

          <div class="modal-body">
            <form onsubmit="saveNewCourse(event)">
              <div class="form-group">
                <label>Module Code *</label>
                <input type="text" id="new_module_code" required placeholder="e.g., COSC 21052">
              </div>

              <div class="form-group">
                <label>Module Name *</label>
                <input type="text" id="new_module_name" required placeholder="Enter module name">
              </div>

              <div class="form-group">
                <label>Credit Value *</label>
                <input type="number" id="new_credit_value" required min="1" max="10" placeholder="Enter credit value">
              </div>

              <div class="form-group">
                <label>GPA Module *</label>
                <select id="new_is_gpa_module" required>
                  <option value="">Select GPA Status</option>
                  <option value="1">GPA Module</option>
                  <option value="0">Non-GPA Module</option>
                </select>
              </div>

              <div class="form-group">
                <label>Module Status *</label>
                <select id="new_module_status" required>
                  <option value="">Select Status</option>
                  <option value="Compulsory">Compulsory</option>
                  <option value="Elective">Elective</option>
                </select>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="hideAddModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Course</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
    </div>

  </main>

  <script src="../assets/js/dashboard.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</body>

</html>