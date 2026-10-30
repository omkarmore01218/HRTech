-- HRTech Dummy Accounts and Realistic Seed Data

USE `hrtech_db`;

-- 1. Insert Dummy Users (if not already existing)
-- Passwords:
-- admin@hrtech.com     => admin123
-- manager@hrtech.com   => manager123
-- employee@hrtech.com  => employee123

INSERT INTO `users` (`name`, `email`, `password`, `role`)
VALUES
  ('HR Administrator', 'admin@hrtech.com', '$2y$10$H2dKWAe5lsyyxfklkgT1BeTquHHHYEStzOMlqEGv6E8ytXRUNCERe', 'admin'),
  ('Sarah Connor', 'manager@hrtech.com', '$2y$10$qie7uE4k/FMS0A9zTncSFu5nITJJ5SBg12G5o7jqh09rt/GCaJkha', 'manager'),
  ('John Doe', 'employee@hrtech.com', '$2y$10$5VeEySqPDv6d00pG6PKyF.9JWBYuVU4A0zYKFLP4VTbYomg9V3rZa', 'employee')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role` = VALUES(`role`);

-- 2. Insert Employees Records (Linking with users by full_name and email)
INSERT INTO `employees` (`employee_id`, `full_name`, `email`, `phone`, `department`, `designation`, `joining_date`)
VALUES
  ('EMP001', 'John Doe', 'employee@hrtech.com', '+1-555-0101', 'Engineering', 'Senior Full Stack Developer', '2026-01-10'),
  ('EMP002', 'Omkar More', 'abcd@gmail.com', '+91-9876543210', 'Product & Tech', 'Software Engineer', '2026-02-15'),
  ('EMP003', 'Omkar', 'xyz@gmail.com', '+91-9823456789', 'Product', 'UI/UX Designer', '2026-03-01'),
  ('EMP004', 'Kru', 'abc@gmail.coom', '+91-9812345678', 'Operations', 'Operations Analyst', '2026-03-15'),
  ('EMP005', 'Sarah Connor', 'manager@hrtech.com', '+1-555-0102', 'Engineering', 'Engineering Manager', '2025-11-01'),
  ('EMP006', 'Alice Smith', 'alice@hrtech.com', '+1-555-0103', 'Human Resources', 'Talent Acquisition Specialist', '2026-04-01')
ON DUPLICATE KEY UPDATE
  `full_name` = VALUES(`full_name`),
  `phone` = VALUES(`phone`),
  `department` = VALUES(`department`),
  `designation` = VALUES(`designation`),
  `joining_date` = VALUES(`joining_date`);

-- 3. Onboarding Tasks for John Doe, Omkar More, and Kru
-- Resolve John Doe's employee id
SET @john_id = (SELECT `id` FROM `employees` WHERE `email` = 'employee@hrtech.com' LIMIT 1);
SET @omkar_more_id = (SELECT `id` FROM `employees` WHERE `email` = 'abcd@gmail.com' LIMIT 1);
SET @kru_id = (SELECT `id` FROM `employees` WHERE `email` = 'abc@gmail.coom' LIMIT 1);

-- Tasks for John Doe
INSERT INTO `onboarding_tasks` (`employee_id`, `task_title`, `task_description`, `due_date`, `status`, `submission_text`, `submitted_at`)
VALUES
  (@john_id, 'Complete Corporate HR Orientation', 'Attend the introductory onboarding session, review policies, and submit acknowledgment.', '2026-11-05', 'Completed', 'Attended orientation session on Nov 1st and signed all policy documents.', '2026-11-01 14:30:00'),
  (@john_id, 'Setup Local Development Environment', 'Clone the repository, configure XAMPP Apache and MariaDB, and verify local build.', '2026-11-10', 'In Progress', NULL, NULL),
  (@john_id, 'Security Compliance & Data Privacy Training', 'Watch cybersecurity awareness modules and pass the 10-question compliance quiz.', '2026-11-15', 'Pending', NULL, NULL);

-- Tasks for Omkar More
INSERT INTO `onboarding_tasks` (`employee_id`, `task_title`, `task_description`, `due_date`, `status`, `submission_text`, `submitted_at`)
VALUES
  (@omkar_more_id, 'Submit Identity & Tax Documents', 'Upload Aadhaar, PAN card, and banking information for payroll setup.', '2026-11-08', 'Completed', 'Submitted scanned copies of PAN, Aadhaar, and bank details to HR.', '2026-10-27 18:00:00'),
  (@omkar_more_id, 'Engineering Team Intro & Mentorship Meeting', 'Schedule a 1-on-1 meeting with team lead and mentor to align on sprint goals.', '2026-11-12', 'Pending', NULL, NULL);

-- Tasks for Kru
INSERT INTO `onboarding_tasks` (`employee_id`, `task_title`, `task_description`, `due_date`, `status`, `submission_text`, `submitted_at`)
VALUES
  (@kru_id, 'Operations Tooling Access Setup', 'Request credentials for ERP, JIRA, and internal ticketing system.', '2026-11-06', 'Completed', 'All access credentials received and verified.', '2026-10-26 11:20:00');

-- 4. Performance Reviews
INSERT INTO `performance_reviews` (`employee_id`, `rating`, `feedback`, `review_date`)
VALUES
  (@john_id, 5, 'Outstanding performance! Quickly adapted to our tech stack, solved legacy technical debt, and delivered the onboarding workflow ahead of schedule.', '2026-10-15'),
  (@john_id, 4, 'Great teamwork and proactive communication. Continues to maintain high code quality standards and helpful documentation.', '2026-08-20'),
  (@omkar_more_id, 5, 'Demonstrated excellent initiative during the onboarding process. High attention to detail and strong engineering foundation.', '2026-10-25'),
  (@kru_id, 4, 'Reliable and consistent execution of operational workflows. Streamlined team reporting processes significantly.', '2026-09-30');

-- 5. Employee Goals
INSERT INTO `employee_goals` (`employee_id`, `goal_title`, `goal_description`, `target_date`, `status`)
VALUES
  (@john_id, 'Architect RESTful API Suite', 'Design and implement scalable REST endpoints for mobile app integration.', '2026-12-15', 'In Progress'),
  (@john_id, 'Achieve 95% Automated Test Coverage', 'Implement unit and integration tests across all onboarding and appraisal modules.', '2026-11-30', 'Completed'),
  (@john_id, 'Technical Mentorship', 'Conduct weekly pair programming and knowledge-sharing sessions with junior developers.', '2027-01-31', 'Not Started'),
  (@omkar_more_id, 'Complete Product Feature Sprint 1', 'Deliver assigned feature backlog items for the employee portal on schedule.', '2026-11-20', 'In Progress'),
  (@omkar_more_id, 'Learn Docker & Containerization', 'Containerize the local development environment using Docker Compose.', '2026-12-10', 'Not Started'),
  (@kru_id, 'Automate Monthly Operations Metrics', 'Build automated monthly dashboard reports for team throughput.', '2026-11-25', 'Completed');

-- 6. Leave Requests
INSERT INTO `leave_requests` (`employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`)
VALUES
  (@john_id, 'Annual Leave', '2026-11-20', '2026-11-24', 'Family vacation and travel.', 'Approved'),
  (@john_id, 'Sick Leave', '2026-10-12', '2026-10-13', 'Viral fever and doctor consultation.', 'Approved'),
  (@omkar_more_id, 'Casual Leave', '2026-11-18', '2026-11-19', 'Personal family function.', 'Pending'),
  (@kru_id, 'Annual Leave', '2026-12-24', '2026-12-28', 'Year-end holiday.', 'Approved');
