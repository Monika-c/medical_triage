<?php
// reports/patient_history.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$search = $_GET['search'] ?? '';
$patient_data = null;
$visits_data = [];

if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $q_patient = "SELECT * FROM patients WHERE name LIKE '%$safe_search%' OR phone = '$safe_search' LIMIT 1";
    $p_res = mysqli_query($conn, $q_patient);
    
    if (mysqli_num_rows($p_res) > 0) {
        $patient_data = mysqli_fetch_assoc($p_res);
        $pid = $patient_data['patient_id'];
        
        // Execute Stored Procedure
        $stmt_h = $conn->prepare("CALL get_patient_history(?)");
        $stmt_h->bind_param("i", $pid);
        $stmt_h->execute();
        $v_res = $stmt_h->get_result();
        
        while($row = $v_res->fetch_assoc()){
            $visits_data[] = $row;
        }
        $stmt_h->close();
    }
}
?>

<div class="row justify-content-center mb-4">
    <div class="col-md-8 text-center">
        <h2 class="mb-3"><i class="bi bi-search"></i> Patient History Search</h2>
        <p class="text-muted">Utilizes the <code>get_patient_history</code> stored procedure to fetch complex joined data.</p>
        
        <form method="GET" action="patient_history.php" class="d-flex justify-content-center mx-auto shadow-sm" style="max-width: 500px;">
            <input type="text" name="search" class="form-control form-control-lg me-2 border-primary" placeholder="Enter Patient Name or Phone" value="<?php echo htmlspecialchars($search); ?>" required>
            <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<?php if ($search && !$patient_data): ?>
    <div class="alert alert-warning text-center mx-auto shadow-sm" style="max-width: 600px;">
        <i class="bi bi-exclamation-triangle"></i> No patient found matching "<strong><?php echo htmlspecialchars($search); ?></strong>".
    </div>
<?php endif; ?>

<?php if ($patient_data): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0 border-top border-info border-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="bi bi-person-badge"></i> Profile Found</h5>
                <a href="../patients/view.php?id=<?php echo $patient_data['patient_id']; ?>" class="btn btn-sm btn-outline-info">View Full Dashboard</a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase">Name</small>
                        <strong class="fs-5"><?php echo htmlspecialchars($patient_data['name']); ?></strong>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase">ID & Age</small>
                        <strong class="fs-5">#<?php echo $patient_data['patient_id']; ?> | <?php echo $patient_data['age']; ?>y</strong>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block text-uppercase">Phone</small>
                        <strong class="fs-5"><?php echo htmlspecialchars($patient_data['phone']); ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block text-uppercase">Total Visits</small>
                        <span class="badge bg-primary rounded-pill fs-5"><?php echo count($visits_data); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3 text-secondary border-bottom pb-2">Medical Timeline</h4>

<div class="timeline ps-3 border-start border-3 border-secondary ms-3">
    <?php foreach($visits_data as $visit): ?>
    <div class="card shadow-sm mb-4 position-relative border-0">
        <div class="position-absolute top-0 start-0 translate-middle bg-secondary rounded-circle" style="width: 15px; height: 15px; margin-left: -16px; margin-top: 25px;"></div>
        
        <div class="card-header bg-light d-flex justify-content-between">
            <strong><?php echo date('F d, Y - H:i A', strtotime($visit['arrival_time'])); ?></strong>
            <span class="badge bg-secondary"><?php echo htmlspecialchars($visit['status']); ?></span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary border-bottom pb-1">Triage Phase</h6>
                    <p class="mb-1"><strong class="text-muted">Dept:</strong> <?php echo htmlspecialchars($visit['dept_name']); ?></p>
                    <p class="mb-1"><strong class="text-muted">Doctor:</strong> Dr. <?php echo htmlspecialchars($visit['doctor_name']); ?></p>
                    <p class="mb-1"><strong class="text-muted">Priority:</strong> <?php echo htmlspecialchars($visit['priority_assigned'] ?? $visit['priority_level']); ?></p>
                    <p class="mb-1"><strong class="text-muted">Symptoms:</strong> <?php echo htmlspecialchars($visit['symptoms']); ?></p>
                </div>
                <div class="col-md-4 border-start">
                    <h6 class="text-info border-bottom pb-1">Treatment Phase</h6>
                    <?php if($visit['diagnosis']): ?>
                        <p class="mb-1"><strong class="text-muted">Diagnosis:</strong> <?php echo htmlspecialchars($visit['diagnosis']); ?></p>
                        <p class="mb-1"><strong class="text-muted">Meds:</strong> <?php echo htmlspecialchars($visit['medication']); ?></p>
                        <p class="mb-1"><strong class="text-muted">Procedure:</strong> <?php echo htmlspecialchars($visit['procedure_done']); ?></p>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Treatment pending or not recorded.</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 border-start">
                    <h6 class="text-success border-bottom pb-1">Discharge Phase</h6>
                    <?php if($visit['outcome']): ?>
                        <p class="mb-1"><strong class="text-muted">Outcome:</strong> <?php echo htmlspecialchars($visit['outcome']); ?></p>
                        <p class="mb-1"><strong class="text-muted">Notes:</strong> <?php echo htmlspecialchars($visit['discharge_notes']); ?></p>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Not discharged yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
