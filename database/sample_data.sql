-- Sample Data for Testing Exam Management System
-- Run this script after importing schema.sql

USE exam_management;

-- Insert sample students
INSERT INTO students (roll_no, name, branch, semester, section) VALUES
('2021001', 'Aarav Sharma', 'Computer Science', 5, 'A'),
('2021002', 'Aditi Patel', 'Computer Science', 5, 'A'),
('2021003', 'Arjun Kumar', 'Computer Science', 5, 'A'),
('2021004', 'Diya Gupta', 'Computer Science', 5, 'B'),
('2021005', 'Kavya Singh', 'Computer Science', 5, 'B'),
('2021006', 'Rohan Verma', 'Electronics', 5, 'A'),
('2021007', 'Priya Reddy', 'Electronics', 5, 'A'),
('2021008', 'Vivaan Joshi', 'Electronics', 5, 'B'),
('2021009', 'Ananya Desai', 'Electronics', 5, 'B'),
('2021010', 'Ishaan Mehta', 'Mechanical', 5, 'A'),
('2021011', 'Sanya Agarwal', 'Mechanical', 5, 'A'),
('2021012', 'Ayaan Khan', 'Mechanical', 5, 'B'),
('2021013', 'Myra Iyer', 'Civil', 5, 'A'),
('2021014', 'Reyansh Nair', 'Civil', 5, 'A'),
('2021015', 'Aadhya Rao', 'Civil', 5, 'B'),
('2021016', 'Vihaan Pillai', 'Computer Science', 6, 'A'),
('2021017', 'Sara Saxena', 'Computer Science', 6, 'A'),
('2021018', 'Aryan Malhotra', 'Computer Science', 6, 'B'),
('2021019', 'Ira Thakur', 'Electronics', 6, 'A'),
('2021020', 'Kabir Bansal', 'Electronics', 6, 'B');

-- Create user accounts for students (password: student123)
INSERT INTO users (username, password, role, reference_id)
SELECT roll_no, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', student_id
FROM students;

-- Insert sample faculty
INSERT INTO faculty (name, department, designation, total_duties) VALUES
('Dr. Rajesh Kumar', 'Computer Science', 'Professor', 0),
('Dr. Meera Sharma', 'Computer Science', 'Associate Professor', 0),
('Prof. Amit Verma', 'Electronics', 'Assistant Professor', 0),
('Dr. Priya Singh', 'Electronics', 'Professor', 0),
('Prof. Suresh Gupta', 'Mechanical', 'Associate Professor', 0),
('Dr. Neha Kapoor', 'Mechanical', 'Assistant Professor', 0),
('Prof. Vikram Reddy', 'Civil', 'Professor', 0),
('Dr. Anjali Patel', 'Civil', 'Associate Professor', 0),
('Prof. Ravi Joshi', 'Computer Science', 'Assistant Professor', 0),
('Dr. Sanjay Mehta', 'Electronics', 'Professor', 0);

-- Create user accounts for faculty (password: faculty123)
INSERT INTO users (username, password, role, reference_id)
SELECT CONCAT('fac', faculty_id), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', faculty_id
FROM faculty;

-- Insert sample rooms
INSERT INTO rooms (room_no, capacity, building) VALUES
('101', 30, 'Main Building'),
('102', 30, 'Main Building'),
('103', 25, 'Main Building'),
('201', 35, 'CS Block'),
('202', 30, 'CS Block'),
('203', 25, 'CS Block'),
('301', 40, 'Engineering Block'),
('302', 30, 'Engineering Block');

-- Insert sample exams
INSERT INTO exams (exam_name, date, session) VALUES
('Mid-Term Examination', '2026-03-20', 'Morning'),
('Final Examination', '2026-04-15', 'Morning'),
('Practical Examination', '2026-03-25', 'Afternoon');

-- Insert sample exam schedules
INSERT INTO exam_schedule (exam_id, subject_name, exam_date, start_time, end_time, session, duration) VALUES
(1, 'Data Structures', '2026-03-20', '09:00:00', '11:00:00', 'Morning', 120),
(1, 'Database Management', '2026-03-21', '09:00:00', '11:00:00', 'Morning', 120),
(2, 'Operating Systems', '2026-04-15', '09:00:00', '12:00:00', 'Morning', 180),
(2, 'Computer Networks', '2026-04-16', '09:00:00', '12:00:00', 'Morning', 180),
(3, 'Programming Lab', '2026-03-25', '14:00:00', '17:00:00', 'Afternoon', 180);

-- Sample CSV format for bulk import
-- You can create a file named sample_students.csv with this format:
-- Roll No,Name,Branch,Semester,Section
-- 2021021,Student Name 1,Computer Science,5,A
-- 2021022,Student Name 2,Electronics,5,B
-- 2021023,Student Name 3,Mechanical,6,A

-- Instructions:
-- 1. Import schema.sql first to create the database structure
-- 2. Import this sample_data.sql to populate test data
-- 3. Login with:
--    Admin: username = admin, password = admin123
--    Student: username = 2021001 (roll number), password = student123
--    Faculty: username = fac1, password = faculty123
-- 4. Test the seating and invigilation allocation features

-- Note: After importing, you can:
-- - Allocate seating for exams
-- - Allocate invigilation duties
-- - Mark attendance
-- - Generate reports
-- - Test student portal
