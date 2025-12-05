<?php
include 'db.php';
include 'auth_check.php';

// Only Admin can access users page
if ($user_role != 'Admin') {
    header("Location: dashboard.php");
    exit();
}

// CREATE NEW USER
if (isset($_POST['newusers'])) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Plain password (will be stored as is for demo)
    $role_id = $_POST['role_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, username, password_hash, role_id, is_active) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssii", $full_name, $username, $password, $role_id, $is_active);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'User Created Successfully',
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    window.location.reload();
                });
            });
        </script>";
    } else {
        $error = "Error: Username might already exist!";
    }
    mysqli_stmt_close($stmt);
}

// DELETE USER
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM users WHERE user_id = $id";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: users.php?msg=deleted");
        exit();
    }
}

// UPDATE USER
if (isset($_POST['updateUser'])) {
    $id = $_POST['edit_id'];
    $full_name = trim($_POST['edit_full_name']);
    $username = trim($_POST['edit_username']);
    $role_id = $_POST['edit_role_id'];
    $is_active = isset($_POST['edit_is_active']) ? 1 : 0;
    
    $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, username=?, role_id=?, is_active=? WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, "ssiii", $full_name, $username, $role_id, $is_active, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: users.php?msg=updated");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// CHANGE PASSWORD
if (isset($_POST['changePassword'])) {
    $id = $_POST['pass_id'];
    $new_password = $_POST['new_password'];
    
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash=? WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, "si", $new_password, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: users.php?msg=password_changed");
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
            <!-- <div class="col-md-2 px-0 sidebar"> -->
                <!-- <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="testing_types.php">
                                <i class="bi bi-clipboard-check"></i> Testing Types
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="test_records.php">
                                <i class="bi bi-file-earmark-text"></i> Test Records
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="orders.php">
                                <i class="bi bi-cart"></i> Orders
                            </a>
                        </li>
                        
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="financial_tracking.php">
                                <i class="bi bi-cash-stack"></i> Financial Tracking
                            </a>
                        </li>
                    </ul>
                </div> -->
            <!-- </div> -->
            
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="bi bi-people"></i> Users Management</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-person-plus"></i> Add New User
                        </button>
                    </div>

                    <!-- Display Messages -->
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            $messages = [
                                'deleted' => 'User deleted successfully!',
                                'updated' => 'User updated successfully!',
                                'password_changed' => 'Password changed successfully!'
                            ];
                            echo $messages[$_GET['msg']] ?? 'Action completed successfully!';
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="usersTable" class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Full Name</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query($conn, "
                                            SELECT u.*, r.role_name 
                                            FROM users u 
                                            JOIN roles r ON u.role_id = r.role_id 
                                            ORDER BY u.user_id DESC
                                        ");
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $status_badge = $row['is_active'] == 1 
                                                ? '<span class="badge bg-success">Active</span>' 
                                                : '<span class="badge bg-danger">Inactive</span>';
                                            
                                            echo "<tr>
                                                <td>{$row['user_id']}</td>
                                                <td>{$row['full_name']}</td>
                                                <td>{$row['username']}</td>
                                                <td><span class='badge bg-info'>{$row['role_name']}</span></td>
                                                <td>{$status_badge}</td>
                                                <td>{$row['created_at']}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-primary editBtn'
                                                        data-id='{$row['user_id']}'
                                                        data-full_name='{$row['full_name']}'
                                                        data-username='{$row['username']}'
                                                        data-role_id='{$row['role_id']}'
                                                        data-is_active='{$row['is_active']}'>
                                                        <i class='bi bi-pencil-square'></i>
                                                    </button>
                                                    
                                                    <button class='btn btn-sm btn-outline-warning changePassBtn' 
                                                        data-id='{$row['user_id']}'
                                                        data-name='{$row['full_name']}'>
                                                        <i class='bi bi-key'></i>
                                                    </button>
                                                    
                                                    <button class='btn btn-sm btn-outline-danger deleteBtn' 
                                                        data-id='{$row['user_id']}'
                                                        data-name='{$row['full_name']}'>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="userForm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required 
                                   pattern="^[A-Za-z\s.]+$" title="Only letters, spaces and dots allowed">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="^[a-zA-Z0-9_]+$" title="Only letters, numbers and underscore allowed">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required minlength="3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php
                                $roles = mysqli_query($conn, "SELECT * FROM roles");
                                while ($role = mysqli_fetch_assoc($roles)) {
                                    echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active User</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="newusers" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editUserForm">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="edit_full_name" name="edit_full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" id="edit_username" name="edit_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select class="form-select" id="edit_role_id" name="edit_role_id" required>
                                <?php
                                $roles = mysqli_query($conn, "SELECT * FROM roles");
                                while ($role = mysqli_fetch_assoc($roles)) {
                                    echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="edit_is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Active User</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="updateUser" class="btn btn-warning">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="changePasswordForm">
                    <input type="hidden" name="pass_id" id="pass_id">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="bi bi-key"></i> Change Password</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" class="form-control" name="new_password" required minlength="3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="3">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="changePassword" class="btn btn-info">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "pageLength": 10,
            "order": [[0, 'desc']]
        });
    });

    // DELETE CONFIRMATION
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete user: <b>${name}</b><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'users.php?delete=' + id;
                }
            });
        });
    });

    // EDIT BUTTON POPULATE
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_full_name').value = this.getAttribute('data-full_name');
            document.getElementById('edit_username').value = this.getAttribute('data-username');
            document.getElementById('edit_role_id').value = this.getAttribute('data-role_id');
            document.getElementById('edit_is_active').checked = this.getAttribute('data-is_active') == '1';
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        });
    });

    // CHANGE PASSWORD BUTTON
    document.querySelectorAll('.changePassBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            document.getElementById('pass_id').value = userId;
            
            // Set modal title with user name
            document.querySelector('#changePasswordModal .modal-title').innerHTML = 
                `<i class="bi bi-key"></i> Change Password for ${userName}`;
            
            const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        });
    });

    // VALIDATE CHANGE PASSWORD FORM
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPass = document.querySelector('input[name="new_password"]').value;
        const confirmPass = document.querySelector('input[name="confirm_password"]').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'New password and confirm password do not match!'
            });
        }
        
        if (newPass.length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Too Short',
                text: 'Password must be at least 3 characters long!'
            });
        }
    });

    // VALIDATE ADD USER FORM
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        
        if (password.length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Too Short',
                text: 'Password must be at least 3 characters long!'
            });
        }
    });
    </script>
    
    <?php include 'footer.php'; ?>

    <?php include 'footer.php'; ?>

<!-- ADD THIS PART BELOW FOOTER -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>