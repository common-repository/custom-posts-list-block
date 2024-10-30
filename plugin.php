<?php
/**
 * Plugin Name: Custom Posts List Block
 * Description: Provides a Gutenberg Block to list posts based on Selected category.
 * Author: Chintan Makwana
 * Author URI: https://github.com/ChintanMakwana/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CustomPostsList
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
