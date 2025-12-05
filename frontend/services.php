<?php
$page_title = "Services - ElectraLab";
include 'db.php';
include 'header.php';

// Fetch testing types
$query = "SELECT * FROM testing_type WHERE is_modular = 1";
$result = mysqli_query($conn, $query);
?>

<h1 class="mb-4">Our Testing Services</h1>

<div class="row">
    <?php if(mysqli_num_rows($result) > 0): 
        while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['type_name']; ?></h5>
                        <p class="card-text">
                            <strong>Test Code:</strong> <?php echo $row['test_code']; ?><br>
                            <strong>Type:</strong> <?php echo $row['is_modular'] ? 'Modular' : 'Specific'; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endwhile;
    else: ?>
        <div class="col-12">
            <div class="alert alert-info">No testing services available.</div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>