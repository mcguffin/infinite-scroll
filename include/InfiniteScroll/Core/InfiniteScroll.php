<?php

namespace InfiniteScroll\Core;

use DOMDocument;
use DOMElement;
use WP_Block_Styles_Registry;
use WP_Query;
use WP_HTML_Tag_Processor;

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
		wp_add_inline_script('infinite-scroll-editor',
			sprintf(
				'const infiniteScrollEdit = %s',
				wp_json_encode([
					'buttonStyles' => array_values( WP_Block_Styles_Registry::get_instance()->get_registered_styles_for_block('core/button') )
				])
			)
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
			$args['attributes']['loadTrigger'] = array(
				'type'    => 'string',
				'enum'    => ['scroll','button'],
				'default' => 'ghost',
			);
			$args['attributes']['buttonStyle'] = array(
				'type'    => 'string',
				'enum'    => [],
				'default' => 'fill'
			);
			$args['attributes']['buttonLabel'] = array(
				'type'    => 'string',
				'default' => __('Load more','infinite-scroll'),
			);
			$args['attributes']['loadPlaceholder'] = array(
				'type'    => 'string',
				'enum'    => ['none','ghost','spinner'],
				'default' => 'ghost',
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
				$query = $block->context['query'];
				$page  = (int) isset( $query['paged'] ) ? $query['paged'] + 1 : 2;
				$q     = new WP_Query( build_query_vars_from_query_block($block, 1 ) );

				if ( $page <= $q->max_num_pages ) {
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
					if ( ! get_transient( "{$block_key}-uid" ) ) {
						set_transient( "{$block_key}-uid", (int) wp_unique_id() );
					}
					$block_content = str_replace( '</ul>', $this->get_more_trigger( $block, $parsed_block, $block_content ) . '</ul>', $block_content );
				}
			}
		}
		return $block_content;
	}

	private function get_more_trigger( $block, $parsed_block, $block_content ) {
		$query     = $block->context['query'];
		$page      = (int) isset( $query['paged'] ) ? $query['paged'] + 1 : 2;
		$query_key = 'infinite-scroll-query-' . md5( serialize( $query ) );
		$block_key = 'infinite-scroll-block-' . md5( serialize( $parsed_block ) );

		if ( 'button' === $block->attributes['loadTrigger'] ) {

			$btn_html = sprintf(
				'<div class="wp-block-button is-style-%1$s"><button type="button" class="wp-block-button__link wp-element-button infinite-scroll-button">%2$s</button></div>',
				$block->attributes['buttonStyle'],
				$block->attributes['buttonLabel']
			);
			$btn = render_block([
				'blockName' => 'core/buttons',
				'attrs'     => [
					'layout' =>
					[
						'type' => 'flex',
						'justifyContent' => 'center',
					],
				],
				'innerBlocks' => [
					[
						'blockName' => 'core/button',
						'attrs'     => [
							'tagName'   => 'button',
							'className' => sprintf(
								'is-style-%s',
								$block->attributes['buttonStyle']
							),
							'type'      => 'button',
						],
						'innerBlocks' => [],
						'innerHTML' => $btn_html,
						'innerContent' => [
							$btn_html
						],
					],
				],
				'innerHTML' => '<div class="wp-block-buttons"></div>',
				'innerContent' => [
					'<div class="wp-block-buttons">',
					NULL,
					'</div>'
				],
			]);

			return sprintf(
				'<li class="infinite-scroll-container" data-query-key="%1$s" data-block-key="%2$s" data-page="%3$d">%4$s%5$s</li>',
				$query_key,
				$block_key,
				$page,
				$btn,
				$this->get_placeholder($block, $block_content)
			);
		} else {
			return sprintf(
				'<li class="infinite-scroll-container infinite-scroll-trigger" data-query-key="%1$s" data-block-key="%2$s" data-page="%3$d">%4$s</li>',
				$query_key,
				$block_key,
				$page,
				$this->get_placeholder($block, $block_content)
			);
		}
	}

	private function get_placeholder($block, $block_content) {
		if ( 'ghost' === $block->attributes['loadPlaceholder'] ) {
			$ghost = '';
			$doc = new DOMDocument('1.0','UTF-8');
			$doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$block_content);
			$lis = $doc->getElementsByTagName('li');
			$li = $lis->item(0);
			$li->setAttribute(
				'class',
				$li->getAttribute('class') . ' infinite-scroll-placeholder infinite-scroll-ghost'
			);
			foreach ( ['h1','h2','h3','h4','h5','h6','p'] as $tagname ) {
				foreach ( $li->getElementsByTagName($tagname) as $el ) {
					$wrap = $doc->createElement('span',$el->textContent);
					$wrap->setAttribute('class','infinite-scroll-text');
					foreach ($el->childNodes as $child ) {
						$child->remove();
					}
					$el->append($wrap);
				}
			}

			$ghost = $doc->saveHTML($li);

			// preg_match('/<li()>(.*)</li>/imsU')
			return sprintf(
				'<template class="infinite-scroll-placeholder-template">%s</template>',
				str_repeat($ghost,(int) $block->context['query']['perPage'])
			);
		} else if ( 'spinner' === $block->attributes['loadPlaceholder'] ) {
			return sprintf(
				'<template class="infinite-scroll-placeholder-template"><li class="infinite-scroll-container infinite-scroll-placeholder">%s</li></template>',
				apply_filters('infinite-scroll/spinner', '<div class="infinite-scroll-spinner"></div>' )
			);
		}
		return '';
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
		$uid = (int) get_transient("{$block}-uid");
		if ( ! ( $block = get_transient($block) ) ) {
			http_response_code(410);
			exit();
		}

		while (( (int) wp_unique_id()) < ($uid*($page-1)) );

		global $id_counter;
		$id_counter = $page * 1000;
		$_GET['query-page'] = $page;
		$styles_block = null;
		add_filter( 'render_block_data', 'wp_render_block_style_variation_support_styles', 10, 2 );
		add_filter( 'render_block', 'wp_render_block_style_variation_class_name', 10, 2 );
		add_filter('render_block_context', function( $context, $parsed_block, $parent_block ) use ( $query, $page, &$styles_block ) {
			$query['paged']   = $page;
			$context['query'] = $query;
			$styles_block     = $parsed_block;
			return $context;
		}, 10, 3);
		echo render_block($block)
			. sprintf(
				'<style>%s</style>',
				implode("\n",wp_styles()->get_data('block-style-variation-styles','after'))
			);
	}
}
