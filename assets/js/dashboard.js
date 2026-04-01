
// ================== DATA MODEL – PHYSICAL SCIENCE ONLY ==================
const academic_years = [
  { academic_year_id: 0, year_label: '2018/2019' },
  { academic_year_id: 1, year_label: '2019/2020' },
  { academic_year_id: 2, year_label: '2020/2021' },
  { academic_year_id: 3, year_label: '2021/2022' },
  { academic_year_id: 4, year_label: '2022/2023' },
  { academic_year_id: 5, year_label: '2023/2024' },
  { academic_year_id: 6, year_label: '2024/2025' }
];

const programs = [
  { program_id: 1, program_name: 'Physical Science', program_code: 'PS', max_year: 3 },
  { program_id: 2, program_name: 'Bio Science', program_code: 'BS', max_year: 3 },
  { program_id: 3, program_name: 'Sport Science', program_code: 'SS', max_year: 4 },
  { program_id: 4, program_name: 'Electronic and Computer Science', program_code: 'EC', max_year: 4 },
  { program_id: 5, program_name: 'Software Engineering', program_code: 'SE', max_year: 4 },
  { program_id: 6, program_name: 'Physics and Electronics', program_code: 'PE', max_year: 3 },
  { program_id: 7, program_name: 'Environmental Conservation and Management', program_code: 'EN', max_year: 3 },
  { program_id: 8, program_name: 'Applied Chemistry', program_code: 'AC', max_year: 4 }
];

// student_info will be populated from the server
let student_info = [];

let enrollment = [];
let gpa_records = [];

function buildData() {
  const map = {
    'PS/2018/001': [{ year: '2018/2019', level: 1, credits: 30, gpa: 3.75 }, { year: '2019/2020', level: 2, credits: 30, gpa: 3.80 }, { year: '2020/2021', level: 3, credits: 30, gpa: 3.85 }],
    'PS/2019/101': [{ year: '2019/2020', level: 1, credits: 30, gpa: 3.60 }, { year: '2020/2021', level: 2, credits: 30, gpa: 3.70 }, { year: '2021/2022', level: 3, credits: 30, gpa: 3.80 }],
    'PS/2019/105': [{ year: '2019/2020', level: 1, credits: 30, gpa: 2.80 }, { year: '2020/2021', level: 2, credits: 30, gpa: 3.00 }, { year: '2021/2022', level: 3, credits: 30, gpa: 2.90 }],
    'PS/2020/015': [{ year: '2020/2021', level: 1, credits: 30, gpa: 2.50 }, { year: '2021/2022', level: 2, credits: 30, gpa: 2.60 }, { year: '2022/2023', level: 3, credits: 30, gpa: 2.70 }],
    'PS/2021/022': [{ year: '2021/2022', level: 1, credits: 30, gpa: 3.20 }, { year: '2022/2023', level: 2, credits: 30, gpa: 3.30 }, { year: '2023/2024', level: 3, credits: 30, gpa: 3.40 }],
    'PS/2022/142': [{ year: '2022/2023', level: 1, credits: 27, gpa: 3.42 }],
    'PS/2022/147': [{ year: '2022/2023', level: 1, credits: 27, gpa: 3.45 }],
    'PS/2022/157': [{ year: '2022/2023', level: 1, credits: 27, gpa: 2.84 }],
    'PS/2022/168': [{ year: '2022/2023', level: 1, credits: 27, gpa: 1.02 }],
    'PS/2022/169': [{ year: '2022/2023', level: 1, credits: 27, gpa: 1.80 }]
  };

  let eid = 1;
  for (let sno in map) {
    for (let c of map[sno]) {
      let ay = academic_years.find(a => a.year_label === c.year);
      if (!ay) continue;
      enrollment.push({ enrollment_id: eid++, student_no: sno, academic_year_id: ay.academic_year_id, year_of_study: c.level, status: 'Official' });
      gpa_records.push({ student_no: sno, academic_year_id: ay.academic_year_id, total_credits: c.credits, non_gpa_credits: 0, gpa_value: c.gpa });
    }
  }
}
buildData();

// Load courses when page loads
document.addEventListener('DOMContentLoaded', function () {
  loadAcademicYears();
  loadPrograms();
  loadCourses();
  loadStudents();
  setupEnterKeyActions();
});

function setupEnterKeyActions() {
  const bindEnter = (el, action) => {
    if (!el) return;
    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        action();
      }
    });
  };

  const gpaStudentInput = document.getElementById('gpa-student-id');
  bindEnter(gpaStudentInput, () => loadStudentCourses());

  const combinedStudentInput = document.getElementById('combined-student-id');
  bindEnter(combinedStudentInput, () => loadCombinedData());

  const courseSearchInput = document.getElementById('search-input');
  bindEnter(courseSearchInput, () => searchCourses(courseSearchInput.value || ''));

  const studentSearchInput = document.querySelector('#page-students .table-header .search-box');
  bindEnter(studentSearchInput, () => searchStudents(studentSearchInput.value || ''));
}

function loadAcademicYears() {
  fetch('/degree_eligibility/gui/get_academic_years.php')
    .then(r => {
      if (!r.ok) {
        throw new Error('Network response was not ok, status ' + r.status);
      }
      return r.json();
    })
    .then(data => {
      if (!data.success || !Array.isArray(data.data)) {
        throw new Error(data.error || 'Invalid academic year response');
      }
      populateAcademicYearFilters(data.data);
    })
    .catch(err => {
      console.error('Error loading academic years:', err);
    });
}

function populateAcademicYearFilters(yearRows) {
  const selects = [document.getElementById('home-year'), document.getElementById('student-year')];

  selects.forEach(select => {
    if (!select) return;

    const prevValue = select.value || 'all';
    let options = '<option value="all">All Years</option>';

    yearRows.forEach(y => {
      if (!y || y.year_label === undefined) return;
      const label = String(y.year_label).trim();
      const display = label.replace('/', ' / ');
      options += `<option value="${label}">${display}</option>`;
    });

    select.innerHTML = options;

    if (prevValue !== 'all' && yearRows.some(y => String(y.year_label) === String(prevValue))) {
      select.value = String(prevValue);
    }
  });
}

function loadPrograms() {
  fetch('/degree_eligibility/gui/get_programs.php')
    .then(r => {
      if (!r.ok) {
        throw new Error('Network response was not ok, status ' + r.status);
      }
      return r.json();
    })
    .then(data => {
      if (!data.success || !Array.isArray(data.data)) {
        throw new Error(data.error || 'Invalid program response');
      }
      populateProgramFilters(data.data);
    })
    .catch(err => {
      console.error('Error loading programs:', err);
    });
}

function populateProgramFilters(programRows) {
  const selects = [document.getElementById('home-degree'), document.getElementById('student-program')];

  selects.forEach(select => {
    if (!select) return;

    const prevValue = select.value || 'all';
    let options = '<option value="all">All Programs</option>';

    programRows.forEach(p => {
      if (!p || p.program_id === undefined || p.program_name === undefined) return;
      options += `<option value="${p.program_id}">${p.program_name}</option>`;
    });

    select.innerHTML = options;

    if (prevValue !== 'all' && programRows.some(p => String(p.program_id) === String(prevValue))) {
      select.value = String(prevValue);
    }
  });
}

// fetch students from server and update front-end arrays
// mode can be 'all' (default) or 'mismatch' to filter immediately after load
function loadStudents(searchTerm = '', mode = 'all') {
  console.log('loadStudents start', searchTerm, mode);
  let url = '/degree_eligibility/gui/get_students.php';
  if (searchTerm) {
    url += '?search=' + encodeURIComponent(searchTerm);
  }
  fetch(url)
    .then(r => {
      if (!r.ok) {
        throw new Error('Network response was not ok, status ' + r.status);
      }
      return r.json();
    })
    .then(data => {
      console.log('loadStudents response', data);
      if (data.success) {
        // store raw rows so aggregation logic can reference student_no etc
        student_info = data.data || [];

        // Fetch DETAILED student courses so that eligibility calc has full data
        student_courses = []; // Clear array to prevent duplicates on navigation
        let promises = student_info.map(s => {
          return fetch(`/degree_eligibility/gui/get_student_courses.php?student_no=${encodeURIComponent(s.student_no)}`)
            .then(r => r.json())
            .then(courseData => {
              if (courseData.success) {
                // push the detailed courses into the global array
                courseData.data.forEach(c => {
                  student_courses.push({ ...c, student_no: s.student_no });
                });
              }
            })
            .catch(err => console.error("Could not fetch detailed courses for", s.student_no));
        });

        // Wait for all courses to be seeded, then compute aggregates
        Promise.all(promises).then(() => {
          refreshStudents();
          filterStudents(mode);
          updateDashboard(); // Refresh the home dashboard with loaded data
        });

      } else {
        console.error('Error loading students:', data.error);
        document.getElementById('student-tbody').innerHTML = '<tr><td colspan="10" style="text-align:center;color:red;">Error loading students: ' + data.error + '</td></tr>';
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      let msg = 'Failed to load students';
      if (err && err.message) msg += ': ' + err.message;
      document.getElementById('student-tbody').innerHTML = `<tr><td colspan="10" style="text-align:center;color:red;">${msg}</td></tr>`;
    });
}

let all_courses = [];

function loadCourses(searchTerm = '') {
  let url = 'get_courses.php';
  // If we want to force a server search we can, but we'll prioritize loading all and filtering on client
  if (searchTerm) {
    url += '?search=' + encodeURIComponent(searchTerm);
  }

  fetch(url)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Cache courses
        all_courses = data.data;
        // If there's text in search box, filter client side, otherwise show all
        let currentSearch = document.getElementById('search-input') ? document.getElementById('search-input').value : '';
        if (currentSearch && !searchTerm) {
          searchCourses(currentSearch);
        } else {
          displayCourses(all_courses);
          if (document.getElementById('course-count')) {
            document.getElementById('course-count').textContent =
              `${all_courses.length} of ${all_courses.length} courses`;
          }
        }
      } else {
        console.error('Error loading courses:', data.error);
        // Show error in table
        document.getElementById('course-tbody').innerHTML =
          '<tr><td colspan="5" style="text-align: center; color: red;">Error loading courses: ' + data.error + '</td></tr>';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('course-tbody').innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: red;">Failed to connect to server</td></tr>';
    });
}

