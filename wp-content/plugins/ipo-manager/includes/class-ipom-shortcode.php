<?php
/**
 * IPOM Shortcode Class
 *
 * Handles frontend table rendering.
 *
 * @package IPO_Master_Admin
 */

if (!defined('ABSPATH')) exit;

class IPOM_Shortcode {

    public function __construct() {
        add_shortcode("ipo_master_table", [$this, "render"]);
    }

    public function render() {
        global $wpdb;
        $table = defined('IPOM_TABLE') ? IPOM_TABLE : $wpdb->prefix . 'ipomaster';

        $search = isset($_GET['ipo_search']) ? sanitize_text_field($_GET['ipo_search']) : "";
        $filter = isset($_GET['ipo_filter']) ? sanitize_text_field($_GET['ipo_filter']) : "";
        $paged = isset($_GET['ipo_page']) ? max(1, intval($_GET['ipo_page'])) : 1;
        $limit = 10;
        $offset = ($paged - 1) * $limit;

        $where = "WHERE 1=1";
        $args = [];

        if ($search) {
            $where .= " AND name LIKE %s";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }
        if ($filter == "sme") $where .= " AND is_sme=1";
        if ($filter == "mainboard") $where .= " AND is_sme=0";

        // Separate Count Query
        if (!empty($args)) {
            $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table $where", $args));
            $sql = $wpdb->prepare("SELECT * FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($args, [$limit, $offset]));
        } else {
            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
            $sql = $wpdb->prepare("SELECT * FROM $table $where ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
        }

        $rows = $wpdb->get_results($sql);
        $last = get_option("ipom_last_fetch", "Never");

        ob_start();
        ?>
        <div class="ipom-wrapper">
            <p><strong>Last Refetch:</strong> <?php echo esc_html($last); ?></p>

            <form method="GET" class="ipom-filter-form">
                <input type="text" name="ipo_search" value="<?php echo esc_attr($search); ?>" placeholder="Search IPO">
                <select name="ipo_filter">
                    <option value="">All</option>
                    <option value="sme" <?php selected($filter, "sme"); ?>>SME</option>
                    <option value="mainboard" <?php selected($filter, "mainboard"); ?>>Mainboard</option>
                </select>
                <button type="submit">Apply</button>
            </form>

            <div class="ipom-table-responsive">
                <table class="ipom-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Open</th>
                            <th>Close</th>
                            <th>Price</th>
                            <th>Premium</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows): foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo esc_html($r->name); ?></td>
                                <td><span class="ipom-badge <?php echo $r->is_sme ? 'sme' : 'main'; ?>"><?php echo $r->is_sme ? "SME" : "Main"; ?></span></td>
                                <td><?php echo esc_html($r->open_date); ?></td>
                                <td><?php echo esc_html($r->close_date); ?></td>
                                <td><?php echo esc_html($r->price_band); ?></td>
                                <td><?php echo esc_html($r->premium); ?></td>
                                <td><?php echo esc_html($r->status); ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7">No IPOs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $pages = ceil($total / $limit);
            if ($pages > 1) {
                echo '<div class="ipom-pagination">';
                for ($i = 1; $i <= $pages; $i++) {
                    $class = ($i == $paged) ? 'active' : '';
                    $link = add_query_arg(["ipo_page" => $i]);
                    echo "<a href='" . esc_url($link) . "' class='$class'>$i</a>";
                }
                echo '</div>';
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
