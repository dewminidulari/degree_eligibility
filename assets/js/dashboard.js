
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
  { program_id: 6, program_name: 'Physics and Electronic', program_code: 'PE', max_year: 3 },
  { program_id: 7, program_name: 'Environmental Conservation and Management', program_code: 'EN', max_year: 3 },
  { program_id: 8, program_name: 'Applied Chemistry', program_code: 'AC', max_year: 4 }
];

// Only Physical Science students
const student_info = [
  { student_no: 'PS/2018/001', name_with_initials: 'A.M. Senanayake', full_name: 'Amal Madusanka Senanayake' },
  { student_no: 'PS/2019/101', name_with_initials: 'H.M. Perera', full_name: 'Hettiarachchi Mudiyanselage Perera' },
  { student_no: 'PS/2019/105', name_with_initials: 'W.A. Nimal', full_name: 'Wijesinghe Arachchige Nimal' },
  { student_no: 'PS/2020/015', name_with_initials: 'K.P. Jayasuriya', full_name: 'Kavindu Pasan Jayasuriya' },
  { student_no: 'PS/2021/022', name_with_initials: 'R.M. Rathnayake', full_name: 'Rashmi Madushani Rathnayake' },
  { student_no: 'PS/2022/142', name_with_initials: 'R.M.N. Kumarasiri', full_name: 'Ranasingha Mohottalalage Nethmini Kumarasiri' },
  { student_no: 'PS/2022/147', name_with_initials: 'K.D.L. Perera', full_name: 'Kumarage Don Lal Perera'},
  { student_no: 'PS/2022/157', name_with_initials: 'M.P. Wijayawardhana', full_name: 'Mohottalalage Pamith Wijayawardhana' },
  { student_no: 'PS/2022/168', name_with_initials: 'S.M.N. Kavindi', full_name: 'Samarasingha Mudiyanselage Nethuni Kavindi' },
  { student_no: 'PS/2022/169', name_with_initials: 'I.G.U. Senevirathna', full_name: 'Ihalagedara Ganguli Upeksha Senevirathna'}
];

let enrollment = [];
let gpa_records = [];

