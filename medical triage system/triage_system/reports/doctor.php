<?php
// reports/doctor.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

// Fetching from the SQL VIEW doctor_workload
$query = "SELECT * FROM doctor_workload ORDER BY active_patients DESC, dept_name ASC";
$result = mysqli_query($conn, $query);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-person-video2 text-info"></i> Doctor Workload Report</h2>
        <p class="text-muted">Aggregate active patient assignments sourced from the <code>doctor_workload</code> VIEW.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Doctor Info</th>
                                <th>Department</th>
                                <th class="text-center">Total Treated Today</th>
                                <th class="text-center">Currently Active Patients</th>
                                <th>Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <?php 
                                $active = (int)$row['active_patients'];
                                $loadClass = 'bg-success';
                                $loadText = 'Available / Light';
                                
                                if ($row['available_status'] == 'Off Duty') {
                                    $loadClass = 'bg-secondary';
                                    $loadText = 'Off Duty';
                                } elseif ($active >= 4) {
                                    $loadClass = 'bg-danger';
                                    $loadText = 'Overloaded';
                                } elseif ($active >= 2) {
                                    $loadClass = 'bg-warning text-dark';
                                    $loadText = 'Moderate Load';
                                }
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold">Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['specialization']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                                <td class="text-center">
                                    <span class="fs-5 text-secondary"><?php echo $row['patients_today']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-circle p-2 fs-6"><?php echo $active; ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $loadClass; ?> py-2 px-3"><?php echo $loadText; ?></span>
                                    <?php if($row['available_status'] == 'Busy'): ?>
                                        <br><small class="text-danger"><i class="bi bi-x-circle"></i> Busy (No New Admissions)</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($result) == 0): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No doctor data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
