const wrapper = document.querySelector('.reviews-wrapper');
const dots = document.querySelectorAll('.dot');
const slides = document.querySelectorAll('.review-flex');
const slideCount = slides.length;
let currentIndex = 0;
let interval;

const updateSlider = () => {
    wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
    dots.forEach(dot => dot.classList.remove('active'));
    dots[currentIndex].classList.add('active');
};

const startAutoSlide = () => {
    interval = setInterval(() => {
        currentIndex = (currentIndex + 1) % slideCount;
        updateSlider();
    }, 3000);
};

const stopAutoSlide = () => {
    clearInterval(interval);
};

dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        stopAutoSlide();
        currentIndex = index;
        updateSlider();
        startAutoSlide();
    });
});