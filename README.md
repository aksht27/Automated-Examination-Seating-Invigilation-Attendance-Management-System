# Automated Examination Seating Management System

A comprehensive web-based system for automating examination seating allocation, invigilation duty assignment, attendance management, and exam scheduling for universities.

## Features

### Core Modules
1. **Authentication System** - Role-based access control (Admin, Exam Cell, Faculty, Student)
2. **Student Management** - Add, edit, delete students with CSV bulk upload
3. **Faculty Management** - Manage faculty members and track invigilation duties
4. **Room Management** - Configure examination halls with capacity limits
5. **Exam Schedule Management** - Create exam schedules with conflict detection
6. **Seating Allocation** - Automated student seating based on room capacity
7. **Invigilation Allocation** - Fair distribution of invigilation duties
8. **Attendance Management** - Mark and track student attendance
9. **Replacement Management** - Handle faculty replacement requests
10. **Reporting Dashboard** - Generate various reports and analytics
11. **Student Portal** - Students can view exam schedule and seating allocation

### Key Features
- ✅ Automated seating allocation algorithm
- ✅ Fair invigilation duty distribution
- ✅ Conflict detection for exam scheduling
- ✅ Room capacity validation
- ✅ CSV bulk import for students
- ✅ Printable seating charts and duty rosters
- ✅ Attendance tracking with Present/Absent status
- ✅ Faculty replacement workflow
- ✅ Comprehensive reporting system
- ✅ Student portal for exam details
- ✅ Responsive design

## Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP Compatible

## Installation Instructions

### Prerequisites
- XAMPP (or WAMP/LAMP) with PHP 7.4+ and MySQL 5.7+
- Web browser (Chrome, Firefox, Edge)

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP and start Apache and MySQL services

### Step 2: Setup Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `exam_management`
3. Import the database schema:
   - Click on the `exam_management` database
   - Go to "Import" tab
   - Select `database/schema.sql` file
   - Click "Go" to import

### Step 3: Configure Application
1. Copy the project folder to XAMPP's `htdocs` directory:
   ```
   C:\xampp\htdocs\Automated Seating management\
   ```

2. Update database credentials if needed in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'exam_management');
   ```

### Step 4: Access the System
1. Open your web browser
2. Navigate to: `http://localhost/Automated%20Seating%20management/`
3. Login with default credentials:

   **Admin Account:**
   - Username: `admin`
   - Password: `admin123`
   - Role: Admin

   **Note:** Change the default password immediately after first login!

## Default Login Credentials

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| Admin | admin | admin123 | Full system access |
| Student | (Roll Number) | student123 | Student portal only |
| Faculty | fac1, fac2, etc. | faculty123 | Duty and replacement management |
| Exam Cell | examcell | examcell123 | Exam management access |

**Important:** Change all default passwords after first login!

## User Guide

### For Admin

1. **Add Students**
   - Navigate to Students module
   - Click "Add Student" or "Upload CSV"
   - Fill in student details: Roll No, Name, Branch, Semester, Section
   - User accounts are automatically created (Username: Roll Number)

2. **Add Faculty**
   - Navigate to Faculty module
   - Click "Add Faculty"
   - Enter faculty details: Name, Department, Designation
   - User accounts are automatically created (Username: fac{ID})

3. **Add Rooms**
   - Navigate to Rooms module
   - Click "Add Room"
   - Enter room details: Room No, Capacity, Building

4. **Create Exam Schedule**
   - Navigate to Exam Schedule module
   - Click "Create Exam Schedule"
   - Fill in: Exam Name, Subject, Date, Time, Session
   - System validates for scheduling conflicts

5. **Allocate Seating**
   - Navigate to Seating Allocation module
   - Select exam and click "Allocate Seating"
   - System automatically allocates students to rooms based on:
     - Students sorted by roll number
     - Room capacity limits
     - Sequential seat numbering

6. **Allocate Invigilation Duties**
   - Navigate to Invigilation module
   - Select exam and click "Allocate Duties"
   - System automatically assigns faculty based on:
     - Fair distribution (least duties first)
     - No duplicate assignments in same session

7. **Mark Attendance**
   - Navigate to Attendance module
   - Select exam from dropdown
   - Mark Present/Absent for each student
   - Save attendance records

8. **Generate Reports**
   - Navigate to Reports module
   - Choose report type:
     - Room-wise Seating Plan
     - Duty Chart
     - Faculty Duty Summary
     - Attendance Summary
     - Room Utilization
     - Exam Timetable
   - Print or export reports

