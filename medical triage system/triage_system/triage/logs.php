<?php
// triage/logs.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$filter_event = $_GET['event_type'] ?? '';

$query = "
    SELECT l.*, p.name as patient_name 
    FROM triage_logs l
    LEFT JOIN patients p ON l.patient_id = p.patient_id
    WHERE 1=1
";

if ($filter_event) {
    $safe_filter = mysqli_real_escape_string($conn, $filter_event);
    $query .= " AND l.event_type = '$safe_filter'";
}

$query .= " ORDER BY l.changed_at DESC LIMIT 200";
$result = mysqli_query($conn, $query);

// Get distinct event types for the dropdown
$event_res = mysqli_query($conn, "SELECT DISTINCT event_type FROM triage_logs ORDER BY event_type");
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h2 class="mb-0"><i class="bi bi-journal-text text-secondary"></i> System Triage Logs</h2>
        <p class="text-muted mb-0">Audit trail of automatic MySQL triggers.</p>
    </div>
    <div class="col-md-5">
        <form method="GET" action="logs.php" class="d-flex float-md-end w-100" style="max-width: 400px;">
            <select name="event_type" class="form-select me-2 border-secondary">
                <option value="">All Trigger Events...</option>
                <?php while($e = mysqli_fetch_assoc($event_res)): ?>
                    <option value="<?php echo htmlspecialchars($e['event_type']); ?>" <?php if($filter_event == $e['event_type']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($e['event_type']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if($filter_event): ?><a href="logs.php" class="btn btn-outline-secondary ms-2">Clear</a><?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Timestamp</th>
                        <th>Trigger Event Type</th>
                        <th>Patient Info</th>
                        <th>State Changes</th>
                        <th>Remarks / Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="text-nowrap">
                            <strong><?php echo date('M d', strtotime($row['changed_at'])); ?></strong><br>
                            <small class="text-muted"><?php echo date('H:i:s', strtotime($row['changed_at'])); ?></small>
                        </td>
                        <td>
                            <?php 
                                $evClass = 'bg-secondary';
                                if(strpos($row['event_type'], 'Priority') !== false) $evClass = 'bg-warning text-dark';
                                if(strpos($row['event_type'], 'Registered') !== false) $evClass = 'bg-primary';
                                if(strpos($row['event_type'], 'Transferred') !== false) $evClass = 'bg-info text-dark';
                            ?>
                            <span class="badge <?php echo $evClass; ?> py-2 px-3"><?php echo htmlspecialchars($row['event_type']); ?></span>
                        </td>
                        <td>
                            <?php if($row['patient_name']): ?>
                                <span class="fw-bold text-primary"><?php echo htmlspecialchars($row['patient_name']); ?></span><br>
                                <small class="text-muted">Visit ID: #<?php echo $row['visit_id']; ?></small>
                            <?php else: ?>
                                <span class="text-muted">Visit ID: #<?php echo $row['visit_id']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['old_status'] || $row['new_status']): ?>
                                <div class="small">
                                    <span class="text-muted">Status:</span> 
                                    <span class="text-danger text-decoration-line-through"><?php echo htmlspecialchars($row['old_status']); ?></span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="text-success fw-bold"><?php echo htmlspecialchars($row['new_status']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($row['old_priority'] || $row['new_priority']): ?>
                                <div class="small mt-1">
                                    <span class="text-muted">Priority:</span> 
                                    <span class="text-danger text-decoration-line-through"><?php echo htmlspecialchars($row['old_priority']); ?></span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="text-success fw-bold"><?php echo htmlspecialchars($row['new_priority']); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['remarks']): ?>
                                <small class="fst-italic text-muted">"<?php echo htmlspecialchars($row['remarks']); ?>"</small><br>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark border border-secondary mt-1"><i class="bi bi-person-gear"></i> <?php echo htmlspecialchars($row['changed_by']); ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No system logs found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
