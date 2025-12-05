<?php
include 'db.php';
include 'auth_check.php';

if ($user_role != 'Admin') {
    header("Location: dashboard.php");
    exit();
}

if(!isset($_GET['id'])) die("Invalid Request");

$id = $_GET['id'];
$query = mysqli_query($conn,"SELECT * FROM users WHERE user_id=$id");
$user = mysqli_fetch_assoc($query);

// ----------- Update User -----------
if(isset($_POST['updateUser'])){
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $role_id  = $_POST['role_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $update = mysqli_query($conn,
    "UPDATE users SET full_name='$full_name',username='$username',role_id='$role_id',is_active='$is_active' WHERE user_id=$id");

    if($update){
        $msg = "User updated successfully!";
        // redirect removed so modal stays open
    }
}
?>

<!DOCTYPE html>
<html>
<head><?php include 'header.php';?></head>
<body>

<?php if(isset($msg)){ ?>
<div class="alert alert-success text-center m-3"><?=$msg?></div>
<?php } ?>

<script>
window.onload = function(){
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit User</h5>
                </div>

                <div class="modal-body">

                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" 
                    value="<?=$user['full_name']?>" required>

                    <label>Username</label>
                    <input type="text" name="username" class="form-control" 
                    value="<?=$user['username']?>" required>

                    <label>Role</label>
                    <select class="form-select" name="role_id" required>
                        <?php
                        $roles = mysqli_query($conn,"SELECT * FROM roles");
                        while($r=mysqli_fetch_assoc($roles)){
                            $sel = ($user['role_id']==$r['role_id'])?"selected":"";
                            echo "<option value='{$r['role_id']}' $sel>{$r['role_name']}</option>";
                        }
                        ?>
                    </select>

                    <label class="mt-2">
                        <input type="checkbox" name="is_active" <?=$user['is_active']==1?'checked':''?>> Active
                    </label>

                </div>

                <div class="modal-footer">
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                    <button name="updateUser" class="btn btn-warning">Update</button>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
