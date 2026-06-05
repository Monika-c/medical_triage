-- phpMyAdmin SQL Dump
-- Medical Triage System (BCS403 DBMS Project)
-- Authors: Dhanya S & Monika C J

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- -------------------------------------------------------
-- DATABASE CREATION
-- -------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `triage_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `triage_db`;

-- -------------------------------------------------------
-- TABLE 10: admins (For authentication)
-- -------------------------------------------------------
CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password is 'admin123' (hashed using PHP password_hash)
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$QO2mIokK1P7bE2E2.Hk0KOsrW3P.K5x7f1Mh7B6D1s5y3O1o8M6m6'); 


-- -------------------------------------------------------
-- TABLE 1: patients
-- -------------------------------------------------------
CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `phone` varchar(15) UNIQUE DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `registered_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 2: departments
-- -------------------------------------------------------
CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(100) NOT NULL,
  `dept_head` varchar(100) DEFAULT NULL,
  `total_beds` int(11) DEFAULT 0,
  `available_beds` int(11) DEFAULT 0,
  `contact_number` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 3: doctors
-- -------------------------------------------------------
CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `available_status` enum('Available','Busy','Off Duty') DEFAULT 'Available',
  `joined_date` date DEFAULT NULL,
  PRIMARY KEY (`doctor_id`),
  FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 4: visits
