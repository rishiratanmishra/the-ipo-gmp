<?php
namespace TIGC\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Ajax
{

    public function __construct()
    {
        add_action('wp_ajax_tigc_filter_ipos', [$this, 'filter_ipos']);
        add_action('wp_ajax_nopriv_tigc_filter_ipos', [$this, 'filter_ipos']);
    }

    public function filter_ipos()
    {
        // checks nonce
        // check_ajax_referer('tigc_nonce', 'nonce'); // TODO: Add nonce to template

        global $wpdb;
        $t_master = $wpdb->prefix . 'ipomaster';
        $t_buybacks = $wpdb->prefix . 'buybacks';

        $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $limit = 20; // Per page
        $offset = ($paged - 1) * $limit;

        $context = isset($_POST['context']) ? sanitize_text_field($_POST['context']) : 'mainboard';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $filter_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';

        $items = [];
        $total_items = 0;

        if ($context === 'buyback') {
            $where = "1=1";
            if (!empty($search)) {
                $where .= $wpdb->prepare(" AND company LIKE %s", '%' . $wpdb->esc_like($search) . '%');
            }
            if ($filter_status !== 'all') {
                if ($filter_status === 'active') {
                    $where .= " AND (type LIKE '%Open%' OR type LIKE '%Upcoming%')";
                } elseif ($filter_status === 'closed') {
                    $where .= " AND (type LIKE '%Closed%')";
                }
            }

            $items = $wpdb->get_results("SELECT * FROM $t_buybacks WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $t_buybacks WHERE $where");

        } else {
            // Mainboard or SME
            $is_sme = ($context === 'sme') ? 1 : 0;
            $where = $wpdb->prepare("is_sme = %d", $is_sme);

            if (!empty($search)) {
                $where .= $wpdb->prepare(" AND name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
                // When searching, we ignore the status filter to make it "Global"
            } else {
                // Only apply status filter if NOT searching
                if ($filter_status !== 'all') {
                    if ($filter_status === 'active') {
                        // Strictly Open/Live
                        $where .= " AND (status = 'open' OR status LIKE '%live%')";
                    } elseif ($filter_status === 'pre-listing') {
                        // Closed / Allotment / Out (Not yet listed)
                        $where .= " AND (status IN ('close', 'closed', 'allotment') OR status LIKE '%out%') AND status NOT LIKE '%list%'";
                    } elseif ($filter_status === 'closed') {
                        // Actually Listed or just Closed
                        $where .= " AND (status LIKE '%list%' OR status IN ('close', 'closed'))";
                    } elseif ($filter_status === 'upcoming') {
                        $where .= " AND status = 'upcoming'";
                    }
                }
            }

            $items = $wpdb->get_results("SELECT * FROM $t_master WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE $where");
        }

        $html = '';
        if ($items) {
            foreach ($items as $item) {
                // Render Row (keeping logic consistent with archive-ipo.php)
                // We will return HTML to make it easier for frontend
                $html .= $this->render_row($item, $context);
            }
        } else {
            $html .= '<tr><td colspan="5" class="py-12 text-center text-slate-500">No records found.</td></tr>';
        }

        wp_send_json_success([
            'html' => $html,
            'total_pages' => ceil($total_items / $limit),
            'current_page' => $paged
        ]);
    }

    private function render_row($item, $context)
    {
        $link = '#';
        $status_class = '';

        if ($context === 'buyback') {
            $name = $item->company;
            $col2 = $item->type;
            $col3 = $item->price;
            $col4 = $item->issue_size;
            $col5 = 'Active';
        } else {
            $name = $item->name;
            $col2 = $item->price_band;
            $col3 = '+ ₹' . ($item->premium ?: '0');
            $col4 = date('M j', strtotime($item->open_date)) . ' - ' . date('M j', strtotime($item->close_date));
            $col5 = $item->status;
            $link = home_url('/ipo-details/?slug=' . $item->slug);
            $status_class = strtolower($item->status);
        }

        // Output logic similar to template
        // Note: Using output buffering or string concat

        $icon_html = '';
        if ($context !== 'buyback') {
            $icon = !empty($item->icon_url) ?
                '<img src="' . esc_url($item->icon_url) . '" alt="' . esc_attr($name) . '" class="w-full h-full object-contain" />' :
                substr($name, 0, 1);

            $icon_html = '<div class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">' . $icon . '</div>';
        }

        return '
        <tr class="group hover:bg-slate-800/30 transition-colors cursor-pointer" onclick="window.location.href=\'' . esc_url($link) . '\'">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    ' . $icon_html . '
                    <div>
                        <p class="text-sm font-bold text-white group-hover:text-primary transition-colors">' . esc_html($name) . '</p>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium text-slate-300">' . esc_html($col2) . '</td>
            <td class="px-6 py-4 text-sm font-black text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10">' . esc_html($col3) . '</td>
            <td class="px-6 py-4 text-sm font-medium text-slate-300">' . esc_html($col4) . '</td>
            <td class="px-6 py-4">
                <span class="flex items-center gap-1.5 text-xs font-bold text-primary">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                    ' . esc_html($col5) . '
                </span>
            </td>
        </tr>';
    }
}
