<?php


namespace InfiniteScroll\Core;

abstract class Singleton {
	private static $instance = null;

	final public static function get(...$args) {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static(...$args);
		}
		return static::$instance;
	}

	abstract protected function __construct(...$args);

}