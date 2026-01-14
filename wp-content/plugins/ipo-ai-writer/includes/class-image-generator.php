<?php

class IPO_AI_Image_Generator
{

    /**
     * Generate and Attach Featured Image to Post.
     *
     * @param int $post_id The Post ID to attach to.
     * @param array $data Data for image (name, gmp, price).
     * @return int|WP_Error Attachment ID or Error.
     */
    public static function generate_and_attach($post_id, $data)
    {
        // 1. Setup Canvas (1200x630 - Facebook/Twitter OG Standard)
        $width = 1200;
        $height = 630;
        $im = imagecreatetruecolor($width, $height);

        // 2. Create Premium Background (Dark Blue/Navy Gradient)
        // Top Left Color (Dark Navy)
        $color_1 = imagecolorallocate($im, 5, 10, 24);
        // Bottom Right Color (Slightly Lighter Blue)
        $color_2 = imagecolorallocate($im, 13, 20, 40);

        // Simple Vertical Gradient
        for ($i = 0; $i < $height; $i++) {
            $r = ($i / $height) * (($color_2 >> 16 & 0xFF) - ($color_1 >> 16 & 0xFF)) + ($color_1 >> 16 & 0xFF);
            $g = ($i / $height) * (($color_2 >> 8 & 0xFF) - ($color_1 >> 8 & 0xFF)) + ($color_1 >> 8 & 0xFF);
            $b = ($i / $height) * (($color_2 & 0xFF) - ($color_1 & 0xFF)) + ($color_1 & 0xFF);
            $color = imagecolorallocate($im, $r, $g, $b);

            imageline($im, 0, $i, $width, $i, $color);
        }

        // 3. Add Colors for Text
        $white = imagecolorallocate($im, 255, 255, 255);
        $green = imagecolorallocate($im, 0, 255, 148); // Neon Green
        $grey = imagecolorallocate($im, 150, 160, 180);

        // 4. Text Settings
        // We'll use a built-in font for simplicity, or load TTF if available.
        // Since we can't guarantee TTF file existence, we use GD built-in 5 (largest)
        // For production, referencing a TTF is better, but this ensures 0 crashes.
        // Ideally: $font = IPO_AI_PATH . 'assets/fonts/Inter-Bold.ttf';

        $font_file = WP_PLUGIN_DIR . '/ipo-ai-writer/assets/fonts/Inter-Bold.ttf';
        // Force fallback if file missing
        $use_ttf = file_exists($font_file);

        // Debug
        // error_log("IPO AI Image: Accessing Font at: " . $font_file . " Exists: " . ($use_ttf ? 'Yes' : 'No'));

        $ipo_name = strtoupper($data['name']);
        $ipo_name = (strlen($ipo_name) > 30) ? substr($ipo_name, 0, 27) . '...' : $ipo_name;

        $status_text = "IPO STATUS: " . strtoupper($data['status']);

        // Determine GMP Color
        $gmp_val = preg_replace('/[^0-9]/', '', $data['gmp']);
        $gmp_color = ($gmp_val > 0) ? $green : $grey;

        $gmp_text = "GMP: " . $data['gmp'];

        if ($use_ttf) {
            // Center Text
            $bbox = imagettfbbox(60, 0, $font_file, $ipo_name);
            $x = $width / 2 - ($bbox[2] - $bbox[0]) / 2;
            imagettftext($im, 60, 0, $x, 315, $white, $font_file, $ipo_name);

            // Top Label
            $bbox_st = imagettfbbox(20, 0, $font_file, $status_text);
            $x_st = $width / 2 - ($bbox_st[2] - $bbox_st[0]) / 2;
            imagettftext($im, 20, 0, $x_st, 150, $grey, $font_file, $status_text);

            // Bottom GMP
            $bbox_gmp = imagettfbbox(40, 0, $font_file, $gmp_text);
            $x_gmp = $width / 2 - ($bbox_gmp[2] - $bbox_gmp[0]) / 2;
            imagettftext($im, 40, 0, $x_gmp, 480, $gmp_color, $font_file, $gmp_text);

        } else {
            // Fallback to basic font
            $font = 5; // Built-in font 5 is typically ~13px width
            // Rough Centering Logic for generic font
            $img_width = imagesx($im);
            $img_width = imagesx($im);

            $x_name = ($img_width - (strlen($ipo_name) * imagefontwidth($font) * 2)) / 2; // Scale x2 rough
            // GD scaling is manual, so we just center normal font 5 string
            $x_name = ($img_width - strlen($ipo_name) * imagefontwidth($font)) / 2;
            imagestring($im, $font, $x_name, 280, $ipo_name, $white);

            $x_st = ($img_width - strlen($status_text) * imagefontwidth($font)) / 2;
            imagestring($im, $font, $x_st, 200, $status_text, $grey);

            $x_gmp = ($img_width - strlen($gmp_text) * imagefontwidth($font)) / 2;
            imagestring($im, $font, $x_gmp, 360, $gmp_text, $gmp_color);
        }

        // 5. Save Logic
        $upload_dir = wp_upload_dir();
        $filename = 'ipo-ai-' . $post_id . '-thumb.png';
        $file_path = $upload_dir['path'] . '/' . $filename;

        imagepng($im, $file_path);
        imagedestroy($im);

        // 6. insert Attachment
        $attachment = array(
            'guid' => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => 'image/png',
            'post_title' => 'SEO Thumbnail for ' . $data['name'],
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        set_post_thumbnail($post_id, $attach_id);

        return $attach_id;
    }
}
