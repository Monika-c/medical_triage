<?php
// reports/daily.php
// BCS403 - DBMS Project
require_once '../config/db.php';
require_once '../includes/header.php';

// Fetching from the SQL VIEW daily_report
$query = "SELECT * FROM daily_report ORDER BY visit_date ASC";
$result = mysqli_query($conn, $query);

$dates = [];
$total_visits = [];
$critical = [];
$discharged = [];
$table_data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $table_data[] = $row;
    $dates[] = date('M d', strtotime($row['visit_date']));
    $total_visits[] = (int)$row['total_visits'];
    $critical[] = (int)$row['critical'];
    $discharged[] = (int)$row['discharged'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-calendar3 text-success"></i> Daily Activity Report</h2>
        <p class="text-muted">Aggregate daily trends sourced from the <code>daily_report</code> VIEW.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-secondary">Visit Trends (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover table-striped align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start">Date</th>
                            <th>Total Visits</th>
                            <th class="text-danger">Critical Cases</th>
                            <th class="text-warning">High Priority</th>
                            <th class="text-success">Discharged Successfully</th>
                            <th>Avg Treatment Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Reverse the array to show newest first in the table -->
                        <?php foreach(array_reverse($table_data) as $row): ?>
                        <tr>
                            <td class="text-start fw-bold"><?php echo date('F d, Y', strtotime($row['visit_date'])); ?></td>
                            <td><span class="badge bg-primary fs-6"><?php echo $row['total_visits']; ?></span></td>
                            <td class="text-danger fw-bold"><?php echo $row['critical']; ?></td>
                            <td class="text-warning fw-bold"><?php echo $row['high']; ?></td>
                            <td class="text-success fw-bold"><?php echo $row['discharged']; ?></td>
                            <td><?php echo round($row['avg_treatment_time'] ?? 0); ?> mins</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($table_data)): ?>
                        <tr><td colspan="6" class="py-4 text-muted">No daily data available.</td></tr>
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
    const ctx = document.getElementById('dailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Total Visits',
                    data: <?php echo json_encode($total_visits); ?>,
                    borderColor: 'rgba(13, 110, 253, 1)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Critical Cases',
                    data: <?php echo json_encode($critical); ?>,
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.3
                },
                {
                    label: 'Discharged',
                    data: <?php echo json_encode($discharged); ?>,
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
