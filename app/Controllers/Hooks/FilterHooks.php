<?php

class FilterHooks {
	public function register() {
		add_filter( 'upload_mimes', [ __CLASS__, 'fileTypes' ] );
	}

	public static function fileTypes($file_types) {
		$new_filetypes        = [];
		$new_filetypes['svg'] = 'image/svg+xml';
		$file_types           = array_merge( $file_types, $new_filetypes );

		return $file_types;
	}
}
