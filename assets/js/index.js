let overlayVisible = false;
let overlayContainer = document.getElementsByClassName('hover-menu-container')[0];
let btnPreview = document.getElementById('overlay-imgen');
let context = null;
let scrollPos = 0;


document.querySelectorAll('.hover-menu table tr')
    .forEach(e => e.addEventListener("click", (e) => {
        // Here, `this` refers to the element the event was hooked on
        overlayContainer.classList.remove('d-none');
        overlayContainer.parentElement.classList.remove('overflow-auto');
        overlayContainer.parentElement.classList.add('overflow-hidden');
        scrollPos = overlayContainer.parentElement.scrollTop;
        overlayContainer.parentElement.scrollTop = 0;
        overlayVisible = true;

        context = e.currentTarget.dataset;
        if (typeof context.preview === 'undefined') {
            btnPreview.classList.add('d-none');
        }
    }));


document.getElementById('overlay-hide').addEventListener('click', () => {
    if (overlayVisible === true) {
        overlayVisible = false;
        overlayContainer.classList.add('d-none');
        overlayContainer.parentElement.classList.remove('overflow-hidden');
        overlayContainer.parentElement.classList.add('overflow-auto');
        btnPreview.classList.remove('d-none');
        overlayContainer.parentElement.scrollTop = scrollPos;
        scrollPos = 0;
    }
});

document.getElementById('overlay-edit').addEventListener('click', (e) => {
    if (overlayVisible === true) {
        window.location.href = buildUrl(context.edit);
    }
});

document.getElementById('overlay-imgen').addEventListener('click', (e) => {
    if (overlayVisible === true) {
        window.location.href = buildUrl(context.preview);
    }
});

function buildUrl(action) {
    let length = window.location.href.lastIndexOf('/') + 1;
    return window.location.href.substr(0, length) + action;
}