const fs = require('fs');

// Load exported DB test data
const raw = JSON.parse(fs.readFileSync('test_data.json', 'utf8'));

// Mock dashboard.js global scoped vars
global.student_info = [
    { student_no: 'PS/2022/145', gpa_value: 3.40, total_credits: 95 },
    { student_no: 'PS/2022/147', gpa_value: 2.10, total_credits: 70 },
    { student_no: 'PS/2022/047', gpa_value: 3.80, total_credits: 120 }
];
global.gpa_records = [];
global.enrollment = [
    { student_no: 'PS/2022/145', year_of_study: 1 },
    { student_no: 'PS/2022/145', year_of_study: 2 },
    { student_no: 'PS/2022/145', year_of_study: 3 },
    { student_no: 'PS/2022/047', year_of_study: 1 },
    { student_no: 'PS/2022/047', year_of_study: 2 },
    { student_no: 'PS/2022/047', year_of_study: 3 }
];
global.student_courses = raw.courses;
global.programs = [{ program_id: 1, program_code: 'PS', program_name: 'Physical Science', max_year: 3 }];
global.academic_years = [];

// Copy the exact dashboard.js implementation to test headless execution
function computeStudentAggregate(sno) {
  if (!sno || typeof sno !== 'string') return null;
  const info = student_info.find(s=>s.student_no===sno);
  if (!info) return null;

  let program = programs[0];

  const recs = gpa_records.filter(r=>r.student_no===sno);
  const totalCredits = info.total_credits !== undefined ? parseFloat(info.total_credits) : recs.reduce((s,r)=>s+r.total_credits,0);
  
  const officialGpa = (info.database_gpa !== undefined && info.database_gpa !== null) ? parseFloat(info.database_gpa) : 0;
  let calculatedGpa = 0;
  if (info.gpa_value !== undefined && info.gpa_value !== null) {
    calculatedGpa = parseFloat(info.gpa_value);
  } else {
    let totalP=0, totalC=0;
    recs.forEach(r=>{ totalP += r.gpa_value * r.total_credits; totalC += r.total_credits; });
    calculatedGpa = totalC ? totalP/totalC : 0;
  }

  let overallGpa = calculatedGpa;

  // Compute years based on enrollment
  const enrolls = enrollment.filter(e=>e.student_no===sno);
  const currentYear = enrolls.length ? Math.max(...enrolls.map(e=>e.year_of_study)) : 1;
  const studentCourses = student_courses.filter(c => c.student_no === sno);

  // Core eligibility variables
  let totalDCredits = 0;
  let totalCCredits = 0;
  let firstTwoYearsDCredits = 0;
  let yearlyCredits = {};
  
  // Class prediction variables
  let gradeACredits = 0;
  let gradeBCredits = 0;

  // Subject specialization variables (only counting C or higher)
  let subjectRecords = {}; // Format: { "CHEM": { credits: 30, hasPractical: true } }

  const validGradesDPlus = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D'];
  const validGradesCPlus = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C'];

  studentCourses.forEach(c => {
    let grade = c.grade_code || c.grade || ''; 
    let credits = parseInt(c.credit_value || c.credits || 0);
    let modCode = (c.module_code || c.course_id || '').trim();
    if (!modCode || credits === 0 || grade === 'AB' || grade === 'NULL' || grade === '') return;

    let subject = modCode.substring(0, 4);
    let yearOfStudy = parseInt(modCode.substring(4, 5)) || 1;
    let isPractical = modCode.substring(modCode.length - 1) === '1';

    let isDOrHigher = validGradesDPlus.includes(grade);
    let isCOrHigher = validGradesCPlus.includes(grade);
    let isA = grade.startsWith('A');
    let isBOrHigher = grade.startsWith('A') || grade.startsWith('B');

    // Rule 1, 2, 3: D or higher counts
    if (isDOrHigher) {
      totalDCredits += credits;
      yearlyCredits[yearOfStudy] = (yearlyCredits[yearOfStudy] || 0) + credits;
      if (yearOfStudy === 1 || yearOfStudy === 2) {
        firstTwoYearsDCredits += credits;
      }
    }

    // Rule 4, 5, 6: C or higher counts
    if (isCOrHigher) {
      totalCCredits += credits;
      if (!subjectRecords[subject]) {
        subjectRecords[subject] = { credits: 0, hasPractical: false };
      }
      subjectRecords[subject].credits += credits;
      if (isPractical) {
        subjectRecords[subject].hasPractical = true;
      }
    }

    // Stats for Classification
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
  for (let subj in subjectRecords) {
    if (subjectRecords[subj].credits >= 24) {
      specCount++;
      if (subjectRecords[subj].hasPractical) {
        hasSpecWithPractical = true;
      }
    }
  }
  if (specCount >= 2) hasTwoSpecializations = true;

  // Must have 30 credits in EVERY year (1 to currentYear)
  let meetsYearlyMinimums = true;
  for (let y = 1; y <= currentYear; y++) {
    if ((yearlyCredits[y] || 0) < 30) {
      meetsYearlyMinimums = false;
      break;
    }
  }

  // Fallback to basic eligibility if we don't have full course records loaded in memory yet
  let eligible = false;
  if (studentCourses.length === 0 && Array.isArray(recs) && recs.length > 0) {
     eligible = overallGpa >= 2.0 && totalCredits >= 90;
  } else {
     eligible = (
        totalDCredits >= 90 &&
        meetsYearlyMinimums &&
        firstTwoYearsDCredits >= 60 &&
        totalCCredits >= 72 &&
        hasTwoSpecializations &&
        hasSpecWithPractical &&
        overallGpa >= 2.00 &&
        duration <= 5
     );
  }

  // console debugging logic block inside test script
  console.log(`\nEvaluation for ${sno}:`);
  console.log(`   D+ Credits: ${totalDCredits} (Required >= 90)`);
  console.log(`   Yearly Min 30: ${meetsYearlyMinimums}`);
  console.log(`   First 2 Yrs D+: ${firstTwoYearsDCredits} (Required >= 60)`);
  console.log(`   C+ Credits: ${totalCCredits} (Required >= 72)`);
  console.log(`   Two Specs (24+ C+): ${hasTwoSpecializations} (Found: ${specCount})`);
  console.log(`   One Spec has Practical: ${hasSpecWithPractical}`);
  console.log(`   GPA: ${overallGpa.toFixed(2)} (Required >= 2.00)`);
  console.log(`   Duration: ${duration} (Required <= 5)`);
  console.log(`>> Result Eligible: ${eligible} <<`);

  let cls = 'No Class';
  if (eligible && duration <= 3) {
      if (totalCCredits >= 90 && gradeACredits >= (totalCCredits * 0.5) && overallGpa >= 3.70) {
          cls = 'First Class';
      } else if (totalCCredits >= 80 && gradeBCredits >= (totalCCredits * 0.5) && overallGpa >= 3.30) {
          cls = 'Second Upper';
      } else if (totalCCredits >= 80 && gradeBCredits >= (totalCCredits * 0.5) && overallGpa >= 3.00) {
          cls = 'Second Lower';
      } else {
          cls = 'Pass';
      }
  } else if (eligible) {
      cls = 'Pass'; 
  }

  if (!eligible) cls = 'Fail';
  console.log(`>> Predicted Class: ${cls} <<\n`);
  return { id: sno, elig: eligible, cls: cls };
}

computeStudentAggregate('PS/2022/145');
computeStudentAggregate('PS/2022/147');
computeStudentAggregate('PS/2022/047');
