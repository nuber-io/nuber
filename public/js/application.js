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

$.ajaxSetup({
  headers: {
    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
  }
});

$.put = function (url, data, callback, type) {
  if ($.isFunction(data)) {
    (type = type || callback), (callback = data), (data = {});
  }

  return $.ajax({
    url: url,
    type: "PUT",
    success: callback,
    data: data,
    contentType: type,
  });
};

$.delete = function (url, data, callback, type) {
  if ($.isFunction(data)) {
    (type = type || callback), (callback = data), (data = {});
  }

  return $.ajax({
    url: url,
    type: "DELETE",
    success: callback,
    data: data,
    contentType: type,
  });
};



function alertSuccess(message) {
  var alert = '<div class="alert alert-success" role="alert"> ' + message + '</div>';
  $("main.container").prepend(alert);
}

function alertError(message) {
  var alert = '<div class="alert alert-danger" role="alert"> ' + message + '</div>';
  $("main.container").prepend(alert);
}

function alertWarning(message) {
  var alert = '<div class="alert alert-warning" role="alert"> ' + message + '</div>';
  $("main.container").prepend(alert);
}

function debugError(xhr) {
  var response = JSON.parse(xhr.responseText);
  console.log(response.error.message);
}

// auto remove alerts to prevent stacking
$(function () {
  window.setTimeout(function () {
    $(".alert").slideUp(500, function () {
      $(this).remove();
    });
  }, 5000);
});