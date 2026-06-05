<?php
// visits/update.php
require_once '../config/db.php';
require_once '../includes/header.php';

$visit_id = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v_id = $_POST['visit_id'];
    $new_priority = $_POST['priority_level'];
    $new_status = $_POST['status'];

    // Update query - triggers will handle logging
    $stmt = $conn->prepare("UPDATE visits SET priority_level = ?, status = ? WHERE visit_id = ?");
    $stmt->bind_param("ssi", $new_priority, $new_status, $v_id);

    if ($stmt->execute()) {
        $message = "Visit updated successfully! Triggers automatically logged the change.";
        $messageType = "success";
    } else {
        $message = "Error updating visit: " . $stmt->error;
        $messageType = "danger";
    }
    $stmt->close();
    $visit_id = $v_id; // Keep viewing the updated record
}

// Fetch visit details
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
    die("<div class='alert alert-danger m-3'>Visit not found or invalid ID.</div>");
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Update Visit Status</h5>
                <a href="list.php" class="btn btn-sm btn-light text-primary">Back to List</a>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="mb-4 p-3 bg-light rounded border">
                    <strong>Patient:</strong> <?php echo htmlspecialchars($visit['patient_name']); ?><br>
                    <strong>Department:</strong> <?php echo htmlspecialchars($visit['dept_name']); ?><br>
                    <strong>Doctor:</strong> <?php echo htmlspecialchars($visit['doctor_name']); ?><br>
                    <strong>Symptoms:</strong> <?php echo htmlspecialchars($visit['symptoms']); ?>
                </div>

                <form method="POST" action="update.php?id=<?php echo $visit_id; ?>">
                    <input type="hidden" name="visit_id" value="<?php echo $visit_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Update Priority Level</label>
                        <select name="priority_level" class="form-select" required>
                            <option value="Critical" <?php if($visit['priority_level'] == 'Critical') echo 'selected'; ?>>Critical</option>
                            <option value="High" <?php if($visit['priority_level'] == 'High') echo 'selected'; ?>>High</option>
                            <option value="Medium" <?php if($visit['priority_level'] == 'Medium') echo 'selected'; ?>>Medium</option>
                            <option value="Low" <?php if($visit['priority_level'] == 'Low') echo 'selected'; ?>>Low</option>
                        </select>
                        <div class="form-text">Changing priority will automatically log an entry in Triage Logs.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Waiting" <?php if($visit['status'] == 'Waiting') echo 'selected'; ?>>Waiting</option>
                            <option value="In Treatment" <?php if($visit['status'] == 'In Treatment') echo 'selected'; ?>>In Treatment</option>
                            <!-- Note: Discharge and Transfer have dedicated pages for more details -->
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
