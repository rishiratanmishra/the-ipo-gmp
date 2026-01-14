<?php

class IPO_AI_Logger
{
    public static function log($message, $data = null)
    {
        // Force logging for debugging
        // if (!defined('WP_DEBUG') || !WP_DEBUG) {
        //     return;
        // }

        $log_entry = date('Y-m-d H:i:s') . " [IPO AI]: " . $message;
        if ($data) {
            $log_entry .= " Data: " . print_r($data, true);
        }

        $log_file = WP_CONTENT_DIR . '/ipo-ai-debug.log';

        // Log Rotation: Check if file > 1MB
        if (file_exists($log_file) && filesize($log_file) > 1 * 1024 * 1024) {
            // Keep last 50 lines or just truncate
            $content = file_get_contents($log_file);
            $lines = explode(PHP_EOL, $content);
            $keep = array_slice($lines, -50); // Keep last 50 lines
            file_put_contents($log_file, implode(PHP_EOL, $keep) . PHP_EOL);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " [Log Rotated] File truncated." . PHP_EOL, FILE_APPEND);
        }

        file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
    }
}
