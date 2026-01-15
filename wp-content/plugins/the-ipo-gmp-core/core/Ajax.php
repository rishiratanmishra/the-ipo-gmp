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
                // Search is Global: Ignore status filters
            } else {
                // Not Searching: Apply Filters
                if ($filter_status !== 'all') {
                    if ($filter_status === 'active') {
                        $where .= " AND (type LIKE '%Open%')"; // Type is usually 'Open'
                    } elseif ($filter_status === 'upcoming') {
                        $where .= " AND (type LIKE '%Upcoming%')";
                    } elseif ($filter_status === 'closed' || $filter_status === 'pre-listing') {
                        $where .= " AND (type LIKE '%Closed%')";
                    }
                }
            }

            $items = $wpdb->get_results("SELECT * FROM $t_buybacks WHERE $where ORDER BY period DESC LIMIT $limit OFFSET $offset");
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
            if ($context === 'buyback') {
                $html .= '<div class="col-span-full py-12 text-center text-slate-500 font-medium">No records found matching your criteria.</div>';
            } else {
                $html .= '<tr><td colspan="5" class="py-12 text-center text-slate-500">No records found.</td></tr>';
            }
        }

        wp_send_json_success([
            'html' => $html,
            'total_pages' => ceil($total_items / $limit),
            'current_page' => $paged
        ]);
    }

    private function render_row($item, $context)
    {
        // ------------------------------------------
        // RENDER CARD (Buyback)
        // ------------------------------------------
        if ($context === 'buyback') {
            $offer_price = (float) preg_replace('/[^0-9.]/', '', $item->price);
            $mkt_price = (float) preg_replace('/[^0-9.]/', '', $item->market_price);
            $premium = 0;
            if ($mkt_price > 0 && $offer_price > 0) {
                $premium = round((($offer_price - $mkt_price) / $mkt_price) * 100, 1);
            }
            $status_color = stripos($item->status, 'close') !== false ? 'text-red-400 border-red-400/20 bg-red-400/10' : 'text-emerald-400 border-emerald-400/20 bg-emerald-400/10';

            $logo = !empty($item->logo) ?
                '<img src="' . esc_url($item->logo) . '" alt="' . esc_attr($item->company) . '" class="w-full h-full object-contain">' :
                '<span class="text-slate-900 font-bold">' . substr($item->company, 0, 1) . '</span>';

            $premium_txt = $premium > 0 ? '+' . $premium . '%' : '0%';
            $premium_class = $premium > 0 ? 'text-neon-emerald' : 'text-slate-400';

            return '
            <div class="bg-card-dark border border-border-navy rounded-2xl p-6 hover:border-primary/50 transition-all group relative overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white p-1 flex items-center justify-center overflow-hidden shrink-0">
                            ' . $logo . '
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base leading-tight group-hover:text-primary transition-colors max-w-[150px] line-clamp-2">
                                ' . esc_html($item->company) . '
                            </h3>
                            <span class="text-[10px] uppercase font-bold text-slate-500 mt-1 block">
                                ' . esc_html($item->type) . '
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-slate-500">Premium</div>
                        <div class="text-lg font-black ' . $premium_class . '">
                            ' . $premium_txt . '
                        </div>
                    </div>
                </div>

                <!-- Data Grid -->
                <div class="grid grid-cols-2 gap-y-4 gap-x-2 py-4 border-t border-dashed border-slate-800 text-sm">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Buyback Price</div>
                        <div class="text-white font-bold">₹' . esc_html($item->price) . '</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Market Price</div>
                        <div class="text-slate-300 font-medium">₹' . esc_html($item->market_price ?: '-') . '</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Issue Size</div>
                        <div class="text-slate-300 font-medium">' . esc_html($item->issue_size) . '</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Shares</div>
                        <div class="text-slate-300 font-medium">' . esc_html($item->shares) . '</div>
                    </div>
                    <div class="col-span-2 border-t border-dashed border-slate-800 pt-2 mt-1">
                        <div class="text-[10px] text-slate-500 uppercase font-bold">Tender Period</div>
                        <div class="text-white font-medium text-xs">' .
                (function ($period) {
                    if (empty($period))
                        return 'Dates TBA';
                    preg_match_all('/\d{4}-\d{2}-\d{2}/', $period, $matches);
                    if (!empty($matches[0]) && count($matches[0]) >= 2) {
                        return date('d M \'y', strtotime($matches[0][0])) . ' - ' . date('d M \'y', strtotime($matches[0][1]));
                    }
                    return esc_html($period);
                })($item->period)
                . '</div>
                    </div>
                </div>

                <!-- Footer / Dates -->
                <div class="mt-auto pt-4 border-t border-slate-800 flex justify-between items-end">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase font-bold mb-0.5">Record Date</div>
                        <div class="text-white font-bold text-xs">' . esc_html($item->record_date ?: 'TBA') . '</div>
                    </div>
                    <div class="px-2 py-1 rounded text-[10px] font-bold uppercase border ' . $status_color . '">
                        ' . esc_html($item->status) . '
                    </div>
                </div>
            </div>';
        }

        // ------------------------------------------
        // RENDER ROW (Standard IPO)
        // ------------------------------------------
        $name = $item->name;
        $col2 = $item->price_band;

        $gmp_val = $item->premium ?: '0';
        $gmp_clean = (float) preg_replace('/[^0-9.-]/', '', $gmp_val);
        $is_neg = $gmp_clean < 0;
        $col3 = ($is_neg ? '- ₹' . abs($gmp_clean) : '+ ₹' . $gmp_val);
        $col3_class = $is_neg ? 'text-red-400' : 'text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10';

        $col4 = date('M j', strtotime($item->open_date)) . ' - ' . date('M j', strtotime($item->close_date));
        $col5 = $item->status;
        $link = home_url('/ipo-details/?slug=' . $item->slug);

        $icon = !empty($item->icon_url) ?
            '<img src="' . esc_url($item->icon_url) . '" alt="' . esc_attr($name) . '" class="w-full h-full object-contain" />' :
            substr($name, 0, 1);
        $icon_html = '<div class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">' . $icon . '</div>';

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
            <td class="px-6 py-4 text-sm font-black ' . $col3_class . '">' . esc_html($col3) . '</td>
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
