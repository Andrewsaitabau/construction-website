// Background Image Slideshow
document.addEventListener('DOMContentLoaded', function() {
    const backgroundImages = document.querySelectorAll('.background-slideshow img');
    let currentImage = 0;
    
    // Show first image immediately
    backgroundImages[currentImage].classList.add('active');
    
    // Change image every 3 seconds
    setInterval(() => {
        backgroundImages[currentImage].classList.remove('active');
        currentImage = (currentImage + 1) % backgroundImages.length;
        backgroundImages[currentImage].classList.add('active');
    }, 3000);
    
    // Mobile menu toggle (if needed)
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
});