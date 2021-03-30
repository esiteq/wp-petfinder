<?php
/**
 * Plugin Name: WP Petfinder
 * Plugin URI: https://wp.esiteq.com/wp-petfinder/
 * Description: Integrates Petfinder database with your Wordpress site via API v2
 * Author: ESITEQ
 * Version: 0.6
 * Requires at least: 4.0
 * Tested up to: 5.4
 * Text Domain: wppf
 * Domain Path: /languages
 * Author URI: https://www.esiteq.com
 */

define('WPPF_DIR', realpath(dirname(__file__)));
require_once WPPF_DIR. '/inc/vafpress-framework/bootstrap.php';
require_once WPPF_DIR. '/inc/class-wp-petfinder.php';
require_once WPPF_DIR. '/inc/petfinder-api-v2.php';
// Widgets
require_once WPPF_DIR. '/inc/widgets/animals-from-shelter.php';
require_once WPPF_DIR. '/inc/widgets/search-form.php';
register_activation_hook(__file__, 'wppf_activate');

/**
 * wppf_activate()
 * Plugin activation hook
 * @return void
 */
function wppf_activate()
{
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $options = get_option('wppf_options', []);
    //results_page
    if (!isset($options['results_page']) || $options['results_page'] == 0)
    {
        $page =
        [
            'post_title'    => __('Animal Search Results', 'wppf'),
            'post_content'  => '[pf_search_results]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        ];
        $post_id = wp_insert_post($page);
        if (is_numeric($post_id))
        {
            $options['results_page'] = $post_id;
            update_option('wppf_options', $options, 'yes');
        }
    }
    //
    if (!isset($options['animal_page']) || $options['animal_page'] == 0)
    {
        $page =
        [
            'post_title'    => __('Animal Details', 'wppf'),
            'post_content'  => '[pf_details]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        ];
        $post_id = wp_insert_post($page);
        if (is_numeric($post_id))
        {
            $options['animal_page'] = $post_id;
            update_option('wppf_options', $options, 'yes');
        }
    }
    //
    if (!isset($options['adopt_page']) || $options['adopt_page'] == 0)
    {
        $page =
        [
            'post_title'    => __('Adopt Me', 'wppf'),
            'post_content'  => 'Put your Pet Adoption form here. You can use any form builder plugin, such as Contact Form 7.',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        ];
        $post_id = wp_insert_post($page);
        if (is_numeric($post_id))
        {
            $options['adopt_page_dog'] = $post_id;
            $options['adopt_page_cat'] = $post_id;
            update_option('wppf_options', $options, 'yes');
        }
    }
    //
    /*
    $cache_dir = wp_upload_dir()['basedir']. '/wp-petfinder';
    @mkdir($cache_dir, 0777);
    */
    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpps_cache` (`cache_key` char(100) NOT NULL, `contents` text NOT NULL, `expires` int(12) NOT NULL, PRIMARY KEY (`cache_key`))";
    dbDelta($sql);
}

/**
 * wppf()
 * Creates WP_Petfinder class, stores it in global variable and returns its instance
 * @return
 */
function wppf()
{
    global $_wppf;
    if (!isset($_wppf))
    {
        $_wppf = new \WP_Petfinder\WP_Petfinder;
    }
    return $_wppf;
}

wppf();
?>