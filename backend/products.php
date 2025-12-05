<?php
include 'db.php';
include 'auth_check.php';

// ADD NEW PRODUCT
if (isset($_POST['addProduct'])) {
    $product_name = trim($_POST['product_name']);
    $manufacturer_id = $_SESSION['user_id'];
    $revise_number = $_POST['revise_number'];
    $created_by = $_SESSION['user_id'];

    // Image Upload Handling (FIXED)
    $product_img = '';
    if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] == 0) {

        // Ensure upload directory exists
        $uploadPath = __DIR__ . "/../assets/uploads/"; // absolute path
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['product_img']['name']);
        $target_file = $uploadPath . $filename;

        if (move_uploaded_file($_FILES['product_img']['tmp_name'], $target_file)) {
            $product_img = "assets/uploads/" . $filename; // stored relative path for view
        }
    }

    // Insert Data
    $stmt = mysqli_prepare($conn,
        "INSERT INTO products (product_name, manufacturer_id, revise_number, created_by, product_img) 
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "siiss", $product_name, $manufacturer_id, $revise_number, $created_by, $product_img);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Product Added Successfully!',
                    text: 'Product code generated automatically',
                    timer: 2500,
                    showConfirmButton:false
                }).then(()=>{ window.location.reload(); });
              </script>";
    }
    mysqli_stmt_close($stmt);
}

// DELETE PRODUCT
if (isset($_GET['delete'])) {
    $product_code = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE product_code='$product_code'");
    header("Location: products.php?msg=deleted");
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
                    <div class="d-flex justify-content-between mb-4">
                        <h3><i class="bi bi-box"></i> Products Management</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-circle"></i> Add New Product
                        </button>
                    </div>

                    <div class="card shadow">
                        <div class="card-body table-responsive">
                            <table id="productsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Manufacturer</th>
                                        <th>Revise No.</th>
                                        <th>Created By</th>
                                        <th>Image</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, "
                                        SELECT p.*, u.full_name AS manufacturer_name, uc.full_name AS creator_name
                                        FROM products p
                                        JOIN users u ON p.manufacturer_id=u.user_id
                                        LEFT JOIN users uc ON p.created_by=uc.user_id
                                        ORDER BY p.created_at DESC
                                    ");

                                    while($row=mysqli_fetch_assoc($result)){
                                        $img = $row['product_img'] ?
                                            "<img src='../{$row['product_img']}' width='50' height='50' class='rounded'>" :
                                            "<i class='bi bi-image text-muted' style='font-size:50px'></i>";

                                        echo "
                                        <tr>
                                            <td><span class='badge bg-dark'>{$row['product_code']}</span></td>
                                            <td>{$row['product_name']}</td>
                                            <td>{$row['manufacturer_name']}</td>
                                            <td><span class='badge bg-info'>{$row['revise_number']}</span></td>
                                            <td>{$row['creator_name']}</td>
                                            <td>{$img}</td>
                                            <td>{$row['created_at']}</td>
                                            <td>
                                                <button class='btn btn-sm btn-outline-info viewProductBtn'
                                                    data-code='{$row['product_code']}'
                                                    data-name='{$row['product_name']}'
                                                    data-manufacturer='{$row['manufacturer_name']}'
                                                    data-revise='{$row['revise_number']}'
                                                    data-image='{$row['product_img']}'>
                                                    <i class='bi bi-eye'></i>
                                                </button>
                                                <button class='btn btn-sm btn-outline-danger deleteProductBtn'
                                                    data-code='{$row['product_code']}'
                                                    data-name='{$row['product_name']}'>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New Product</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Product Name</label>
                    <input type="text" class="form-control mb-3" name="product_name" required>

                    <label>Revise Number</label>
                    <input type="number" class="form-control mb-3" name="revise_number" value="1" min="1" required>

                    <label>Product Image</label>
                    <input type="file" class="form-control mb-3" name="product_img" accept="image/*">

                    <div class="alert alert-info">
                        Product code auto-generate hoga
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(()=>$("#productsTable").DataTable());

// View Product Modal
document.querySelectorAll(".viewProductBtn").forEach(btn=>{
    btn.onclick=()=>{
        document.getElementById("viewProductCode").textContent = btn.dataset.code;
        document.getElementById("viewProductName").textContent = btn.dataset.name;
        document.getElementById("viewManufacturer").textContent = btn.dataset.manufacturer;
        document.getElementById("viewRevise").textContent = btn.dataset.revise;

        document.getElementById("productImage").innerHTML = 
            btn.dataset.image ?
            `<img src="../${btn.dataset.image}" class="img-fluid rounded" style="max-height:200px">` :
            `<i class="bi bi-image text-muted" style="font-size:100px"></i>`;

        new bootstrap.Modal("#viewProductModal").show();
    }
});

// Delete
document.querySelectorAll(".deleteProductBtn").forEach(btn=>{
    btn.onclick=()=>{
        Swal.fire({
            title:"Delete Product?",
            html:`Remove <b>${btn.dataset.name}</b>?`,
            icon:"warning",
            showCancelButton:true,
            confirmButtonColor:"#d33"
        }).then(res=>{
            if(res.isConfirmed) location.href="products.php?delete="+btn.dataset.code;
        });
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