// Display courses in the table
function displayCourses(courses) {
  const tbody = document.getElementById('course-tbody');
  if (!tbody) return;

  if (courses.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No courses found</td></tr>';
    return;
  }

  tbody.innerHTML = courses.map(course => `
        <tr>
            <td>
                <strong>${course.module_code}</strong><br>
                <span class="module-name">${course.module_name}</span>
            </td>
            <td>${course.credit_value}</td>
            <td>
                <span class="badge ${course.is_gpa_module ? 'badge-gpa' : 'badge-non-gpa'}">
                    ${course.is_gpa_module ? 'YES' : 'NO'}
                </span>
            </td>
            <td>
              ${getModuleStatusMarkup(course.module_status)}
            </td>

            <td>
                <button class="action-btn edit-btn" onclick="editCourse('${course.module_code}')" title="Edit">
                    <span class="material-icons">edit</span>
                </button>
            </td>
        </tr>
    `).join('');
}

function normalizeModuleStatus(status) {
  const value = String(status || '').trim().toUpperCase();
  if (value === 'C' || value === 'COMPULSORY') return 'C';
  if (value === 'O' || value === 'OPTIONAL' || value === 'ELECTIVE') return 'O';
  if (value === 'A' || value === 'AUXILIARY' || value === 'AUX') return 'A';
  if (value === 'C/O' || value === 'COMPULSORY/OPTIONAL') return 'C/O';
  return '';
}

function getModuleStatusLabel(status) {
  const code = normalizeModuleStatus(status);
  if (code === 'C') return 'Compulsory';
  if (code === 'O') return 'Optional';
  if (code === 'A') return 'Auxiliary';
  if (code === 'C/O') return 'Compulsory, Optional';
  return String(status || '');
}

function getModuleStatusBadgeClass(status) {
  const code = normalizeModuleStatus(status);
  if (code === 'C') return 'badge-compulsory';
  if (code === 'O') return 'badge-elective';
  if (code === 'A') return 'badge-auxiliary';
  if (code === 'C/O') return 'badge-elective';
  return 'badge-elective';
}

function getModuleStatusMarkup(status) {
  const code = normalizeModuleStatus(status);
  if (code === 'C/O') {
    return `
      <div class="status-badge-stack">
        <span class="badge badge-compulsory">Compulsory</span>
        <span class="badge badge-elective">Optional</span>
      </div>
    `;
  }

  return `<span class="badge ${getModuleStatusBadgeClass(status)}">${getModuleStatusLabel(status)}</span>`;
}

// Search courses client-side
function searchCourses(value) {
  if (!value) {
    displayCourses(all_courses);
    if (document.getElementById('course-count')) {
      document.getElementById('course-count').textContent =
        `${all_courses.length} of ${all_courses.length} courses`;
    }
    return;
  }

  const query = String(value).toLowerCase().replace(/\s+/g, '');
  const filtered = all_courses.filter(c => {
    const mCode = c.module_code ? String(c.module_code).toLowerCase().replace(/\s+/g, '') : '';
    const mName = c.module_name ? String(c.module_name).toLowerCase() : '';
    return mCode.includes(query) || mName.includes(String(value).toLowerCase());
  });

  displayCourses(filtered);
  if (document.getElementById('course-count')) {
    document.getElementById('course-count').textContent =
      `${filtered.length} of ${all_courses.length} courses`;
  }
}

// Function to show the add course modal (popup)
function addNewCourse() {
  document.getElementById('addCourseModal').style.display = 'block';
  // Clear any previous values
  document.getElementById('new_module_code').value = '';
  document.getElementById('new_module_name').value = '';
  document.getElementById('new_credit_value').value = '';
  document.getElementById('new_is_gpa_module').value = '';
  document.getElementById('new_module_status').value = '';
}

// Function to hide the add course modal
function hideAddModal() {
  document.getElementById('addCourseModal').style.display = 'none';
}

