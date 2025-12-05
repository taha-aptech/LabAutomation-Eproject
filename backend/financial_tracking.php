<?php
include 'db.php';
include 'auth_check.php';

// Only Admin and CPRI can access
if (!in_array($user_role, ['Admin', 'CPRI'])) {
    header("Location: dashboard.php");
    exit();
}

// APPROVE BY CPRI
if (isset($_POST['approveByCPRI'])) {
    $cost_id = $_POST['cost_id'];
    $approved_by_cpri = $_SESSION['user_id'];
    
    $stmt = mysqli_prepare($conn, "
        UPDATE financial_tracking 
        SET approved_by_cpri = ? 
        WHERE cost_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ii", $approved_by_cpri, $cost_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: financial_tracking.php?msg=approved");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <div class="p-4">
                    <h3><i class="bi bi-cash-stack"></i> Financial Tracking</h3>
                    
                    <!-- Financial Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Revenue</h6>
                                    <?php
                                    $revenue_query = mysqli_query($conn, 
                                        "SELECT SUM(random_cost) as total FROM financial_tracking 
                                         WHERE result = 'Passed' AND approval_status = 'Approved'");
                                    $revenue = mysqli_fetch_assoc($revenue_query)['total'] ?: 0;
                                    ?>
                                    <h4>Rs. <?php echo number_format($revenue, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Pending Approval</h6>
                                    <?php
                                    $pending_query = mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM financial_tracking 
                                         WHERE approved_by_cpri IS NULL");
                                    $pending = mysqli_fetch_assoc($pending_query)['count'];
                                    ?>
                                    <h4><?php echo $pending; ?> Records</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Approved Tests</h6>
                                    <?php
                                    $approved_query = mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM financial_tracking 
                                         WHERE approved_by_cpri IS NOT NULL");
                                    $approved = mysqli_fetch_assoc($approved_query)['count'];
                                    ?>
                                    <h4><?php echo $approved; ?> Tests</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Failed Tests Cost</h6>
                                    <?php
                                    $failed_query = mysqli_query($conn, 
                                        "SELECT SUM(random_cost) as total FROM financial_tracking 
                                         WHERE result = 'Failed'");
                                    $failed = mysqli_fetch_assoc($failed_query)['total'] ?: 0;
                                    ?>
                                    <h4>Rs. <?php echo number_format($failed, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Records Table -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white d-flex justify-content-between">
                            <h5 class="mb-0">Financial Records</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-light" id="exportBtn">
                                    <i class="bi bi-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="financialTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Cost ID</th>
                                            <th>Testing ID</th>
                                            <th>Product</th>
                                            <th>Result</th>
                                            <th>Cost (Rs.)</th>
                                            <th>CPRI Status</th>
                                            <th>Tester</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "
                                            SELECT ft.*, tr.testing_id, p.product_name, u.full_name as tester_name
                                            FROM financial_tracking ft
                                            JOIN test_records tr ON ft.record_id_fk = tr.record_id
                                            JOIN products p ON ft.product_code = p.product_code
                                            JOIN users u ON ft.checking_manager = u.user_id
                                            ORDER BY ft.created_at DESC
                                        ";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Result badge
                                            $result_badge = $row['result'] == 'Passed' 
                                                ? '<span class="badge bg-success">Passed</span>' 
                                                : '<span class="badge bg-danger">Failed</span>';
                                            
                                            // CPRI status
                                            $cpri_status = $row['approved_by_cpri'] 
                                                ? '<span class="badge bg-success">Approved</span>' 
                                                : '<span class="badge bg-warning">Pending</span>';
                                            
                                            // Actions
                                            $actions = '';
                                            if (!$row['approved_by_cpri'] && $user_role == 'CPRI') {
                                                $actions = "
                                                    <button class='btn btn-sm btn-outline-success approveCPRIBtn'
                                                        data-id='{$row['cost_id']}'
                                                        data-testing_id='{$row['testing_id']}'
                                                        data-cost='{$row['random_cost']}'>
                                                        <i class='bi bi-check-circle'></i> Approve
                                                    </button>
                                                ";
                                            } else {
                                                $actions = '<span class="text-muted">No action</span>';
                                            }
                                            
                                            echo "<tr>
                                                <td>{$row['cost_id']}</td>
                                                <td><span class='badge bg-dark'>{$row['testing_id']}</span></td>
                                                <td>{$row['product_name']}</td>
                                                <td>{$result_badge}</td>
                                                <td>Rs. {$row['random_cost']}</td>
                                                <td>{$cpri_status}</td>
                                                <td>{$row['tester_name']}</td>
                                                <td>{$row['created_at']}</td>
                                                <td>{$actions}</td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CPRI Approval Modal -->
    <div class="modal fade" id="cpriApprovalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="cost_id" id="cpriCostId">
                    
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-shield-check"></i> CPRI Approval</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="cpriMessage"></p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            By approving, you confirm that the testing costs are valid and appropriate.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="approveByCPRI" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#financialTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });

    // CPRI APPROVAL
    document.querySelectorAll('.approveCPRIBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const costId = this.getAttribute('data-id');
            const testingId = this.getAttribute('data-testing_id');
            const cost = this.getAttribute('data-cost');
            
            document.getElementById('cpriCostId').value = costId;
            document.getElementById('cpriMessage').innerHTML = `
                Approve financial record for:<br>
                <strong>Testing ID:</strong> ${testingId}<br>
                <strong>Cost:</strong> Rs. ${cost}<br><br>
                Are you sure you want to approve this?
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('cpriApprovalModal'));
            modal.show();
        });
    });

    // EXPORT FUNCTION
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Trigger DataTable export
        $('.dt-buttons .btn').first().click();
    });
    </script>
    
    <!-- DataTables Export Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <?php include 'footer.php'; ?>
</body>
</html>