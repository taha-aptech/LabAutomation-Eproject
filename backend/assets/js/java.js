// Global JavaScript Functions

// Auto-hide alerts after 5 seconds
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Close alert on click
    $('.alert').click(function() {
        $(this).fadeOut('slow');
    });
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Password strength checker
function checkPasswordStrength(password) {
    const strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
    const mediumRegex = new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");
    
    if (strongRegex.test(password)) {
        return { strength: 'strong', color: 'success' };
    } else if (mediumRegex.test(password)) {
        return { strength: 'medium', color: 'warning' };
    } else {
        return { strength: 'weak', color: 'danger' };
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Confirm before delete
function confirmDelete(itemName, url) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete ${itemName}. This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Text copied to clipboard',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

// Load image preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Auto-calculate total
function calculateTotal(quantityId, priceId, totalId) {
    const quantity = parseFloat(document.getElementById(quantityId).value) || 0;
    const price = parseFloat(document.getElementById(priceId).value) || 0;
    const total = quantity * price;
    document.getElementById(totalId).value = total.toFixed(2);
}

// Initialize tooltips
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// Initialize popovers
$(function () {
    $('[data-bs-toggle="popover"]').popover();
});