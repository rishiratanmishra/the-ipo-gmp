<?php
/**
 * IPO Premium Dashboard Page
 *
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPO_Theme_Dashboard
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_page'], 5);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'process_form_submission']);
    }

    public function register_page()
    {
        add_menu_page(
            __('IPO Premium', 'ipo-premium'),
            __('IPO Premium', 'ipo-premium'),
            'manage_options',
            'ipo-premium-dashboard',
            [$this, 'render_page'],
            'dashicons-chart-line',
            2
        );
    }

    public function register_settings()
    {
        register_setting('ipo_premium_options', 'ipopro_license_key');
    }

    public function process_form_submission()
    {
        // 1. License Handling
        if (isset($_POST['ipopro_license_submit']) && check_admin_referer('ipopro_license_nonce')) {
            $key = sanitize_text_field($_POST['ipopro_license_key']);

            // Validate
            $activation = IPO_License_Manager::activate_license($key);

            if ($activation['success']) {
                set_theme_mod('ipopro_license_key', $key);
                wp_safe_redirect(admin_url('admin.php?page=ipo-premium-dashboard&status=success'));
                exit;
            } else {
                set_theme_mod('ipopro_license_key', '');
                wp_safe_redirect(admin_url('admin.php?page=ipo-premium-dashboard&status=error&msg=' . urlencode($activation['message'])));
                exit;
            }
        }

        // 2. Quick Setup Handling (Local Demo)
        if (isset($_POST['ipopro_setup_home']) && check_admin_referer('ipopro_setup_nonce')) {
            // Create Home Page
            $home_id = wc_create_page('home', 'home', 'Home', '<!-- wp:heading --><h2>Welcome to IPO Premium</h2><!-- /wp:heading --><!-- wp:paragraph --><p>This is your professional homepage. Configure widgets and layout via Customizer.</p><!-- /wp:paragraph -->', 0);
            // Create Blog Page
            $blog_id = wc_create_page('blog', 'blog', 'News', '', 0);

            if ($home_id && $blog_id) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $home_id);
                update_option('page_for_posts', $blog_id);

                // Assign Menu if exists
                $locations = get_theme_mod('nav_menu_locations');
                if (empty($locations['primary'])) {
                    $menu = wp_get_nav_menu_object('Primary Menu');
                    if (!$menu) {
                        $menu_id = wp_create_nav_menu('Primary Menu');
                        $menu = wp_get_nav_menu_object($menu_id);
                        wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => 'Home', 'menu-item-object-id' => $home_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish']);
                        wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => 'IPOs', 'menu-item-url' => '/mainboard-ipos/', 'menu-item-type' => 'custom', 'menu-item-status' => 'publish']);
                    }
                    $locations['primary'] = $menu->term_id;
                    set_theme_mod('nav_menu_locations', $locations);
                }

                wp_safe_redirect(admin_url('admin.php?page=ipo-premium-dashboard&status=setup_success'));
                exit;
            }
        }
    }

    public function render_page()
    {
        $active_key = get_theme_mod('ipopro_license_key');
        $is_valid = !empty($active_key) && strlen($active_key) > 5;
        ?>
        <div class="wrap">
            <h1
                style="background:linear-gradient(135deg, #0d7ff2, #00FF94); -webkit-background-clip:text; -webkit-text-fill-color:transparent; font-weight:900; letter-spacing:-1px; font-size: 2.5em;">
                IPO Premium
            </h1>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible inline-block">
                        <p><?php _e('License successfully activated! Features unlocked.', 'ipo-premium'); ?></p>
                    </div>
                <?php elseif ($_GET['status'] === 'setup_success'): ?>
                    <div class="notice notice-success is-dismissible inline-block">
                        <p><?php _e('Homepage and Menu configured successfully!', 'ipo-premium'); ?></p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible inline-block">
                        <p><?php echo esc_html(urldecode($_GET['msg'])); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="display:flex; flex-wrap:wrap; gap:20px; margin-top:20px;">
                <!-- License Card -->
                <div
                    style="background:#fff; border-radius:12px; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05); min-width:300px; flex:1;">
                    <h2 style="margin-top:0;">Premium License</h2>
                    <p>Enter your purchase code to unlock automatic updates, importer, and support.</p>

                    <form method="post" action="">
                        <?php wp_nonce_field('ipopro_license_nonce'); ?>
                        <p>
                            <label for="ipopro_license_key" style="font-weight:bold; display:block; margin-bottom:8px;">License
                                Key</label>
                            <input type="text" name="ipopro_license_key" id="ipopro_license_key"
                                value="<?php echo esc_attr($active_key); ?>" class="regular-text"
                                style="width:100%; padding: 10px; font-family:monospace;" placeholder="IPO-XXXX-XXXX-XXXX">
                        </p>

                        <?php if ($is_valid): ?>
                            <div
                                style="padding:10px 15px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:6px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                                <span class="dashicons dashicons-yes"></span> License Active
                            </div>
                        <?php else: ?>
                            <div
                                style="padding:10px 15px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:6px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                                <span class="dashicons dashicons-lock"></span> Inactive
                            </div>
                        <?php endif; ?>

                        <input type="submit" name="ipopro_license_submit" class="button button-primary button-hero"
                            value="<?php _e('Activate License', 'ipo-premium'); ?>">
                    </form>
                </div>

                <!-- Quick Setup Card -->
                <?php if ($is_valid): ?>
                    <div
                        style="background:#fff; border-radius:12px; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05); min-width:300px; flex:1;">
                        <h2 style="margin-top:0;">Quick Setup</h2>
                        <p>Since this is a fresh install, click below to automatically create a <strong>Home</strong> page,
                            <strong>Blog</strong> page, and assign a <strong>Primary Menu</strong>.
                        </p>
                        <p><em>This replaces the need for an external XML import file for basic setups.</em></p>
                        <form method="post" action="">
                            <?php wp_nonce_field('ipopro_setup_nonce'); ?>
                            <input type="submit" name="ipopro_setup_home" class="button button-secondary"
                                value="Create Homepage & Menu">
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Info Card -->
                <div style="background:#fff; border-radius:12px; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05); flex:1;">
                    <h2 style="margin-top:0;">Status</h2>
                    <ul style="list-style:disc; margin-left:20px;">
                        <li><strong>Theme Version:</strong> <?php echo IPO_THEME_VERSION; ?></li>
                        <li><strong>Core Plugin:</strong>
                            <?php echo function_exists('run_the_ipo_gmp_core') ? 'Active' : 'Inactive'; ?></li>
                        <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                    </ul>
                    <p style="margin-top:20px;"><a href="<?php echo admin_url('customize.php'); ?>" class="button">Open
                            Customizer</a></p>
                </div>
            </div>
        </div>
        <?php
    }
}

// Helper: Safe Create Page
if (!function_exists('wc_create_page')) {
    function wc_create_page($slug, $option, $page_title, $page_content = '', $post_parent = 0)
    {
        $page_id = 0; // Using 0 as no option check needed for general use here
        $page_object = get_page_by_path($slug);
        if (!$page_object) {
            $page_id = wp_insert_post(array(
                'post_name' => $slug,
                'post_title' => $page_title,
                'post_content' => $page_content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_parent' => $post_parent,
                'comment_status' => 'closed'
            ));
        } else {
            $page_id = $page_object->ID;
        }
        return $page_id;
    }
}

new IPO_Theme_Dashboard();
