function initializeDashboardSkeleton() {
    const stage = document.querySelector('[data-dashboard-stage]');
    const content = document.querySelector('[data-dashboard-content]');
    const skeleton = document.querySelector('[data-dashboard-skeleton]');
    const status = document.querySelector('[data-dashboard-status]');

    if (!stage || !content || !skeleton || stage.dataset.dashboardFallback === 'true') {
        return;
    }

    stage.dataset.dashboardControllerReady = 'true';
    const minimumDuration = 400;
    const maximumDuration = 1200;
    const startedAt = performance.now();
    let dataReady = false;
    let finished = false;

    stage.dataset.dashboardState = 'loading';
    content.setAttribute('aria-hidden', 'true');
    content.inert = true;
    skeleton.hidden = false;
    if (status) {
        status.hidden = false;
    }

    const finish = () => {
        if (finished) {
            return;
        }

        finished = true;
        stage.dataset.dashboardState = 'revealing';
        content.inert = false;
        content.removeAttribute('aria-hidden');
        content.classList.add('dashboard-content--entering');

        const motionToken = window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ? '--motion-reduced-duration'
            : '--motion-reveal-total';
        const revealDuration = Number.parseFloat(
            getComputedStyle(document.documentElement).getPropertyValue(motionToken),
        );

        window.setTimeout(() => {
            skeleton.hidden = true;
            if (status) {
                status.hidden = true;
            }
            stage.dataset.dashboardState = 'ready';
            content.classList.remove('dashboard-content--entering');
        }, revealDuration);
    };

    const markReady = () => {
        dataReady = true;

        if (performance.now() - startedAt >= minimumDuration) {
            finish();
        }
    };

    window.addEventListener('dashboard:data-ready', markReady, { once: true });

    const minimumTimer = window.setTimeout(() => {
        if (dataReady) {
            finish();
        }
    }, minimumDuration);

    const maximumTimer = window.setTimeout(() => {
        window.clearTimeout(minimumTimer);
        finish();
    }, maximumDuration);

    window.addEventListener('pagehide', () => {
        window.clearTimeout(minimumTimer);
        window.clearTimeout(maximumTimer);
        stage.dataset.dashboardState = 'ready';
        content.inert = false;
        content.removeAttribute('aria-hidden');
        skeleton.hidden = true;
        if (status) {
            status.hidden = true;
        }
    }, { once: true });

    window.setTimeout(() => window.dispatchEvent(new Event('dashboard:data-ready')), 0);
}

initializeDashboardSkeleton();