### For Students

1. **View Exam Schedule**
   - Login with roll number
   - View all scheduled exams with dates and timings

2. **View Seating Allocation**
   - Navigate to "My Seating"
   - View room number and seat number for each exam
   - Download/Print seating slip

3. **Download Seating Slip**
   - Contains: Name, Roll No, Exam details, Room No, Seat No
   - Print button available

### For Faculty

1. **View Assigned Duties**
   - Login and view "My Invigilation Duties"
   - See exam name, date, room assignment

2. **Request Replacement**
   - Click "Request Replacement" for any duty
   - Provide reason for replacement request
   - Admin will approve/reject request

### For Exam Cell

- Similar access to Admin for exam-related operations
- Can manage: Students, Exam Schedule, Seating, Invigilation, Attendance

## System Algorithms

### Seating Allocation Algorithm
```
1. Get all students sorted by roll number (ascending)
2. Get all rooms sorted by room number
3. For each room:
   a. Allocate students until room capacity is reached
   b. Assign sequential seat numbers (1, 2, 3, ...)
   c. Move to next room
4. Continue until all students are allocated
5. Error if students remain but no rooms available
```

### Invigilation Allocation Algorithm
```
1. Get all faculty sorted by total_duties (ascending)
2. Get all rooms with students for selected exam
3. For each room:
   a. Find faculty with least duties
   b. Check no conflict in same session
   c. Assign faculty to room
   d. Increment faculty's total_duties
   e. Move to next room
4. Continue until all rooms have invigilators
```

## Database Schema

### Main Tables
- **users** - Authentication and role management
- **students** - Student information
- **faculty** - Faculty information
- **rooms** - Examination hall details
- **exams** - Exam master data
- **exam_schedule** - Detailed exam schedule
- **seating_allocation** - Student seat assignments
- **invigilation_allocation** - Faculty duty assignments
- **attendance** - Student attendance records
- **replacement_log** - Faculty replacement requests

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Session-based authentication
- Role-based access control
- Input validation and sanitization

## File Structure

```
Automated Seating management/
├── config/
│   └── database.php          # Database configuration
├── css/
│   └── style.css            # Complete styling
├── js/
│   └── script.js            # JavaScript functions
├── modules/
│   ├── students.php         # Student management
│   ├── faculty.php          # Faculty management
│   ├── rooms.php            # Room management
│   ├── schedule.php         # Exam scheduling
│   ├── seating.php          # Seating allocation
│   ├── invigilation.php     # Invigilation duties
│   ├── attendance.php       # Attendance management
│   ├── replacement.php      # Replacement requests
│   ├── reports.php          # Reporting module
│   └── student_portal.php   # Student portal
├── database/
│   └── schema.sql           # Database schema
├── index.php                # Login page
├── login.php                # Login handler
├── logout.php               # Logout handler
├── dashboard.php            # Admin dashboard
└── README.md               # This file
```

## Troubleshooting

### Common Issues

1. **Database connection error**
   - Check if MySQL is running in XAMPP
   - Verify database credentials in `config/database.php`
   - Ensure database `exam_management` exists

2. **Login not working**
   - Clear browser cache and cookies
   - Check if admin user exists in database
   - Verify password is `admin123`

3. **CSV upload fails**
   - Check CSV format matches: Roll No, Name, Branch, Semester, Section
   - Ensure first row is header
   - Check for duplicate roll numbers

4. **Seating allocation fails**
   - Ensure rooms are added with capacity
   - Ensure students exist in database
   - Check total room capacity >= total students

5. **Invigilation allocation fails**
   - Ensure seating is allocated first
   - Ensure enough faculty members exist
   - Check faculty count >= rooms with students

## Browser Support

- Chrome 90+
- Firefox 88+
- Edge 90+
- Safari 14+

## Performance

- Handles 2000+ students efficiently
- Optimized SQL queries with indexes
- Minimal JavaScript for fast loading
- Responsive design for mobile devices

## Future Enhancements

- Email notifications
- SMS alerts
- QR code for seating slips
- Barcode attendance scanning
- Advanced analytics dashboard
- Excel export functionality
- Multi-language support
- Dark mode theme

## Support

For issues or questions:
- Check troubleshooting section
- Review database schema
- Verify file permissions
- Check PHP error logs

## License

This system is developed for educational and institutional use.

## Credits

Developed as a comprehensive exam management solution for universities and educational institutions.

---

**Version:** 1.0.0
**Last Updated:** March 2026
**Developed By:** Exam Management System Team
