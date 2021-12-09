document.addEventListener("DOMContentLoaded", function() {

    /* render the menu fixed top when the user scroll page */
    const height = window.innerHeight;
    const vhPixels = height * 0.9;

    window.addEventListener('scroll', function() {
        if (window.scrollY > vhPixels) {
            document.getElementById('myNavbar').classList.add('fixed-top');
        } else {
            document.getElementById('myNavbar').classList.remove('fixed-top');
        }
    });


    /* Auto close the menu on click outside the menu or internal link */
    const menuColapsed = document.getElementById("navbarCollapse");
    document.addEventListener("click", closeMenu);

    function closeMenu() {
        if (menuColapsed.classList.contains("show")) {
            menuColapsed.classList.remove("show");
        }
    }


    /* Lazy Load for optimize the charging time */
    const lazyImages = [].slice.call(document.querySelectorAll("img.lazyLoad"));

    if ("IntersectionObserver" in window && "IntersectionObserverEntry" in window && "intersectionRatio" in window.IntersectionObserverEntry.prototype) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.replace("lazyLoad", "loaded");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }

    /*StepByStep, Slideshow automatic and manual with play and pause*/
    let slideIndex = 0;
    let slides = document.getElementsByClassName("grid-container");
    let dots = document.getElementsByClassName("dot");
    let myTimer;
    let playing = true;

    showSlides();

    function showSlides() {
        if (playing) {
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1;
            }
            dots[slideIndex - 1].className += " active";
            slides[slideIndex - 1].style.display = "grid";
            myTimer = setTimeout(showSlides, 10000); // Change every 10 seconds
        } else {
            clearTimeout(myTimer);
            return;
        }
    }

    const dotOne = document.querySelector("#dotOne");
    dotOne.addEventListener("click", () => currentSlide(1));
    const dotTwo = document.querySelector("#dotTwo");
    dotTwo.addEventListener("click", () => currentSlide(2));
    const dotTree = document.querySelector("#dotTree");
    dotTree.addEventListener("click", () => currentSlide(3));


    function currentSlide(no) {
        console.log(no)
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slideIndex = no;
        slides[no - 1].style.display = "grid";
        dots[no - 1].className += " active";
    }

    const prev = document.querySelector('#prev');
    prev.addEventListener("click", () => manualControl(-1));
    const next = document.querySelector('#next');
    next.addEventListener("click", () => manualControl(1));

    function manualControl(IncrementNumber) {
        var newslideIndex = slideIndex + IncrementNumber;
        if (newslideIndex < 4 && newslideIndex > 0) {
            currentSlide(newslideIndex);
        }
        if (newslideIndex == 4) {
            currentSlide(1);
        }
        if (newslideIndex == 0) {
            currentSlide(3);
        }

    }

    let pauseButton = document.getElementById("pause");
    pauseButton.addEventListener("click", function() {
        if (playing) {
            pauseSlideshow();
        } else {
            playSlideshow();
        }
    });

    function pauseSlideshow() {
        pauseButton.innerHTML = "Reprendre la lecture";
        playing = false;
    }

    function playSlideshow() {
        pauseButton.innerHTML = "Mettre le slider sur pause";
        playing = true;
        clearTimeout(myTimer);
        showSlides();
    }

    /* contact form script */

    /* On select contact mode */
    const buttonByPhone = document.querySelector('#byPhone');
    buttonByPhone.addEventListener('click', selectContactMode);
    const buttonByMail = document.querySelector('#byMail');
    buttonByMail.addEventListener('click', selectContactMode);

    function selectContactMode(event) {
        const contactModeChooseByUser = event.path[0].id;
        if (contactModeChooseByUser === "byMail") {
            document.querySelector('#message').required = true;
            document.querySelector('#email').required = true;
            document.querySelector('#divInputDate').style.display = 'none';
            document.querySelector('#divInputHour').style.display = 'none';
            document.getElementById('chooseContactModeResult').value = 'byMail';
        }
        if (contactModeChooseByUser === "byPhone") {
            document.querySelector('#phone').required = true;
            document.querySelector('#date').required = true;
            document.querySelector('#hour').required = true;
            document.querySelector('#divInputMessage').style.display = 'none';
            document.getElementById('chooseContactModeResult').value = 'byPhone';
        }
        document.querySelector('#chooseContactMode').classList.add('displayNone');
        form.classList.remove('displayNone');
        form.classList.add('opacityAnim');
        document.getElementById('prenom').focus();
        buttonSend.style.display = 'flex';
    }

    /* On date change */
    const validate = dateString => {
        const DateSelected = (new Date(dateString)).getTime();
        const dayOfWeekSelected = (new Date(dateString)).getDay();
        const today = (new Date()).getTime();
        const invalidDate = document.querySelector('#invalidDate');
        const validDate = document.querySelector('#validDate');

        if (dayOfWeekSelected == 0 || DateSelected <= today) {
            invalidDate.style.display = 'block';
            validDate.style.display = 'none';
            return false;
        } else {
            validDate.style.display = 'block';
            invalidDate.style.display = 'none';
            return true;
        }
    }

    const dateChooseByUser = document.querySelector('#date');
    dateChooseByUser.onchange = evt => {
        if (!validate(evt.target.value)) {
            evt.target.value = '';
        }
    }

    /* On submit */
    const buttonSend = document.querySelector('.buttonBird');
    buttonSend.addEventListener('click', verify);

    function verify(event) {
        const formValidity = form.checkValidity();
        event.preventDefault();

        if (formValidity === false) {
            event.preventDefault();
            animBtn(formValidity)
        } else {
            form.action = "contact.php";
            animBtn(formValidity);
        }
        form.classList.add('was-validated');
    };

    function animBtn(formValidity) {
        const buttonSendText = document.querySelector('.buttonBirdText');

        if (formValidity === true) {
            buttonSend.classList.add('actif');
            buttonSendText.innerHTML = 'EN COURS D\'ENVOI';

            function waitForAnim() {
                form.submit();
            }
            setTimeout(waitForAnim, 2000);
        } else buttonSendText.innerHTML = 'Saisie incorrecte';
    };

    /* Anim on scroll */

    const allAnimOnScroll = document.querySelectorAll(".animOnScroll");

    const options = {
        root: null,
        threshold: 0.3,
    };

    const animOnScrollObserver = new IntersectionObserver(callback, options);

    allAnimOnScroll.forEach((animOnScroll) => {
        animOnScrollObserver.observe(animOnScroll);
    });

    document.querySelectorAll(".left-column").forEach((column) => {
        column.classList.add("hidden-left");
    });
    document.querySelectorAll(".right-column").forEach((column) => {
        column.classList.add("hidden-right");
    });

    function callback(entries, observer) {
        const [entry] = entries;

        if (!entry.isIntersecting) return;

        const curAmin = entry.target;
        curAmin.firstElementChild.classList.replace("hidden-left", "after-anim");
        curAmin.lastElementChild.classList.replace("hidden-right", "after-anim");

        observer.unobserve(curAmin);
    }



});