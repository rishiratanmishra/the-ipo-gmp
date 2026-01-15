<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Router Class
 * Handles Query Variables registration.
 * Since we kept the URL structure /ipo-details/?slug=xyz, we don't strictly need Rewrite Rules,
 * but proper query var registration is good practice.
 */
class Router
{

    public function __construct()
    {
        add_filter('query_vars', [$this, 'register_query_vars']);
    }

    /**
     * Register 'slug' (or specific 'ipo_slug') as a query var 
     * so get_query_var() works reliably if we ever switch to rewrites.
     */
    public function register_query_vars($vars)
    {
        $vars[] = 'slug'; // The existing param used
        return $vars;
    }
}