// Function to save new course
function saveNewCourse(event) {
  event.preventDefault();
  const fileInput = document.getElementById('course_excel_file');

  // if a file was provided we do bulk import
  if (fileInput && fileInput.files.length > 0) {
    const fileData = new FormData();
    fileData.append('course_file', fileInput.files[0]);
    fetch('/degree_eligibility/gui/add_course.php', {
      method: 'POST',
      body: fileData
    })
      .then(r => r.json())
      .then(data => {
        console.log('import response', data);
        if (data.success) {
          hideAddModal();
          let msg = 'Import successful. ' + (data.imported ?? 0) + ' rows added.';
          if (typeof data.skipped !== 'undefined') {
            msg += ' ' + data.skipped + ' rows skipped.';
          }
          // use sweetalert for nicer notification
          swal({
            title: 'Success',
            text: msg,
            icon: 'success',
          }).then(() => {
            loadCourses();
          });
        } else {
          swal({
            title: 'Error',
            text: data.error,
            icon: 'error',
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to connect to server');
      });
    return;
  }

  // Get manual form data
  const formData = new FormData();
  formData.append('module_code', document.getElementById('new_module_code').value);
  formData.append('module_name', document.getElementById('new_module_name').value);
  formData.append('credit_value', document.getElementById('new_credit_value').value);
  formData.append('is_gpa_module', document.getElementById('new_is_gpa_module').value);
  formData.append('module_status', document.getElementById('new_module_status').value);

  // Send data to server
  fetch('/degree_eligibility/gui/add_course.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Hide modal
        hideAddModal();
        // Show success message
        alert('Course added successfully!');
        // Reload courses
        loadCourses();
      } else {
        alert('Error: ' + data.error);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to connect to server');
    });
}

// update manual fields enabled/disabled when file selected
const excelInput = document.getElementById('course_excel_file');
if (excelInput) {
  excelInput.addEventListener('change', function () {
    const manualFields = [
      'new_module_code',
      'new_module_name',
      'new_credit_value',
      'new_is_gpa_module',
      'new_module_status'
    ].map(id => document.getElementById(id));
    if (this.files.length > 0) {
      manualFields.forEach(el => el && (el.disabled = true));
    } else {
      manualFields.forEach(el => el && (el.disabled = false));
    }
  });
}

// Close modal when clicking outside
window.onclick = function (event) {
  const addModal = document.getElementById('addCourseModal');
  const editModal = document.getElementById('editCourseModal');
  if (event.target == addModal) {
    hideAddModal();
  }
  if (event.target == editModal) {
    hideEditModal();
  }
}

function editCourse(moduleCode) {
  const fallbackCourse = all_courses.find(c => c.module_code === moduleCode);

  // Always fetch the latest record from DB so edit values reflect current data.
  fetch('get_courses.php?search=' + encodeURIComponent(moduleCode))
    .then(response => response.json())
    .then(data => {
      let course = fallbackCourse;
      if (data.success && Array.isArray(data.data)) {
        const exact = data.data.find(c => String(c.module_code).trim() === String(moduleCode).trim());
        if (exact) course = exact;
      }

      if (!course) {
        swal({
          title: 'Error',
          text: 'Course details could not be loaded.',
          icon: 'error',
        });
        return;
      }

      document.getElementById('editCourseModal').style.display = 'block';
      document.getElementById('edit_original_module_code').value = course.module_code;
      document.getElementById('edit_module_code').value = course.module_code;
      document.getElementById('edit_module_name').value = course.module_name;
      document.getElementById('edit_credit_value').value = course.credit_value;
      document.getElementById('edit_is_gpa_module').value = course.is_gpa_module ? '1' : '0';
      document.getElementById('edit_module_status').value = normalizeModuleStatus(course.module_status);
    })
    .catch(() => {
      if (!fallbackCourse) {
        swal({
          title: 'Error',
          text: 'Failed to load course details from server.',
          icon: 'error',
        });
        return;
      }

      document.getElementById('editCourseModal').style.display = 'block';
      document.getElementById('edit_original_module_code').value = fallbackCourse.module_code;
      document.getElementById('edit_module_code').value = fallbackCourse.module_code;
      document.getElementById('edit_module_name').value = fallbackCourse.module_name;
      document.getElementById('edit_credit_value').value = fallbackCourse.credit_value;
      document.getElementById('edit_is_gpa_module').value = fallbackCourse.is_gpa_module ? '1' : '0';
      document.getElementById('edit_module_status').value = normalizeModuleStatus(fallbackCourse.module_status);
    });
}

function hideEditModal() {
  document.getElementById('editCourseModal').style.display = 'none';
}

function saveEditedCourse(event) {
  event.preventDefault();

  const formData = new FormData();
  formData.append('original_module_code', document.getElementById('edit_original_module_code').value);
  formData.append('module_code', document.getElementById('edit_module_code').value);
  formData.append('module_name', document.getElementById('edit_module_name').value);
  formData.append('credit_value', document.getElementById('edit_credit_value').value);
  formData.append('is_gpa_module', document.getElementById('edit_is_gpa_module').value);
  formData.append('module_status', document.getElementById('edit_module_status').value);

  fetch('/degree_eligibility/gui/update_course.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        hideEditModal();
        swal({
          title: 'Success',
          text: 'Course updated successfully!',
          icon: 'success',
        }).then(() => {
          loadCourses();
        });
      } else {
        swal({
          title: 'Error',
          text: data.error,
          icon: 'error',
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      swal({
        title: 'Error',
        text: 'Failed to connect to server',
        icon: 'error',
      });
    });
}


const gradePoints = { 'A+': 4.0, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7, 'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'E': 0.0 };
function getCourse(id) { return courses.find(c => c.course_id === id); }

// student_courses array reserved for any legacy code; will be populated manually if needed
let student_courses = []; // will not be populated automatically


const masterStudentFullRecords = {};
student_info.forEach(si => {
  masterStudentFullRecords[si.student_no] = {
    student_info: si,
    enrollments: enrollment.filter(e => e.student_no === si.student_no),
    gpas: gpa_records.filter(g => g.student_no === si.student_no),
    courses: student_courses.filter(sc => sc.student_no === si.student_no)
  };
});

// ---------- ELIGIBILITY LOGIC ----------
function computeStudentAggregate(sno) {
  if (!sno || typeof sno !== 'string') return null;
  const info = student_info.find(s => s.student_no === sno);
  if (!info) return null;

  const programCode = sno.split('/')[0] || '';
  const programName = (info.program_name && String(info.program_name).trim())
    ? String(info.program_name).trim()
    : 'Unknown Program';
  const dbClassification = (info.db_classification && String(info.db_classification).trim())
    ? String(info.db_classification).trim()
    : '';

  const recs = gpa_records.filter(r => r.student_no === sno);
  const totalCredits = info.total_credits !== undefined ? parseFloat(info.total_credits) : recs.reduce((s, r) => s + r.total_credits, 0);

  const officialGpa = (info.database_gpa !== undefined && info.database_gpa !== null) ? parseFloat(info.database_gpa) : 0;
  let calculatedGpa = 0;
  if (info.gpa_value !== undefined && info.gpa_value !== null) {
    calculatedGpa = parseFloat(info.gpa_value);
  } else {
    // Fallback: compute directly from detailed courses excluding ACLT/CMSK/MGMT
    let totalP = 0, totalC = 0;
    const studentCoursesRaw = student_courses.filter(c => c.student_no === sno);

    if (studentCoursesRaw.length > 0) {
      studentCoursesRaw.forEach(c => {
        let modCode = (c.module_code || c.course_id || '').trim().toUpperCase();
        let grade = c.grade_code || c.grade || '';
        let credits = parseInt(c.credit_value || c.credits || 0);
        let pts = parseFloat(c.grade_point || c.gradePoint || 0); // If pre-calculated points exist

        if (grade === 'AB' || grade === 'NULL' || grade === '') return;
        if (modCode.startsWith('ACLT') || modCode.startsWith('CMSK') || modCode.startsWith('MGMT')) return;

        // Assuming point scale based on grade if points not provided in raw data
        if (!pts && grade) {
          const map = { 'A+': 4.0, 'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7, 'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'E': 0.0 };
          pts = map[grade] !== undefined ? map[grade] : 0;
        }

        totalP += pts * credits;
        totalC += credits;
      });
      calculatedGpa = totalC ? totalP / totalC : 0;
    } else {
      recs.forEach(r => { totalP += r.gpa_value * r.total_credits; totalC += r.total_credits; });
      calculatedGpa = totalC ? totalP / totalC : 0;
    }
  }
  let overallGpa = calculatedGpa;

  // Compute years based on enrollment
  const enrolls = enrollment.filter(e => e.student_no === sno);
  const studentCourses = student_courses.filter(c => c.student_no === sno);
  const currentYear = enrolls.length ? Math.max(...enrolls.map(e => e.year_of_study)) : (studentCourses.length ? Math.max(...studentCourses.map(c => {
    let raw = (c.module_code || c.course_id || '').replace(/\s+/g, '');
    return parseInt(raw.substring(4, 5)) || 1;
  })) : 1);

  // Core eligibility variables
  let totalDCredits = 0;
  let totalCCredits = 0;
  let firstTwoYearsDCredits = 0;
  let yearThreeDCredits = 0;
  let yearlyCredits = {};

  // Additional mandatory checks
  let aclt11013CompletedWithin2Years = false;
  let cmskCompleted = false;
  let gnctCompleted = false;
  let inteCompleted = false;
  let compulsoryCCredits = 0;
  const compulsoryBelowCModules = [];
  const attendanceViolations = [];

  // Class prediction variables
  let gradeACredits = 0;
  let gradeBCredits = 0;

  // Subject specialization variables (only counting C or higher)
  let subjectRecords = {}; // Format: { "CHEM": { credits: 30, hasPractical: true } }

  const validGradesDPlus = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D'];
  const validGradesCPlus = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C'];
  const gradeRank = { 'A+': 12, 'A': 11, 'A-': 10, 'B+': 9, 'B': 8, 'B-': 7, 'C+': 6, 'C': 5, 'C-': 4, 'D+': 3, 'D': 2, 'E': 1 };
  const rank = g => gradeRank[g] || 0;

  const upperProgramName = String(programName || '').toUpperCase();
  const upperProgramCode = String(programCode || '').toUpperCase();
  const isITorMIT = /\bMIT\b|\bIT\b|INFORMATION\s*TECHNOLOGY/.test(upperProgramName) || /^(IT|MIT)$/.test(upperProgramCode);
  const isENCMorPE = /ENCM|ENVIRONMENTAL|PHYSICS\s*(AND|&)\s*ELECTRONICS|\bPE\b/.test(upperProgramName) || /^PE$/.test(upperProgramCode) || /^EN/.test(upperProgramCode);
  const isGeneralBSc = !isITorMIT && !isENCMorPE;

  studentCourses.forEach(c => {
    let grade = String(c.grade_code || c.grade || '').trim().toUpperCase();
    let credits = parseInt(c.credit_value || c.credits || 0);
    let modCodeRaw = String(c.module_code || c.course_id || '').trim();
    if (!modCodeRaw || credits === 0 || grade === 'AB' || grade === 'NULL' || grade === '') return;

    let modCode = modCodeRaw.replace(/\s+/g, '').toUpperCase();
    let moduleStatus = String(c.module_status || '').trim().toUpperCase().replace(/\s+/g, '');
    const isCmskModule = modCode.startsWith('CMSK');
    const isAclt11013 = modCode.startsWith('ACLT11013');
    const isGNCT = modCode.startsWith('GNCT');
    const isINTE = modCode.startsWith('INTE');

    let subject = modCode.substring(0, 4);
    let yearOfStudy = parseInt(modCode.substring(4, 5)) || 1;
    let isPractical = /LAB|PRACT|PRAC/.test(modCode) || modCode.endsWith('1');

    let isDOrHigher = validGradesDPlus.includes(grade);
    let isCOrHigher = validGradesCPlus.includes(grade);
    let isA = grade.startsWith('A');
    let isBOrHigher = grade.startsWith('A') || grade.startsWith('B');
    const isCompulsory = moduleStatus === 'C';
    const isCompletionGrade = grade === 'COMPLETE';

    if (isDOrHigher) {
      totalDCredits += credits;
      yearlyCredits[yearOfStudy] = (yearlyCredits[yearOfStudy] || 0) + credits;
      if (yearOfStudy === 1 || yearOfStudy === 2) firstTwoYearsDCredits += credits;
      if (yearOfStudy === 3) yearThreeDCredits += credits;

      if (isCmskModule) cmskCompleted = true;
      if (isAclt11013 && (yearOfStudy === 1 || yearOfStudy === 2)) aclt11013CompletedWithin2Years = true;
      if (isGNCT) gnctCompleted = true;
      if (isINTE) inteCompleted = true;
    }

    // CMSK can be recorded as a completion-type grade instead of letter grades.
    if (isCmskModule && isCompletionGrade) {
      cmskCompleted = true;
    }

    if (isCOrHigher) {
      totalCCredits += credits;
      if (!subjectRecords[subject]) subjectRecords[subject] = { credits: 0, hasPractical: false };
      subjectRecords[subject].credits += credits;
      if (isPractical) subjectRecords[subject].hasPractical = true;

      if (isCompulsory) compulsoryCCredits += credits;
    } else if (isCompulsory) {
      compulsoryBelowCModules.push(modCode);
    }

    // Attendance-based grade cap checks (if encoded in exam_status text)
    const examStatus = String(c.exam_status || '').toUpperCase();
    if ((examStatus.includes('<50') || examStatus.includes('BELOW 50')) && rank(grade) > rank('D')) {
      attendanceViolations.push(`${modCode}: lab attendance <50% allows max D`);
    }
    if ((examStatus.includes('50-79') || examStatus.includes('50%') && examStatus.includes('79')) && rank(grade) > rank('C')) {
      attendanceViolations.push(`${modCode}: lab attendance 50-79% allows max C`);
    }

    if (isA) gradeACredits += credits;
    if (isBOrHigher) gradeBCredits += credits;
  });

  // Calculate Duration
  let admissionYear = info.admission_year ? parseInt(info.admission_year) : new Date().getFullYear() - 3;
  let completionYear = info.completion_year ? parseInt(info.completion_year) : (admissionYear + currentYear);
  let duration = completionYear - admissionYear;
  if (duration <= 0) duration = currentYear; // Fallback if admission year missing

  // Evaluate Eligibility Rules
  let hasTwoSpecializations = false;
  let hasSpecWithPractical = false;

  let specCount = 0;
  const specializationSubjects = [];
  for (let subj in subjectRecords) {
    if (subjectRecords[subj].credits >= 24) {
      specCount++;
      specializationSubjects.push(subjectRecords[subj].credits);
      if (subjectRecords[subj].hasPractical) hasSpecWithPractical = true;
    }
  }
  hasTwoSpecializations = specCount >= 2;
  specializationSubjects.sort((a, b) => b - a);
  const topTwoSpecializationCredits = (specializationSubjects[0] || 0) + (specializationSubjects[1] || 0);

  // Program-specific minimums
  const requiredTotalCredits = isITorMIT ? 102 : 90;
  const requiredFirstTwoYearsCredits = isITorMIT ? 72 : 60;
  const requiredYearThreeCredits = 30;
  const requiredCOrBetterCredits = isITorMIT ? 85 : 72;
  const requiredCompulsoryCCredits = isITorMIT ? 68 : 0;

  const meetsYear1 = (yearlyCredits[1] || 0) >= 30;
  const meetsYear2 = (yearlyCredits[2] || 0) >= 30;
  const meetsYear3 = (yearlyCredits[3] || 0) >= requiredYearThreeCredits;
  const meetsYearlyMinimums = meetsYear1 && meetsYear2 && meetsYear3;

  // To properly test these complex rules when UI lacks detailed courses on load, 
  // we fallback to basic eligibility if we don't have full course records loaded in memory yet
  let eligible = false;
  const eligibilityReasons = [];
  if (studentCourses.length === 0 && Array.isArray(recs) && recs.length > 0) {
    // Legacy basic logic if no detailed courses available
    const meetsGpa = overallGpa >= 2.0;
    const meetsCredits = totalCredits >= requiredTotalCredits;
    eligible = meetsGpa && meetsCredits;

    if (!meetsCredits) {
      eligibilityReasons.push(`Total credits are ${totalCredits.toFixed(0)}. Minimum required is ${requiredTotalCredits}.`);
    }
    if (!meetsGpa) {
      eligibilityReasons.push(`Overall GPA is ${overallGpa.toFixed(2)}. Minimum required is 2.00.`);
    }
    eligibilityReasons.push('Full rule validation requires detailed module records.');
  } else {
    const meetsTotalD = totalDCredits >= requiredTotalCredits;
    const meetsFirstTwoYearsD = firstTwoYearsDCredits >= requiredFirstTwoYearsCredits;
    const meetsYearThreeD = yearThreeDCredits >= requiredYearThreeCredits;
    const meetsTotalC = totalCCredits >= requiredCOrBetterCredits;
    const meetsCompulsoryC = requiredCompulsoryCCredits === 0 || compulsoryCCredits >= requiredCompulsoryCCredits;
    const meetsGpa = overallGpa >= 2.0;
    const meetsDuration = duration <= 5;
    const meetsAclt = aclt11013CompletedWithin2Years;
    const meetsCmsk = cmskCompleted;
    const meetsAttendance = attendanceViolations.length === 0;

    let meetsProgramSpecificRule = true;
    if (isGeneralBSc) {
      meetsProgramSpecificRule = hasTwoSpecializations && hasSpecWithPractical;
    } else if (isENCMorPE) {
      meetsProgramSpecificRule = hasTwoSpecializations && topTwoSpecializationCredits >= 48;
    } else if (isITorMIT) {
      meetsProgramSpecificRule = meetsCompulsoryC && compulsoryBelowCModules.length === 0 && gnctCompleted && inteCompleted;
    }

    eligible = (
      meetsTotalD &&
      meetsYearlyMinimums &&
      meetsFirstTwoYearsD &&
      meetsYearThreeD &&
      meetsTotalC &&
      meetsProgramSpecificRule &&
      meetsGpa &&
      meetsDuration &&
      meetsAclt &&
      meetsCmsk &&
      meetsAttendance
    );

    if (!meetsTotalD) {
      eligibilityReasons.push(`D or above credits are ${totalDCredits}. Minimum required is ${requiredTotalCredits}.`);
    }
    if (!meetsYearlyMinimums) {
      eligibilityReasons.push(`Year-wise minimum not met (30 per year): Year 1=${yearlyCredits[1] || 0}, Year 2=${yearlyCredits[2] || 0}, Year 3=${yearlyCredits[3] || 0}.`);
    }
    if (!meetsFirstTwoYearsD) {
      eligibilityReasons.push(`D or above credits in first two years are ${firstTwoYearsDCredits}. Minimum required is ${requiredFirstTwoYearsCredits}.`);
    }
    if (!meetsYearThreeD) {
      eligibilityReasons.push(`D or above credits in Year 3 are ${yearThreeDCredits}. Minimum required is ${requiredYearThreeCredits}.`);
    }
    if (!meetsTotalC) {
      eligibilityReasons.push(`C or above credits are ${totalCCredits}. Minimum required is ${requiredCOrBetterCredits}.`);
    }
    if (!meetsProgramSpecificRule) {
      if (isGeneralBSc) {
        if (!hasTwoSpecializations) eligibilityReasons.push(`Only ${specCount} subject(s) have at least 24 credits. Minimum required is 2 subjects.`);
        if (!hasSpecWithPractical) eligibilityReasons.push('At least one subject with practical component is required.');
      } else if (isENCMorPE) {
        if (!hasTwoSpecializations) eligibilityReasons.push('At least two main subjects with 24 credits each are required.');
        if (topTwoSpecializationCredits < 48) eligibilityReasons.push(`Main-subject credits are ${topTwoSpecializationCredits}. Minimum required is 48.`);
      } else if (isITorMIT) {
        if (!meetsCompulsoryC) eligibilityReasons.push(`Compulsory modules with C or better total ${compulsoryCCredits} credits. Minimum required is ${requiredCompulsoryCCredits}.`);
        if (compulsoryBelowCModules.length > 0) eligibilityReasons.push(`Compulsory module(s) below C: ${Array.from(new Set(compulsoryBelowCModules)).slice(0, 8).join(', ')}.`);
        if (!gnctCompleted) eligibilityReasons.push('Required GNCT module(s) not completed.');
        if (!inteCompleted) eligibilityReasons.push('Required INTE module(s) not completed.');
      }
    }
    if (!meetsGpa) {
      eligibilityReasons.push(`Overall GPA is ${overallGpa.toFixed(2)}. Minimum required is 2.00.`);
    }
    if (!meetsDuration) {
      eligibilityReasons.push(`Duration is ${duration} years. Maximum allowed is 5 years.`);
    }
    if (!meetsAclt) {
      eligibilityReasons.push('ACLT 11013 must be completed within the first 2 years.');
    }
    if (!meetsCmsk) {
      eligibilityReasons.push('At least one CMSK course must be completed.');
    }
    if (!meetsAttendance) {
      eligibilityReasons.push(`Attendance-linked grade violations: ${attendanceViolations.slice(0, 4).join('; ')}.`);
    }
  }

  // Evaluate Classification Rules
  let cls = 'No Class';
  if (eligible && duration <= 3) {
    if (totalCCredits >= 90 && gradeACredits >= (totalCCredits * 0.5) && overallGpa >= 3.70) {
      cls = 'First Class';
    } else if (totalCCredits >= 80 && gradeBCredits >= (totalCCredits * 0.5) && overallGpa >= 3.30) {
      cls = 'Second Upper';
    } else if (totalCCredits >= 80 && gradeBCredits >= (totalCCredits * 0.5) && overallGpa >= 2.70) {
      cls = 'Second Lower';
    } else {
      cls = 'Pass';
    }
  } else if (eligible) {
    cls = 'Pass'; // Allowed but no class if duration > 3
  }

  // To prevent No Class showing as 'Fail' in chips
  if (!eligible) cls = 'Fail';

  return {
    id: sno, nameInitials: info.name_with_initial || '', fullName: info.full_name || '',
    program: programName, programCode: programCode, programId: info.program_id ? String(info.program_id) : '',
    dbClassification: dbClassification,
    year: currentYear, admissionYear: info.admission_year || '',
    officialGpa: officialGpa,
    calculatedGpa: calculatedGpa,
    gpa: overallGpa,
    credits: totalCredits, repeats: 0,
    elig: eligible, cls: cls,
    eligibilityReasons: eligibilityReasons,
    courses: enrolls.map(e => {
      const ay = academic_years.find(a => a.academic_year_id === e.academic_year_id);
      const g = gpa_records.find(r => r.student_no === sno && r.academic_year_id === e.academic_year_id);
      return {
        year: ay ? ay.year_label : '',
        level: e.year_of_study,
        credits: g ? g.total_credits : 0,
        gpa: g ? g.gpa_value : 0,
        status: e.status
      };
    }).sort((a, b) => a.level - b.level)
  };
}

function getAllStudentsAggregated() {
  return student_info.map(s => computeStudentAggregate(s.student_no)).filter(s => s !== null);
}
let students = getAllStudentsAggregated();
function refreshStudents() { students = getAllStudentsAggregated(); }

// ---------- UI FUNCTIONS ----------
const pageTitles = {
  home: ['Dashboard Overview', 'Faculty of Science · Academic Portal'],
  students: ['Student Records', 'Manage and view all registered students'],
  gpa: ['GPA Calculator', 'Calculate GPA from course results'],
  'eligibility-predictor': ['Degree Eligibility & Classification Predictor', 'Check eligibility and predict classification'],
  courses: ['Course Management', 'Manage and view all course details']
};
function showMismatchStudents() {
  // navigate to students page and load mismatches immediately
  showPage('students', null, { loadMode: 'mismatch' });
}

function showAtRiskStudents() {
  // navigate to students page and load at risk students immediately
  showPage('students', null, { loadMode: 'atrisk' });
}

function isAtRiskStudent(student) {
  if (!student) return false;
  // At-risk should follow full eligibility logic, not GPA-only.
  return student.elig === false || (student.gpa || 0) < 2.0 || String(student.cls || '').toLowerCase() === 'fail';
}

function showEligibleStudents() {
  // navigate to students page and load eligible students immediately
  showPage('students', null, { loadMode: 'eligible' });
}

function showPage(id, el, opts = {}) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + id).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
  document.getElementById('page-title').textContent = pageTitles[id][0];
  document.getElementById('page-sub').textContent = pageTitles[id][1];
  if (id === 'students') { loadStudents('', opts.loadMode || 'all'); }
  else if (id === 'home') { refreshStudents(); updateDashboard(); }
  else if (id === 'courses') loadCourses();
}
function tick() {
  let now = new Date();
  document.getElementById('current-time').textContent = now.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }) + '  ·  ' + now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}
