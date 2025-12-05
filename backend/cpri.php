<?php
include 'db.php';
include 'auth_check.php';

// Only CPRI can access
if ($user_role != 'CPRI') {
    header("Location: dashboard.php");
    exit();
}

// GET TEST RECORDS FOR CPRI REVIEW
$query = "
    SELECT tr.*, p.product_name, tt.type_name, u.full_name as tester_name
    FROM test_records tr
    JOIN products p ON tr.product_id_fk = p.product_code
    JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
    JOIN users u ON tr.tester_user_id = u.user_id
    WHERE tr.approval_status = 'Pending'
    ORDER BY tr.test_date DESC
";
$pending_tests = mysqli_query($conn, $query);
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
                    <h3><i class="bi bi-shield-check"></i> CPRI Approval Panel</h3>
                    <p class="text-muted">Review and approve test records</p>
                    
                    <!-- Pending Tests -->
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Pending Test Records for Approval</h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($pending_tests) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Testing ID</th>
                                            <th>Product</th>
                                            <th>Test Type</th>
                                            <th>Test Date</th>
                                            <th>Result</th>
                                            <th>Tester</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($test = mysqli_fetch_assoc($pending_tests)): ?>
                                        <?php
                                        $result_badge = $test['test_result'] == 'Passed' 
                                            ? '<span class="badge bg-success">Passed</span>' 
                                            : '<span class="badge bg-danger">Failed</span>';
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-dark"><?php echo $test['testing_id']; ?></span></td>
                                            <td><?php echo $test['product_name']; ?></td>
                                            <td><?php echo $test['type_name']; ?></td>
                                            <td><?php echo $test['test_date']; ?></td>
                                            <td><?php echo $result_badge; ?></td>
                                            <td><?php echo $test['tester_name']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info viewTestRemarksBtn"
                                                    data-remarks="<?php echo htmlspecialchars($test['tester_remarks']); ?>">
                                                    <i class="bi bi-chat-text"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success approveTestBtn"
                                                    data-id="<?php echo $test['record_id']; ?>"
                                                    data-testing_id="<?php echo $test['testing_id']; ?>">
                                                    <i class="bi bi-check-circle"></i> Approve
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger rejectTestBtn"
                                                    data-id="<?php echo $test['record_id']; ?>"
                                                    data-testing_id="<?php echo $test['testing_id']; ?>">
                                                    <i class="bi bi-x-circle"></i> Reject
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> No pending test records for approval.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recently Approved -->
                    <div class="card shadow mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Recently Approved Tests</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $approved_query = mysqli_query($conn, "
                                SELECT tr.*, p.product_name, tt.type_name, u.full_name as tester_name
                                FROM test_records tr
                                JOIN products p ON tr.product_id_fk = p.product_code
                                JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
                                JOIN users u ON tr.tester_user_id = u.user_id
                                WHERE tr.approval_status = 'Approved'
                                ORDER BY tr.validation_date DESC LIMIT 10
                            ");
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Testing ID</th>
                                            <th>Product</th>
                                            <th>Test Type</th>
                                            <th>Result</th>
                                            <th>Approved Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($approved = mysqli_fetch_assoc($approved_query)): ?>
                                        <?php
                                        $result_badge = $approved['test_result'] == 'Passed' 
                                            ? '<span class="badge bg-success">Passed</span>' 
                                            : '<span class="badge bg-danger">Failed</span>';
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-dark"><?php echo $approved['testing_id']; ?></span></td>
                                            <td><?php echo $approved['product_name']; ?></td>
                                            <td><?php echo $approved['type_name']; ?></td>
                                            <td><?php echo $result_badge; ?></td>
                                            <td><?php echo $approved['validation_date']; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Remarks Modal -->
    <div class="modal fade" id="viewTestRemarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Tester Remarks</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="testRemarksContent" class="border p-3 rounded bg-light"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Modal -->
    <div class="modal fade" id="cpriActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="test_records.php">
                    <input type="hidden" name="record_id" id="cpriRecordId">
                    <input type="hidden" name="approval_status" id="cpriStatus">
                    
                    <div class="modal-header" id="cpriModalHeader">
                        <h5 class="modal-title" id="cpriModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="cpriActionMessage"></p>
                        <div class="mb-3">
                            <label class="form-label">CPRI Remarks</label>
                            <textarea class="form-control" name="manager_remarks" rows="3" required 
                                      placeholder="Enter your remarks for this decision..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="updateApproval" class="btn" id="cpriSubmitBtn"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // VIEW TEST REMARKS
    document.querySelectorAll('.viewTestRemarksBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('testRemarksContent').textContent = 
                this.getAttribute('data-remarks') || 'No remarks provided';
            
            const modal = new bootstrap.Modal(document.getElementById('viewTestRemarksModal'));
            modal.show();
        });
    });

    // APPROVE TEST
    document.querySelectorAll('.approveTestBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const recordId = this.getAttribute('data-id');
            const testingId = this.getAttribute('data-testing_id');
            
            document.getElementById('cpriRecordId').value = recordId;
            document.getElementById('cpriStatus').value = 'Approved';
            document.getElementById('cpriModalTitle').textContent = 'Approve Test Record';
            document.getElementById('cpriActionMessage').textContent = 
                `Approve test record ${testingId}? This will also create a financial record.`;
            document.getElementById('cpriSubmitBtn').textContent = 'Approve';
            document.getElementById('cpriSubmitBtn').className = 'btn btn-success';
            document.getElementById('cpriModalHeader').className = 'modal-header bg-success text-white';
            
            const modal = new bootstrap.Modal(document.getElementById('cpriActionModal'));
            modal.show();
        });
    });

    // REJECT TEST
    document.querySelectorAll('.rejectTestBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const recordId = this.getAttribute('data-id');
            const testingId = this.getAttribute('data-testing_id');
            
            document.getElementById('cpriRecordId').value = recordId;
            document.getElementById('cpriStatus').value = 'Rejected';
            document.getElementById('cpriModalTitle').textContent = 'Reject Test Record';
            document.getElementById('cpriActionMessage').textContent = 
                `Reject test record ${testingId}?`;
            document.getElementById('cpriSubmitBtn').textContent = 'Reject';
            document.getElementById('cpriSubmitBtn').className = 'btn btn-danger';
            document.getElementById('cpriModalHeader').className = 'modal-header bg-danger text-white';
            
            const modal = new bootstrap.Modal(document.getElementById('cpriActionModal'));
            modal.show();
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>