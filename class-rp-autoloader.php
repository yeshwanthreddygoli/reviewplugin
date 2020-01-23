<?php

class RP_Autoloader {

	
	protected static $file_ext = '.php';

	
	protected static $path_top = __DIR__;

	
	protected static $namespaces = array();

	
	protected static $excluded_files = array( 'node_modules' );

	
	protected static $file_iterator = null;

	
	public static function loader( $class_name ) {

		if ( ! empty( static::$namespaces ) ) {
			$found = static::check_namespaces( $class_name );
			if ( ! $found ) {
				return $found;
			}
		}

		$directory = new RecursiveDirectoryIterator( static::$path_top . DIRECTORY_SEPARATOR . 'includes', RecursiveDirectoryIterator::SKIP_DOTS );
		require_once 'class-rp-recursive-filter.php';

		if ( is_null( static::$file_iterator ) ) {
			$iterator              = new RecursiveIteratorIterator(
				new rp_Recursive_Filter(
					$directory,
					array(
						'RP_Autoloader',
						'filter_excluded_files',
					)
				)
			);
			$regex                 = new RegexIterator( $iterator, '/^.+\.php$/i', RecursiveRegexIterator::MATCH );
			static::$file_iterator = iterator_to_array( $regex, false );
		}

		$filename = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . static::$file_ext;
		foreach ( static::$file_iterator as $file ) {

			if ( strtolower( $file->getFileName() ) === strtolower( $filename ) && is_readable( $file->getPathName() ) ) {
				require( $file->getPathName() );

				return true;
			}
		}
	}

	
	protected static function check_namespaces( $class_name ) {
		$found = false;
		foreach ( static::$namespaces as $namespace ) {
			if ( substr( $class_name, 0, strlen( $namespace ) ) === $namespace ) {
				$found = true;
			}
		}

		return $found;
	}

	
	public static function set_file_ext( $file_ext ) {
		static::$file_ext = $file_ext;
	}

	
	public static function set_plugins_path( $path ) {
		static::$plugins_path = $path;
	}

	
	public static function set_path( $path ) {
		static::$path_top = $path;
	}

	
	public static function exclude_file( $file_name ) {
		static::$excluded_files[] = $file_name;
	}

	
	public static function define_namespaces( $namespaces = array() ) {
		static::$namespaces = $namespaces;
	}

	
	public static function filter_excluded_files( \SplFileInfo $file, $key, \RecursiveDirectoryIterator $iterator ) {
		if ( ! in_array( $file->getFilename(), static::$excluded_files, true ) ) {
			return true;
		}

		return false;
	}
}
