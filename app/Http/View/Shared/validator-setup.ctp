<?php
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
?>
<script>
    $(document).ready(function() {
        $.validator.addMethod('regex', function(value, element, param) {
                return this.optional(element) ||
                    value.match(typeof param == 'string' ? new RegExp(param) : param);
            },
            'Invalid value');

        $.validator.setDefaults({
            errorPlacement: function(error, element) {}
        });
    });
</script>