<?php
// reports/queries_demo.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

// --- QUERY 1: By Visit Time ---
$q1 = "
    SELECT p.name, v.arrival_time, v.priority_level, v.status
    FROM visits v JOIN patients p ON v.patient_id = p.patient_id
    WHERE DATE(v.arrival_time) = CURDATE()
    ORDER BY v.arrival_time ASC
";
$r1 = mysqli_query($conn, $q1);

// --- QUERY 2: Critical Cases ---
$q2 = "
    SELECT p.name, v.symptoms, v.priority_level, d.name as doctor, dept.dept_name
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    JOIN doctors d ON v.doctor_id = d.doctor_id
    JOIN departments dept ON v.dept_id = dept.dept_id
    WHERE v.priority_level = 'Critical' AND v.status != 'Discharged'
    ORDER BY v.arrival_time
";
$r2 = mysqli_query($conn, $q2);

// --- QUERY 3: Wait Time Report ---
$q3 = "
    SELECT p.name, v.priority_level, TIMESTAMPDIFF(MINUTE, v.arrival_time, NOW()) as waiting_minutes, dept.dept_name
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    JOIN departments dept ON v.dept_id = dept.dept_id
    WHERE v.status = 'Waiting'
    ORDER BY FIELD(v.priority_level,'Critical','High','Medium','Low')
";
$r3 = mysqli_query($conn, $q3);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-code-slash text-primary"></i> SQL Queries Demonstration</h2>
        <p class="text-muted">This page explicitly demonstrates the complex SQL JOIN queries required for the BCS403 DBMS Project.</p>
    </div>
</div>

<!-- Query 1 -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white py-3">
        <h5 class="mb-0">Query 1: Today's Visits ordered by Arrival Time</h5>
    </div>
    <div class="card-body bg-light">
        <code>SELECT p.name, v.arrival_time, v.priority_level, v.status FROM visits v JOIN patients p ON v.patient_id = p.patient_id WHERE DATE(v.arrival_time) = CURDATE() ORDER BY v.arrival_time ASC;</code>
    </div>
    <table class="table table-bordered mb-0">
        <thead class="table-light"><tr><th>Patient</th><th>Arrival</th><th>Priority</th><th>Status</th></tr></thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($r1)): ?>
                <tr><td><?php echo $row['name']; ?></td><td><?php echo $row['arrival_time']; ?></td><td><?php echo $row['priority_level']; ?></td><td><?php echo $row['status']; ?></td></tr>
            <?php endwhile; ?>
            <?php if(mysqli_num_rows($r1) == 0) echo '<tr><td colspan="4" class="text-center">No data</td></tr>'; ?>
        </tbody>
    </table>
</div>

<!-- Query 2 -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-danger text-white py-3">
        <h5 class="mb-0">Query 2: Active Critical Cases Across Departments</h5>
    </div>
    <div class="card-body bg-light">
        <code>SELECT p.name, v.symptoms, v.priority_level, d.name as doctor, dept.dept_name FROM visits v JOIN patients p ON v.patient_id = p.patient_id JOIN doctors d ON v.doctor_id = d.doctor_id JOIN departments dept ON v.dept_id = dept.dept_id WHERE v.priority_level = 'Critical' AND v.status != 'Discharged' ORDER BY v.arrival_time;</code>
    </div>
    <table class="table table-bordered mb-0">
        <thead class="table-light"><tr><th>Patient</th><th>Symptoms</th><th>Doctor</th><th>Department</th></tr></thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($r2)): ?>
                <tr><td><?php echo $row['name']; ?></td><td><?php echo $row['symptoms']; ?></td><td><?php echo $row['doctor']; ?></td><td><?php echo $row['dept_name']; ?></td></tr>
            <?php endwhile; ?>
            <?php if(mysqli_num_rows($r2) == 0) echo '<tr><td colspan="4" class="text-center">No data</td></tr>'; ?>
        </tbody>
    </table>
</div>

<!-- Query 3 -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-warning text-dark py-3">
        <h5 class="mb-0">Query 3: Triage Wait Time Analysis using TIMESTAMPDIFF()</h5>
    </div>
    <div class="card-body bg-light">
        <code>SELECT p.name, v.priority_level, TIMESTAMPDIFF(MINUTE, v.arrival_time, NOW()) as waiting_minutes, dept.dept_name FROM visits v JOIN patients p ON v.patient_id = p.patient_id JOIN departments dept ON v.dept_id = dept.dept_id WHERE v.status = 'Waiting' ORDER BY FIELD(v.priority_level,'Critical','High','Medium','Low');</code>
    </div>
    <table class="table table-bordered mb-0">
        <thead class="table-light"><tr><th>Patient</th><th>Priority</th><th>Wait Time (Mins)</th><th>Department</th></tr></thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($r3)): ?>
                <tr><td><?php echo $row['name']; ?></td><td><?php echo $row['priority_level']; ?></td><td class="fw-bold"><?php echo $row['waiting_minutes']; ?></td><td><?php echo $row['dept_name']; ?></td></tr>
            <?php endwhile; ?>
            <?php if(mysqli_num_rows($r3) == 0) echo '<tr><td colspan="4" class="text-center">No data</td></tr>'; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
