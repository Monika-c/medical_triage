<?php
// patients/add.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $address = $_POST['address'] ?? '';

    // Prepared statement
    $stmt = $conn->prepare("INSERT INTO patients (name, age, gender, phone, blood_group, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $name, $age, $gender, $phone, $blood_group, $address);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        $message = "Patient registered successfully! Patient ID: " . $new_id;
        $messageType = "success";
    } else {
        // Handle unique constraint on phone
        if ($conn->errno == 1062) {
            $message = "Error: A patient with this phone number already exists.";
        } else {
            $message = "Error registering patient: " . $stmt->error;
        }
        $messageType = "danger";
    }
    $stmt->close();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i> Register New Patient</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number (Unique) *</label>
                            <input type="text" name="phone" class="form-control" required placeholder="10-digit number">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Age *</label>
                            <input type="number" name="age" class="form-control" required min="0" max="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">Unknown</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Full residential address..."></textarea>
                    </div>

                    <div class="text-end">
                        <button type="reset" class="btn btn-outline-secondary me-2">Clear Form</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Save Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