-- -------------------------------------------------------
CREATE TABLE `visits` (
  `visit_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `arrival_time` timestamp DEFAULT current_timestamp(),
  `visit_purpose` varchar(255) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `priority_level` enum('Critical','High','Medium','Low') DEFAULT 'Medium',
  `status` enum('Waiting','In Treatment','Discharged','Transferred') DEFAULT 'Waiting',
  `last_updated` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`visit_id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 5: triage_records
-- -------------------------------------------------------
CREATE TABLE `triage_records` (
  `triage_id` int(11) NOT NULL AUTO_INCREMENT,
  `visit_id` int(11) DEFAULT NULL,
  `assessed_by` varchar(100) DEFAULT NULL,
  `severity_score` int(11) CHECK (`severity_score` BETWEEN 1 AND 10),
  `priority_assigned` enum('Critical','High','Medium','Low') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assessed_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`triage_id`),
  FOREIGN KEY (`visit_id`) REFERENCES `visits` (`visit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 6: treatments
-- -------------------------------------------------------
CREATE TABLE `treatments` (
  `treatment_id` int(11) NOT NULL AUTO_INCREMENT,
  `visit_id` int(11) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `medication` text DEFAULT NULL,
  `procedure_done` text DEFAULT NULL,
  `treatment_start` timestamp NULL DEFAULT NULL,
  `treatment_end` timestamp NULL DEFAULT NULL,
  `treated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`treatment_id`),
  FOREIGN KEY (`visit_id`) REFERENCES `visits` (`visit_id`) ON DELETE CASCADE,
  FOREIGN KEY (`treated_by`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 7: discharge_records
-- -------------------------------------------------------
CREATE TABLE `discharge_records` (
  `discharge_id` int(11) NOT NULL AUTO_INCREMENT,
  `visit_id` int(11) DEFAULT NULL,
  `discharge_time` timestamp DEFAULT current_timestamp(),
  `discharge_notes` text DEFAULT NULL,
  `outcome` enum('Recovered','Referred','Deceased','Follow-up Required') DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  PRIMARY KEY (`discharge_id`),
  FOREIGN KEY (`visit_id`) REFERENCES `visits` (`visit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 8: triage_logs
-- -------------------------------------------------------
CREATE TABLE `triage_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `visit_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `old_priority` varchar(50) DEFAULT NULL,
  `new_priority` varchar(50) DEFAULT NULL,
  `changed_at` timestamp DEFAULT current_timestamp(),
  `changed_by` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`visit_id`) REFERENCES `visits` (`visit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- TABLE 9: resource_allocation
-- -------------------------------------------------------
CREATE TABLE `resource_allocation` (
  `allocation_id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_id` int(11) DEFAULT NULL,
  `visit_id` int(11) DEFAULT NULL,
  `bed_number` varchar(20) DEFAULT NULL,
  `equipment_used` text DEFAULT NULL,
  `allocated_at` timestamp DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`allocation_id`),
  FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE,
  FOREIGN KEY (`visit_id`) REFERENCES `visits` (`visit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- INDEXES
-- -------------------------------------------------------
CREATE INDEX idx_visits_priority ON visits(priority_level);
CREATE INDEX idx_visits_status ON visits(status);
CREATE INDEX idx_visits_arrival ON visits(arrival_time);
CREATE INDEX idx_visits_patient ON visits(patient_id);
CREATE INDEX idx_visits_doctor ON visits(doctor_id);
CREATE INDEX idx_visits_dept ON visits(dept_id);
CREATE INDEX idx_triage_logs_visit ON triage_logs(visit_id);
CREATE INDEX idx_patients_phone ON patients(phone);

-- -------------------------------------------------------
-- TRIGGERS
-- -------------------------------------------------------
DELIMITER $$

-- TRIGGER 1: after_visit_insert
CREATE TRIGGER `after_visit_insert` AFTER INSERT ON `visits`
FOR EACH ROW
BEGIN
    INSERT INTO triage_logs (visit_id, event_type, new_status, new_priority, patient_id, changed_by)
    VALUES (NEW.visit_id, 'Patient Registered', NEW.status, NEW.priority_level, NEW.patient_id, 'System');
    
    UPDATE departments SET available_beds = available_beds - 1 WHERE dept_id = NEW.dept_id;
END$$

-- TRIGGER 2: after_visit_status_update
CREATE TRIGGER `after_visit_status_update` AFTER UPDATE ON `visits`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO triage_logs (visit_id, event_type, old_status, new_status, patient_id, changed_by)
        VALUES (NEW.visit_id, 'Status Changed', OLD.status, NEW.status, NEW.patient_id, 'System');
    END IF;
END$$

-- TRIGGER 3: after_priority_change
CREATE TRIGGER `after_priority_change` AFTER UPDATE ON `visits`
FOR EACH ROW
BEGIN
    IF OLD.priority_level != NEW.priority_level THEN
        INSERT INTO triage_logs (visit_id, event_type, old_priority, new_priority, patient_id, changed_by, remarks)
        VALUES (NEW.visit_id, 'Priority Updated', OLD.priority_level, NEW.priority_level, NEW.patient_id, 'System', 'Auto-logged by trigger');
    END IF;
END$$

-- TRIGGER 4: after_discharge
CREATE TRIGGER `after_discharge` AFTER INSERT ON `discharge_records`
FOR EACH ROW
BEGIN
    DECLARE v_dept_id INT;
    
    UPDATE visits SET status = 'Discharged' WHERE visit_id = NEW.visit_id;
    
    SELECT dept_id INTO v_dept_id FROM visits WHERE visit_id = NEW.visit_id;
    UPDATE departments SET available_beds = available_beds + 1 WHERE dept_id = v_dept_id;
END$$

DELIMITER ;

-- -------------------------------------------------------
-- STORED PROCEDURES
-- -------------------------------------------------------
DELIMITER $$

-- PROCEDURE 1: admit_patient
CREATE PROCEDURE `admit_patient`(
    IN p_patient_id INT, IN p_dept_id INT, IN p_doctor_id INT, 
    IN p_symptoms TEXT, IN p_visit_purpose VARCHAR(255),
    IN p_priority ENUM('Critical','High','Medium','Low'),
    IN p_assessed_by VARCHAR(100), IN p_severity_score INT, IN p_notes TEXT
)
BEGIN
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    INSERT INTO visits (patient_id, dept_id, doctor_id, symptoms, visit_purpose, priority_level, status)
    VALUES (p_patient_id, p_dept_id, p_doctor_id, p_symptoms, p_visit_purpose, p_priority, 'Waiting');
    
    SET @new_visit_id = LAST_INSERT_ID();
    
    INSERT INTO triage_records (visit_id, assessed_by, severity_score, priority_assigned, notes)
    VALUES (@new_visit_id, p_assessed_by, p_severity_score, p_priority, p_notes);
    
    UPDATE doctors SET available_status = 'Busy' WHERE doctor_id = p_doctor_id;
    
    COMMIT;
END$$

-- PROCEDURE 2: discharge_patient
CREATE PROCEDURE `discharge_patient`(
    IN p_visit_id INT, IN p_diagnosis TEXT, IN p_medication TEXT, 
    IN p_procedure_done TEXT, IN p_discharge_notes TEXT, 
    IN p_outcome ENUM('Recovered','Referred','Deceased','Follow-up Required'),
    IN p_doctor_id INT, IN p_follow_up_date DATE
)
BEGIN
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    INSERT INTO treatments (visit_id, diagnosis, medication, procedure_done, treatment_start, treatment_end, treated_by)
    VALUES (p_visit_id, p_diagnosis, p_medication, p_procedure_done, (SELECT arrival_time FROM visits WHERE visit_id = p_visit_id), NOW(), p_doctor_id);
    
    INSERT INTO discharge_records (visit_id, discharge_notes, outcome, follow_up_date)
    VALUES (p_visit_id, p_discharge_notes, p_outcome, p_follow_up_date);
    
    UPDATE doctors SET available_status = 'Available' WHERE doctor_id = p_doctor_id;
    
    COMMIT;
END$$

-- PROCEDURE 3: transfer_patient
CREATE PROCEDURE `transfer_patient`(
    IN p_visit_id INT, IN p_new_dept_id INT, IN p_new_doctor_id INT, IN p_reason TEXT
)
BEGIN
    DECLARE v_old_dept_id INT;
    
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
    
    SELECT dept_id INTO v_old_dept_id FROM visits WHERE visit_id = p_visit_id;
    
    UPDATE visits SET dept_id = p_new_dept_id, doctor_id = p_new_doctor_id, status = 'Transferred' WHERE visit_id = p_visit_id;
    
    INSERT INTO triage_logs (visit_id, event_type, remarks, changed_by)
    VALUES (p_visit_id, 'Patient Transferred', p_reason, 'System');
    
    UPDATE departments SET available_beds = available_beds + 1 WHERE dept_id = v_old_dept_id;
    UPDATE departments SET available_beds = available_beds - 1 WHERE dept_id = p_new_dept_id;
    
    COMMIT;
END$$

-- PROCEDURE 4: get_patient_history
CREATE PROCEDURE `get_patient_history`(IN p_patient_id INT)
BEGIN
    SELECT v.*, d.dept_name, doc.name AS doctor_name, 
           tr.severity_score, tr.priority_assigned, tr.notes AS triage_notes,
           t.diagnosis, t.medication, t.procedure_done,
           dr.discharge_notes, dr.outcome, dr.follow_up_date
    FROM visits v
    LEFT JOIN triage_records tr ON v.visit_id = tr.visit_id
    LEFT JOIN treatments t ON v.visit_id = t.visit_id
    LEFT JOIN discharge_records dr ON v.visit_id = dr.visit_id
    LEFT JOIN departments d ON v.dept_id = d.dept_id
    LEFT JOIN doctors doc ON v.doctor_id = doc.doctor_id
    WHERE v.patient_id = p_patient_id
    ORDER BY v.arrival_time DESC;
END$$

DELIMITER ;

-- -------------------------------------------------------
-- VIEWS
-- -------------------------------------------------------

-- VIEW 1: department_summary
CREATE VIEW `department_summary` AS
SELECT d.dept_id, d.dept_name, d.total_beds, d.available_beds,
       COUNT(v.visit_id) AS active_visits_today,
       AVG(TIMESTAMPDIFF(MINUTE, v.arrival_time, NOW())) AS avg_wait_time,
       SUM(CASE WHEN v.priority_level = 'Critical' THEN 1 ELSE 0 END) AS critical_cases
FROM departments d
LEFT JOIN visits v ON d.dept_id = v.dept_id AND DATE(v.arrival_time) = CURDATE() AND v.status != 'Discharged'
GROUP BY d.dept_id, d.dept_name, d.total_beds, d.available_beds;

-- VIEW 2: doctor_workload
CREATE VIEW `doctor_workload` AS
SELECT doc.doctor_id, doc.name AS doctor_name, doc.specialization, d.dept_name,
       SUM(CASE WHEN v.status IN ('Waiting', 'In Treatment') THEN 1 ELSE 0 END) AS active_patients,
       COUNT(v.visit_id) AS patients_today,
       doc.available_status
FROM doctors doc
LEFT JOIN departments d ON doc.dept_id = d.dept_id
LEFT JOIN visits v ON doc.doctor_id = v.doctor_id AND DATE(v.arrival_time) = CURDATE()
GROUP BY doc.doctor_id, doc.name, doc.specialization, d.dept_name, doc.available_status;

-- VIEW 3: priority_queue
CREATE VIEW `priority_queue` AS
SELECT v.visit_id, p.name AS patient_name, v.symptoms, v.priority_level, v.arrival_time,
       TIMESTAMPDIFF(MINUTE, v.arrival_time, NOW()) AS wait_minutes,
       d.dept_name, doc.name AS doctor_name, v.status
FROM visits v
JOIN patients p ON v.patient_id = p.patient_id
JOIN departments d ON v.dept_id = d.dept_id
JOIN doctors doc ON v.doctor_id = doc.doctor_id
WHERE v.status IN ('Waiting', 'In Treatment')
ORDER BY FIELD(v.priority_level, 'Critical', 'High', 'Medium', 'Low'), v.arrival_time ASC;

-- VIEW 4: daily_report
CREATE VIEW `daily_report` AS
SELECT DATE(arrival_time) AS visit_date,
       COUNT(*) AS total_visits,
       SUM(CASE WHEN priority_level = 'Critical' THEN 1 ELSE 0 END) AS critical,
       SUM(CASE WHEN priority_level = 'High' THEN 1 ELSE 0 END) AS high,
       SUM(CASE WHEN status = 'Discharged' THEN 1 ELSE 0 END) AS discharged,
       AVG(TIMESTAMPDIFF(MINUTE, arrival_time, last_updated)) AS avg_treatment_time
FROM visits
GROUP BY DATE(arrival_time)
ORDER BY visit_date DESC;

-- -------------------------------------------------------
-- SAMPLE DATA
-- -------------------------------------------------------

INSERT INTO `departments` (`dept_name`, `dept_head`, `total_beds`, `available_beds`, `contact_number`) VALUES
('Emergency', 'Dr. Adams', 30, 30, '1001'),
('Cardiology', 'Dr. Blake', 20, 20, '1002'),
('Neurology', 'Dr. Carter', 15, 15, '1003'),
('Orthopedics', 'Dr. Davis', 25, 25, '1004'),
('General Medicine', 'Dr. Evans', 40, 40, '1005');

INSERT INTO `doctors` (`name`, `specialization`, `phone`, `dept_id`, `available_status`, `joined_date`) VALUES
('Alice Smith', 'ER Physician', '555-1111', 1, 'Available', '2020-01-15'),
('Bob Johnson', 'Trauma Surgeon', '555-2222', 1, 'Available', '2019-03-22'),
('Charlie Brown', 'Cardiologist', '555-3333', 2, 'Available', '2018-07-11'),
('Diana Prince', 'Neurologist', '555-4444', 3, 'Available', '2021-09-05'),
('Evan Wright', 'Orthopedic Surgeon', '555-5555', 4, 'Available', '2017-11-30'),
('Fiona Gallagher', 'Physician', '555-6666', 5, 'Available', '2022-02-14'),
('George Martin', 'Cardiac Surgeon', '555-7777', 2, 'Available', '2015-05-20'),
('Hannah Abbott', 'Neurosurgeon', '555-8888', 3, 'Available', '2016-08-18'),
('Ian Somerhalder', 'General Surgeon', '555-9999', 5, 'Available', '2020-10-10'),
('Julia Roberts', 'ER Nurse Practitioner', '555-0000', 1, 'Available', '2023-01-01');

INSERT INTO `patients` (`name`, `age`, `gender`, `phone`, `blood_group`, `address`) VALUES
('John Doe', 45, 'Male', '9876543210', 'O+', '123 Main St'),
('Jane Roe', 32, 'Female', '9876543211', 'A-', '456 Elm St'),
('Mike Ross', 28, 'Male', '9876543212', 'B+', '789 Oak St'),
('Rachel Green', 30, 'Female', '9876543213', 'AB+', '321 Pine St'),
('Harvey Specter', 40, 'Male', '9876543214', 'O-', '654 Maple St'),
('Donna Paulsen', 35, 'Female', '9876543215', 'A+', '987 Cedar St'),
('Louis Litt', 42, 'Male', '9876543216', 'B-', '147 Birch St'),
('Jessica Pearson', 48, 'Female', '9876543217', 'AB-', '258 Ash St'),
('Katrina Bennett', 31, 'Female', '9876543218', 'O+', '369 Walnut St'),
('Alex Williams', 38, 'Male', '9876543219', 'A-', '753 Chestnut St'),
('Samantha Wheeler', 36, 'Female', '9876543220', 'B+', '951 Spruce St'),
('Robert Zane', 55, 'Male', '9876543221', 'AB+', '852 Fir St'),
('Sheila Sazs', 34, 'Female', '9876543222', 'O-', '753 Redwood St'),
('Brian Altman', 29, 'Male', '9876543223', 'A+', '159 Sequoia St'),
('Gretchen Bodinski', 50, 'Female', '9876543224', 'B-', '357 Cypress St');

-- We use the stored procedure to ensure triggers fire properly for sample data
CALL admit_patient(1, 1, 1, 'Severe chest pain', 'Emergency', 'Critical', 'Nurse Joy', 9, 'Needs immediate ECG');
CALL admit_patient(2, 5, 6, 'High fever and cough', 'Checkup', 'Medium', 'Nurse Joy', 4, 'Prescribe paracetamol');
CALL admit_patient(3, 4, 5, 'Broken arm from fall', 'Trauma', 'High', 'Nurse Ratched', 7, 'X-Ray required');
CALL admit_patient(4, 3, 4, 'Severe migraine and blurred vision', 'Consultation', 'Medium', 'Nurse Ratched', 5, 'Neurologist consult needed');
CALL admit_patient(5, 2, 3, 'Heart palpitations', 'Consultation', 'High', 'Nurse Joy', 8, 'Monitor vitals closely');

CALL admit_patient(6, 1, 2, 'Car accident, head trauma', 'Emergency', 'Critical', 'Nurse Joy', 10, 'Prep for surgery');
CALL admit_patient(7, 5, 9, 'Stomach ache', 'Checkup', 'Low', 'Nurse Ratched', 2, 'Wait in lobby');
CALL admit_patient(8, 4, 5, 'Sprained ankle', 'Consultation', 'Low', 'Nurse Ratched', 3, 'Ice pack given');
CALL admit_patient(9, 3, 8, 'Numbness in left arm', 'Consultation', 'High', 'Nurse Joy', 7, 'Urgent scan required');
CALL admit_patient(10, 2, 7, 'Routine heart check', 'Follow-up', 'Low', 'Nurse Joy', 1, 'Patient stable');

-- Discharge some patients to show history
CALL discharge_patient(1, 'Mild Heart Attack', 'Aspirin', 'ECG, Angioplasty', 'Stable now', 'Follow-up Required', 1, '2026-07-01');
CALL discharge_patient(2, 'Viral Infection', 'Antibiotics', 'None', 'Rest for 3 days', 'Recovered', 6, NULL);
CALL discharge_patient(7, 'Food poisoning', 'Antacids', 'None', 'Drink fluids', 'Recovered', 9, NULL);

COMMIT;