function buildData() {
  const map = {
    'PS/2018/001': [ { year:'2018/2019', level:1, credits:30, gpa:3.75 }, { year:'2019/2020', level:2, credits:30, gpa:3.80 }, { year:'2020/2021', level:3, credits:30, gpa:3.85 } ],
    'PS/2019/101': [ { year:'2019/2020', level:1, credits:30, gpa:3.60 }, { year:'2020/2021', level:2, credits:30, gpa:3.70 }, { year:'2021/2022', level:3, credits:30, gpa:3.80 } ],
    'PS/2019/105': [ { year:'2019/2020', level:1, credits:30, gpa:2.80 }, { year:'2020/2021', level:2, credits:30, gpa:3.00 }, { year:'2021/2022', level:3, credits:30, gpa:2.90 } ],
    'PS/2020/015': [ { year:'2020/2021', level:1, credits:30, gpa:2.50 }, { year:'2021/2022', level:2, credits:30, gpa:2.60 }, { year:'2022/2023', level:3, credits:30, gpa:2.70 } ],
    'PS/2021/022': [ { year:'2021/2022', level:1, credits:30, gpa:3.20 }, { year:'2022/2023', level:2, credits:30, gpa:3.30 }, { year:'2023/2024', level:3, credits:30, gpa:3.40 } ],
    'PS/2022/142': [ { year:'2022/2023', level:1, credits:27, gpa:3.42 } ],
    'PS/2022/147': [ { year:'2022/2023', level:1, credits:27, gpa:3.45 } ],
    'PS/2022/157': [ { year:'2022/2023', level:1, credits:27, gpa:2.84 } ],
    'PS/2022/168': [ { year:'2022/2023', level:1, credits:27, gpa:1.02 } ],
    'PS/2022/169': [ { year:'2022/2023', level:1, credits:27, gpa:1.80 } ]
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
document.addEventListener('DOMContentLoaded', function() {
    loadCourses();
});

function loadCourses(searchTerm = '') {
    let url = 'get_courses.php';
    if (searchTerm) {
        url += '?search=' + encodeURIComponent(searchTerm);
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCourses(data.data);
                // Update the course count display
                document.getElementById('course-count').textContent = 
                    `${data.data.length} of ${data.data.length} courses`;
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
                <span class="badge ${course.module_status === 'Compulsory' ? 'Auxilary' : 'Optional'}">
                    ${course.module_status}
                </span>
            </td>

            <td>
                <button class="action-btn delete-btn" onclick="deleteCourse('${course.module_code}')" title="Delete">
                    <span class="material-icons">delete</span>
                </button>
            </td>
        </tr>
    `).join('');
}

// Search courses
function searchCourses(value) {
    loadCourses(value);
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
    
    // Get form data
    const formData = new FormData();
    formData.append('module_code', document.getElementById('new_module_code').value);
    formData.append('module_name', document.getElementById('new_module_name').value);
    formData.append('credit_value', document.getElementById('new_credit_value').value);
    formData.append('is_gpa_module', document.getElementById('new_is_gpa_module').value);
    formData.append('module_status', document.getElementById('new_module_status').value);
    
    // Send data to server
    fetch('../gui/add_course.php', {
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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addCourseModal');
    if (event.target == modal) {
        hideAddModal();
    }
}
// Placeholder functions for edit/delete/add
function editCourse(moduleCode) {
    console.log('Edit course:', moduleCode);
    // Implement edit functionality
    alert('Edit course: ' + moduleCode);
}

function deleteCourse(moduleCode) {
    console.log('Delete course:', moduleCode);
    // Implement delete functionality with confirmation
    if (confirm('Are you sure you want to delete this course?')) {
        // Add delete logic here
        alert('Delete course: ' + moduleCode);
    }
}

// function addNewCourse() {
//     console.log('Add new course');
//     // Implement add new course functionality
//     alert('Add new course functionality');
// }

let student_courses = [];

const gradePoints = { 'A':4.0,'A-':3.7,'B+':3.3,'B':3.0,'B-':2.7,'C+':2.3,'C':2.0,'C-':1.7,'D':1.0,'E':0.0 };
function getCourse(id) { return courses.find(c=>c.course_id===id); }

function generateStudentCourses() {
  student_courses = [];
  const yearCoursesMap = {
    1: ['ACLT11013', 'PMAT12282', 'CMSK14032', 'COSC11083'],
    2: ['CHEM12652', 'ELEC22534', 'PHYS22533', 'COSC12033'],
    3: ['CHEM11102', 'CHEM11312', 'PMAT12053', 'COSC12173'],
    4: ['COSC12173', 'ELEC22534', 'CHEM12652', 'PHYS22533']
  };
  function gpaToGrade(gpa) {
    if (gpa >= 3.7) return 'A';
    if (gpa >= 3.3) return 'A-';
    if (gpa >= 3.0) return 'B+';
    if (gpa >= 2.7) return 'B';
    if (gpa >= 2.3) return 'B-';
    if (gpa >= 2.0) return 'C+';
    if (gpa >= 1.7) return 'C';
    if (gpa >= 1.3) return 'C-';
    if (gpa >= 1.0) return 'D';
    return 'E';
  }

  student_info.forEach(si => {
    const sno = si.student_no;
    const enrolls = enrollment.filter(e => e.student_no === sno);
    enrolls.forEach(enr => {
      const yearLevel = enr.year_of_study;
      const courseIds = yearCoursesMap[yearLevel] || [];
      const gpaRec = gpa_records.find(g => g.student_no === sno && g.academic_year_id === enr.academic_year_id);
      const yearGpa = gpaRec ? gpaRec.gpa_value : 2.0;
      const grade = gpaToGrade(yearGpa);
      courseIds.forEach(cid => {
        if (courses.some(c => c.course_id === cid)) {
          student_courses.push({
            student_no: sno,
            course_id: cid,
            grade: grade,
            level: yearLevel
          });
        }
      });
    });
  });
}
generateStudentCourses();

const masterStudentFullRecords = {};
student_info.forEach(si => {
  masterStudentFullRecords[si.student_no] = {
    student_info: si,
    enrollments: enrollment.filter(e=>e.student_no===si.student_no),
    gpas: gpa_records.filter(g=>g.student_no===si.student_no),
    courses: student_courses.filter(sc=>sc.student_no===si.student_no)
  };
});

// ---------- ELIGIBILITY LOGIC ----------
function computeStudentAggregate(sno) {
  const info = student_info.find(s=>s.student_no===sno);
  if (!info) return null;
  const progCode = sno.split('/')[0];
  const program = programs.find(p=>p.program_code===progCode) || programs[0];
  const maxYear = program.max_year;
  const recs = gpa_records.filter(r=>r.student_no===sno);
  const totalCredits = recs.reduce((s,r)=>s+r.total_credits,0);
  let totalP=0, totalC=0;
  recs.forEach(r=>{ totalP += r.gpa_value * r.total_credits; totalC += r.total_credits; });
  const overallGpa = totalC ? totalP/totalC : 0;
  const enrolls = enrollment.filter(e=>e.student_no===sno);
  const currentYear = enrolls.length ? Math.max(...enrolls.map(e=>e.year_of_study)) : 1;
  
  let eligible = overallGpa >= 2.0;
  if (eligible) {
    for (let y=1; y<=maxYear; y++) {
      const yearRec = recs.find(r => {
        const e = enrolls.find(en => en.academic_year_id === r.academic_year_id);
        return e && e.year_of_study === y;
      });
      if (!yearRec || yearRec.total_credits !== 30) {
        eligible = false;
        break;
      }
    }
  }
  
  let cls = '';
  if (overallGpa >= 3.7) cls = 'First Class';
  else if (overallGpa >= 3.3) cls = 'Second Upper';
  else if (overallGpa >= 2.7) cls = 'Second Lower';
  else if (overallGpa >= 2.0) cls = 'Pass';
  else cls = 'Fail';
  
  return {
    id: sno, nameInitials: info.name_with_initials, fullName: info.full_name,
    program: program.program_name, programCode: program.program_code,
    year: currentYear, gpa: overallGpa, credits: totalCredits, repeats: 0,
    elig: eligible, cls: cls,
    courses: enrolls.map(e=>{
      const ay = academic_years.find(a=>a.academic_year_id===e.academic_year_id);
      const g = gpa_records.find(r=>r.student_no===sno && r.academic_year_id===e.academic_year_id);
      return {
        year: ay ? ay.year_label : '',
        level: e.year_of_study,
        credits: g ? g.total_credits : 0,
        gpa: g ? g.gpa_value : 0,
        status: e.status
      };
    }).sort((a,b)=>a.level-b.level)
  };
}

function getAllStudentsAggregated() {
  return student_info.map(s=>computeStudentAggregate(s.student_no)).filter(s=>s!==null);
}
let students = getAllStudentsAggregated();
function refreshStudents() { students = getAllStudentsAggregated(); }

// ---------- UI FUNCTIONS ----------
const pageTitles = {
  home: ['Dashboard Overview','Faculty of Science · Academic Portal'],
  students: ['Student Records','Manage and view all registered students'],
  gpa: ['GPA Calculator','Calculate GPA from course results'],
  'eligibility-predictor': ['Eligibility & Predictor','Check eligibility and predict classification'],
  courses: ['Course Management','Manage and view all course details']
};
function showPage(id, el) {
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.getElementById('page-'+id).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if (el) el.classList.add('active');
  document.getElementById('page-title').textContent = pageTitles[id][0];
  document.getElementById('page-sub').textContent = pageTitles[id][1];
  if (id==='students') { refreshStudents(); filterStudents(); }
  else if (id==='home') { refreshStudents(); updateDashboard(); }
  else if (id==='courses') renderCourses();
}
function tick() {
  let now = new Date();
  document.getElementById('current-time').textContent = now.toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short'}) + '  ·  ' + now.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
}
setInterval(tick,1000); tick();

function getFilteredStudents(source='home') {
  let yearFilter, programFilter, studyYearFilter, classFilter;
  if (source==='home') {
    yearFilter = document.getElementById('home-year').value;
    programFilter = document.getElementById('home-degree').value;
    studyYearFilter = document.getElementById('home-studyyear').value;
    classFilter = 'all';
  } else {
    yearFilter = document.getElementById('student-year').value;
    programFilter = document.getElementById('student-program').value;
    studyYearFilter = document.getElementById('student-year-level').value;
    classFilter = document.getElementById('student-class').value;
  }
  return students.filter(s=>{
    if (programFilter!=='all' && s.programCode!==programFilter) return false;
    if (studyYearFilter!=='all' && s.year!==parseInt(studyYearFilter)) return false;
    if (yearFilter!=='all' && yearFilter!=='All Years') {
      let adm = s.id.split('/')[1];
      let yearPrefix = yearFilter.split('-')[0];
      if (adm !== yearPrefix) return false;
    }
    if (classFilter!=='all') {
      if (s.cls !== classFilter || !s.elig) return false;
    }
    return true;
  });
}

function getDashboardData() {
  let f = getFilteredStudents('home');
  let eligible = f.filter(s => s.elig);
  return {
    total: f.length,
    grad: eligible.length,
    passrate: f.length ? Math.round((f.filter(s=>s.gpa>=2.0).length/f.length)*100) : 0,
    risk: f.filter(s=>s.gpa<2.0).length,
    first: eligible.filter(s=>s.cls==='First Class').length,
    su: eligible.filter(s=>s.cls==='Second Upper').length,
    sl: eligible.filter(s=>s.cls==='Second Lower').length,
    pass: eligible.filter(s=>s.cls==='Pass').length,
    fail: eligible.filter(s=>s.cls==='Fail').length
  };
}

function updateDashboard() {
  let d = getDashboardData();
  document.getElementById('stat-total').textContent = d.total;
  document.getElementById('stat-grad').textContent = d.grad;
  document.getElementById('stat-passrate').textContent = d.passrate+'%';
  document.getElementById('stat-risk').textContent = d.risk;
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
  ctx.clearRect(0,0,200,200);
  let vals = [d.first, d.su, d.sl, d.pass, d.fail];
  let total = vals.reduce((a,b)=>a+b,0);
  
  if (total === 0) {
    ctx.font = 'bold 12px DM Sans, sans-serif';
    ctx.fillStyle = '#6c757d';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No eligible', 100, 90);
    ctx.fillText('students', 100, 110);
    document.getElementById('donut-legend').innerHTML = '<div class="legend-item">No eligible students</div>';
    return;
  }
  
  let colors = ['#2E7D32','#3E5C76','#5A7B99','#C7922B','#B23A48'];
  let labels = ['First Class','Second Upper','Second Lower','Pass','Fail'];
  let start = -Math.PI/2;
  for (let i=0;i<5;i++) {
    let angle = (vals[i]/total)*2*Math.PI;
    ctx.beginPath(); ctx.moveTo(100,100); ctx.arc(100,100,80,start,start+angle); ctx.closePath();
    ctx.fillStyle = colors[i]; ctx.fill();
    start += angle;
  }
  ctx.beginPath(); ctx.arc(100,100,40,0,2*Math.PI); ctx.fillStyle='#F8F9FB'; ctx.fill();
  let leg = document.getElementById('donut-legend');
  leg.innerHTML = labels.map((l,i)=>`<div class="legend-item"><span class="legend-color" style="background:${colors[i]};"></span>${l} (${vals[i]})</div>`).join('');
}

const chipMap = {'First Class':'chip-first','Second Upper':'chip-su','Second Lower':'chip-sl','Pass':'chip-warn','Fail':'chip-fail'};
function renderStudents(data) {
  let tb = document.getElementById('student-tbody');
  tb.innerHTML = data.map(s=>`<tr>
    <td>${s.id}</td><td>${s.nameInitials}</td><td>${s.fullName}</td><td>${s.program}</td>
    <td>Year ${s.year}</td><td>${s.credits}</td><td><strong>${s.gpa.toFixed(2)}</strong></td>
    <td><span class="chip ${s.elig?'chip-pass':'chip-fail'}">${s.elig?'Eligible':'Not Eligible'}</span></td>
    <td>${s.elig?`<span class="chip ${chipMap[s.cls]}">${s.cls}</span>`:''}</td>
    <td><div class="action-icons">
      <span class="action-icon" onclick="toggleDetails('${s.id}')"><span class="material-icons">visibility</span></span>
      <span class="action-icon" onclick="editStudent('${s.id}')"><span class="material-icons">edit</span></span>
      <span class="action-icon" onclick="deleteStudent('${s.id}')"><span class="material-icons">delete</span></span>
    </div></td>
  </tr>`).join('');
  document.getElementById('student-count').textContent = `${data.length} of ${students.length} students`;
}
function filterStudents() { refreshStudents(); renderStudents(getFilteredStudents('students')); }
function searchStudents(q) {
  let all = getFilteredStudents('students');
  let f = all.filter(s=>s.id.toLowerCase().includes(q.toLowerCase())||s.fullName.toLowerCase().includes(q.toLowerCase())||s.nameInitials.toLowerCase().includes(q.toLowerCase())||s.program.toLowerCase().includes(q.toLowerCase()));
  renderStudents(f);
}
function toggleDetails(id) {
  let exist = document.getElementById('details-'+id);
  if (exist) { exist.remove(); return; }
  let student = students.find(s=>s.id===id);
  if (!student) return;
  let rows = document.querySelectorAll('#student-tbody tr');
  let target = null;
  for (let r of rows) { if (r.querySelector('td') && r.querySelector('td').textContent.trim()===id) { target=r; break; } }
  if (!target) return;
  let sc = student_courses.filter(sc=>sc.student_no===id);
  let courseRows = sc.map(enr=>{
    let c = getCourse(enr.course_id);
    if (c) return `<tr><td>${c.course_id}</td><td>${c.name}</td><td>${c.credits}</td><td>${enr.grade}</td><td>Level ${enr.level}</td><td>${c.status}</td></tr>`;
    return '';
  }).join('');
  let row = document.createElement('tr'); row.id = 'details-'+id; row.className='details-row';
  row.innerHTML = `<td colspan="10" style="padding:16px;"><h4>Academic Record: ${student.fullName}</h4><table class="details-table"><thead><tr><th>Course</th><th>Name</th><th>Credits</th><th>Grade</th><th>Level</th><th>Status</th></tr></thead><tbody>${courseRows||'<tr><td colspan="6">No detailed course data</td></tr>'}</tbody></table></td>`;
  target.parentNode.insertBefore(row, target.nextSibling);
}
function editStudent(id) {
  let s = students.find(s=>s.id===id);
  if (!s) return;
  let g = prompt('New GPA',s.gpa); if (g && !isNaN(parseFloat(g))) s.gpa = parseFloat(g);
  let c = prompt('New total credits',s.credits); if (c && !isNaN(parseInt(c))) s.credits = parseInt(c);
  s.elig = s.gpa>=2.0 && s.credits>= (s.programCode==='PS'||s.programCode==='BS'||s.programCode==='PE'||s.programCode==='EN'?90:120);
  if (s.gpa>=3.7) s.cls='First Class'; else if (s.gpa>=3.3) s.cls='Second Upper'; else if (s.gpa>=2.7) s.cls='Second Lower'; else if (s.gpa>=2.0) s.cls='Pass'; else s.cls='Fail';
  filterStudents(); updateDashboard();
}
function deleteStudent(id) {
  if (confirm('Delete?')) {
    let idx = student_info.findIndex(s=>s.student_no===id); if (idx>=0) student_info.splice(idx,1);
    enrollment = enrollment.filter(e=>e.student_no!==id);
    gpa_records = gpa_records.filter(g=>g.student_no!==id);
    student_courses = student_courses.filter(sc=>sc.student_no!==id);
    refreshStudents(); filterStudents(); updateDashboard();
  }
}
function addStudent() {
  let id = prompt('Enter student ID (e.g. PS/2023/001):');
  if (!id) return;
  if (student_info.some(s=>s.student_no===id)) { alert('Already exists.'); return; }
  let master = masterStudentFullRecords[id];
  if (!master) { alert('Not found in central database.'); return; }
  student_info.push(master.student_info);
  master.enrollments.forEach(e=>{ enrollment.push({...e, enrollment_id: enrollment.length+1}); });
  master.gpas.forEach(g=>gpa_records.push({...g}));
  master.courses.forEach(c=>student_courses.push({...c}));
  refreshStudents(); filterStudents(); updateDashboard();
  alert(`Added ${id}`);
}
function loadCombinedData() {
  let sid = document.getElementById('combined-student-id').value.trim();
  let s = students.find(s=>s.id===sid);
  let res = document.getElementById('combined-results');
  let nf = document.getElementById('combined-notfound');
  let cres = document.getElementById('classification-result');
  let nel = document.getElementById('not-eligible-message');
  if (!s) { res.style.display='none'; nf.style.display='block'; return; }
  nf.style.display='none'; res.style.display='block';
  let adm = sid.split('/')[1]||'N/A';
  document.getElementById('student-details').innerHTML = `<div class="student-avatar"><span class="material-icons">account_circle</span></div><div class="student-info"><p class="student-name">${s.fullName}</p><p>${s.id} | ${s.program}</p><p>Admission: ${adm} | Year: ${s.year}</p><p>GPA: ${s.gpa.toFixed(2)} | Credits: ${s.credits}</p></div>`;
  let elig = s.elig;
  document.getElementById('eligibility-badge-container').innerHTML = `<span class="elig-badge ${elig?'eligible':'not-eligible'}">${elig?'✅ Eligible':'❌ Not Eligible'}</span>`;
  if (elig) {
    let pred = s.cls; if (s.cls==='Second Upper') pred='Second Upper Class'; else if (s.cls==='Second Lower') pred='Second Lower Class';
    document.getElementById('predicted-class-title').textContent = pred;
    document.getElementById('predicted-gpa').textContent = `Current GPA: ${s.gpa.toFixed(2)}`;
    cres.style.display='block'; nel.style.display='none';
  } else { cres.style.display='none'; nel.style.display='block'; }
}
function renderCourses(filter='') {
  let tb = document.getElementById('course-tbody');
  let f = filter ? courses.filter(c=>c.course_id.toLowerCase().includes(filter.toLowerCase())||c.name.toLowerCase().includes(filter.toLowerCase())) : courses;
  tb.innerHTML = f.map(c=>`<tr><td>${c.course_id} ${c.name}</td><td>${c.credits}</td><td>${c.is_gpa?'YES':'NO'}</td><td>${c.status} <span class="material-icons" style="font-size:16px;color:var(--pass);">check_circle</span></td><td><div class="action-icons"><span class="action-icon" onclick="editCourse('${c.course_id}')"><span class="material-icons">edit</span></span><span class="action-icon" onclick="deleteCourse('${c.course_id}')"><span class="material-icons">delete</span></span></div></td></tr>`).join('');
  document.getElementById('course-count').textContent = `${f.length} of ${courses.length} courses`;
}
function searchCourses(q) { renderCourses(q); }
function addCourse() {
  let id = prompt('Course ID:'); if (!id) return;
  let name = prompt('Course name:'); if (!name) return;
  let cred = parseInt(prompt('Credits:')); if (isNaN(cred)) return;
  let isGpa = prompt('GPA course? (yes/no)').toLowerCase()==='yes';
  courses.push({ course_id: id.replace(/\s/g,''), name, credits: cred, is_gpa: isGpa, status: 'COMPULSORY' });
  renderCourses();
}
function editCourse(id) {
  let c = courses.find(c=>c.course_id===id);
  if (!c) return;
  let n = prompt('New name',c.name); if (n) c.name=n;
  let cr = parseInt(prompt('New credits',c.credits)); if (!isNaN(cr)) c.credits=cr;
  let g = prompt('Is GPA? (yes/no)',c.is_gpa?'yes':'no'); c.is_gpa = g.toLowerCase()==='yes';
  renderCourses();
}
function deleteCourse(id) {
  if (confirm('Delete?')) {
    let idx = courses.findIndex(c=>c.course_id===id);
    if (idx>=0) courses.splice(idx,1);
    student_courses = student_courses.filter(sc=>sc.course_id!==id);
    renderCourses();
  }
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
  
  const sc = student_courses.filter(sc => sc.student_no === sid);
  if (sc.length === 0) {
    alert('No course records found for this student.');
    return;
  }
  
  const tbody = document.getElementById('gpa-course-tbody');
  let rows = '';
  sc.forEach(enr => {
    const course = getCourse(enr.course_id);
    if (!course) return;
    const grade = enr.grade;
    const gradePoint = gradePoints[grade] || 0;
    const credits = course.credits;
    const total = (gradePoint * credits).toFixed(2);
    rows += `<tr>
      <td>${course.course_id}</td>
      <td>${grade}</td>
      <td>${gradePoint.toFixed(2)}</td>
      <td>${credits}</td>
      <td>${total}</td>
    </tr>`;
  });
  tbody.innerHTML = rows;
  container.style.display = 'block';

  // Populate verification section with student's cumulative official GPA
  document.getElementById('verify-student-id').value = sid;
  document.getElementById('verify-official-gpa-display').textContent = student.gpa.toFixed(2);
  document.getElementById('verify-result').innerHTML = '';
  document.getElementById('verify-badge').style.display = 'none';
  verifySection.style.display = 'block';
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
    const gradePoint = parseFloat(cells[2].textContent);
    const credits = parseInt(cells[3].textContent);
    totalCredits += credits;
    totalGradePoints += gradePoint * credits;
  });
  
  const calculatedGpa = totalCredits > 0 ? (totalGradePoints / totalCredits) : 0;
  
  const resultDiv = document.getElementById('gpa-computed-result');
  resultDiv.innerHTML = `
    <div style="display: flex; justify-content: space-around; align-items: center;">
      <div><strong>Total Credits:</strong> ${totalCredits}</div>
      <div><strong>Total Grade Points:</strong> ${totalGradePoints.toFixed(2)}</div>
      <div><strong>GPA:</strong> ${calculatedGpa.toFixed(2)}</div>
    </div>
  `;
  resultDiv.style.display = 'block';

  // Cross-check with official GPA from student record
  const sid = document.getElementById('gpa-student-id').value.trim();
  const student = students.find(s => s.id === sid);
  if (!student) return;
  const officialGpa = student.gpa;
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
window.searchStudents = searchStudents; window.editStudent = editStudent; window.addStudent = addStudent;
window.deleteStudent = deleteStudent; window.toggleDetails = toggleDetails; window.loadCombinedData = loadCombinedData;
window.searchCourses = searchCourses; window.addCourse = addCourse; window.editCourse = editCourse;
window.deleteCourse = deleteCourse; window.loadStudentCourses = loadStudentCourses; window.computeGPAFromTable = computeGPAFromTable;
window.verifyGPA = verifyGPA;

refreshStudents(); updateDashboard(); renderCourses();
