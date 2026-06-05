<?php
// index.php
// BCS403 - DBMS Project
require_once 'config/db.php';
require_once 'includes/header.php';

// 6 Summary Queries
$q_patients = "SELECT COUNT(*) as count FROM patients";
$res_patients = mysqli_query($conn, $q_patients);
$total_patients = mysqli_fetch_assoc($res_patients)['count'] ?? 0;

$q_active = "SELECT COUNT(*) as count FROM visits WHERE status IN ('Waiting', 'In Treatment')";
$res_active = mysqli_query($conn, $q_active);
$active_visits = mysqli_fetch_assoc($res_active)['count'] ?? 0;

$q_crit = "SELECT COUNT(*) as count FROM visits WHERE priority_level = 'Critical' AND status IN ('Waiting', 'In Treatment')";
$res_crit = mysqli_query($conn, $q_crit);
$critical_cases = mysqli_fetch_assoc($res_crit)['count'] ?? 0;

$q_beds = "SELECT SUM(available_beds) as count FROM departments";
$res_beds = mysqli_query($conn, $q_beds);
$available_beds = mysqli_fetch_assoc($res_beds)['count'] ?? 0;

$q_doc = "SELECT COUNT(*) as count FROM doctors WHERE available_status = 'Available'";
$res_doc = mysqli_query($conn, $q_doc);
$docs_available = mysqli_fetch_assoc($res_doc)['count'] ?? 0;

$q_disc = "SELECT COUNT(*) as count FROM discharge_records WHERE DATE(discharge_time) = CURDATE()";
$res_disc = mysqli_query($conn, $q_disc);
$discharges_today = mysqli_fetch_assoc($res_disc)['count'] ?? 0;

// Fetch Last 10 visits from the priority_queue VIEW
$q_recent = "SELECT * FROM priority_queue LIMIT 10";
$recent_visits = mysqli_query($conn, $q_recent);
?>

<!-- Auto refresh every 60 seconds -->
<meta http-equiv="refresh" content="60">

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="mb-0 fw-bold">Admin Dashboard</h2>
        <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>. Real-time overview of triage operations.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo $base_url; ?>/visits/admit.php" class="btn btn-primary shadow"><i class="bi bi-plus-circle"></i> Admit New Patient</a>
    </div>
</div>

<!-- 6 Summary Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-primary text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Total Patients</h6>
                <h2 class="fw-bold mb-0"><?php echo $total_patients; ?></h2>
                <i class="bi bi-people card-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-warning text-dark h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Active Visits</h6>
                <h2 class="fw-bold mb-0"><?php echo $active_visits; ?></h2>
                <i class="bi bi-activity card-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-danger text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Critical Cases</h6>
                <h2 class="fw-bold mb-0"><?php echo $critical_cases; ?></h2>
                <i class="bi bi-exclamation-triangle card-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-success text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Available Beds</h6>
                <h2 class="fw-bold mb-0"><?php echo $available_beds; ?></h2>
                <i class="bi bi-hospital card-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-info text-dark h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Doctors Avail</h6>
                <h2 class="fw-bold mb-0"><?php echo $docs_available; ?></h2>
                <i class="bi bi-person-badge card-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card card-stat bg-secondary text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-uppercase fw-light">Discharged (Today)</h6>
                <h2 class="fw-bold mb-0"><?php echo $discharges_today; ?></h2>
                <i class="bi bi-box-arrow-right card-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-list-ol text-primary"></i> Top 10 Active Triage Queue</h5>
                <a href="<?php echo $base_url; ?>/triage/dashboard.php" class="btn btn-sm btn-outline-danger">View Full Triage Board</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Wait Time</th>
                                <th>Patient Name</th>
                                <th>Priority</th>
                                <th>Symptoms</th>
                                <th>Department & Doctor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_visits)): ?>
                            <tr>
                                <td class="fw-bold <?php echo ($row['wait_minutes'] > 30) ? 'text-danger' : ''; ?>">
                                    <?php echo $row['wait_minutes']; ?> mins
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = '';
                                        switch($row['priority_level']) {
                                            case 'Critical': $badgeClass = 'bg-danger'; break;
                                            case 'High': $badgeClass = 'bg-warning text-dark'; break;
                                            case 'Medium': $badgeClass = 'bg-info text-dark'; break;
                                            case 'Low': $badgeClass = 'bg-success'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($row['priority_level']); ?>
                                    </span>
                                </td>
                                <td><small><?php echo htmlspecialchars($row['symptoms']); ?></small></td>
                                <td>
                                    <?php echo htmlspecialchars($row['dept_name']); ?><br>
                                    <small class="text-muted">Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></small>
                                </td>
                                <td class="status-<?php echo str_replace(' ', '', $row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($recent_visits) == 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No active visits currently waiting or in treatment.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
