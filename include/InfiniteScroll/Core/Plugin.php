<?php

namespace InfiniteScroll\Core;

abstract class Plugin extends Singleton {

	protected $plugin_file;

	final public function init_plugin($plugin_file) {
		$this->plugin_file = $plugin_file;
		return $this;
	}

	public function __get($what) {
		switch ( $what ) {
			case 'dir_url':
				return plugin_dir_url($this->plugin_file);
			case 'dir_path':
				return plugin_dir_path($this->plugin_file);
			case 'file':
				return str_replace(WP_PLUGIN_DIR.'/','',$this->plugin_file);
			case 'slug':
				return pathinfo($this->file,PATHINFO_DIRNAME);
		}
	}

}