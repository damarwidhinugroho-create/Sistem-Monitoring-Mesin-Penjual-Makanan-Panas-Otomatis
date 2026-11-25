import gsap from 'gsap';
import Lenis from 'lenis';
import { animate, inView } from 'motion';
import 'lenis/dist/lenis.css';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const loginLayout = document.querySelector('.guest-layout');

if (loginLayout) {
    window.addEventListener('pageshow', () => {
        gsap.set(loginLayout, { clearProps: 'opacity,transform' });
    });

    window.addEventListener('saji:login-success', (event) => {
        const destination = event.detail?.destination;
        if (!destination) return;

        event.preventDefault();
        try {
            gsap.to(loginLayout, {
                y: reducedMotion ? 0 : -window.innerHeight,
                opacity: 0,
                duration: reducedMotion ? 0.12 : 0.68,
                ease: 'power2.inOut',
                onComplete: () => window.location.assign(destination),
            });
        } catch (error) {
            console.error('Login transition failed; navigating without animation.', error);
            window.location.assign(destination);
        }
    });
}

if (!reducedMotion) {
    const lenis = new Lenis({ autoRaf: true });
    window.addEventListener('operator:page-transition-start', () => lenis.stop());
    window.addEventListener('pageshow', () => lenis.start());
}

if (!document.querySelector('.dashboard__content')) {
    document.querySelectorAll('.operator-content h2, .operator-content h3, .error-page h1, .error-page h2')
        .forEach((heading) => {
            inView(heading, (element) => {
                animate(element, {
                    opacity: [0, 1],
                    y: reducedMotion ? [0, 0] : [8, 0],
                }, {
                    duration: reducedMotion ? 0.12 : 0.36,
                    ease: 'easeOut',
                });
            }, { once: true });
        });
}

