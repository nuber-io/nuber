/**
 * Nuber.io
 * Copyright 2020 - 2021 Jamiel Sharief.
 *
 * SPDX-License-Identifier: AGPL-3.0
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.nuber.io
 * @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
 */
(function () {
    let idleTimeout;

    const resetTimeout = function () {
        if (idleTimeout) {
            clearTimeout(idleTimeout);
        }

        idleTimeout = setTimeout(() => location.href = location.href, 300 * 1000);
    };

    resetTimeout();

    document.addEventListener('click', resetTimeout, false);
    document.addEventListener('mousemove', resetTimeout, false);
    document.addEventListener('touchstart', resetTimeout, false);
})();