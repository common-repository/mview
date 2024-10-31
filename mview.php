<?php
/*
Plugin Name: mView
Plugin URI: http://mufeng.me
Description: mView plugin build for wordpress
Version: 1.0.3
Author: mufeng
Author URI: http://mufeng.me
*/

define ( 'MVIEW_VERSION' , '1.0.3' );
define ( 'MVIEW_SITE_URL' , site_url () );
define ( 'MVIEW_URL' , plugins_url ( '' , __FILE__ ) );
define ( 'MVIEW_PATH' , dirname ( __FILE__ ) );
define ( 'MVIEW_ADMIN_URL' , admin_url () );

/**
 * Create new database
 */
global $mView , $wpdb , $mview_table_name;
$mview_table_name = $wpdb->prefix . 'mview';

/*
 * Load base library
 */
require MVIEW_PATH . '/libs/core.php';

/*
 * Initialize
 */
$mView = new MView();

/*
 * Load basis function
 */
require MVIEW_PATH . '/libs/functions.php';

/*
 * Plugin activation, create new database
 */
register_activation_hook ( __FILE__ , 'mview_install' );
