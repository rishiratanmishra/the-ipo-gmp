<?php
require_once get_template_directory() . '/inc/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'ipopro_register_required_plugins');

function ipopro_register_required_plugins()
{
    $plugins = [
        [
            'name' => 'The IPO GMP Core',
            'slug' => 'the-ipo-gmp-core',
            'required' => true,
            'source' => 'https://github.com/zolaha/the-ipo-gmp-core/archive/refs/heads/main.zip', // Example source or local
        ],
        [
            'name' => 'One Click Demo Import',
            'slug' => 'one-click-demo-import',
            'required' => false,
        ],
        [
            'name' => 'Elementor',
            'slug' => 'elementor',
            'required' => false,
        ],
    ];

    $config = [
        'id' => 'ipo-premium-theme',
        'default_path' => '',
        'menu' => 'tgmpa-install-plugins',
        'has_notices' => true,
        'dismissable' => true,
        'dismiss_msg' => '',
        'is_automatic' => false,
        'message' => '',
    ];

    tgmpa($plugins, $config);
}
