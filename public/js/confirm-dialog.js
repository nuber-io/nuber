/**
 * Bootstrap 4.0 confirm dialog
 * @Copyright 2020 - 2021 Jamiel Sharief
 *
 * Only the body needs to be provided, if no title is provided then it wont show the header. Currently
 * it requires Jquery, but could easily be rewritten to use just vanilla javascript
 *
 * Usage:
 *
 * $(document).ready(function () {
 *     confirmDialog('are you sure you want to do this?', function () {
 *          console.log('confirmed');
 *      });
 * });
 *
 * $(document).ready(function () {
 *     confirmDialog({
 *         "title" : "Delete Volume",
 *         "body": "<p>Are you sure you want to delete the volume xyz</p>",
 *         "ok": "Delete volume", // optional
 *         "okClass": "btn btn-danger", // optional
 *      }, function () {
 *          console.log('confirmed');
 *      });
 * });
 */
var confirmDialog = function (settings, callback) {
  if (settings === null) {
    throw "You did not provide settings";
  }

  if (typeof setting === "string") {
    settings = {
      body: settings,
    };
  }

  /**
   * Remove previous dialog if there, this is important since events will
   * also be registered.
   */
  if ($("#confirm-dialog").length) {
    $("#confirm-dialog").remove();
  }

  // Build Modal 
  $("body").append(
    '<div id="confirm-dialog" class="modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" id="modal-btn-cancel" class="btn btn-secondary" data-dismiss="modal"></button><button type="button" id="modal-btn-ok" class="btn btn-primary"></button></div></div></div></div>'
  );

  if (settings.title) {
    $("#confirm-dialog .modal-header").show();
    $("#confirm-dialog .modal-title").html(settings.title);
    
  } else {
    $("#confirm-dialog .modal-header").hide();
  }

  $("#confirm-dialog .modal-body").html(
    settings.body ?? "<p>Are you sure you want to continue?</p>"
  );
  $("#confirm-dialog #modal-btn-ok").text(settings.ok ?? "ok");
  $("#confirm-dialog #modal-btn-cancel").text(settings.cancel ?? "cancel");

  $("#confirm-dialog #modal-btn-ok").attr("class",settings.okClass  ??  'btn btn-primary');
  $("#confirm-dialog #modal-btn-cancel").attr("class",settings.cancelClass  ?? 'btn btn-secondary');

  // Register Events
  $("#confirm-dialog #modal-btn-ok").on("click", function () {
    $("#confirm-dialog").modal("hide");
    callback();
  });

  // Display to user
  $("#confirm-dialog").modal("show");
};
