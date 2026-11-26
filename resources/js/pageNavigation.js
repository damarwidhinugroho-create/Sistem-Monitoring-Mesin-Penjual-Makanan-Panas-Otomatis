import gsap from 'gsap';
import { animate } from 'motion';

const prefetchedUrls = new Set();
const loadingScreen = document.querySelector('[data-page-loading]');
const pageContent = document.querySelector('.operator-content');

function getMotionTargets() {
    const children = Array.from(pageContent.children).filter((child) => child !== loadingScreen);
    return children.length ? children : [pageContent];
}

function getInternalDestination(anchor) {
    if (anchor.hasAttribute('download') || (anchor.target && anchor.target !== '_self')) {
        return null;
    }

    const destination = new URL(anchor.href, window.location.href);

    if (!['http:', 'https:'].includes(destination.protocol) || destination.origin !== window.location.origin) {
        return null;
    }

    return destination;
}

function prefetchDestination(anchor) {
    const destination = getInternalDestination(anchor);

    if (!destination || destination.pathname === window.location.pathname && destination.search === window.location.search) {
        return;
    }

    if (prefetchedUrls.has(destination.href)) {
        return;
    }

    prefetchedUrls.add(destination.href);
    const prefetchLink = document.createElement('link');
    prefetchLink.rel = 'prefetch';
    prefetchLink.as = 'document';
    prefetchLink.href = destination.href;
    document.head.append(prefetchLink);
}

document.addEventListener('pointerover', (event) => {
    if (event.pointerType === 'touch' || !(event.target instanceof Element)) {
        return;
    }

    const anchor = event.target.closest('a[href]');

    if (anchor && !anchor.contains(event.relatedTarget)) {
        prefetchDestination(anchor);
    }
});

document.addEventListener('focusin', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const anchor = event.target.closest('a[href]');

    if (anchor) {
        prefetchDestination(anchor);
    }
});

document.addEventListener('click', (event) => {
    if (
        !loadingScreen
        || !pageContent
        || event.defaultPrevented
        || event.button !== 0
        || event.metaKey
        || event.ctrlKey
        || event.shiftKey
        || event.altKey
        || !(event.target instanceof Element)
    ) {
        return;
    }

    const anchor = event.target.closest('a[href]');

    if (!anchor || anchor.hasAttribute('data-no-page-loading')) {
        return;
    }

    const destination = getInternalDestination(anchor);

    if (!destination || destination.href === window.location.href) {
        return;
    }

    if (
        destination.pathname === window.location.pathname
        && destination.search === window.location.search
        && destination.hash
    ) {
        return;
    }

    event.preventDefault();
    loadingScreen.hidden = false;
    loadingScreen.setAttribute('aria-hidden', 'false');
    pageContent.setAttribute('aria-busy', 'true');
    window.dispatchEvent(new Event('operator:page-transition-start'));
    document.documentElement.classList.add('page-scroll-locked');
    window.scrollTo(0, 0);
    try {
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        animate(loadingScreen, { opacity: [0, 1] }, {
            duration: reduced ? 0.12 : 0.22,
            ease: 'easeOut',
        });
        gsap.to(getMotionTargets(), {
            opacity: 0,
            y: reduced ? 0 : -8,
            duration: reduced ? 0.12 : 0.22,
            ease: 'power1.in',
            onComplete: () => window.location.assign(destination.href),
        });
    } catch (error) {
        console.error('Page transition failed; navigating without animation.', error);
        window.location.assign(destination.href);
    }
});

window.addEventListener('pageshow', () => {
    if (!loadingScreen || !pageContent) {
        return;
    }

    loadingScreen.hidden = true;
    loadingScreen.setAttribute('aria-hidden', 'true');
    pageContent.removeAttribute('aria-busy');
    document.documentElement.classList.remove('page-scroll-locked');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
    getMotionTargets().forEach((target) => {
        animate(target, {
            opacity: [0, 1],
            y: reduced ? [0, 0] : [10, 0],
        }, {
            duration: reduced ? 0.12 : 0.34,
            ease: 'easeOut',
        });
    });
});

