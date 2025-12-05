// Main JavaScript for ElectraLab - PHP/DATABASE CART VERSION

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Search form validation
    const searchForm = document.querySelector('form[action="products.php"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                Swal.fire({
                    title: 'Search Empty',
                    text: 'Please enter a search term',
                    icon: 'warning'
                });
            }
        });
    }
    
    // Quantity input validation for cart page
    document.querySelectorAll('.cart-quantity').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 10) this.value = 10;
        });
    });
    
    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[type="email"]').value;
            const message = this.querySelector('textarea').value;
            
            if (!validateEmail(email)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address',
                    icon: 'error'
                });
            }
            
            if (message.length < 10) {
                e.preventDefault();
                Swal.fire({
                    title: 'Message Too Short',
                    text: 'Please enter a message of at least 10 characters',
                    icon: 'warning'
                });
            }
        });
    }
    
    // Email validation helper
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // SweetAlert for add to cart success (triggered by PHP)
    if (typeof window.addToCartSuccess !== 'undefined' && window.addToCartSuccess) {
        Swal.fire({
            title: 'Added to Cart!',
            text: window.addToCartSuccess,
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'View Cart',
            cancelButtonText: 'Continue Shopping'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cart.php';
            }
        });
    }
    
    // Remove confirmation for cart items
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to remove this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Clear localStorage cart to avoid conflicts
    if (localStorage.getItem('cart')) {
        localStorage.removeItem('cart');
    }
});

// No more updateCartSession function needed!