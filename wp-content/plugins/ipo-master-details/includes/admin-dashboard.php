<?php
if (!defined('ABSPATH')) exit;

/**
 * Renders the Admin Dashboard for IPO Details Pro.
 * Displays statistics, coverage metrics, and full data table with search.
 */
function ipodetails_admin_page() {
    global $wpdb;

    // --- Statistics ---
    $count = $wpdb->get_var("SELECT COUNT(*) FROM " . IPOD_TABLE);
    $last  = $wpdb->get_var("SELECT MAX(fetched_at) FROM " . IPOD_TABLE);
    
    // --- Coverage Calculation (Master vs Details) ---
    $total_master = $wpdb->get_var("SELECT COUNT(*) FROM " . IPOD_MASTER . " WHERE status != 'Closed' OR id IN (SELECT ipo_id FROM ".IPOD_TABLE.")");
    $coverage_percent = $total_master > 0 ? round(($count / $total_master) * 100) : 0;

    // --- Table Filtering Logic ---
    $paged = max(1, intval($_GET['paged'] ?? 1));
    $limit = 20;
    $offset = ($paged - 1) * $limit;
    $search = isset($_GET['s']) ? trim($_GET['s']) : '';

    $where = "WHERE 1=1";
    if (!empty($search)) {
        $where .= $wpdb->prepare(" AND m.name LIKE %s", "%" . $wpdb->esc_like($search) . "%");
    }

    // Query: Join Details with Master to get Names
    $sql = "SELECT d.*, m.name, m.status 
            FROM " . IPOD_TABLE . " d 
            LEFT JOIN " . IPOD_MASTER . " m ON d.ipo_id = m.id 
            $where 
            ORDER BY d.updated_at DESC 
            LIMIT %d OFFSET %d";

    $rows = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));

    // Total count for pagination
    $count_sql = "SELECT COUNT(*) 
                  FROM " . IPOD_TABLE . " d 
                  LEFT JOIN " . IPOD_MASTER . " m ON d.ipo_id = m.id 
                  $where";
    $total_items = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_items / $limit);

    ?>
    <style>
        .ipod-wrapper { max-width: 1200px; margin-top: 20px; }
        
        /* Header */
        .ipod-header { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #c3c4c7; display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .ipod-title h1 { margin: 0; font-size: 24px; color: #1d2327; font-weight: 700; }
        .ipod-status { display: flex; align-items: center; gap: 8px; background: #f0f6fc; padding: 5px 12px; border-radius: 20px; color: #1d2327; font-weight: 500; font-size: 12px; border: 1px solid rgba(0,0,0,0.05); }
        .dot { width: 8px; height: 8px; background: #00a32a; border-radius: 50%; display: inline-block; }

        /* Stats Grid */
        .ipod-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .ipod-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #c3c4c7; text-align: center; }
        .ipod-card h4 { margin: 0 0 10px; color: #646970; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        .ipod-card .num { font-size: 32px; font-weight: 700; color: #2271b1; }
        .ipod-card .sub { font-size: 12px; color: #a7aaad; margin-top: 5px; }

        .progress-bar { height: 6px; background: #f0f0f1; border-radius: 3px; overflow: hidden; margin-top: 8px; }
        .progress-fill { height: 100%; background: #2271b1; }

        /* Table Box */
        .ipod-table-box { background: #fff; border-radius: 8px; border: 1px solid #c3c4c7; overflow: hidden; }
        
        /* Table Controls (Search) */
        .ipod-controls { padding: 15px 20px; background: #f9f9f9; border-bottom: 1px solid #eaecf0; display: flex; justify-content: space-between; align-items: center; }
        .ipod-search-box { display: flex; gap: 10px; }
        .ipod-search-box input { height: 36px; line-height: 1; min-width: 250px; }
        .ipod-search-box .button { height: 36px; line-height: 1; display: flex; align-items: center; }
        
        /* Table */
        .ipod-table { width: 100%; border-collapse: collapse; }
        .ipod-table th { text-align: left; padding: 15px 20px; color: #1d2327; font-weight: 600; font-size: 13px; border-bottom: 2px solid #eaecf0; background: #fff; }
        .ipod-table td { padding: 12px 20px; border-bottom: 1px solid #eaecf0; color: #3c434a; font-size: 13px; vertical-align: middle; }
        .ipod-table tr:last-child td { border-bottom: none; }
        .ipod-table tr:hover { background: #fcfcfc; }
        
        .ipod-id { display: inline-block; background: #f0f0f1; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 11px; color: #646970; }
        .ipod-size { background: #f0f6fc; padding: 2px 8px; border-radius: 12px; color: #2271b1; font-size: 11px; font-weight: 500; }
        .ipod-time { color: #8c8f94; font-size: 12px; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-open { background: #e6ffed; color: #008a20; }
        .status-closed { background: #f6f7f7; color: #646970; }
        .status-upcoming { background: #fff8e5; color: #996800; }

        /* Modal */
        .ipod-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 99999; }
        .ipod-modal { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; width: 80%; max-width: 900px; height: 80%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .ipod-modal-header { padding: 15px 20px; border-bottom: 1px solid #eaecf0; background: #f9f9f9; display: flex; justify-content: space-between; align-items: center; font-weight: 600; }
        .ipod-modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #646970; }
        .ipod-modal-body { flex: 1; padding: 0; overflow: hidden; }
        .ipod-modal-body textarea { width: 100%; height: 100%; border: none; padding: 20px; font-family: monospace; font-size: 12px; resize: none; outline: none; background: #fcfcfc; color: #3c434a; }
        .ipod-btn-view { background: #2271b1; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; transition: 0.2s; }
        .ipod-btn-view:hover { background: #135e96; }

        /* Pagination */
        .ipod-pagination { padding: 15px 20px; border-top: 1px solid #eaecf0; background: #fff; display: flex; justify-content: space-between; align-items: center; }
        .page-links a, .page-links span { display: inline-block; padding: 4px 10px; margin-left: 4px; border-radius: 4px; background: #f0f0f1; color: #1d2327; text-decoration: none; font-size: 12px; }
        .page-links .current { background: #2271b1; color: #fff; }
        .page-links a:hover { background: #dcdcde; }
    </style>

    <div class="wrap ipod-wrapper">
        
        <div class="ipod-header">
            <div class="ipod-title">
                <h1>IPO Details Pro</h1>
                <p style="margin: 5px 0 0; color: #646970;">Automated Deep-Data Scraping Engine</p>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="<?php echo admin_url('admin-post.php?action=ipod_manual_batch'); ?>" class="button button-primary" style="display: flex; align-items: center; gap: 5px;">
                    <span class="dashicons dashicons-update" style="padding-top:2px"></span> Run Batch (15)
                </a>
                <div class="ipod-status">
                    <span class="dot"></span> System Active
                </div>
            </div>
        </div>

        <div class="ipod-stats">
            <div class="ipod-card">
                <h4>Total Details Stored</h4>
                <div class="num"><?php echo number_format($count); ?></div>
                <div class="sub">Records in DB</div>
            </div>
            <div class="ipod-card">
                <h4>Success Rate</h4>
                <div class="num"><?php echo $coverage_percent; ?>%</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $coverage_percent; ?>%"></div>
                </div>
            </div>
            <div class="ipod-card">
                <h4>Last Activity</h4>
                <div class="num" style="font-size: 18px; line-height: 32px; color: #1d2327;">
                    <?php echo $last ? time_ago($last) : 'Never'; ?>
                </div>
                <div class="sub"><?php echo $last; ?></div>
            </div>
        </div>

        <div class="ipod-table-box">
            <!-- Table Header with Search -->
            <div class="ipod-controls">
                <div style="font-weight: 600; color: #1d2327; font-size: 14px;">
                    All Scraped Details (<?php echo number_format($total_items); ?>)
                </div>
                <form method="GET" class="ipod-search-box">
                    <input type="hidden" name="page" value="ipo-details">
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search IPO Name...">
                    <button type="submit" class="button">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="<?php echo admin_url('admin.php?page=ipo-details'); ?>" class="button">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <table class="ipod-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>IPO Name / Slug</th>
                        <th>Master Status</th>
                        <th>JSON Size</th>
                        <th>Last Fetched</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($rows): ?>
                        <?php foreach($rows as $r): 
                            $size = strlen($r->details_json);
                            $size_kb = round($size / 1024, 1) . ' KB';
                            $status_class = 'status-closed';
                            $st = strtoupper($r->status);
                            if(strpos($st, 'OPEN') !== false) $status_class = 'status-open';
                            elseif(strpos($st, 'UPCOMING') !== false) $status_class = 'status-upcoming';
                            
                            // Safe Textarea Data
                            $safe_json = esc_textarea($r->details_json);
                        ?>
                            <tr>
                                <td><span class="ipod-id">#<?php echo $r->ipo_id; ?></span></td>
                                <td>
                                    <strong><?php echo esc_html($r->name ?: 'Unknown IPO'); ?></strong><br>
                                    <small style="color: #646970;"><?php echo esc_html($r->slug); ?></small>
                                </td>
                                <td>
                                    <?php if($r->name): ?>
                                        <span class="status-badge <?php echo $status_class; ?>"><?php echo esc_html($r->status); ?></span>
                                    <?php else: ?>
                                        <span style="color:#a7aaad; font-style:italic;">Deleted from Master</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="ipod-size"><?php echo $size_kb; ?></span></td>
                                <td class="ipod-time">
                                    <?php echo esc_html($r->fetched_at); ?><br>
                                    <span style="color:#a7aaad; font-size:11px;"><?php echo time_ago($r->fetched_at); ?></span>
                                </td>
                                <td>
                                    <button class="ipod-btn-view" onclick="openModal('<?php echo $r->ipo_id; ?>')">View Data</button>
                                    <!-- Hidden Data Store -->
                                    <textarea id="data-<?php echo $r->ipo_id; ?>" style="display:none;"><?php echo $safe_json; ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding: 30px; color: #a7aaad;">No matching records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="ipod-pagination">
                    <span style="color: #646970; font-size: 12px;">Showing page <?php echo $paged; ?> of <?php echo $total_pages; ?></span>
                    <div class="page-links">
                        <?php 
                        $range = 2; 
                        for($i=1; $i<=$total_pages; $i++):
                            if ($i==1 || $i==$total_pages || ($i >= $paged-$range && $i <= $paged+$range)):
                                $class = ($paged == $i) ? 'current' : '';
                                $url = add_query_arg(['paged'=>$i]);
                                echo "<a href='$url' class='$class'>$i</a>";
                            elseif ($i == $paged - $range - 1 || $i == $paged + $range + 1):
                                echo "<span>...</span>";
                            endif;
                        endfor; 
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Data Modal -->
    <div id="ipod-modal" class="ipod-modal-overlay">
        <div class="ipod-modal">
            <div class="ipod-modal-header">
                <span id="ipod-modal-title">IPO Details Data</span>
                <button class="ipod-modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="ipod-modal-body">
                <textarea id="ipod-modal-content" readonly></textarea>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            var raw = document.getElementById('data-' + id).value;
            var nice = raw;
            try {
                // Formatting JSON
                nice = JSON.stringify(JSON.parse(raw), null, 4);
            } catch(e) { console.error("Invalid JSON"); }
            
            document.getElementById('ipod-modal-content').value = nice;
            document.getElementById('ipod-modal-title').innerText = 'Data View: IPO #' + id;
            document.getElementById('ipod-modal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('ipod-modal').style.display = 'none';
        }
        // Close on outside click
        window.onclick = function(event) {
            var modal = document.getElementById('ipod-modal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <?php
}

/**
 * Helper function to calculate "Time Ago" string from a datetime.
 * Uses WordPress human_time_diff for localization and timezone correctness.
 *
 * @param string $datetime The datetime string.
 * @param bool $full Unused legacy parameter, kept for compatibility.
 * @return string Human-readable time ago string.
 */
function time_ago($datetime, $full = false) {
    if(!$datetime || $datetime === '0000-00-00 00:00:00') return "Never";
    
    // Parse the mysql time assuming it is in WP's configured timezone
    $from = strtotime($datetime); 
    
    if (!$from) return "Never";

    // current_time('timestamp') retrieves WP local timestamp
    $to = current_time('timestamp');
    
    // If the difference is very small (e.g. 0-60s), showing "1 min ago" via human_time_diff is fine,
    // but sometimes human_time_diff starts at "1 min".
    // Let's rely on human_time_diff as it is standard.
    
    $diff = human_time_diff($from, $to);
    
    return $diff . ' ago';
}

