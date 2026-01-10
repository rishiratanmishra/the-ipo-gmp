<?php
class BBM_CPT {
    static function register(){
        register_post_type("buybacks",[
            "label"=>"Buybacks",
            "public"=>true,
            "supports"=>["title"],
            "menu_icon"=>"dashicons-chart-line",
            "show_in_rest"=>true
        ]);
    }
}
