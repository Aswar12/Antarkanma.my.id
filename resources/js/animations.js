// Team Cards Scroll Interaction
document.addEventListener('DOMContentLoaded', () => {
    const scrollContainer = document.querySelector('.animate-auto-scroll');
    if (!scrollContainer) return;

    let isDown = false;
    let startX;
    let scrollLeft;

    scrollContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        scrollContainer.style.cursor = 'grabbing';
        startX = e.pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollContainer.scrollLeft;
        // Pause animation while dragging
        scrollContainer.style.animationPlayState = 'paused';
    });

    scrollContainer.addEventListener('mouseleave', () => {
        isDown = false;
        scrollContainer.style.cursor = 'grab';
        // Resume animation
        scrollContainer.style.animationPlayState = 'running';
    });

    scrollContainer.addEventListener('mouseup', () => {
        isDown = false;
        scrollContainer.style.cursor = 'grab';
        // Resume animation
        scrollContainer.style.animationPlayState = 'running';
    });

    scrollContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 2;
        scrollContainer.scrollLeft = scrollLeft - walk;
    });

    // Touch events for mobile
    scrollContainer.addEventListener('touchstart', (e) => {
        scrollContainer.style.animationPlayState = 'paused';
    });

    scrollContainer.addEventListener('touchend', () => {
        scrollContainer.style.animationPlayState = 'running';
    });
});
