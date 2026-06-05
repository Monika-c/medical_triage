<?php
// patients/list.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$search = $_GET['search'] ?? '';

// Using LIKE query for search as required
$query = "SELECT * FROM patients";
if ($search) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $query .= " WHERE name LIKE '%$safe_search%' OR phone LIKE '%$safe_search%'";
}
$query .= " ORDER BY registered_at DESC";

$result = mysqli_query($conn, $query);
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-people text-primary"></i> Patient Directory</h2>
    </div>
    <div class="col-md-6">
        <form method="GET" action="list.php" class="d-flex float-md-end w-100" style="max-width: 400px;">
            <input type="text" name="search" class="form-control me-2 border-primary" placeholder="Search name or phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if($search): ?>
                <a href="list.php" class="btn btn-outline-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age / Gender</th>
                        <th>Contact</th>
                        <th>Blood Group</th>
                        <th>Registered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?php echo $row['patient_id']; ?></td>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['age']; ?> / <?php echo substr($row['gender'], 0, 1); ?></td>
                        <td><i class="bi bi-telephone text-muted"></i> <?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <?php if($row['blood_group']): ?>
                                <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($row['blood_group']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['registered_at'])); ?></td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="view.php?id=<?php echo $row['patient_id']; ?>" class="btn btn-sm btn-info text-white" title="View Full History">
                                    <i class="bi bi-file-earmark-medical"></i> History
                                </a>
                                <a href="../visits/admit.php?patient_id=<?php echo $row['patient_id']; ?>" class="btn btn-sm btn-primary" title="Admit Patient">
                                    <i class="bi bi-plus-circle"></i> Admit
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No patients found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
