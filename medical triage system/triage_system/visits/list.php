<?php
// visits/list.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';

$query = "
    SELECT v.*, p.name as patient_name, d.dept_name, doc.name as doctor_name
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    JOIN departments d ON v.dept_id = d.dept_id
    JOIN doctors doc ON v.doctor_id = doc.doctor_id
    WHERE 1=1
";

if ($status_filter) {
    $query .= " AND v.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}
if ($priority_filter) {
    $query .= " AND v.priority_level = '" . mysqli_real_escape_string($conn, $priority_filter) . "'";
}

$query .= " ORDER BY v.arrival_time DESC";
$result = mysqli_query($conn, $query);
?>

<div class="row mb-3 align-items-center">
    <div class="col-md-4">
        <h2 class="mb-0"><i class="bi bi-card-list text-primary"></i> All Visits</h2>
    </div>
    <div class="col-md-8 text-end">
        <form method="GET" action="list.php" class="d-inline-flex gap-2 bg-white p-2 rounded shadow-sm border">
            <select name="status" class="form-select form-select-sm border-0 bg-light">
                <option value="">All Statuses</option>
                <option value="Waiting" <?php if($status_filter=='Waiting') echo 'selected'; ?>>Waiting</option>
                <option value="In Treatment" <?php if($status_filter=='In Treatment') echo 'selected'; ?>>In Treatment</option>
                <option value="Discharged" <?php if($status_filter=='Discharged') echo 'selected'; ?>>Discharged</option>
                <option value="Transferred" <?php if($status_filter=='Transferred') echo 'selected'; ?>>Transferred</option>
            </select>
            <select name="priority" class="form-select form-select-sm border-0 bg-light">
                <option value="">All Priorities</option>
                <option value="Critical" <?php if($priority_filter=='Critical') echo 'selected'; ?>>Critical</option>
                <option value="High" <?php if($priority_filter=='High') echo 'selected'; ?>>High</option>
                <option value="Medium" <?php if($priority_filter=='Medium') echo 'selected'; ?>>Medium</option>
                <option value="Low" <?php if($priority_filter=='Low') echo 'selected'; ?>>Low</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
            <a href="list.php" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Visit ID</th>
                        <th>Patient Name</th>
                        <th>Department & Doctor</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Arrival Time</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?php echo $row['visit_id']; ?></td>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['dept_name']); ?><br>
                            <small class="text-muted">Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></small>
                        </td>
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
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['priority_level']); ?></span>
                        </td>
                        <td>
                            <?php 
                                $sClass = '';
                                switch($row['status']) {
                                    case 'Discharged': $sClass = 'text-success'; break;
                                    case 'In Treatment': $sClass = 'text-primary'; break;
                                    case 'Waiting': $sClass = 'text-warning'; break;
                                    case 'Transferred': $sClass = 'text-secondary'; break;
                                }
                            ?>
                            <span class="fw-bold <?php echo $sClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                        </td>
                        <td>
                            <?php echo date('Y-m-d', strtotime($row['arrival_time'])); ?><br>
                            <small class="text-muted"><?php echo date('H:i A', strtotime($row['arrival_time'])); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm shadow-sm">
                                <a href="../patients/view.php?id=<?php echo $row['patient_id']; ?>" class="btn btn-outline-info" title="View Patient"><i class="bi bi-eye"></i></a>
                                <?php if($row['status'] !== 'Discharged'): ?>
                                    <a href="update_priority.php?id=<?php echo $row['visit_id']; ?>" class="btn btn-outline-warning text-dark" title="Update Priority"><i class="bi bi-arrow-up-circle"></i></a>
                                    <a href="discharge.php?id=<?php echo $row['visit_id']; ?>" class="btn btn-outline-success" title="Discharge"><i class="bi bi-box-arrow-right"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No visits found matching criteria.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
