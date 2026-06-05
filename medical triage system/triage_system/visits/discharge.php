<?php
// visits/discharge.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$visit_id = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v_id = $_POST['visit_id'];
    $doctor_id = $_POST['doctor_id'];
    $diagnosis = $_POST['diagnosis'];
    $medication = $_POST['medication'];
    $procedure_done = $_POST['procedure_done'];
    $discharge_notes = $_POST['discharge_notes'];
    $outcome = $_POST['outcome'];
    $follow_up_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : NULL;

    // Call stored procedure: discharge_patient
    $stmt = $conn->prepare("CALL discharge_patient(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssis", $v_id, $diagnosis, $medication, $procedure_done, $discharge_notes, $outcome, $doctor_id, $follow_up_date);

    if ($stmt->execute()) {
        $message = "Patient discharged successfully! Procedures and triggers (Trigger 4) fired perfectly.";
        $messageType = "success";
        $visit_id = 0; 
    } else {
        $message = "Error discharging patient: " . $stmt->error;
        $messageType = "danger";
    }
    $stmt->close();
}

if ($visit_id > 0) {
    $stmt = $conn->prepare("
        SELECT v.*, p.name as patient_name, d.dept_name, doc.name as doctor_name 
        FROM visits v
        JOIN patients p ON v.patient_id = p.patient_id
        JOIN departments d ON v.dept_id = d.dept_id
        JOIN doctors doc ON v.doctor_id = doc.doctor_id
        WHERE v.visit_id = ? AND v.status != 'Discharged'
    ");
    $stmt->bind_param("i", $visit_id);
    $stmt->execute();
    $visit = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$visit) {
        $message = "Visit not found, already discharged, or invalid ID.";
        $messageType = "danger";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 border-top border-success border-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0 text-success"><i class="bi bi-box-arrow-right me-2"></i> Patient Discharge Form</h4>
                <a href="list.php" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> p-4 text-center">
                        <h5><?php echo htmlspecialchars($message); ?></h5>
                        <?php if($messageType == 'success') echo '<a href="list.php" class="btn btn-success mt-3"><i class="bi bi-list"></i> Return to Visits List</a>'; ?>
                    </div>
                <?php endif; ?>

                <?php if ($visit_id > 0 && isset($visit)): ?>
                <div class="mb-4 p-3 bg-light rounded border border-success">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Patient:</strong> <span class="text-primary fw-bold"><?php echo htmlspecialchars($visit['patient_name']); ?></span><br>
                            <strong>Department:</strong> <?php echo htmlspecialchars($visit['dept_name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Treating Doctor:</strong> Dr. <?php echo htmlspecialchars($visit['doctor_name']); ?><br>
                            <strong>Arrival Time:</strong> <?php echo date('M d, Y H:i', strtotime($visit['arrival_time'])); ?>
                        </div>
                    </div>
                </div>

                <form method="POST" action="discharge.php?id=<?php echo $visit_id; ?>">
                    <input type="hidden" name="visit_id" value="<?php echo $visit_id; ?>">
                    <input type="hidden" name="doctor_id" value="<?php echo $visit['doctor_id']; ?>">
                    
                    <h6 class="text-secondary border-bottom pb-1 mb-3">Medical Treatment Summary</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Final Diagnosis *</label>
                        <input type="text" name="diagnosis" class="form-control" required placeholder="e.g., Viral Infection, Fracture">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Medication Prescribed</label>
                            <textarea name="medication" class="form-control" rows="2" placeholder="List medications..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Procedures Performed</label>
                            <textarea name="procedure_done" class="form-control" rows="2" placeholder="e.g. X-Ray, Blood Test..."></textarea>
                        </div>
                    </div>

                    <h6 class="text-secondary border-bottom pb-1 mb-3 mt-4">Discharge Record</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Discharge Notes *</label>
                        <textarea name="discharge_notes" class="form-control" rows="3" required placeholder="General advice, diet plans, etc."></textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Outcome *</label>
                            <select name="outcome" class="form-select border-success" required>
                                <option value="">Select Outcome...</option>
                                <option value="Recovered">Recovered</option>
                                <option value="Follow-up Required">Follow-up Required</option>
                                <option value="Referred">Referred</option>
                                <option value="Deceased">Deceased</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Follow-up Date (If applicable)</label>
                            <input type="date" name="follow_up_date" class="form-control">
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm" onclick="return confirm('Confirm Discharge? This will free the assigned bed and mark the doctor as Available.');">
                            <i class="bi bi-check2-circle"></i> Finalize Discharge
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
