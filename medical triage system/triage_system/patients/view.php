<?php
// patients/view.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$patient_id = $_GET['id'] ?? 0;

if (!$patient_id) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Invalid Patient ID.</div></div>");
}

// 1. Fetch patient basic details
$stmt_p = $conn->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt_p->bind_param("i", $patient_id);
$stmt_p->execute();
$patient = $stmt_p->get_result()->fetch_assoc();
$stmt_p->close();

if (!$patient) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Patient not found.</div></div>");
}

// 2. Call the get_patient_history stored procedure
$stmt_h = $conn->prepare("CALL get_patient_history(?)");
$stmt_h->bind_param("i", $patient_id);
$stmt_h->execute();
$history_res = $stmt_h->get_result();

$visits = [];
while($row = $history_res->fetch_assoc()){
    $visits[] = $row;
}
$stmt_h->close();
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-file-person text-info"></i> Patient Profile & History</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="list.php" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="../visits/admit.php?patient_id=<?php echo $patient['patient_id']; ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Admission</a>
    </div>
</div>

<div class="row">
    <!-- Patient Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 border-top border-info border-4 h-100">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($patient['name']); ?></h4>
                    <p class="text-muted">ID: #<?php echo $patient['patient_id']; ?></p>
                </div>
                
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Age/Gender</td><td class="fw-bold"><?php echo $patient['age'] . ' / ' . $patient['gender']; ?></td></tr>
                    <tr><td class="text-muted">Blood Group</td><td><span class="badge bg-danger"><?php echo $patient['blood_group'] ?? 'Unknown'; ?></span></td></tr>
                    <tr><td class="text-muted">Phone</td><td class="fw-bold"><?php echo htmlspecialchars($patient['phone']); ?></td></tr>
                    <tr><td class="text-muted">Registered</td><td class="fw-bold"><?php echo date('M d, Y', strtotime($patient['registered_at'])); ?></td></tr>
                    <tr><td class="text-muted" colspan="2">Address:<br><span class="fw-bold"><?php echo nl2br(htmlspecialchars($patient['address'])); ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Full Visit History from Stored Procedure -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-secondary"></i> Medical Timeline (<?php echo count($visits); ?> Visits)</h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="historyAccordion">
                    <?php if(empty($visits)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-folder-x fs-1"></i>
                            <p class="mt-2">No visits recorded for this patient yet.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($visits as $index => $v): ?>
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo ($index !== 0) ? 'collapsed' : ''; ?> bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#visit<?php echo $index; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                    <span>
                                        <strong><?php echo date('M d, Y - H:i', strtotime($v['arrival_time'])); ?></strong>
                                        <span class="ms-2 text-muted">| <?php echo htmlspecialchars($v['dept_name']); ?></span>
                                    </span>
                                    <span>
                                        <?php 
                                            $badgeClass = '';
                                            switch($v['priority_level']) {
                                                case 'Critical': $badgeClass = 'bg-danger'; break;
                                                case 'High': $badgeClass = 'bg-warning text-dark'; break;
                                                case 'Medium': $badgeClass = 'bg-info text-dark'; break;
                                                case 'Low': $badgeClass = 'bg-success'; break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> me-2"><?php echo htmlspecialchars($v['priority_level']); ?></span>
                                        <span class="badge bg-secondary status-<?php echo str_replace(' ','',$v['status']); ?> text-white"><?php echo htmlspecialchars($v['status']); ?></span>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="visit<?php echo $index; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" data-bs-parent="#historyAccordion">
                            <div class="accordion-body">
                                
                                <!-- Tabs for Visit Details -->
                                <ul class="nav nav-tabs mb-3" id="tab-<?php echo $index; ?>" role="tablist">
                                  <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admit-<?php echo $index; ?>" type="button">Admission & Triage</button>
                                  </li>
                                  <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#treat-<?php echo $index; ?>" type="button">Treatment & Discharge</button>
                                  </li>
                                </ul>
                                
                                <div class="tab-content">
                                    <!-- Admission Tab -->
                                    <div class="tab-pane fade show active" id="admit-<?php echo $index; ?>">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong class="text-muted">Doctor:</strong> Dr. <?php echo htmlspecialchars($v['doctor_name']); ?></p>
                                                <p class="mb-1"><strong class="text-muted">Purpose:</strong> <?php echo htmlspecialchars($v['visit_purpose']); ?></p>
                                                <p class="mb-1"><strong class="text-muted">Symptoms:</strong><br><?php echo nl2br(htmlspecialchars($v['symptoms'])); ?></p>
                                            </div>
                                            <div class="col-sm-6 border-start border-light">
                                                <?php if($v['severity_score']): ?>
                                                    <p class="mb-1"><strong class="text-muted">Severity Score:</strong> <span class="badge bg-dark"><?php echo $v['severity_score']; ?>/10</span></p>
                                                    <p class="mb-1"><strong class="text-muted">Assigned Priority:</strong> <?php echo htmlspecialchars($v['priority_assigned']); ?></p>
                                                    <p class="mb-1"><strong class="text-muted">Triage Notes:</strong><br><?php echo nl2br(htmlspecialchars($v['triage_notes'])); ?></p>
                                                <?php else: ?>
                                                    <em class="text-muted">Triage records not fully populated.</em>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Treatment Tab -->
                                    <div class="tab-pane fade" id="treat-<?php echo $index; ?>">
                                        <?php if($v['diagnosis']): ?>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <h6 class="text-primary border-bottom pb-1 mb-2">Treatment Details</h6>
                                                    <p class="mb-1"><strong class="text-muted">Diagnosis:</strong> <?php echo htmlspecialchars($v['diagnosis']); ?></p>
                                                    <p class="mb-1"><strong class="text-muted">Medications:</strong> <?php echo htmlspecialchars($v['medication']); ?></p>
                                                    <p class="mb-1"><strong class="text-muted">Procedures:</strong> <?php echo htmlspecialchars($v['procedure_done']); ?></p>
                                                </div>
                                                <div class="col-sm-6 border-start border-light">
                                                    <h6 class="text-success border-bottom pb-1 mb-2">Discharge Summary</h6>
                                                    <?php if($v['outcome']): ?>
                                                        <p class="mb-1"><strong class="text-muted">Outcome:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars($v['outcome']); ?></span></p>
                                                        <?php if($v['follow_up_date']): ?>
                                                            <p class="mb-1"><strong class="text-muted">Follow-up:</strong> <?php echo date('M d, Y', strtotime($v['follow_up_date'])); ?></p>
                                                        <?php endif; ?>
                                                        <p class="mb-1"><strong class="text-muted">Notes:</strong><br><?php echo nl2br(htmlspecialchars($v['discharge_notes'])); ?></p>
                                                    <?php else: ?>
                                                        <em class="text-muted">No discharge data yet.</em>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-light border m-2 text-center text-muted">
                                                <i class="bi bi-hourglass-split"></i> Treatment has not been recorded or patient is still active.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
