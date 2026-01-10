<?php
if (!defined('ABSPATH')) exit;

class BM_Shortcode {
    public function __construct() {
        add_shortcode('brokers', [$this, 'display_brokers']);
        add_action('wp_ajax_bm_track_click', [$this, 'track_click']);
        add_action('wp_ajax_nopriv_bm_track_click', [$this, 'track_click']);
    }

    public function track_click() {
        if(isset($_POST['broker_id'])) {
            $pid = intval($_POST['broker_id']);
            $current = (int) get_post_meta($pid, 'bm_click_count', true);
            update_post_meta($pid, 'bm_click_count', $current + 1);
            wp_send_json_success(['new_count' => $current + 1]);
        }
        wp_send_json_error();
    }

    public function display_brokers($atts){
        $atts = shortcode_atts([
            'category' => '', // Filter by category slug
            'limit' => -1
        ], $atts);

        $args = [
            'post_type' => 'broker',
            'posts_per_page' => $atts['limit'],
            'meta_key' => 'bm_featured',
            'orderby' => ['meta_value' => 'DESC', 'date' => 'DESC']
        ];

        if(!empty($atts['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'broker_category',
                    'field'    => 'slug',
                    'terms'    => $atts['category'],
                ]
            ];
        }

        $query = new WP_Query($args);

        ob_start();
        ?>
        <div class="bm-brokers-list">
            <?php while($query->have_posts()): $query->the_post();
                $id = get_the_ID();
                $affiliate = get_post_meta($id, 'bm_affiliate', true);
                $featured = get_post_meta($id, 'bm_featured', true);
                $rating = get_post_meta($id, 'bm_rating', true);
                $min_dep = get_post_meta($id, 'bm_min_deposit', true);
                $fees = get_post_meta($id, 'bm_fees', true);
                $pros = array_filter(explode("\n", get_post_meta($id, 'bm_pros', true)));
                $logo_url = get_post_meta($id, 'bm_logo_url', true);
                
                if(has_post_thumbnail($id)) {
                    $logo = get_the_post_thumbnail($id, 'medium', ['class'=>'bm-logo']);
                } elseif($logo_url) {
                    $logo = '<img src="'.esc_url($logo_url).'" class="bm-logo" alt="'.esc_attr(get_the_title()).'">';
                } else {
                    $logo = '';
                }
                
                // Generate Stars
                $star_html = '';
                if($rating) {
                    $full = floor($rating);
                    $half = ($rating - $full) >= 0.5;
                    $star_html .= str_repeat('★', $full);
                    if($half) $star_html .= '½';
                    $star_html .= str_repeat('☆', 5 - ceil($rating));
                }
            ?>
                <div class="bm-broker-card <?php echo $featured === 'yes' ? 'bm-featured' : ''; ?>">
                    
                    <?php if($featured === 'yes'): ?>
                        <div class="bm-badge">Recommended</div>
                    <?php endif; ?>

                    <div class="bm-card-inner">
                        
                        <!-- Logo & Rating -->
                        <div class="bm-col-logo">
                            <div class="bm-logo-wrapper">
                                <?php echo $logo ?: '<div class="bm-no-logo">No Logo</div>'; ?>
                            </div>
                            <?php if($rating): ?>
                                <div class="bm-stars"><?php echo $star_html; ?></div>
                                <div class="bm-rating-num"><?php echo $rating; ?>/5</div>
                            <?php endif; ?>
                        </div>

                        <!-- Details -->
                        <div class="bm-col-details">
                            <h3><?php the_title(); ?></h3>
                            
                            <div class="bm-meta-row">
                                <?php if($min_dep): ?>
                                    <span>💰 Min Dep: <strong><?php echo esc_html($min_dep); ?></strong></span>
                                <?php endif; ?>
                                <?php if($fees): ?>
                                    <span>🏷️ Fees: <strong><?php echo esc_html($fees); ?></strong></span>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($pros)): ?>
                                <div class="bm-pros">
                                    <strong>PROS:</strong>
                                    <ul>
                                        <?php foreach(array_slice($pros, 0, 3) as $p) echo '<li>'.esc_html($p).'</li>'; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action -->
                        <div class="bm-col-action">
                             <?php if($affiliate): ?>
                                <a href="<?php echo esc_url($affiliate); ?>" target="_blank" class="bm-track-click bm-btn" data-id="<?php the_ID(); ?>">
                                    Open Account
                                </a>
                                <small>Secure Link</small>
                            <?php else: ?>
                                <button disabled class="bm-btn disabled">Unavailable</button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

new BM_Shortcode();
