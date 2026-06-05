<?php
// triage/dashboard.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

// Fetch all active visits using the priority_queue VIEW
$query = "SELECT * FROM priority_queue";
$result = mysqli_query($conn, $query);

// Summary counts
$counts = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
$active_visits = [];
while ($row = mysqli_fetch_assoc($result)) {
    $counts[$row['priority_level']]++;
    $active_visits[] = $row;
}
?>

<!-- Auto refresh every 30 seconds -->
<meta http-equiv="refresh" content="30">

<div class="row mb-3 align-items-center">
    <div class="col-md-7">
        <h2 class="mb-0"><i class="bi bi-heart-pulse-fill text-danger"></i> Live Triage Dashboard</h2>
        <p class="text-muted mb-0">Auto-refreshes every 30s. Powered by `priority_queue` SQL View.</p>
    </div>
    <div class="col-md-5 text-end">
        <span class="badge bg-danger fs-5 me-2 shadow-sm">Critical: <?php echo $counts['Critical']; ?></span>
        <span class="badge bg-warning text-dark fs-5 me-2 shadow-sm">High: <?php echo $counts['High']; ?></span>
        <span class="badge bg-info text-dark fs-5 me-2 shadow-sm">Med: <?php echo $counts['Medium']; ?></span>
        <span class="badge bg-success fs-5 shadow-sm">Low: <?php echo $counts['Low']; ?></span>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Priority Level</th>
                        <th>Patient Name</th>
                        <th>Wait Time</th>
                        <th>Symptoms</th>
                        <th>Status</th>
                        <th>Assigned Dept/Doctor</th>
                        <th class="text-center pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($active_visits as $visit): ?>
                    <?php 
                        $rowClass = '';
                        $badgeClass = '';
                        switch($visit['priority_level']) {
                            case 'Critical': $rowClass = 'table-danger'; $badgeClass = 'bg-danger'; break;
                            case 'High': $rowClass = 'table-warning'; $badgeClass = 'bg-warning text-dark'; break;
                            case 'Medium': $rowClass = ''; $badgeClass = 'bg-info text-dark'; break;
                            case 'Low': $rowClass = ''; $badgeClass = 'bg-success'; break;
                        }
                        
                        // Highlight long wait times for critical/high
                        $waitWarning = '';
                        $icon = '<i class="bi bi-clock"></i>';
                        if ($visit['priority_level'] == 'Critical' && $visit['wait_minutes'] >= 10) {
                            $waitWarning = 'text-danger fw-bold fs-5';
                            $icon = '<i class="bi bi-exclamation-octagon-fill text-danger animation-blink"></i>';
                        } elseif ($visit['priority_level'] == 'High' && $visit['wait_minutes'] >= 30) {
                            $waitWarning = 'text-danger fw-bold';
                        }
                    ?>
                    <tr class="<?php echo $rowClass; ?> border-bottom">
                        <td class="ps-3">
                            <span class="badge <?php echo $badgeClass; ?> fs-6 w-100 shadow-sm py-2">
                                <?php echo htmlspecialchars($visit['priority_level']); ?>
                            </span>
                        </td>
                        <td class="fw-bold fs-5"><?php echo htmlspecialchars($visit['patient_name']); ?></td>
                        <td class="<?php echo $waitWarning; ?>">
                            <?php echo $icon; ?> <?php echo $visit['wait_minutes']; ?> mins
                        </td>
                        <td><small><?php echo htmlspecialchars($visit['symptoms']); ?></small></td>
                        <td>
                            <?php if($visit['status'] == 'In Treatment'): ?>
                                <span class="badge bg-primary rounded-pill"><i class="bi bi-bandaid"></i> In Treatment</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill"><i class="bi bi-hourglass-split"></i> Waiting</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($visit['dept_name']); ?><br>
                            <small class="text-muted">Dr. <?php echo htmlspecialchars($visit['doctor_name']); ?></small>
                        </td>
                        <td class="text-center pe-3">
                            <a href="../visits/update_priority.php?id=<?php echo $visit['visit_id']; ?>" class="btn btn-sm btn-outline-dark" title="Re-assess Priority">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($active_visits)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 fs-5 bg-light">
                            <i class="bi bi-check-circle-fill text-success mb-2" style="font-size: 2.5rem;"></i><br>
                            All clear. No active patients waiting in the queue.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @keyframes blink { 50% { opacity: 0.3; } }
    .animation-blink { animation: blink 1s linear infinite; }
</style>

<?php require_once '../includes/footer.php'; ?>
