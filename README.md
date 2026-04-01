# 🎓 Degree Eligibility and Class Predictor

### Faculty of Science – University of Kelaniya

---

# 📌 Project Overview

The **Degree Eligibility and Class Predictor** is a web-based system designed to manage student academic records, calculate GPAs, and predict degree eligibility and final classification for students in the Faculty of Science at the University of Kelaniya.

This system allows faculty administrators to:

* Manage student academic records
* Upload and process student enrollment data
* Calculate GPA automatically
* Predict degree eligibility & final class
* Generate reports and analytics

---

# 🚀 Key Features

## 1. Faculty Authentication

* Secure login system
* Cookie-based session management
* Admin access control

## 2. Student Records Management

* View all student records
* Search and filter students
* Track credits per student
* CSV upload support

## 3. Course / Module Management

* Manage course offerings
* Define credit values
* Map modules to academic years

## 4. GPA Calculation Engine

* Automatic GPA calculation
* Credit weighted calculation
* Handles failed attempts
* Supports multiple grading schemes

## 5. Degree Eligibility Predictor

* Predict graduation eligibility
* Predict final class

Classification Types:

* First Class Honors
* Second Upper
* Second Lower
* Third Class
* Pass

---

# 🏗️ Project Structure

```
Connection/
gui/
assets/
vendor/

test files
debug files
utilities
configuration files
```

---

# 💻 Frontend Technologies

* HTML5
* CSS3
* JavaScript
* Material Icons
* Responsive UI

---

# ⚙️ Backend Technologies

* PHP 7+
* MySQL
* Composer
* PHPSpreadsheet

---

# 🗄️ Database Tables

* student
* module
* module_enrollment
* academic_year
* program
* grade_result

---

# 🔧 Setup & Installation

## 1. Prerequisites

* PHP 7.4+
* MySQL
* Composer
* Apache Server

---

## 2. Database Setup

Create database:

```
degree_eligibility_db
```

Update connection:

```
Connection/connection.php
```

---

## 3. Install Dependencies

```
composer install
```

---

## 4. Run Project

Open:

```
faculty-login.php
```

Login and start using system.

---

# 📊 GPA Calculation

GPA Formula:

```
GPA = Sum (Grade Point × Credits) / Total Credits
```

System handles:

* Multiple attempts
* Failed modules
* Incomplete modules

---

# 🎯 Eligibility Criteria

Minimum requirements:

* Required credits completed
* Minimum GPA reached
* Core modules completed

Classification:

| Class        | GPA  |
| ------------ | ---- |
| First Class  | 3.5+ |
| Second Upper | 3.0+ |
| Second Lower | 2.5+ |
| Third Class  | 2.0+ |
| Pass         | 1.5+ |

---

# 🔐 Security Features

* Faculty login authentication
* Session management
* Input validation
* Admin-only access

---

# 🧪 Testing Files

```
test_eligibility.php
test_gpa_calculation.php
test_upload.php
verify_total_credits.php
```

---

# 🛠️ API Endpoints

### Get Students

```
GET /gui/get_students.php
```

### Get Courses

```
GET /gui/get_courses.php
```

### Upload Students CSV

```
POST /gui/upload_students_csv.php
```

---

# 📦 Dependencies

```
phpoffice/phpspreadsheet
```

Install:

```
composer install
```

---

# 📂 Git Repository

```
https://github.com/dewminidulari/degree_eligibility.git
```

Clone:

```
git clone https://github.com/dewminidulari/degree_eligibility.git
```

---

# 👩‍💻 Project Information

Project: Degree Eligibility and Class Predictor
Institution: University of Kelaniya
Faculty: Science
Type: Academic Project

---

# ⚠️ Important Notes

* Backup database regularly
* Protect student data
* Use modern browser
* Enable JavaScript

---

# 📝 License

This project is developed for academic purposes at University of Kelaniya.

---

# ✅ Version

Version: 1.0
Year: 2026

---
