<?php

namespace InfiniteScroll\Core;

use WP_Query;

class InfiniteScroll extends Plugin {

	/**
	 * Setup hooks
	 */
	protected function __construct(...$args) {
		add_action('wp_ajax_load_more', [ $this, 'ajax_load_more' ] );
		add_action('wp_ajax_nopriv_load_more', [ $this, 'ajax_load_more' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_action( 'register_block_type_args', [ $this, 'register_block_type_args' ], 10, 2 );
		add_filter('render_block_core/post-template', [ $this, 'render_post_template_block' ], 10, 3 );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_scripts() {
		$asset_file = include $this->dir_path . 'build/front.asset.php';

		wp_enqueue_script(
			'infinite-scroll',
			$this->dir_url . 'build/front.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			[
				'strategy' => 'async',
				'in_footer' => true,
			]
		);

		wp_enqueue_style(
			'infinite-scroll',
			$this->dir_url . 'build/front.css',
			[],
			$asset_file['version']
		);
	}

	/**
	 * Enqueue edit scripts
	 */
	public function enqueue_block_editor_assets() {
		$asset_file = include $this->dir_path . 'build/index.asset.php';

		wp_enqueue_script(
			'infinite-scroll-editor',
			$this->dir_url . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version']
		);
	}

	/**
	 * Add isInfiniteScroll Property to PostTemplate Block
	 */
	public function register_block_type_args($args, $block_type) {
		if ( $block_type === 'core/post-template') {
			$args['attributes']['isInfiniteScroll'] = array(
				'type'    => 'boolean',
				'default' => false,
			);
		}
		return $args;
	}


	/**
	 * Render Post Template Block
	 */
	public function render_post_template_block($block_content, $parsed_block, $block) {
		if ( $block->name === 'core/post-template' ) {
			if ( $block->attributes['isInfiniteScroll'] ) {
				$page  = (int) isset( $query['paged'] ) ? $query['paged'] + 1 : 2;
				$q     = new WP_Query( build_query_vars_from_query_block($block, 1 ) );

				if ( $page <= $q->max_num_pages ) {
					$query = $block->context['query'];
					// store query
					$query_key = 'infinite-scroll-query-' . md5( serialize( $query ) );
					if ( ! get_transient( $query_key ) ) {
						set_transient( $query_key, $query );
					}
					// store post template block
					$block_key = 'infinite-scroll-block-' . md5( serialize( $parsed_block ) );
					if ( ! get_transient( $block_key ) ) {
						set_transient( $block_key, $parsed_block );
					}
					$block_content = str_replace( '</ul>', '<li class="infinite-scroll-trigger" data-query-key="'.$query_key.'" data-block-key="'.$block_key.'" data-page="'.$page.'"></li></ul>', $block_content );
				}
			}
		}
		return $block_content;
	}

	/**
	 * Ajax response
	 */
	public function ajax_load_more() {
		// setup query
		// render block
		foreach ( ['query','page','block'] as $prop ) {
			if ( ! isset( $_REQUEST[$prop] ) ) {
				http_response_code(400);
				exit();
			} else {
				$$prop = wp_unslash( $_REQUEST[$prop] );
			}
		}

		// could this happen?
		if ( ! ( $query = get_transient($query) ) ) {
			http_response_code(410);
			exit();
		}
		if ( ! ( $block = get_transient($block) ) ) {
			http_response_code(410);
			exit();
		}

		// setup query
		// $query['paged'] = (int) $page;
		$_GET['query-page'] = $page;

		add_filter('render_block_context', function( $context, $parsed_block, $parent_block ) use ( $query ) {
			$context['query'] = $query;
			return $context;
		}, 10, 3);
		echo render_block($block);
	}
}