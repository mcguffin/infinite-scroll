<?php
/**
 * Plugin Name: Infinite Scroll
 * Plugin URI: https://github.com/mcguffin/infinite-scroll
 * Description: Enable Infinite scroll for Post Template Blocks inside a query loop
 * Author: mcguffin
 * Author URI: https://github.com/mcguffin
 * Version: 0.0.2
 * Requires PHP: 7.4
 * Text Domain: infinite-scroll
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI: https://github.com/mcguffin/infinite-scroll/raw/main/.wp-release-info.json
 */


namespace InfiniteScroll;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'include/autoload.php';

$is = Core\InfiniteScroll::get()->init_plugin(__FILE__);

// Enable WP auto update
add_filter( 'update_plugins_github.com', function( $update, $plugin_data, $plugin_file, $locales ) {

	if ( ! preg_match( "@{$plugin_file}$@", __FILE__ ) ) { // not our plugin
		return $update;
	}

	$response = wp_remote_get( $plugin_data['UpdateURI'] );

	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) > 200 ) { // response error
		return $update;
	}

	return json_decode( wp_remote_retrieve_body( $response ), true, 512 );
}, 10, 4 );