setInterval(tick, 1000); tick();

function getFilteredStudents(source = 'home') {
  let yearFilter, programFilter, studyYearFilter, classFilter;
  if (source === 'home') {
    yearFilter = document.getElementById('home-year').value;
    programFilter = document.getElementById('home-degree').value;
    studyYearFilter = 'all'; // hide year‑of‑study filter on home page
    classFilter = 'all';
  } else {
    yearFilter = document.getElementById('student-year').value;
    programFilter = document.getElementById('student-program').value;
    studyYearFilter = 'all';
    classFilter = document.getElementById('student-class').value;
  }
  const filtered = students.filter(s => {
    if (!s || typeof s.id !== 'string') return false; // guard missing records
    if (programFilter !== 'all' && String(s.programId || '') !== String(programFilter)) return false;
    if (studyYearFilter !== 'all' && s.year !== parseInt(studyYearFilter)) return false;
    if (yearFilter !== 'all' && yearFilter !== 'All Years') {
      let adm = s.id ? s.id.split('/')[1] : '';
      let yearPrefixMatch = String(yearFilter).match(/\d{4}/);
      let yearPrefix = yearPrefixMatch ? yearPrefixMatch[0] : String(yearFilter);
      if (adm !== yearPrefix) return false;
    }
    if (classFilter !== 'all') {
      if (String(s.dbClassification || '') !== String(classFilter)) return false;
    }
    return true;
  });
  console.log('filtered students count', filtered.length, 'from', students.length);
  return filtered;
}

