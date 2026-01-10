<?php

class TIGC_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu() {
        add_menu_page(
            'The IPO GMP Settings',
            'The IPO GMP',
            'manage_options',
            'the-ipo-gmp-core',
            [$this, 'render_dashboard'],
            'dashicons-chart-line',
            25
        );
    }

    public function render_dashboard() {
        // Handle Action (Re-create pages)
        if (isset($_POST['tigc_recreate_pages']) && check_admin_referer('tigc_recreate_action')) {
            $this->create_missing_pages();
            echo '<div class="notice notice-success is-dismissible"><p>Missing pages recreated successfully!</p></div>';
        }

        $pages = [
            'mainboard-ipos' => 'Mainboard IPOs',
            'sme-ipos'       => 'SME IPOs',
            'buybacks'       => 'Buybacks',
            'ipo-details'    => 'IPO Details'
        ];
        ?>
        <div class="wrap">
            <h1>The IPO GMP Core Dashboard</h1>
            <p>Manage the core configuration and pages for your IPO platform.</p>
            
            <div class="card" style="max-width: 600px; padding-top: 0;">
                <h2 class="title">Page Status Monitor</h2>
                <p>Ensure all critical pages exist and are mapped correctly.</p>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pages as $slug => $title): 
                            $page = get_page_by_path($slug);
                            $exists = !empty($page);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($title); ?></strong></td>
                            <td><code><?php echo esc_html($slug); ?></code></td>
                            <td>
                                <?php if($exists): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Active
                                <?php else: ?>
                                    <span class="dashicons dashicons-warning" style="color: red;"></span> Missing
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($exists): ?>
                                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank" class="button button-small">View</a>
                                    <a href="<?php echo get_edit_post_link($page->ID); ?>" class="button button-small">Edit</a>
                                <?php else: ?>
                                    <span class="description">Run repair below</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <form method="post" action="">
                    <?php wp_nonce_field('tigc_recreate_action'); ?>
                    <input type="hidden" name="tigc_recreate_pages" value="1">
                    <button type="submit" class="button button-primary">Repair / Create Missing Pages</button>
                </form>
            </div>
        </div>
        <?php
    }

    private function create_missing_pages() {
        $pages = [
            'mainboard-ipos' => 'Mainboard IPOs',
            'sme-ipos'       => 'SME IPOs',
            'buybacks'       => 'Buybacks',
            'ipo-details'    => 'IPO Details'
        ];
    
        foreach ($pages as $slug => $title) {
            if (!get_page_by_path($slug)) {
                wp_insert_post([
                    'post_title'   => $title,
                    'post_name'    => $slug,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => ''
                ]);
            }
        }
    }
}
