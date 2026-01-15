<?php
/**
 * Plugin Name: Allotment Manager
 * Plugin URI:  https://theipogmp.com
 * Description: Manages Allotment Registrar Links for the "Allotment Corner" section.
 * Version:     1.0.0
 * Author:      The IPO GMP
 * Author URI:  https://theipogmp.com
 * License:     GPL-2.0+
 * Text Domain: allotment-manager
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Allotment_Manager {

    public function __construct() {
        add_action( 'init', array( $this, 'register_cpt' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
        add_filter( 'manage_allotment_link_posts_columns', array( $this, 'add_admin_columns' ) );
        add_action( 'manage_allotment_link_posts_custom_column', array( $this, 'manage_admin_columns' ), 10, 2 );
    }

    /**
     * Register Custom Post Type
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => _x( 'Allotment Links', 'Post Type General Name', 'allotment-manager' ),
            'singular_name'         => _x( 'Allotment Link', 'Post Type Singular Name', 'allotment-manager' ),
            'menu_name'             => __( 'Allotment Links', 'allotment-manager' ),
            'name_admin_bar'        => __( 'Allotment Link', 'allotment-manager' ),
            'archives'              => __( 'Item Archives', 'allotment-manager' ),
            'attributes'            => __( 'Item Attributes', 'allotment-manager' ),
            'parent_item_colon'     => __( 'Parent Item:', 'allotment-manager' ),
            'all_items'             => __( 'All Links', 'allotment-manager' ),
            'add_new_item'          => __( 'Add New Link', 'allotment-manager' ),
            'add_new'               => __( 'Add New', 'allotment-manager' ),
            'new_item'              => __( 'New Link', 'allotment-manager' ),
            'edit_item'             => __( 'Edit Link', 'allotment-manager' ),
            'update_item'           => __( 'Update Link', 'allotment-manager' ),
            'view_item'             => __( 'View Link', 'allotment-manager' ),
            'view_items'            => __( 'View Links', 'allotment-manager' ),
            'search_items'          => __( 'Search Link', 'allotment-manager' ),
            'not_found'             => __( 'Not found', 'allotment-manager' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'allotment-manager' ),
            'featured_image'        => __( 'Featured Image', 'allotment-manager' ),
            'set_featured_image'    => __( 'Set featured image', 'allotment-manager' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'allotment-manager' ),
            'items_list'            => __( 'Items list', 'allotment-manager' ),
            'items_list_navigation' => __( 'Items list navigation', 'allotment-manager' ),
            'filter_items_list'     => __( 'Filter items list', 'allotment-manager' ),
        );
        $args = array(
            'label'                 => __( 'Allotment Link', 'allotment-manager' ),
            'description'           => __( 'Links to registrar allotment pages.', 'allotment-manager' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'hierarchical'          => false,
            'public'                => false, 
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 50,
            'menu_icon'             => 'dashicons-admin-links',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
        );
        register_post_type( 'allotment_link', $args );
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'allotment_details',
            'Link Details',
            array( $this, 'render_meta_box' ),
            'allotment_link',
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'save_allotment_details', 'allotment_nonce' );

        $url = get_post_meta( $post->ID, '_allotment_url', true );
        $subtitle = get_post_meta( $post->ID, '_allotment_subtitle', true );
        $shortcode = get_post_meta( $post->ID, '_allotment_shortcode', true );
        ?>
        <p>
            <label for="allotment_url" style="display:block; font-weight:bold; margin-bottom:5px;">Target URL</label>
            <input type="url" id="allotment_url" name="allotment_url" value="<?php echo esc_url( $url ); ?>" style="width:100%;" placeholder="https://..." />
        </p>
        <p>
            <label for="allotment_subtitle" style="display:block; font-weight:bold; margin-bottom:5px;">Subtitle</label>
            <input type="text" id="allotment_subtitle" name="allotment_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" style="width:100%;" placeholder="e.g. Major Registrar" />
        </p>
        <p>
            <label for="allotment_shortcode" style="display:block; font-weight:bold; margin-bottom:5px;">Icon Text (Shortcode)</label>
            <input type="text" id="allotment_shortcode" name="allotment_shortcode" value="<?php echo esc_attr( $shortcode ); ?>" style="width:100px;" placeholder="e.g. LI" maxlength="3" />
            <span class="description">Max 3 chars (e.g. LI, KF, BS)</span>
        </p>
        <?php
    }

    /**
     * Save Meta Data
     */
    public function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['allotment_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['allotment_nonce'], 'save_allotment_details' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['allotment_url'] ) ) {
            update_post_meta( $post_id, '_allotment_url', esc_url_raw( $_POST['allotment_url'] ) );
        }
        if ( isset( $_POST['allotment_subtitle'] ) ) {
            update_post_meta( $post_id, '_allotment_subtitle', sanitize_text_field( $_POST['allotment_subtitle'] ) );
        }
        if ( isset( $_POST['allotment_shortcode'] ) ) {
            update_post_meta( $post_id, '_allotment_shortcode', sanitize_text_field( $_POST['allotment_shortcode'] ) );
        }
    }

    /**
     * Admin Columns
     */
    public function add_admin_columns( $columns ) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['url'] = 'URL';
        $new_columns['subtitle'] = 'Subtitle';
        $new_columns['shortcode'] = 'Icon';
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    public function manage_admin_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'url':
                echo '<a href="' . esc_url( get_post_meta( $post_id, '_allotment_url', true ) ) . '" target="_blank">Link</a>';
                break;
            case 'subtitle':
                echo esc_html( get_post_meta( $post_id, '_allotment_subtitle', true ) );
                break;
            case 'shortcode':
                echo '<strong>' . esc_html( get_post_meta( $post_id, '_allotment_shortcode', true ) ) . '</strong>';
                break;
        }
    }
}

new Allotment_Manager();
