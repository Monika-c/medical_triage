<?php
// visits/update_priority.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$visit_id = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v_id = $_POST['visit_id'];
    $new_priority = $_POST['priority_level'];

    // Update query - TRIGGER 3 will handle logging
    $stmt = $conn->prepare("UPDATE visits SET priority_level = ? WHERE visit_id = ?");
    $stmt->bind_param("si", $new_priority, $v_id);

    if ($stmt->execute()) {
        $message = "Priority updated successfully - logged automatically by trigger!";
        $messageType = "success";
    } else {
        $message = "Error updating priority: " . $stmt->error;
        $messageType = "danger";
    }
    $stmt->close();
    $visit_id = $v_id;
}

$stmt = $conn->prepare("
    SELECT v.*, p.name as patient_name, d.dept_name, doc.name as doctor_name 
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    JOIN departments d ON v.dept_id = d.dept_id
    JOIN doctors doc ON v.doctor_id = doc.doctor_id
    WHERE v.visit_id = ?
");
$stmt->bind_param("i", $visit_id);
$stmt->execute();
$visit = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$visit) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Visit not found.</div></div>");
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-top border-warning border-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Re-Assess Triage Priority</h5>
                <a href="list.php" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="mb-4 p-3 bg-light rounded border border-warning">
                    <strong>Patient:</strong> <span class="text-primary fw-bold"><?php echo htmlspecialchars($visit['patient_name']); ?></span><br>
                    <strong>Current Priority:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($visit['priority_level']); ?></span><br>
                    <strong>Current Status:</strong> <?php echo htmlspecialchars($visit['status']); ?><br>
                    <strong>Symptoms:</strong> <small class="text-muted"><?php echo htmlspecialchars($visit['symptoms']); ?></small>
                </div>

                <form method="POST" action="update_priority.php?id=<?php echo $visit_id; ?>">
                    <input type="hidden" name="visit_id" value="<?php echo $visit_id; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select New Priority Level</label>
                        <select name="priority_level" class="form-select form-select-lg border-warning" required>
                            <option value="Critical" <?php if($visit['priority_level'] == 'Critical') echo 'selected'; ?>>Critical (Immediate)</option>
                            <option value="High" <?php if($visit['priority_level'] == 'High') echo 'selected'; ?>>High (Urgent)</option>
                            <option value="Medium" <?php if($visit['priority_level'] == 'Medium') echo 'selected'; ?>>Medium (Semi-Urgent)</option>
                            <option value="Low" <?php if($visit['priority_level'] == 'Low') echo 'selected'; ?>>Low (Standard)</option>
                        </select>
                        <div class="form-text text-danger mt-2"><i class="bi bi-info-circle"></i> This action fires TRIGGER 3 (`after_priority_change`) which automatically records the change in `triage_logs`.</div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning fw-bold px-4 shadow-sm">Update Priority</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
