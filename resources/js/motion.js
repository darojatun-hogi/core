// Motion landing page: elemen [data-reveal] diberi kelas is-visible saat masuk layar (gaya di motion.css)
import '../css/motion.css';

const items = document.querySelectorAll('[data-reveal]');

if (!('IntersectionObserver' in window)) {
    items.forEach((el) => el.classList.add('is-visible'));
} else {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' },
    );

    items.forEach((el) => observer.observe(el));
}
