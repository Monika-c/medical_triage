<?php
// reports/department.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

// Fetching from the SQL VIEW department_summary
$query = "SELECT * FROM department_summary ORDER BY active_visits_today DESC";
$result = mysqli_query($conn, $query);

$dept_names = [];
$active_visits = [];
$critical_cases = [];

$table_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $table_data[] = $row;
    $dept_names[] = $row['dept_name'];
    $active_visits[] = (int)$row['active_visits_today'];
    $critical_cases[] = (int)$row['critical_cases'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-bar-chart-fill text-primary"></i> Department Load Report</h2>
        <p class="text-muted">Real-time aggregate data sourced from the <code>department_summary</code> VIEW.</p>
    </div>
</div>

<div class="row mb-4">
    <!-- Chart Column -->
    <div class="col-md-7">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-secondary">Active Patients per Department</h5>
            </div>
            <div class="card-body">
                <canvas id="deptChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Table Column -->
    <div class="col-md-5">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-secondary">Department Statistics</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Active Patients</th>
                            <th>Avg Wait</th>
                            <th>Bed Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($table_data as $row): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['dept_name']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?php echo $row['active_visits_today']; ?></span>
                                <?php if($row['critical_cases'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill" title="Critical Cases"><?php echo $row['critical_cases']; ?> <i class="bi bi-exclamation-circle"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo round($row['avg_wait_time'] ?? 0, 1); ?>m</td>
                            <td>
                                <?php 
                                    $pct = ($row['total_beds'] > 0) ? round(($row['available_beds'] / $row['total_beds']) * 100) : 0;
                                    $barClass = 'bg-success';
                                    if($pct < 50) $barClass = 'bg-warning';
                                    if($pct < 20) $barClass = 'bg-danger';
                                ?>
                                <div class="small mb-1"><?php echo $row['available_beds']; ?> / <?php echo $row['total_beds']; ?> avail</div>
                                <div class="progress" style="height: 6px;">
                                  <div class="progress-bar <?php echo $barClass; ?>" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($table_data)): ?>
                        <tr><td colspan="4" class="text-center py-4">No data available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deptChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dept_names); ?>,
            datasets: [
                {
                    label: 'Critical Cases',
                    data: <?php echo json_encode($critical_cases); ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Other Active Visits',
                    data: <?php echo json_encode(array_map(function($a, $c) { return $a - $c; }, $active_visits, $critical_cases)); ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
