<?php
include 'db.php';
include 'auth_check.php';

// ADD TESTING TYPE
if (isset($_POST['addTestingType'])) {
    $type_name = trim($_POST['type_name']);
    $test_code = trim($_POST['test_code']);
    $is_modular = isset($_POST['is_modular']) ? 1 : 0;
    $parent_type_id = !empty($_POST['parent_type_id']) ? $_POST['parent_type_id'] : NULL;
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO testing_type (type_name, test_code, is_modular, parent_type_id) 
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssii", $type_name, $test_code, $is_modular, $parent_type_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Testing Type Added!',
                timer: 3000
            }).then(() => {
                window.location.reload();
            });
        </script>";
    }
    mysqli_stmt_close($stmt);
}

// DELETE TESTING TYPE
if (isset($_GET['delete'])) {
    $test_type_id = $_GET['delete'];
    $delete_query = "DELETE FROM testing_type WHERE test_type_id = $test_type_id";
    mysqli_query($conn, $delete_query);
    header("Location: testing_types.php?msg=deleted");
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
                        <h3><i class="bi bi-clipboard-check"></i> Testing Types</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestingTypeModal">
                            <i class="bi bi-plus-circle"></i> Add Testing Type
                        </button>
                    </div>

                    <!-- Testing Types Table -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="testingTypesTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Test Code</th>
                                            <th>Type Name</th>
                                            <th>Modular</th>
                                            <th>Parent Type</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "
                                            SELECT t1.*, t2.type_name as parent_name 
                                            FROM testing_type t1
                                            LEFT JOIN testing_type t2 ON t1.parent_type_id = t2.test_type_id
                                            ORDER BY t1.is_modular DESC, t1.type_name
                                        ";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $modular_badge = $row['is_modular'] == 1 
                                                ? '<span class="badge bg-primary">Modular</span>' 
                                                : '<span class="badge bg-secondary">Sub-Modular</span>';
                                            
                                            $parent_name = $row['parent_name'] ?: '-';
                                            
                                            echo "<tr>
                                                <td><span class='badge bg-dark'>{$row['test_code']}</span></td>
                                                <td>{$row['type_name']}</td>
                                                <td>{$modular_badge}</td>
                                                <td>{$parent_name}</td>
                                                <td>{$row['created_at']}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-danger deleteTestingTypeBtn'
                                                        data-id='{$row['test_type_id']}'
                                                        data-name='{$row['type_name']}'>
                                                        <i class='bi bi-trash'></i>
                                                    </button>
                                                </td>
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

    <!-- Add Testing Type Modal -->
    <div class="modal fade" id="addTestingTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Testing Type</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Test Code</label>
                            <input type="text" class="form-control" name="test_code" 
                                   pattern="^[A-Z0-9]+$" title="Uppercase letters and numbers only" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type Name</label>
                            <input type="text" class="form-control" name="type_name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_modular" 
                                   id="is_modular" value="1" checked>
                            <label class="form-check-label" for="is_modular">Modular Test Type</label>
                        </div>
                        <div class="mb-3" id="parentTypeContainer" style="display: none;">
                            <label class="form-label">Parent Type</label>
                            <select class="form-select" name="parent_type_id">
                                <option value="">Select Parent Type</option>
                                <?php
                                $modular_types = mysqli_query($conn, 
                                    "SELECT * FROM testing_type WHERE is_modular = 1");
                                while ($type = mysqli_fetch_assoc($modular_types)) {
                                    echo "<option value='{$type['test_type_id']}'>{$type['type_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="addTestingType" class="btn btn-primary">Add Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#testingTypesTable').DataTable();
    });

    // Show/hide parent type based on modular checkbox
    document.getElementById('is_modular').addEventListener('change', function() {
        const parentContainer = document.getElementById('parentTypeContainer');
        parentContainer.style.display = this.checked ? 'none' : 'block';
    });

    // DELETE TESTING TYPE
    document.querySelectorAll('.deleteTestingTypeBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Delete Testing Type?',
                html: `Are you sure you want to delete <b>${name}</b>?<br>
                      This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'testing_types.php?delete=' + id;
                }
            });
        });
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>