function getDashboardData() {
  let f = getFilteredStudents('home');
  let eligible = f.filter(s => s.elig);
  // count mismatches by recomputing GPA from course list vs. stored value
  let mismatch = f.filter(s => {
    // compare the two GPA values stored on the record
    return Math.abs((s.calculatedGpa || 0) - (s.officialGpa || 0)) > 0.01;
  }).length;
  return {
    total: f.length,
    grad: eligible.length,
    passrate: f.length ? Math.round((f.filter(s => s.gpa >= 2.0).length / f.length) * 100) : 0,
    risk: f.filter(s => isAtRiskStudent(s)).length,
    mismatch: mismatch,
    first: f.filter(s => s.cls === 'First Class').length,
    su: f.filter(s => s.cls === 'Second Upper').length,
    sl: f.filter(s => s.cls === 'Second Lower').length,
    pass: f.filter(s => s.cls === 'Pass').length,
    fail: f.filter(s => s.cls === 'Fail').length
  };
}

function updateDashboard() {
  let d = getDashboardData();
  document.getElementById('stat-total').textContent = d.total;
  document.getElementById('stat-grad').textContent = d.grad;
  document.getElementById('stat-passrate').textContent = d.passrate + '%';
  document.getElementById('stat-risk').textContent = d.risk;
  document.getElementById('stat-mismatch').textContent = d.mismatch;
  document.getElementById('cls-first').textContent = d.first;
  document.getElementById('cls-su').textContent = d.su;
  document.getElementById('cls-sl').textContent = d.sl;
  document.getElementById('cls-pass').textContent = d.pass;
  document.getElementById('cls-fail').textContent = d.fail;
  drawDonutChart(d);
}

function drawDonutChart(d) {
  let canvas = document.getElementById('donutChart');
  if (!canvas) return;
  let ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, 200, 200);
  let vals = [d.first, d.su, d.sl, d.pass, d.fail];
  let total = vals.reduce((a, b) => a + b, 0);

  if (total === 0) {
    ctx.font = 'bold 12px DM Sans, sans-serif';
    ctx.fillStyle = '#6c757d';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No students', 100, 100);
    document.getElementById('donut-legend').innerHTML = '<div class="legend-item">No students found</div>';
    return;
  }

  let colors = ['#2E7D32', '#3E5C76', '#5A7B99', '#C7922B', '#B23A48'];
  let labels = ['First Class', 'Second Upper', 'Second Lower', 'Pass', 'Fail'];
  let start = -Math.PI / 2;
  for (let i = 0; i < 5; i++) {
    let angle = (vals[i] / total) * 2 * Math.PI;
    ctx.beginPath(); ctx.moveTo(100, 100); ctx.arc(100, 100, 80, start, start + angle); ctx.closePath();
    ctx.fillStyle = colors[i]; ctx.fill();
    start += angle;
  }
  ctx.beginPath(); ctx.arc(100, 100, 40, 0, 2 * Math.PI); ctx.fillStyle = '#F8F9FB'; ctx.fill();
  let leg = document.getElementById('donut-legend');
  leg.innerHTML = labels.map((l, i) => `<div class="legend-item"><span class="legend-color" style="background:${colors[i]};"></span>${l} (${vals[i]})</div>`).join('');
}

const chipMap = { 'First Class': 'chip-first', 'Second Upper': 'chip-su', 'Second Lower': 'chip-sl', 'Pass': 'chip-warn', 'Fail': 'chip-fail' };

