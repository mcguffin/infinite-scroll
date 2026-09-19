<?php
/**
 * Plugin Name: Infinite Scroll
 * Plugin URI: https://github.com/mcguffin/infinite-scroll
 * Description: Enable Infinite scroll for Post Template Blocks inside a query loop
 * Author: mcguffin
 * Author URI: https://github.com/mcguffin
 * Version: 0.0.0
 * Requires PHP: 7.4
 * Text Domain: infinite-scroll
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 */


namespace InfiniteScroll;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'include/autoload.php';

$is = Core\InfiniteScroll::get()->init_plugin(__FILE__);
