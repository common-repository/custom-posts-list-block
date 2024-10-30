<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CustomPostsList
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function cm_custom_posts_list_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'cm_custom_posts_list-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'cm_custom_posts_list-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-server-side-render' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'cm_custom_posts_list-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cmplGlobal` object.
	wp_localize_script(
		'cm_custom_posts_list-block-js',
		'cmplGlobal', // Array containing dynamic data for a JS Global.
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
			// Add more data here that you want to access from `cmplGlobal` object.
			'site_url'		=> site_url()
		]
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'cm/custom-posts-list', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'cm_custom_posts_list-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'cm_custom_posts_list-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'cm_custom_posts_list-block-editor-css',
			'render_callback' => 'cm_custom_posts_list_render_callback',
			'api_version' => 2,
			'attributes'      => array(
				'categories'    => array(
					'type'      => 'object'
				),
				'selectedCategory' => array(
					'type'      => 'integer',
					'default'   => 1,
				),
				'postsPerPage' => array(
					'type'      => 'integer'
				),
				'showImage'    => array(
					'type'      => 'boolean',
					'default'	=> 1
				),
				'showCategoryList' => array(
					'type'      => 'boolean',
					'default'	=> 1
				),
				'showExcerpt' => array(
					'type'      => 'boolean',
					'default'	=> 1
				),
				'showReadMore' => array(
					'type'      => 'boolean',
					'default'	=> 1
				),
				'excerptLength' => array(
					'type'      => 'integer',
					'default'	=> 50
				),
				'textAlign'		=> array(
					'type'      => 'string',
					'default'	=> 'left'
				),
				'bgColor'  		=> array(
					'type'		=> 'string',
					'default'	=> '#fff'
				)
			),
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'cm_custom_posts_list_block_assets' );

// Render Callback
function cm_custom_posts_list_render_callback( $attributes ){

	$posts = get_posts([
		'category' => $attributes['selectedCategory'],
		'posts_per_page' => $attributes['postsPerPage']
	]);

	ob_start();

	if( count( $posts ) ){

		$block_style = array();
		$block_style[] = 'background-color:'. $attributes['bgColor'];
		
		$block_style_attr = sprintf(' style="%s"', implode(';', $block_style));

		$block_classes = array();
		$block_classes[] = $attributes['className'];
		$block_classes[] = 'text-align-' . $attributes['textAlign'];
		
		$block_classes_attr = implode(' ', $block_classes );

		echo '<div class="cm-posts-list-wrapper '. esc_attr( $block_classes_attr ) .'" id="cm-posts-list-wrapper" '.esc_attr( $block_style_attr ).'>';
		foreach( $posts as $post ){
			
			$classes = implode( ' ', get_post_class('cm-posts-list-item') );

			echo '<div class="'. esc_attr( $classes ) .'" id="post-'. esc_attr( get_the_ID() ) .'">';

			echo '<p><label>'. esc_attr( $post->post_title ) .'</label></p>';			
			if( $attributes['showCategoryList'] ){
				echo '<p>'. esc_attr( get_the_category_list(', ', '', $post->ID) ) . '</p>';
			}
			if( $attributes['showImage'] && has_post_thumbnail( $post->ID ) ){
				echo '<p>'. esc_attr( get_the_post_thumbnail($post->ID, 'post-thumbnail') ) . '</p>';
			}

			if( $attributes['showExcerpt'] ){
				$content = substr( $post->post_excerpt, 0, $attributes['excerptLength'] );

				echo '<p>'. esc_attr( $content ) .'...</p>';
			}

			if( $attributes['showReadMore'] ){
				echo '<p><a href="'. esc_url( get_permalink( $post->ID ) ) .'">Read More</a></p>';
			}
			echo '</div>';
		}	
		echo '</div>';

	} else {
		echo '<p>No posts found.</p>';
	}

	return ob_get_clean();
}

