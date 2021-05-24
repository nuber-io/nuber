/**
 * This is redundant if set to the same time as the session timeout reset.
 */
let idleTimeout;
const duration = 15 * 60 * 1000;

const inactivityListener = function () {
    if (idleTimeout) {
        clearTimeout(idleTimeout);
    }

    idleTimeout = setTimeout(() => location.href = '/logout', duration);
};

function enableTimeout() {
    console.log('inactivity timeout enabled');
    inactivityListener();
    ['click', 'mousemove', 'touchstart'].forEach(event =>
        document.addEventListener(event, inactivityListener, false)
    );
}

function disableTimeout() {
    console.log('inactivity timeout disabled');
    ['click', 'mousemove', 'touchstart'].forEach(event =>
        document.removeEventListener(event, inactivityListener, false)
    );
}

$(document).ready(function () {
    enableTimeout();
});