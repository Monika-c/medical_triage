<?php
// visits/admit.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$message = '';
$messageType = '';

$pre_patient_id = $_GET['patient_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $dept_id = $_POST['dept_id'];
    $doctor_id = $_POST['doctor_id'];
    $symptoms = $_POST['symptoms'];
    $visit_purpose = $_POST['visit_purpose'];
    $priority = $_POST['priority_level'];
    $assessed_by = $_POST['assessed_by'];
    $severity_score = $_POST['severity_score'];
    $notes = $_POST['notes'];

    // Call stored procedure: admit_patient
    $stmt = $conn->prepare("CALL admit_patient(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissssss", $patient_id, $dept_id, $doctor_id, $symptoms, $visit_purpose, $priority, $assessed_by, $severity_score, $notes);

    if ($stmt->execute()) {
        $message = "Patient admitted successfully! Triage triggers and procedures fired successfully.";
        $messageType = "success";
    } else {
        $message = "Error admitting patient: " . $stmt->error;
        $messageType = "danger";
    }
    $stmt->close();
}

$patients_res = mysqli_query($conn, "SELECT patient_id, name, phone FROM patients ORDER BY name ASC");
$depts_res = mysqli_query($conn, "SELECT dept_id, dept_name FROM departments ORDER BY dept_name ASC");
$doctors_res = mysqli_query($conn, "SELECT doctor_id, name, dept_id FROM doctors WHERE available_status = 'Available' ORDER BY name ASC");

$doctors_by_dept = [];
while ($doc = mysqli_fetch_assoc($doctors_res)) {
    $doctors_by_dept[$doc['dept_id']][] = $doc;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 border-top border-primary border-4">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 text-primary"><i class="bi bi-hospital-fill me-2"></i> Patient Admission & Triage Form</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="admit.php">
                    <h5 class="text-secondary border-bottom pb-2 mb-3">1. Patient Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Registered Patient *</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Search Patient...</option>
                                <?php 
                                    mysqli_data_seek($patients_res, 0);
                                    while ($p = mysqli_fetch_assoc($patients_res)): 
                                        $selected = ($p['patient_id'] == $pre_patient_id) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $p['patient_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($p['name']) . ' (' . htmlspecialchars($p['phone']) . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Patient not listed? <a href="../patients/add.php">Register them first</a>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Visit Purpose *</label>
                            <input type="text" name="visit_purpose" class="form-control" required placeholder="e.g. Emergency, Routine Checkup, Follow-up">
                        </div>
                    </div>

                    <h5 class="text-secondary border-bottom pb-2 mb-3">2. Department & Doctor Assignment</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department *</label>
                            <select name="dept_id" id="deptSelect" class="form-select" required onchange="filterDoctors()">
                                <option value="">Select Department...</option>
                                <?php 
                                    mysqli_data_seek($depts_res, 0);
                                    while ($d = mysqli_fetch_assoc($depts_res)): 
                                ?>
                                    <option value="<?php echo $d['dept_id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned Doctor (Available Only) *</label>
                            <select name="doctor_id" id="doctorSelect" class="form-select border-info" required>
                                <option value="">Select Doctor...</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="text-secondary border-bottom pb-2 mb-3">3. Triage Assessment</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Initial Symptoms *</label>
                        <textarea name="symptoms" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Severity Score (1-10) *</label>
                            <input type="number" name="severity_score" class="form-control bg-light" required min="1" max="10" placeholder="10 = Most Severe">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Assigned Priority Level *</label>
                            <select name="priority_level" class="form-select border-danger" required>
                                <option value="">Assess Priority...</option>
                                <option value="Critical" class="bg-danger text-white">Critical</option>
                                <option value="High" class="bg-warning text-dark">High</option>
                                <option value="Medium" class="bg-info text-dark">Medium</option>
                                <option value="Low" class="bg-success text-white">Low</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Assessed By (Nurse/Admin) *</label>
                            <input type="text" name="assessed_by" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Triage Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional triage observations..."></textarea>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="bi bi-check2-square me-2"></i> Submit Admission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const doctorsData = <?php echo json_encode($doctors_by_dept); ?>;
    function filterDoctors() {
        const deptId = document.getElementById('deptSelect').value;
        const doctorSelect = document.getElementById('doctorSelect');
        doctorSelect.innerHTML = '<option value="">Select Doctor...</option>';
        if (deptId && doctorsData[deptId]) {
            doctorsData[deptId].forEach(function(doc) {
                const option = document.createElement('option');
                option.value = doc.doctor_id;
                option.textContent = "Dr. " + doc.name;
                doctorSelect.appendChild(option);
            });
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
