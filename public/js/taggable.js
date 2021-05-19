/**
 * Taggable Jquery plugin
 * @Copyright 2020 - 2021 Jamiel Sharief
 * 
 * usage:
 *
 * 1. create an input box with an ID
 *
 *  <input type="text" id="tags" class="form-control" autocomplete="off">
 *
 * 2. enable it
 *
 *  $(document).ready(function() {
 *    $('#tags').taggable();
 *   });
 *
 * or for inline
 *
 *  $('#tags').taggable({inline:true});
 *
 * You can also use autocomplete if jquery-ui is loaded
 *
 *  $('#tags').taggable({autocomplete:["Hot","Warm","Cold"]});
 *
 *  or
 *
 *  $('#tags').taggable({autocomplete:'https://remoteserver.com/tags/lookup'});
 * 
 *   $('#company').taggable({
 *      inline:true,
 *      class:'company',
 *      autocomplete: '/companies/autocomplete'
 *     });
 * 
 *   JSON: {['text'=>'foo','value'=>'foo']}
 */
$.fn.taggable = function(options) {
    if ( !this.length || !this.attr('id')) {
  			console.log('Taggable error: no element selected or it has no id');
  			return;
  		}
      // Establish our default settings
      var settings = $.extend({
          class: 'tags',
          inline: false,
          autocomplete: false
      }, options);
      if(settings.inline){
        settings.class = settings.class + " list-inline";
      }
      else{
        settings.class = settings.class + " list-unstyled";
      }
      console.log(settings );
    new Taggable("#" + this.attr('id'),settings);
    return this;
}

class Taggable  {
  constructor(selector,settings) {

    this.selector = selector;
    this.settings = settings;
    this.tags = [];

    $(selector).attr("autocomplete", "off");
    $(selector)[0].outerHTML+='<ul class="'+ settings.class + '"></ul>';

    var me = this;

    // When return or , is pressed create tag
    $(selector).bind('keypress keydown keyup', function(e){
         if(e.keyCode == 13 || e.keyCode == 188) {
           me.add($(this).val());
           $(this).val("");
           e.preventDefault();
         }
      });

      // Enable autocomplete (required Jquery UI)
      if(jQuery.ui && settings.autocomplete){
        $(selector).autocomplete({
          source: settings.autocomplete,
          select:function(event, ui){
             me.add(ui.item.value);
             $(me.selector).val('');
             return false;
           }
        });
      }
      // Convert tags to string on form submit
      $("form").submit(function (e) {
          $(me.selector).val(me.tags.toString());
      });

      // if the form has existing values, then load tags
      if($(selector).val()){
        var tags = $(selector).val();
        if(tags){
          tags.split(/\s*,\s*/).forEach(function(tag) {
              me.add(tag);
          });
          $(selector).val("");
        }
      }

  }
  add(tag){
    if(tag == null || tag == false){
      return false;
    }
    // Dont add if already Added
    for (var i = 0; i < this.tags.length; i++) {
        if (tag == this.tags[i]) {
          return true;
        }
      }
    this.tags.push(tag);
    this.redraw();
  }
  redraw(){
    var me = this;
    var ulElement = $(this.selector).next();
    
    var liclass = '';
    if(this.settings.inline == true){
      liclass ="list-inline-item";
    }

    $(ulElement).find("li").remove(); // Remove all existing Li
    // Create LI element for each Tag
    for (var i = 0; i < this.tags.length; i++) {
        $(ulElement).append('<li class="'+ liclass + '" data-id="'+i +'"><span class="badge badge-primary"><a href="#">x</a> ' +  this.tags[i] +'</span></li>' );
      }
      // Setup delete action
      $(ulElement).find("li a").click(function(){
        var liElement = $(this).closest("li");
        me.tags.splice(liElement.data('id'),1);
        $(liElement).remove();
        me.redraw();
        return true;
      })
  }
}