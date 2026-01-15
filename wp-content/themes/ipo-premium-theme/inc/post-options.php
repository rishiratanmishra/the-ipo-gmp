<?php
/**
 * Single Post Visibility Options
 */

function ipopro_add_custom_meta_box()
{
    add_meta_box(
        'ipopro_post_options',       // ID
        'Single Post Options',       // Title
        'ipopro_render_meta_box',    // Callback
        'post',                      // Screen
        'side',                      // Context
        'high'                       // Priority
    );
}
add_action('add_meta_boxes', 'ipopro_add_custom_meta_box');

function ipopro_render_meta_box($post)
{
    wp_nonce_field('ipopro_save_post_options', 'ipopro_post_options_nonce');

    // Get current values (default to 'yes' if not set, or check specific logic)
    // We strive for "Checked by default" behavior for new posts.
    // If metadata doesn't exist, we assume it's a new post or legacy post where we want them ON.
    // However, get_post_meta returns '' if not found.
    // Logic: If value is 'no', then unchecked. Else checked.

    $fields = [
        '_show_breadcrumbs' => 'Show Breadcrumbs',
        '_show_share_buttons' => 'Show Share Buttons',
        '_show_quick_highlights' => 'Show Quick Highlights',
        '_show_toc' => 'Show Table of Contents',
        '_show_related_posts' => 'Show Related Posts'
    ];

    echo '<div style="display:flex; flex-direction:column; gap:10px;">';

    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        // Default to checked if empty (first load) or if explicitly 'yes'
        $checked = ($value !== 'no') ? 'checked' : '';

        echo '<label style="cursor:pointer;">';
        echo '<input type="checkbox" name="' . esc_attr($key) . '" value="yes" ' . $checked . ' /> ';
        echo esc_html($label);
        echo '</label>';
    }

    echo '</div>';
    echo '<p style="font-size:11px; color:#666; margin-top:10px;">Uncheck to hide specific sections for this post.</p>';
}

function ipopro_save_post_options($post_id)
{
    if (!isset($_POST['ipopro_post_options_nonce']))
        return;
    if (!wp_verify_nonce($_POST['ipopro_post_options_nonce'], 'ipopro_save_post_options'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    $fields = [
        '_show_breadcrumbs',
        '_show_share_buttons',
        '_show_quick_highlights',
        '_show_toc',
        '_show_related_posts'
    ];

    foreach ($fields as $key) {
        // If checkbox is checked, it posts 'yes'. If unchecked, it posts nothing.
        // So if isset -> yes, else -> no.
        $val = isset($_POST[$key]) ? 'yes' : 'no';
        update_post_meta($post_id, $key, $val);
    }
}
add_action('save_post', 'ipopro_save_post_options');