function getPredictedClassLabel(student) {
  if (!student) return 'Fail';
  if (student.cls === 'Second Upper') return 'Second Upper Class';
  if (student.cls === 'Second Lower') return 'Second Lower Class';
  return student.cls || 'Fail';
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function downloadStudentResultSheet(id) {
  const student = students.find(s => s.id === id);
  if (!student) {
    swal({
      title: 'Error',
      text: 'Student record could not be found.',
      icon: 'error',
    });
    return;
  }

  fetch(`/degree_eligibility/gui/get_student_courses.php?student_no=${encodeURIComponent(id)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        throw new Error(data.error || 'Unable to load student result sheet data.');
      }

      const courses = Array.isArray(data.data) ? data.data : [];
      const eligibilityLabel = student.elig ? 'Eligible' : 'Not Eligible';
      const predictedClass = student.elig ? getPredictedClassLabel(student) : 'Not Applicable';
      const reasons = Array.isArray(student.eligibilityReasons) ? student.eligibilityReasons : [];
      const admission = String(student.id || '').split('/')[1] || 'N/A';

      const courseRows = courses.length > 0
        ? courses.map((course, index) => `
            <tr>
              <td>${index + 1}</td>
              <td>${escapeHtml(course.module_code)}</td>
              <td>${escapeHtml(course.module_name)}</td>
              <td>${escapeHtml(course.credit_value)}</td>
              <td>${escapeHtml(course.grade_code || '-')}</td>
              <td>${escapeHtml(course.exam_status || '-')}</td>
            </tr>
          `).join('')
        : '<tr><td colspan="6">No result records available.</td></tr>';

      const reasonSection = !student.elig
        ? `
          <div class="reasons-section">
            <h3>⚠️ Reasons for Not Being Eligible</h3>
            ${reasons.length > 0
              ? `<ul>${reasons.map(reason => `<li>${escapeHtml(reason)}</li>`).join('')}</ul>`
              : '<p>No detailed reasons available.</p>'}
          </div>
        `
        : '';

      const statusBoxContent = !student.elig
        ? `<div class="section status-box" style="border-left: 4px solid #dc2626;"><div><strong>Degree Eligibility:</strong> <span style="color: #dc2626;">Not Eligible</span></div></div>`
        : `<div class="section status-box"><div><strong>Degree Eligibility:</strong> ${escapeHtml(eligibilityLabel)}</div><div><strong>Class:</strong> ${escapeHtml(predictedClass)}</div></div>`;

      // Generate numbered reasons list for mentor report
      const numberedReasons = reasons.length > 0
        ? reasons.map((reason, idx) => `<p style="margin: 8px 0;"><strong>${idx + 1}. </strong>${escapeHtml(reason)}</p>`).join('')
        : '<p>No detailed reasons available.</p>';

      // Create appropriate template based on eligibility
      // Use one consistent result-sheet layout for all students, including not-eligible cases.
      const printableContent = false ? `
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Mentor Advisory Report - ${escapeHtml(student.id)}</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              color: #1f2937;
              margin: 0;
              padding: 32px;
              line-height: 1.6;
            }
            .heading-space {
              height: 100px;
              border: 1px dashed #cbd5e1;
              margin-bottom: 28px;
              background: #f8fafc;
            }
            .document-title {
              margin-bottom: 28px;
            }
            .document-title h1 {
              margin: 0 0 8px;
              font-size: 28px;
              font-weight: 700;
            }
            .document-title p {
              margin: 0;
              font-size: 14px;
              color: #475569;
              font-weight: 500;
            }
            .section-heading {
              font-size: 16px;
              font-weight: 700;
              margin: 24px 0 12px 0;
              color: #1f2937;
              border-bottom: 2px solid #e5e7eb;
              padding-bottom: 8px;
            }
            .status-highlight {
              background: #fef2f2;
              border-left: 4px solid #dc2626;
              padding: 12px 14px;
              margin: 20px 0;
              font-weight: 600;
              color: #dc2626;
            }
            .reasons-list {
              margin: 12px 0 0 0;
              padding: 0;
              list-style: none;
            }
            .reasons-list p {
              margin: 10px 0;
              font-size: 14px;
              line-height: 1.5;
              color: #374151;
            }
            .mentor-notes {
              background: #f0f9ff;
              border-left: 4px solid #3b82f6;
              padding: 14px;
              margin-top: 24px;
              font-size: 14px;
              color: #374151;
              line-height: 1.6;
            }
            .profile-table {
              width: 100%;
              border-collapse: collapse;
              margin: 16px 0;
              border: 2px solid #333;
              font-family: Arial, sans-serif;
            }
            .profile-table .form-header {
              background: #808080;
              color: white;
              font-weight: 700;
              padding: 6px 10px;
              font-size: 12px;
              text-transform: uppercase;
              letter-spacing: 0.5px;
              border: 1px solid #333;
            }
            .profile-table tr.form-section-row {
              display: none;
            }
            .profile-table td {
              padding: 8px 10px;
              font-size: 12px;
              border: 1px solid #999;
              height: 24px;
              vertical-align: middle;
            }
            .profile-table td.form-label {
              background: #d3d3d3;
              font-weight: 600;
              width: 18%;
              color: #333;
              border-right: 1px solid #666;
              padding: 6px 8px;
            }
            .profile-table td.form-value {
              color: #1f2937;
              width: 27%;
              padding: 6px 8px;
            }
            .profile-table tr td:nth-child(4) {
              border-left: 2px solid #666;
            }
            .profile-table tr td:nth-child(5) {
              border-left: 2px solid #666;
            }
            @media print {
              body {
                padding: 18px;
              }
              .heading-space {
                break-inside: avoid;
              }
            }
          </style>
        </head>
        <body>
          <div class="heading-space"></div>

          <div class="document-title">
            <h1>Student Mentor Advisory Report</h1>
            <p>Subject: Degree Ineligibility Review and Required Academic Follow-up</p>
          </div>

          <div class="section-heading">Student Profile</div>
          <table class="profile-table">
            <tr>
              <td colspan="6" class="form-header">Basic Information</td>
            </tr>
            <tr>
              <td class="form-label">Generated On:</td>
              <td class="form-value">${new Date().toLocaleString()}</td>
              <td class="form-label">Student Number:</td>
              <td class="form-value">${escapeHtml(student.id)}</td>
              <td class="form-label">Admission Year:</td>
              <td class="form-value">${escapeHtml(admission)}</td>
            </tr>
            <tr>
              <td class="form-label">Student Name:</td>
              <td colspan="3" class="form-value">${escapeHtml(student.fullName)}</td>
              <td class="form-label">Program:</td>
              <td class="form-value">${escapeHtml(student.program)}</td>
            </tr>
            <tr>
              <td colspan="6" class="form-header">Academic Performance</td>
            </tr>
            <tr>
              <td class="form-label">Official GPA:</td>
              <td class="form-value">${student.officialGpa.toFixed(2)}</td>
              <td class="form-label">Calculated GPA:</td>
              <td class="form-value">${student.calculatedGpa.toFixed(2)}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td class="form-label">Total Credits:</td>
              <td class="form-value">${escapeHtml(student.credits)}</td>
              <td colspan="4"></td>
            </tr>
          </table>
          <div class="status-highlight">
            Current Degree Eligibility Status: NOT ELIGIBLE
          </div>

          <div class="section-heading">Documented Reasons for Ineligibility</div>
          <div class="reasons-list">
            ${numberedReasons}
          </div>

          <div class="mentor-notes">
            <strong>Mentor Recommendation Notes</strong><br>
            Use this section during student mentoring to outline corrective academic actions and timeline.
          </div>
        </body>
        </html>
      ` : `
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Result Sheet - ${escapeHtml(student.id)}</title>
          <style>
            body {
              font-family: Arial, sans-serif;
              color: #1f2937;
              margin: 0;
              padding: 32px;
              line-height: 1.4;
            }
            .heading-space {
              height: 100px;
              border: 1px dashed #cbd5e1;
              margin-bottom: 28px;
              background: #f8fafc;
            }
            .document-title {
              text-align: center;
              margin-bottom: 24px;
            }
            .document-title h1 {
              margin: 0 0 6px;
              font-size: 24px;
            }
            .document-title p {
              margin: 0;
              font-size: 14px;
              color: #475569;
            }
            .section {
              margin-bottom: 24px;
            }
            .summary-table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 0;
            }
            .summary-table td {
              border: none;
              padding: 6px 0;
              font-size: 14px;
              vertical-align: top;
            }
            .summary-table .label {
              width: 170px;
              font-weight: 700;
              white-space: nowrap;
              padding-right: 12px;
            }
            .summary-table .value {
              text-align: left;
              word-break: break-word;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 12px;
            }
            th, td {
              border: 1px solid #cbd5e1;
              padding: 8px 10px;
              text-align: left;
              font-size: 13px;
              vertical-align: top;
            }
            th {
              background: #e2e8f0;
              text-transform: uppercase;
              font-size: 11px;
              letter-spacing: 0.04em;
            }
            ul {
              margin: 8px 0 0 18px;
              padding: 0;
            }
            .status-box {
              padding: 12px 14px;
              border: 1px solid #cbd5e1;
              background: #f8fafc;
            }
            .status-box div,
            .section p,
            .section li {
              text-align: justify;
              text-justify: inter-word;
            }
            @media print {
              body {
                padding: 18px;
              }
              .heading-space {
                break-inside: avoid;
              }
            }
          </style>
        </head>
        <body>
          <div class="heading-space"></div>

          <div class="document-title">
            <h1>Student Complete Result Sheet</h1>
            <p>Degree Eligibility and Class Predictor Summary</p>
          </div>

          <div class="section">
            <table class="summary-table">
              <tbody>
                <tr><td class="label">Student No:</td><td class="value">${escapeHtml(student.id)}</td></tr>
                <tr><td class="label">Admission Year:</td><td class="value">${escapeHtml(admission)}</td></tr>
                <tr><td class="label">Name:</td><td class="value">${escapeHtml(student.fullName)}</td></tr>
                <tr><td class="label">Program:</td><td class="value">${escapeHtml(student.program)}</td></tr>
                <tr><td class="label">Official GPA:</td><td class="value">${escapeHtml(student.officialGpa.toFixed(2))}</td></tr>
                <tr><td class="label">Calculated GPA:</td><td class="value">${escapeHtml(student.calculatedGpa.toFixed(2))}</td></tr>
                <tr><td class="label">Total Credits:</td><td class="value">${escapeHtml(student.credits)}</td></tr>
                <tr><td class="label">Class:</td><td class="value">${escapeHtml(predictedClass)}</td></tr>
              </tbody>
            </table>
          </div>

          ${statusBoxContent}

          <div class="section">
            <h3>Result Sheet</h3>
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Module Code</th>
                  <th>Module Name</th>
                  <th>Credits</th>
                  <th>Grade</th>
                  <th>Exam Status</th>
                </tr>
              </thead>
              <tbody>
                ${courseRows}
              </tbody>
            </table>
          </div>
        </body>
        </html>
      `;

      // Print from an off-screen iframe to avoid opening an extra browser window/tab.
      const printFrame = document.createElement('iframe');
      printFrame.style.position = 'fixed';
      printFrame.style.right = '0';
      printFrame.style.bottom = '0';
      printFrame.style.width = '0';
      printFrame.style.height = '0';
      printFrame.style.border = '0';
      printFrame.setAttribute('aria-hidden', 'true');
      document.body.appendChild(printFrame);

      const frameWindow = printFrame.contentWindow;
      const frameDoc = frameWindow && frameWindow.document;
      if (!frameDoc || !frameWindow) {
        if (printFrame.parentNode) printFrame.parentNode.removeChild(printFrame);
        throw new Error('Unable to initialize print frame. Please try again.');
      }

      frameDoc.open();
      frameDoc.write(printableContent);
      frameDoc.close();

      const cleanup = () => {
        setTimeout(() => {
          if (printFrame.parentNode) {
            printFrame.parentNode.removeChild(printFrame);
          }
        }, 300);
      };

      frameWindow.onafterprint = cleanup;
      setTimeout(() => {
        frameWindow.focus();
        frameWindow.print();
        // Fallback cleanup for browsers that do not fire onafterprint reliably.
        setTimeout(cleanup, 2000);
      }, 200);
    })
    .catch(error => {
      swal({
        title: 'Download Failed',
        text: error.message || 'Unable to generate the student result sheet.',
        icon: 'error',
      });
    });
}

function renderStudents(data) {
  let tb = document.getElementById('student-tbody');
  if (!data || data.length === 0) {
    tb.innerHTML = '<tr><td colspan="10" style="text-align:center;color:#666;">No student records found</td></tr>';
    document.getElementById('student-count').textContent = `0 of ${students.length} students`;
    return;
  }
  tb.innerHTML = data.map(s => `<tr>
    <td>${s.id}</td><td>${s.nameInitials}</td><td>${s.fullName}</td><td>${s.program}</td>
    <td>${s.admissionYear}</td><td>${s.credits}</td>
    <td>${s.officialGpa.toFixed(2)}</td><td><strong>${s.calculatedGpa.toFixed(2)}</strong></td>
    <td><span class="chip ${s.elig ? 'chip-pass' : 'chip-fail'}">${s.elig ? 'Eligible' : 'Not Eligible'}</span></td>
    <td><span class="chip ${chipMap[s.cls] || 'chip-fail'}">${getPredictedClassLabel(s)}</span></td>
    <td><div class="action-icons">
      <span class="action-icon" onclick="toggleDetails('${s.id}')" title="View details"><span class="material-icons">visibility</span></span>
      <span class="action-icon" onclick="downloadStudentResultSheet('${s.id}')" title="Download result sheet"><span class="material-icons">download</span></span>
    </div></td>
  </tr>`).join('');
  document.getElementById('student-count').textContent = `${data.length} of ${students.length} students`;
}
function filterStudents(mode) {
  refreshStudents();
  if (mode === 'mismatch') {
    // use same mismatch logic as dashboard
    const mismatches = getFilteredStudents('home').filter(s => {
      return Math.abs((s.calculatedGpa || 0) - (s.officialGpa || 0)) > 0.01;
    });
    renderStudents(mismatches);
  } else if (mode === 'atrisk') {
    const atRisk = getFilteredStudents('home').filter(s => isAtRiskStudent(s));
    renderStudents(atRisk);
  } else if (mode === 'eligible') {
    const eligible = getFilteredStudents('home').filter(s => s.elig);
    renderStudents(eligible);
  } else {
    renderStudents(getFilteredStudents('students'));
  }
}
function searchStudents(q) {
  let all = getFilteredStudents('students');
  let f = all.filter(s => s.id.toLowerCase().includes(q.toLowerCase()) || s.fullName.toLowerCase().includes(q.toLowerCase()) || s.nameInitials.toLowerCase().includes(q.toLowerCase()) || s.program.toLowerCase().includes(q.toLowerCase()));
  renderStudents(f);
}
function toggleDetails(id) {
  let exist = document.getElementById('details-' + id);
  if (exist) { exist.remove(); return; }
  let student = students.find(s => s.id === id);
  if (!student) return;
  let rows = document.querySelectorAll('#student-tbody tr');
  let target = null;
  for (let r of rows) { if (r.querySelector('td') && r.querySelector('td').textContent.trim() === id) { target = r; break; } }
  if (!target) return;

  fetch(`/degree_eligibility/gui/get_student_courses.php?student_no=${encodeURIComponent(id)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        alert('Unable to load courses: ' + data.error);
        return;
      }
      const sc = data.data || [];
      let courseRows = sc.map(enr => {
        const grade = enr.grade_code || '';
        return `<tr><td>${enr.module_code}</td><td>${enr.module_name}</td><td>${enr.credit_value}</td><td>${grade}</td><td>${enr.exam_status || ''}</td></tr>`;
      }).join('');
      let row = document.createElement('tr'); row.id = 'details-' + id; row.className = 'details-row';
      row.innerHTML = `<td colspan="10" style="padding:16px;"><h4>Academic Record: ${student.fullName}</h4><table class="details-table"><thead><tr><th>Course</th><th>Name</th><th>Credits</th><th>Grade</th><th>Status</th></tr></thead><tbody>${courseRows || '<tr><td colspan="5">No detailed course data</td></tr>'}</tbody></table></td>`;
      target.parentNode.insertBefore(row, target.nextSibling);
    })
    .catch(err => {
      alert('Error loading course details: ' + err);
    });
}
function editStudent(id) {
  let s = students.find(s => s.id === id);
  if (!s) return;
  let og = prompt('Official GPA (from database)', s.officialGpa); if (og && !isNaN(parseFloat(og))) s.officialGpa = parseFloat(og);
  let cg = prompt('Calculated GPA', s.calculatedGpa); if (cg && !isNaN(parseFloat(cg))) {
    s.calculatedGpa = parseFloat(cg);
    s.gpa = s.calculatedGpa; // keep legacy field in sync
  }
  let c = prompt('New total credits', s.credits); if (c && !isNaN(parseInt(c))) s.credits = parseInt(c);
  s.elig = s.gpa >= 2.0 && s.credits >= (s.programCode === 'PS' || s.programCode === 'BS' || s.programCode === 'PE' || s.programCode === 'EN' ? 90 : 120);
  if (s.gpa >= 3.7) s.cls = 'First Class'; else if (s.gpa >= 3.3) s.cls = 'Second Upper'; else if (s.gpa >= 2.7) s.cls = 'Second Lower'; else if (s.gpa >= 2.0) s.cls = 'Pass'; else s.cls = 'Fail';
  filterStudents(); updateDashboard();
}
function deleteStudent(id) {
  if (confirm('Delete?')) {
    let idx = student_info.findIndex(s => s.student_no === id); if (idx >= 0) student_info.splice(idx, 1);
    enrollment = enrollment.filter(e => e.student_no !== id);
    gpa_records = gpa_records.filter(g => g.student_no !== id);
    student_courses = student_courses.filter(sc => sc.student_no !== id);
    refreshStudents(); filterStudents(); updateDashboard();
  }
}
function addStudent() {
  let id = prompt('Enter student ID (e.g. PS/2023/001):');
  if (!id) return;
  if (student_info.some(s => s.student_no === id)) { alert('Already exists.'); return; }
  let master = masterStudentFullRecords[id];
  if (!master) { alert('Not found in central database.'); return; }
  student_info.push(master.student_info);
  master.enrollments.forEach(e => { enrollment.push({ ...e, enrollment_id: enrollment.length + 1 }); });
  master.gpas.forEach(g => gpa_records.push({ ...g }));
  master.courses.forEach(c => student_courses.push({ ...c }));
  refreshStudents(); filterStudents(); updateDashboard();
  alert(`Added ${id}`);
}

function showAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'flex';
}
function hideAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'none';
  document.getElementById('student_csv_file').value = '';
}
function submitStudentCSV(e) {
  e.preventDefault();
  const fileInput = document.getElementById('student_csv_file');
  if (!fileInput.files.length) {
    swal({
      title: 'Upload unsuccesfull',
      text: 'Upload unsuccesfull',
      icon: 'error',
    });
    return;
  }

  const formData = new FormData();
  formData.append('csv_file', fileInput.files[0]);

  const btn = e.target.querySelector('button[type="submit"]');
  const ogText = btn.textContent;
  btn.textContent = 'Uploading...';
  btn.disabled = true;

  fetch('/degree_eligibility/gui/upload_students_csv.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      btn.textContent = ogText; btn.disabled = false;
      if (data.success) {
        const inserted = Number(data.inserted || 0);
        const updated = Number(data.updated || 0);
        const skipped = Number(data.skipped || 0);
        const importedCount = inserted + updated;
        const hasErrors = data.errors && data.errors.length > 0;
        let msg = `Inserted: ${inserted}\nUpdated: ${updated}\nSkipped: ${skipped}`;

        if (hasErrors) {
          msg += `\n\nErrors found in ${data.errors.length} rows. Downloading error report...`;
        }

        // If nothing was imported, treat the upload as unsuccessful.
        if (importedCount === 0) {
          swal({
            title: 'Upload unsuccesfull',
            text: msg,
            icon: 'error',
          }).then(() => {
            if (hasErrors) {
              downloadCSV(data.errors, 'upload_errors.csv');
            }
          });
          return;
        }

        swal({
          title: 'Upload Successful',
          text: msg,
          icon: hasErrors ? 'warning' : 'success',
        }).then(() => {
          if (hasErrors) {
            downloadCSV(data.errors, 'upload_errors.csv');
          }
          hideAddStudentModal();
          window.location.reload();
        });

      } else {
        swal({
          title: 'Upload Failed',
          text: data.error || 'Unknown error occurred.',
          icon: 'error',
        });
      }
    })
    .catch(err => {
      btn.textContent = ogText; btn.disabled = false;
      swal({
        title: 'Request Failed',
        text: err.message,
        icon: 'error',
      });
    });
}

