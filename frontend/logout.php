<?php
session_start();
session_destroy();

echo '<script>
    Swal.fire({
        title: "Logged Out",
        text: "You have been successfully logged out.",
        icon: "success",
        timer: 1500,
        showConfirmButton: false
    }).then(() => {
        window.location.href = "index.php";
    });
</script>';

// Redirect after 2 seconds if JavaScript is disabled
header("refresh:2;url=index.php");
?>