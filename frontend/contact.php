<?php
$page_title = "Contact Us - ElectraLab";
include 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name'], $conn);
    $email = sanitize_input($_POST['email'], $conn);
    $subject = sanitize_input($_POST['subject'], $conn);
    $message = sanitize_input($_POST['message'], $conn);
    
    // In production, send email here
    // mail('info@electralab.com', $subject, $message, "From: $email");
    
    echo '<script>
        Swal.fire({
            title: "Message Sent!",
            text: "Thank you for contacting us. We will respond soon.",
            icon: "success",
            timer: 2000
        });
    </script>';
}
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <h1 class="mb-4">Contact Us</h1>
        
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                    <h5>Address</h5>
                    <p>123 Industrial Area<br>City, Country</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                    <h5>Phone</h5>
                    <p>+92 300 1234567<br>+92 42 1234567</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                    <h5>Email</h5>
                    <p>info@electralab.com<br>support@electralab.com</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="contact.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Your Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Your Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>