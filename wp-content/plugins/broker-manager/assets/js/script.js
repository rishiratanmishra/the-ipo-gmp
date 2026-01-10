jQuery(document).ready(function($){
    $('.bm-track-click').on('click', function(){
        var broker_id = $(this).data('id');
        // 'bm_ajax' is defined via wp_localize_script
        if(typeof bm_ajax !== 'undefined') {
            $.post(bm_ajax.url, {
                action: 'bm_track_click',
                broker_id: broker_id
            });
        }
    });
});
