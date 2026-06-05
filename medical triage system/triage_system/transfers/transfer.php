<?php
// transfers/transfer.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_id = $_POST['visit_id'];
    $new_dept_id = $_POST['new_dept_id'];
    $new_doctor_id = $_POST['new_doctor_id'];
    $reason = $_POST['reason'];

    // Call stored procedure: transfer_patient
    $stmt = $conn->prepare("CALL transfer_patient(?, ?, ?, ?)");
    $stmt->bind_param("iiis", $visit_id, $new_dept_id, $new_doctor_id, $reason);

    if ($stmt->execute()) {
        $message = "Patient successfully transferred. Trigger logs and department bed counts updated automatically.";
        $messageType = "success";
    } else {
        $message = "Error transferring patient: " . $stmt->error;
        $messageType = "danger";
    }
    $stmt->close();
}

$visits_res = mysqli_query($conn, "
    SELECT v.visit_id, p.name, d.dept_name, doc.name as doc_name 
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    JOIN departments d ON v.dept_id = d.dept_id
    JOIN doctors doc ON v.doctor_id = doc.doctor_id
    WHERE v.status IN ('Waiting', 'In Treatment')
    ORDER BY p.name ASC
");

$depts_res = mysqli_query($conn, "SELECT dept_id, dept_name, available_beds FROM departments ORDER BY dept_name ASC");
$doctors_res = mysqli_query($conn, "SELECT doctor_id, name, dept_id FROM doctors WHERE available_status = 'Available' ORDER BY name ASC");

$doctors_by_dept = [];
while ($doc = mysqli_fetch_assoc($doctors_res)) {
    $doctors_by_dept[$doc['dept_id']][] = $doc;
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 border-top border-dark border-4">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0"><i class="bi bi-arrow-left-right text-dark me-2"></i> Internal Patient Transfer</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="transfer.php">
                    <div class="mb-4 bg-light p-3 rounded border">
                        <label class="form-label text-primary fw-bold">Select Active Patient to Transfer *</label>
                        <select name="visit_id" class="form-select border-primary" required>
                            <option value="">Choose Patient...</option>
                            <?php while ($v = mysqli_fetch_assoc($visits_res)): ?>
                                <option value="<?php echo $v['visit_id']; ?>">
                                    <?php echo htmlspecialchars($v['name']) . " - Currently in " . htmlspecialchars($v['dept_name']) . " (Dr. " . htmlspecialchars($v['doc_name']) . ")"; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <h5 class="text-secondary border-bottom pb-2 mb-3">Transfer Destination</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">New Department *</label>
                            <select name="new_dept_id" id="deptSelect" class="form-select" required onchange="filterDoctors()">
                                <option value="">Select New Department...</option>
                                <?php 
                                    mysqli_data_seek($depts_res, 0);
                                    while ($d = mysqli_fetch_assoc($depts_res)): 
                                        $bedWarning = ($d['available_beds'] == 0) ? ' (NO BEDS)' : ' (' . $d['available_beds'] . ' beds)';
                                ?>
                                    <option value="<?php echo $d['dept_id']; ?>"><?php echo htmlspecialchars($d['dept_name']) . $bedWarning; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">New Assigned Doctor *</label>
                            <select name="new_doctor_id" id="doctorSelect" class="form-select" required>
                                <option value="">Select Doctor...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Reason for Transfer *</label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="e.g. Requires cardiology consultation, bed unvailable..."></textarea>
                        <div class="form-text">This reason will be logged automatically into the Triage Logs.</div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-dark btn-lg px-4 shadow-sm" onclick="return confirm('Execute Transfer? This will update bed counts in both departments.');"><i class="bi bi-arrow-right-circle"></i> Execute Transfer</button>
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
