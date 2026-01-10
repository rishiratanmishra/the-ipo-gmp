<?php
if (!defined('ABSPATH')) exit;

class BM_Meta {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
    }

    public function add_meta_boxes() {
        add_meta_box('bm_broker_details', 'Broker Information', [$this, 'render_meta_box'], 'broker', 'normal', 'high');
        add_meta_box('bm_broker_pros_cons', 'Pros & Cons', [$this, 'render_pros_cons_box'], 'broker', 'normal', 'default');
    }

    public function render_meta_box($post) {
        global $wpdb;
        $broker = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . BM_TABLE . " WHERE post_id = %d", $post->ID));
        
        // Defaults
        $affiliate = $broker->affiliate_link ?? '';
        $status = $broker->status ?? 'active';
        $referral = $broker->referral_code ?? '';
        $rating = $broker->rating ?? '';
        $min_deposit = $broker->min_deposit ?? '';
        $fees = $broker->fees ?? '';
        $logo_url = $broker->logo_url ?? '';
        $featured = $broker->is_featured ?? 0;
        $clicks = $broker->click_count ?? 0;
        ?>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <!-- Left Column -->
            <div>
                <p>
                    <label><strong>Affiliate Link:</strong></label><br>
                    <input type="url" name="bm_affiliate" value="<?php echo esc_attr($affiliate); ?>" style="width:100%;" placeholder="https://...">
                </p>
                
                <p>
                    <label><strong>Valuable Link (Logo URL):</strong></label><br>
                    <input type="url" name="bm_logo_url" value="<?php echo esc_attr($logo_url); ?>" style="width:100%;" placeholder="https://... (Optional if using Featured Image)">
                </p>

                
                <p>
                    <label><strong>Referral Code:</strong></label><br>
                    <input type="text" name="bm_referral" value="<?php echo esc_attr($referral); ?>" style="width:100%;" placeholder="e.g. WELCOME50">
                </p>

                <p>
                    <label><strong>Status:</strong></label><br>
                    <select name="bm_status" style="width:100%;">
                        <option value="active" <?php selected($status,'active'); ?>>Active</option>
                        <option value="inactive" <?php selected($status,'inactive'); ?>>Inactive</option>
                    </select>
                </p>
            </div>

            <!-- Right Column -->
            <div>
                <p>
                    <label><strong>Rating (0-5):</strong></label><br>
                    <input type="number" step="0.1" min="0" max="5" name="bm_rating" value="<?php echo esc_attr($rating); ?>" style="width:100%;">
                </p>

                <p>
                    <label><strong>Minimum Deposit:</strong></label><br>
                    <input type="text" name="bm_min_deposit" value="<?php echo esc_attr($min_deposit); ?>" style="width:100%;" placeholder="e.g. $10">
                </p>

                <p>
                    <label><strong>Fees / Commission:</strong></label><br>
                    <input type="text" name="bm_fees" value="<?php echo esc_attr($fees); ?>" style="width:100%;" placeholder="e.g. $0 Equity Delivery">
                </p>

                <p style="margin-top:25px; padding:10px; background:#f0f0f1; border-radius:4px;">
                    <label style="display:inline-block; margin-right:15px;">
                        <input type="checkbox" name="bm_featured" value="yes" <?php checked($featured, 1); ?>>
                        <strong>Mark as Featured / Recommended</strong>
                    </label>
                </p>
                
                <p style="color:#666; font-size:12px;">
                    <strong>Total Clicks:</strong> <?php echo intval($clicks); ?>
                </p>
            </div>
        </div>

    <?php }

    public function render_pros_cons_box($post) {
        global $wpdb;
        $broker = $wpdb->get_row($wpdb->prepare("SELECT pros, cons FROM " . BM_TABLE . " WHERE post_id = %d", $post->ID));
        $pros = $broker->pros ?? '';
        $cons = $broker->cons ?? '';
        ?>
        <p><em>Enter one item per line.</em></p>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div>
                <label><strong>Pros (Why we like it):</strong></label>
                <textarea name="bm_pros" rows="6" style="width:100%;"><?php echo esc_textarea($pros); ?></textarea>
            </div>
            <div>
                <label><strong>Cons (Things to consider):</strong></label>
                <textarea name="bm_cons" rows="6" style="width:100%;"><?php echo esc_textarea($cons); ?></textarea>
            </div>
        </div>
        <?php
    }

    public function save_meta($post_id){
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (get_post_type($post_id) !== 'broker') return;

        global $wpdb;

        $data = [
            'post_id' => $post_id,
            'title' => get_the_title($post_id),
            'slug' => get_post_field('post_name', $post_id),
            'affiliate_link' => esc_url_raw($_POST['bm_affiliate'] ?? ''),
            'referral_code' => sanitize_text_field($_POST['bm_referral'] ?? ''),
            'status' => sanitize_text_field($_POST['bm_status'] ?? 'active'),
            'rating' => (float) ($_POST['bm_rating'] ?? 0),
            'min_deposit' => sanitize_text_field($_POST['bm_min_deposit'] ?? ''),
            'fees' => sanitize_text_field($_POST['bm_fees'] ?? ''),
            'logo_url' => esc_url_raw($_POST['bm_logo_url'] ?? ''),
            'pros' => sanitize_textarea_field($_POST['bm_pros'] ?? ''),
            'cons' => sanitize_textarea_field($_POST['bm_cons'] ?? ''),
            'is_featured' => isset($_POST['bm_featured']) ? 1 : 0,
            'updated_at' => current_time('mysql')
        ];

        // Check if exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . BM_TABLE . " WHERE post_id = %d", $post_id));

        if ($exists) {
            $wpdb->update(BM_TABLE, $data, ['post_id' => $post_id]);
        } else {
            $wpdb->insert(BM_TABLE, $data);
        }

        // CLEAR API CACHE
        delete_transient('bm_api_brokers_list');
    }
}

new BM_Meta();
