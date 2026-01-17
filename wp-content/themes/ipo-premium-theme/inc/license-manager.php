<?php
/**
 * IPO Premium License Manager
 *
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

class IPO_License_Manager
{

    public function __construct()
    {
        add_action('customize_register', [$this, 'register_license_control']);
        add_action('admin_notices', [$this, 'license_notice']);
    }

    /**
     * Register License Field in Customizer
     */
    public function register_license_control($wp_customize)
    {
        $wp_customize->add_section('ipopro_license_section', [
            'title' => __('Theme License', 'ipo-premium'),
            'priority' => 1, // Top Priority
        ]);

        $wp_customize->add_setting('ipopro_license_key', [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage', // No refresh logic yet
        ]);

        $wp_customize->add_control('ipopro_license_key', [
            'label' => __('Enter License Key', 'ipo-premium'),
            'description' => __('Enter your purchase key to unlock automatic updates and One-Click Import.', 'ipo-premium'),
            'section' => 'ipopro_license_section',
            'type' => 'text',
        ]);
    }

    /**
     * Activate License (Mock Remote Check)
     */
    public static function activate_license($key)
    {
        $key = trim($key);

        // 1. Basic Length Check
        if (strlen($key) < 8) {
            return ['success' => false, 'message' => __('License key is too short. Please check your purchase receipt.', 'ipo-premium')];
        }

        // 2. Mock Remote Validation (Use prefix 'IPO' or 'TEST' for valid keys)
        // In production, this would be: $response = wp_remote_post('https://api.zolaha.com/verify'...);
        $valid_prefixes = ['IPO-', 'TEST-', 'PREM-'];
        $is_valid_format = false;
        foreach ($valid_prefixes as $prefix) {
            if (strpos(strtoupper($key), $prefix) === 0) {
                $is_valid_format = true;
                break;
            }
        }

        if (!$is_valid_format) {
            return ['success' => false, 'message' => __('Invalid license format. Key must start with IPO- or PREM-', 'ipo-premium')];
        }

        // Success
        return ['success' => true, 'message' => __('License successfully activated! Features unlocked.', 'ipo-premium')];
    }

    /**
     * Check License Status (Helper)
     */
    public static function is_active()
    {
        $key = get_theme_mod('ipopro_license_key');
        $check = self::activate_license($key);
        return $check['success'];
    }

    /**
     * Admin Notice for Inactive License
     */
    public function license_notice()
    {
        if (!self::is_active() && !isset($_GET['customize_changeset_uuid'])) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>
                        <?php _e('IPO Premium Theme:', 'ipo-premium'); ?>
                    </strong>
                    <?php _e('Please activate your license to unlock full features.', 'ipo-premium'); ?>
                    <a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=ipopro_license_section')); ?>">
                        <?php _e('Enter License Key', 'ipo-premium'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}

new IPO_License_Manager();
