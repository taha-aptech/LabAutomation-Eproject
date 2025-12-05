<?php
include 'db.php';
include 'auth_check.php';

// ADD TEST RECORD
if (isset($_POST['addTestRecord'])) {
    $product_id_fk = $_POST['product_id_fk'];
    $test_type_id = $_POST['test_type_id'];
    $test_date = $_POST['test_date'];
    $tester_user_id = $_SESSION['user_id'];
    $test_result = $_POST['test_result'];
    $tester_remarks = $_POST['tester_remarks'];
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO test_records 
        (product_id_fk, test_type_id, test_date, tester_user_id, test_result, tester_remarks) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "sissis", $product_id_fk, $test_type_id, $test_date, 
                          $tester_user_id, $test_result, $tester_remarks);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Test Record Added!',
                text: 'Testing ID generated automatically',
                timer: 3000
            }).then(() => {
                window.location.reload();
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// APPROVE/REJECT TEST
if (isset($_POST['updateApproval'])) {
    $record_id = $_POST['record_id'];
    $approval_status = $_POST['approval_status'];
    $manager_remarks = $_POST['manager_remarks'];
    $validated_by_user_id = $_SESSION['user_id'];
    
    $stmt = mysqli_prepare($conn, "
        UPDATE test_records 
        SET approval_status = ?, 
            manager_remarks = ?, 
            validated_by_user_id = ?, 
            validation_date = NOW() 
        WHERE record_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ssii", $approval_status, $manager_remarks, 
                          $validated_by_user_id, $record_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Also update financial tracking if approved
    if ($approval_status == 'Approved') {
        // Get test record details
        $test_query = mysqli_query($conn, "
            SELECT testing_id, product_id_fk, test_result 
            FROM test_records 
            WHERE record_id = $record_id
        ");
        $test_data = mysqli_fetch_assoc($test_query);
        
        // Calculate random cost (for demo)
        $random_cost = rand(1000, 5000);
        
        // Insert into financial tracking
        $fin_stmt = mysqli_prepare($conn, "
            INSERT INTO financial_tracking 
            (record_id_fk, product_code, testing_id, result, approval_status, 
             tester_name, random_cost, checking_manager) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $tester_name = $_SESSION['full_name'];
        $checking_manager = $_SESSION['user_id'];
        
        mysqli_stmt_bind_param($fin_stmt, "isssssii", $record_id, $test_data['product_id_fk'], 
                              $test_data['testing_id'], $test_data['test_result'], 
                              $approval_status, $tester_name, $random_cost, $checking_manager);
        mysqli_stmt_execute($fin_stmt);
        mysqli_stmt_close($fin_stmt);
    }
    
    header("Location: test_records.php?msg=updated");
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="bi bi-file-earmark-text"></i> Test Records</h3>
                        <?php if (in_array($user_role, ['Manufacturer', 'Admin'])): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
                            <i class="bi bi-plus-circle"></i> Add Test Record
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Test Records Table -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="testRecordsTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Testing ID</th>
                                            <th>Product</th>
                                            <th>Test Type</th>
                                            <th>Test Date</th>
                                            <th>Result</th>
                                            <th>Approval Status</th>
                                            <th>Tester</th>
                                            <th>Remarks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "
                                            SELECT tr.*, p.product_name, tt.type_name, u.full_name as tester_name,
                                                   uv.full_name as validator_name
                                            FROM test_records tr
                                            JOIN products p ON tr.product_id_fk = p.product_code
                                            JOIN testing_type tt ON tr.test_type_id = tt.test_type_id
                                            JOIN users u ON tr.tester_user_id = u.user_id
                                            LEFT JOIN users uv ON tr.validated_by_user_id = uv.user_id
                                            ORDER BY tr.created_at DESC
                                        ";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Result badge
                                            $result_badge = $row['test_result'] == 'Passed' 
                                                ? '<span class="badge bg-success">Passed</span>' 
                                                : '<span class="badge bg-danger">Failed</span>';
                                            
                                            // Approval status badge
                                            $status_badge = '';
                                            switch($row['approval_status']) {
                                                case 'Approved':
                                                    $status_badge = '<span class="badge bg-success">Approved</span>';
                                                    break;
                                                case 'Rejected':
                                                    $status_badge = '<span class="badge bg-danger">Rejected</span>';
                                                    break;
                                                default:
                                                    $status_badge = '<span class="badge bg-warning">Pending</span>';
                                            }
                                            
                                            // Actions based on role
                                            $actions = '';
                                            if (in_array($user_role, ['Admin', 'CPRI']) && $row['approval_status'] == 'Pending') {
                                                $actions = "
                                                    <button class='btn btn-sm btn-outline-success approveBtn'
                                                        data-id='{$row['record_id']}'
                                                        data-testing_id='{$row['testing_id']}'>
                                                        <i class='bi bi-check-circle'></i> Approve
                                                    </button>
                                                    <button class='btn btn-sm btn-outline-danger rejectBtn'
                                                        data-id='{$row['record_id']}'
                                                        data-testing_id='{$row['testing_id']}'>
                                                        <i class='bi bi-x-circle'></i> Reject
                                                    </button>
                                                ";
                                            } else {
                                                $actions = '<span class="text-muted">No action</span>';
                                            }
                                            
                                            echo "<tr>
                                                <td><span class='badge bg-dark'>{$row['testing_id']}</span></td>
                                                <td>{$row['product_name']}</td>
                                                <td>{$row['type_name']}</td>
                                                <td>{$row['test_date']}</td>
                                                <td>{$result_badge}</td>
                                                <td>{$status_badge}</td>
                                                <td>{$row['tester_name']}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-info viewRemarksBtn'
                                                        data-tester='{$row['tester_remarks']}'
                                                        data-manager='{$row['manager_remarks']}'>
                                                        <i class='bi bi-chat-text'></i>
                                                    </button>
                                                </td>
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

    <!-- Add Test Modal -->
    <div class="modal fade" id="addTestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Test Record</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id_fk" required>
                                <option value="">Select Product</option>
                                <?php
                                $products = mysqli_query($conn, "SELECT * FROM products");
                                while ($product = mysqli_fetch_assoc($products)) {
                                    echo "<option value='{$product['product_code']}'>
                                        {$product['product_code']} - {$product['product_name']}
                                    </option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Type</label>
                            <select class="form-select" name="test_type_id" required>
                                <option value="">Select Test Type</option>
                                <?php
                                $tests = mysqli_query($conn, "SELECT * FROM testing_type WHERE is_modular = 1");
                                while ($test = mysqli_fetch_assoc($tests)) {
                                    echo "<option value='{$test['test_type_id']}'>
                                        {$test['test_code']} - {$test['type_name']}
                                    </option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Date</label>
                            <input type="date" class="form-control" name="test_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Result</label>
                            <select class="form-select" name="test_result" required>
                                <option value="Passed">Passed</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tester Remarks</label>
                            <textarea class="form-control" name="tester_remarks" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Testing ID will be auto-generated 
                            (ProductCode + AutoNumber)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="addTestRecord" class="btn btn-primary">Add Test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="record_id" id="approvalRecordId">
                    <input type="hidden" name="approval_status" id="approvalStatus">
                    
                    <div class="modal-header" id="approvalModalHeader">
                        <h5 class="modal-title" id="approvalModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="approvalMessage"></p>
                        <div class="mb-3">
                            <label class="form-label">Manager Remarks</label>
                            <textarea class="form-control" name="manager_remarks" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="updateApproval" class="btn" id="approvalSubmitBtn"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Remarks Modal -->
    <div class="modal fade" id="remarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-chat-text"></i> Remarks</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Tester Remarks:</h6>
                    <p id="testerRemarks" class="border p-2 rounded bg-light"></p>
                    
                    <h6 class="mt-3">Manager Remarks:</h6>
                    <p id="managerRemarks" class="border p-2 rounded bg-light"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#testRecordsTable').DataTable();
    });

    // VIEW REMARKS
    document.querySelectorAll('.viewRemarksBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('testerRemarks').textContent = 
                this.getAttribute('data-tester') || 'No remarks';
            document.getElementById('managerRemarks').textContent = 
                this.getAttribute('data-manager') || 'No remarks';
            
            const modal = new bootstrap.Modal(document.getElementById('remarksModal'));
            modal.show();
        });
    });

    // APPROVE TEST
    document.querySelectorAll('.approveBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const recordId = this.getAttribute('data-id');
            const testingId = this.getAttribute('data-testing_id');
            
            document.getElementById('approvalRecordId').value = recordId;
            document.getElementById('approvalStatus').value = 'Approved';
            document.getElementById('approvalModalTitle').textContent = 'Approve Test Record';
            document.getElementById('approvalMessage').textContent = 
                `Are you sure you want to approve test record ${testingId}?`;
            document.getElementById('approvalSubmitBtn').textContent = 'Approve';
            document.getElementById('approvalSubmitBtn').className = 'btn btn-success';
            document.getElementById('approvalModalHeader').className = 'modal-header bg-success text-white';
            
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
        });
    });

    // REJECT TEST
    document.querySelectorAll('.rejectBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const recordId = this.getAttribute('data-id');
            const testingId = this.getAttribute('data-testing_id');
            
            document.getElementById('approvalRecordId').value = recordId;
            document.getElementById('approvalStatus').value = 'Rejected';
            document.getElementById('approvalModalTitle').textContent = 'Reject Test Record';
            document.getElementById('approvalMessage').textContent = 
                `Are you sure you want to reject test record ${testingId}?`;
            document.getElementById('approvalSubmitBtn').textContent = 'Reject';
            document.getElementById('approvalSubmitBtn').className = 'btn btn-danger';
            document.getElementById('approvalModalHeader').className = 'modal-header bg-danger text-white';
            
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>