function downloadCSV(rows, filename) {
  let csvContent = "data:text/csv;charset=utf-8,";
  rows.forEach(function(rowArray) {
    let row = rowArray.map(col => `"${String(col).replace(/"/g, '""')}"`).join(",");
    csvContent += row + "\r\n";
  });
  
  var encodedUri = encodeURI(csvContent);
  var link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

async function printNotEligibilityReport(studentId) {
  const student = students.find(s => s.id === studentId);
  if (!student) {
    swal({
      title: 'Error',
      text: 'Student record could not be found.',
      icon: 'error',
    });
    return;
  }

  if (student.elig) {
    swal({
      title: 'Not Applicable',
      text: 'This report is only available for not eligible students.',
      icon: 'info',
    });
    return;
  }

  const jsPDFCtor = window.jspdf && window.jspdf.jsPDF ? window.jspdf.jsPDF : null;
  if (!jsPDFCtor) {
    swal({
      title: 'PDF Library Missing',
      text: 'Unable to generate PDF right now. Please reload the page and try again.',
      icon: 'error',
    });
    return;
  }

  const reasons = Array.isArray(student.eligibilityReasons) ? student.eligibilityReasons : [];
  const admission = String(student.id || '').split('/')[1] || 'N/A';

  const doc = new jsPDFCtor({ unit: 'pt', format: 'a4' });
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const margin = 48;
  const contentWidth = pageWidth - (margin * 2);
  let y = margin;

  const ensureSpace = (neededHeight) => {
    if (y + neededHeight > pageHeight - margin) {
      doc.addPage();
      y = margin;
    }
  };

  doc.setFont('helvetica', 'bold');
  doc.setFontSize(17);
  doc.text('Student Mentor Advisory Report', pageWidth / 2, y + 26, { align: 'center' });
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(71, 85, 105);
  doc.setFontSize(10);
  doc.text(`Generated On: ${new Date().toLocaleString()}`, pageWidth - margin, y - 14, { align: 'right' });
  doc.setTextColor(31, 41, 55);
  y += 52;

  // Student details table
  const colWidths = [contentWidth * 0.34, contentWidth * 0.66];
  const rowH = 26;
  const tableHeaderH = 24;

  const detailRows = [
    ['Student Number', String(student.id || '')],
    ['Student Name', String(student.fullName || student.nameInitials || '')],
    ['Program', String(student.program || '')],
    ['Admission Year', String(admission)],
    ['Official GPA', String(Number(student.officialGpa || 0).toFixed(2))],
    ['Calculated GPA', String(Number(student.calculatedGpa || 0).toFixed(2))],
    ['Total Credits', String(Number(student.credits || 0).toFixed(0))],
  ];

  const tableHeight = tableHeaderH + (detailRows.length * rowH);
  ensureSpace(tableHeight + 10);

  doc.setFillColor(128, 128, 128);
  doc.setDrawColor(80, 80, 80);
  doc.rect(margin, y, contentWidth, tableHeaderH, 'FD');
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(11);
  doc.text('STUDENT DETAILS', margin + 8, y + 16);
  y += tableHeaderH;

  doc.setTextColor(31, 41, 55);
  detailRows.forEach((row) => {
    let x = margin;
    row.forEach((cell, idx) => {
      const w = colWidths[idx];
      const isLabel = idx === 0;
      doc.setFillColor(isLabel ? 230 : 255, isLabel ? 230 : 255, isLabel ? 230 : 255);
      doc.setDrawColor(170, 170, 170);
      doc.rect(x, y, w, rowH, 'FD');
      doc.setFont('helvetica', isLabel ? 'bold' : 'normal');
      doc.setFontSize(10);
      doc.setTextColor(31, 41, 55);
      doc.text(String(cell), x + 6, y + 16, { maxWidth: w - 12 });
      x += w;
    });
    y += rowH;
  });

  // Add breathing room between the details table and sections below.
  y += 28;
  ensureSpace(24);
  doc.setFillColor(254, 242, 242);
  doc.setDrawColor(220, 38, 38);
  doc.rect(margin, y, contentWidth, 22, 'FD');
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(10);
  doc.setTextColor(153, 27, 27);
  doc.text('Current Degree Eligibility Status: NOT ELIGIBLE', margin + 8, y + 14);
  y += 46;

  ensureSpace(30);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(31, 41, 55);
  doc.text('Documented Reasons for Ineligibility', margin, y);
  y += 28;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(10);
  const reasonItems = reasons.length > 0 ? reasons : ['This student does not meet the requirements for graduation.'];
  reasonItems.forEach((reason, index) => {
    const wrapped = doc.splitTextToSize(`${index + 1}. ${String(reason)}`, contentWidth - 12);
    const blockHeight = (wrapped.length * 16) + 14;
    ensureSpace(blockHeight + 6);
    doc.text(wrapped, margin + 6, y);
    y += blockHeight;
  });

  const safeId = String(student.id || 'student').replace(/[^a-zA-Z0-9_-]/g, '_');
  doc.save(`${safeId}_mentor_ineligibility_report.pdf`);
}

function downloadNotEligibilityReport(studentId) {
  printNotEligibilityReport(studentId);
}

function loadCombinedData() {
  let sid = document.getElementById('combined-student-id').value.trim();
  let s = students.find(s => s.id === sid);
  let res = document.getElementById('combined-results');
  let nf = document.getElementById('combined-notfound');
  let cres = document.getElementById('classification-result');
  let nel = document.getElementById('not-eligible-message');
  if (!s) { res.style.display = 'none'; nf.style.display = 'block'; return; }
  nf.style.display = 'none'; res.style.display = 'block';
  let adm = sid.split('/')[1] || 'N/A';
  document.getElementById('student-details').innerHTML = `<div class="student-avatar"><span class="material-icons">account_circle</span></div><div class="student-info"><p class="student-name">${s.fullName}</p><p>${s.id} | ${s.program}</p><p>Admission: ${adm}</p><p>Official GPA: ${s.officialGpa.toFixed(2)} | Calculated GPA: ${s.calculatedGpa.toFixed(2)} | Credits: ${s.credits}</p></div>`;
  let elig = s.elig;
  document.getElementById('eligibility-badge-container').innerHTML = `<span class="elig-badge ${elig ? 'eligible' : 'not-eligible'}">${elig ? '✅ Eligible' : '❌ Not Eligible'}</span>`;
  if (elig) {
    let pred = s.cls; if (s.cls === 'Second Upper') pred = 'Second Upper Class'; else if (s.cls === 'Second Lower') pred = 'Second Lower Class';
    document.getElementById('predicted-class-title').textContent = pred;
    document.getElementById('predicted-gpa').textContent = `Current GPA: ${s.gpa.toFixed(2)}`;
    cres.style.display = 'block'; nel.style.display = 'none';
  } else {
    const reasons = Array.isArray(s.eligibilityReasons) ? s.eligibilityReasons : [];
    const reasonsHtml = reasons.length
      ? `<ul style="margin:10px 0 0 20px; color: var(--muted); text-align: left;">${reasons.map(r => `<li>${escapeHtml(r)}</li>`).join('')}</ul>`
      : '<p style="margin-top: 10px; color: var(--muted);">This student does not meet the requirements for graduation.</p>';

    nel.innerHTML = `
      <strong>⚠️ Not Eligible for Graduation</strong>
      <p style="margin-top: 10px; color: var(--muted);">Reasons for not eligibility:</p>
      ${reasonsHtml}
      <div style="margin-top: 14px;">
        <button class="calc-btn" onclick="downloadNotEligibilityReport('${s.id}')">Download PDF</button>
      </div>
    `;
    cres.style.display = 'none';
    nel.style.display = 'block';
  }
}
function renderCourses(filter = '') {
  let tb = document.getElementById('course-tbody');
  let f = filter ? courses.filter(c => c.course_id.toLowerCase().includes(filter.toLowerCase()) || c.name.toLowerCase().includes(filter.toLowerCase())) : courses;
  tb.innerHTML = f.map(c => `<tr><td>${c.course_id} ${c.name}</td><td>${c.credits}</td><td>${c.is_gpa ? 'YES' : 'NO'}</td><td>${c.status} <span class="material-icons" style="font-size:16px;color:var(--pass);">check_circle</span></td><td><div class="action-icons"><span class="action-icon" onclick="editCourse('${c.course_id}')"><span class="material-icons">edit</span></span><span class="action-icon" onclick="deleteCourse('${c.course_id}')"><span class="material-icons">delete</span></span></div></td></tr>`).join('');
  document.getElementById('course-count').textContent = `${f.length} of ${courses.length} courses`;
}
function searchCoursesLegacy(q) { renderCourses(q); }
function addCourse() {
  let id = prompt('Course ID:'); if (!id) return;
  let name = prompt('Course name:'); if (!name) return;
  let cred = parseInt(prompt('Credits:')); if (isNaN(cred)) return;
  let isGpa = prompt('GPA course? (yes/no)').toLowerCase() === 'yes';
  courses.push({ course_id: id.replace(/\s/g, ''), name, credits: cred, is_gpa: isGpa, status: 'COMPULSORY' });
  renderCourses();
}


// ========== GPA CALCULATOR FUNCTIONS with automatic cross-check ==========
function loadStudentCourses() {
  const sid = document.getElementById('gpa-student-id').value.trim();
  const student = students.find(s => s.id === sid);
  const notFoundDiv = document.getElementById('gpa-notfound');
  const container = document.getElementById('gpa-courses-container');
  const resultDiv = document.getElementById('gpa-computed-result');
  const verifySection = document.getElementById('gpa-verify-section');

  container.style.display = 'none';
  resultDiv.style.display = 'none';
  verifySection.style.display = 'none';
  notFoundDiv.style.display = 'none';

  if (!student) {
    notFoundDiv.style.display = 'block';
    return;
  }

  // fetch actual enrolled courses from server
  fetch(`/degree_eligibility/gui/get_student_courses.php?student_no=${encodeURIComponent(sid)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        alert('Failed to load courses: ' + data.error);
        return;
      }

      const sc = data.data || [];
      if (sc.length === 0) {
        alert('No course records found for this student.');
        return;
      }

      const tbody = document.getElementById('gpa-course-tbody');
      let rows = '';
      sc.forEach(enr => {
        const grade = enr.grade_code || '';
        const gradePoint = gradePoints[grade] || 0;
        const credits = parseInt(enr.credit_value) || 0;
        const total = (gradePoint * credits).toFixed(2);
        const isGpa = enr.is_gpa_module == 1;
        const rowClass = isGpa ? '' : 'non-gpa-row';
        rows += `<tr class="${rowClass}" data-is-gpa="${enr.is_gpa_module}" data-exam-status="${enr.exam_status}">
          <td>${enr.module_code}</td>
          <td>${grade}</td>
          <td>${gradePoint.toFixed(2)}</td>
          <td>${credits}</td>
          <td>${total}</td>
        </tr>`;
      });
      tbody.innerHTML = rows;
      container.style.display = 'block';

      // auto-calc GPA from table
      computeGPAFromTable();

      // Populate verification section with student's cumulative official GPA
      document.getElementById('verify-student-id').value = sid;
      document.getElementById('verify-official-gpa-display').textContent = student.officialGpa.toFixed(2);
      document.getElementById('verify-result').innerHTML = '';
      document.getElementById('verify-badge').style.display = 'none';
      verifySection.style.display = 'block';
    })
    .catch(err => {
      alert('Error fetching courses: ' + err);
    });
}

function computeGPAFromTable() {
  const tbody = document.getElementById('gpa-course-tbody');
  const rows = tbody.querySelectorAll('tr');
  if (rows.length === 0) {
    alert('No courses loaded. Please load a student first.');
    return;
  }

  let totalCredits = 0;
  let totalGradePoints = 0;

  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length < 5) return;
    const moduleCode = cells[0].textContent.trim();
    const isGpa = row.dataset.isGpa === '1';
    const grade = cells[1].textContent.trim();
    if (!isGpa || grade === '' || grade === 'NULL' || grade === 'AB') return;
    if (moduleCode.startsWith('ACLT') || moduleCode.startsWith('CMSK') || moduleCode.startsWith('MGMT')) return;
    const gradePoint = parseFloat(cells[2].textContent);
    const credits = parseInt(cells[3].textContent);
    totalCredits += credits;
    totalGradePoints += gradePoint * credits;
  });

  const calculatedGpa = totalCredits > 0 ? (totalGradePoints / totalCredits) : 0;

  const resultDiv = document.getElementById('gpa-computed-result');
  resultDiv.innerHTML = `
    <div style="display: flex; justify-content: space-around; align-items: center;">
      <div><strong>Total GPA Credits:</strong> ${totalCredits}</div>
      <div><strong>Total Grade Points:</strong> ${totalGradePoints.toFixed(2)}</div>
      <div><strong>GPA:</strong> ${calculatedGpa.toFixed(2)}</div>
    </div>
  `;
  resultDiv.style.display = 'block';

  // Cross-check with official GPA from student record
  const sid = document.getElementById('gpa-student-id').value.trim();
  const student = students.find(s => s.id === sid);
  if (!student) return;
  const officialGpa = student.officialGpa;
  const diff = Math.abs(calculatedGpa - officialGpa).toFixed(2);
  const verifyDiv = document.getElementById('verify-result');
  const badge = document.getElementById('verify-badge');

  if (Math.abs(calculatedGpa - officialGpa) < 0.01) {
    verifyDiv.innerHTML = `
      <div class="verify-section" style="background: rgba(46,125,50,0.1); border-color: var(--pass);">
        <div class="verify-header">
          <span><strong>✅ GPA Verified Successfully</strong></span>
          <span class="success-badge">Verified</span>
        </div>
        <table class="diff-table">
          <tr><td><strong>Calculated GPA:</strong></td><td>${calculatedGpa.toFixed(2)}</td></tr>
          <tr><td><strong>Official GPA:</strong></td><td>${officialGpa.toFixed(2)}</td></tr>
          <tr><td><strong>Difference:</strong></td><td>${diff}</td></tr>
        </table>
      </div>
    `;
    badge.style.display = 'inline-block';
  } else {
    verifyDiv.innerHTML = `
      <div class="verify-section" style="background: rgba(178,58,72,0.1); border-color: var(--danger);">
        <div class="verify-header">
          <span><strong>❌ GPA Verification Failed</strong></span>
        </div>
        <table class="diff-table">
          <tr><td><strong>Calculated GPA:</strong></td><td>${calculatedGpa.toFixed(2)}</td></tr>
          <tr><td><strong>Official GPA:</strong></td><td>${officialGpa.toFixed(2)}</td></tr>
          <tr><td><strong>Difference:</strong></td><td>${diff}</td></tr>
        </table>
      </div>
    `;
    badge.style.display = 'none';
  }
}

// Expose functions
window.showPage = showPage; window.updateDashboard = updateDashboard; window.filterStudents = filterStudents;
window.searchStudents = searchStudents; window.editStudent = editStudent;
window.deleteStudent = deleteStudent; window.toggleDetails = toggleDetails; window.loadCombinedData = loadCombinedData;
window.downloadStudentResultSheet = downloadStudentResultSheet;
window.downloadNotEligibilityReport = downloadNotEligibilityReport;
window.showMismatchStudents = showMismatchStudents;
window.showAddStudentModal = showAddStudentModal; window.hideAddStudentModal = hideAddStudentModal; window.submitStudentCSV = submitStudentCSV;
window.searchCourses = searchCourses; window.addCourse = addCourse; window.editCourse = editCourse;
window.deleteCourse = deleteCourse; window.loadStudentCourses = loadStudentCourses; window.computeGPAFromTable = computeGPAFromTable;
window.verifyGPA = verifyGPA;

refreshStudents(); updateDashboard(); renderCourses();
