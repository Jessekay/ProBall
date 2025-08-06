    let slideIndex = 1;
    let slideTimer;

    showSlides(slideIndex);
    autoSlide(); // Start the auto sliding

    function plusSlides(n) {
        showSlides(slideIndex += n);
        resetTimer(); // reset timer on manual interaction
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
        resetTimer();
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("slide");
        let dots = document.getElementsByClassName("dot");

        if (n > slides.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = slides.length }

        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }

        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }

        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
    }

    function autoSlide() {
        slideTimer = setInterval(() => {
            plusSlides(1); // Go to the next slide
        }, 3000); // 3000ms = 3 seconds
    }

    function resetTimer() {
        clearInterval(slideTimer);
        autoSlide(); // Restart the timer
    }

    // Session check script (unchanged)
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            const sidebar = document.querySelector('.sidebar-menu');
            if (!data.logged_in) {
                sidebar.querySelector('#logout-btn').remove();
            }
        });

//         fetch('check_session.php').then(response => response.json()).then(data => {
//     const sidebar = document.querySelector('.sidebar-menu');
//     if (!data.logged_in) {
//         sidebar.querySelector('#logout-btn').remove();
//     }
// });