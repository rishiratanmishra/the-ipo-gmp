jQuery(document).ready(function($){
 $('.bbm-tab').hide();
 $('.bbm-tab').first().show();
 $('.bbm-tab-nav li').first().addClass('active');
 $('.bbm-tab-nav li').click(function(){
  var t=$(this).data('tab');
  $('.bbm-tab-nav li').removeClass('active');
  $(this).addClass('active');
  $('.bbm-tab').hide();
  $('#tab_'+t).show();
 });
});