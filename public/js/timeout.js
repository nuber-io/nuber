let idleTimeout;
const duration = 4 * 60;

const inactivityListener = function () {
    if (idleTimeout) {
        clearTimeout(idleTimeout);
    }

    idleTimeout = setTimeout(() => location.href = '/logout', duration * 1000);
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