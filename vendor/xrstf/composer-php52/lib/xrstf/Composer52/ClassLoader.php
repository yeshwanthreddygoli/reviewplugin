<?php


class xrstf_Composer52_ClassLoader {
	private $prefixes              = array();
	private $fallbackDirs          = array();
	private $useIncludePath        = false;
	private $classMap              = array();
	private $classMapAuthoratative = false;
	private $allowUnderscore       = false;

	
	public function setAllowUnderscore($flag) {
		$this->allowUnderscore = (boolean) $flag;
	}

	
	public function getPrefixes() {
		return $this->prefixes;
	}

	
	public function setClassMapAuthoritative($classMapAuthoratative) {
		$this->classMapAuthoratative = $classMapAuthoratative;
	}

	
	public function getClassMapAuthoratative() {
		return $this->classMapAuthoratative;
	}

	
	public function getFallbackDirs() {
		return $this->fallbackDirs;
	}

	
	public function getClassMap() {
		return $this->classMap;
	}

	
	public function addClassMap(array $classMap) {
		if ($this->classMap) {
			$this->classMap = array_merge($this->classMap, $classMap);
		}
		else {
			$this->classMap = $classMap;
		}
	}

	
	public function add($prefix, $paths, $prepend = false) {
		if (!$prefix) {
			if ($prepend) {
				$this->fallbackDirs = array_merge(
					(array) $paths,
					$this->fallbackDirs
				);
			}
			else {
				$this->fallbackDirs = array_merge(
					$this->fallbackDirs,
					(array) $paths
				);
			}

			return;
		}

		if (!isset($this->prefixes[$prefix])) {
			$this->prefixes[$prefix] = (array) $paths;
			return;
		}

		if ($prepend) {
			$this->prefixes[$prefix] = array_merge(
				(array) $paths,
				$this->prefixes[$prefix]
			);
		}
		else {
			$this->prefixes[$prefix] = array_merge(
				$this->prefixes[$prefix],
				(array) $paths
			);
		}
	}

	
	public function set($prefix, $paths) {
		if (!$prefix) {
			$this->fallbackDirs = (array) $paths;
			return;
		}

		$this->prefixes[$prefix] = (array) $paths;
	}

	
	public function setUseIncludePath($useIncludePath) {
		$this->useIncludePath = $useIncludePath;
	}

	
	public function getUseIncludePath() {
		return $this->useIncludePath;
	}

	
	public function register() {
		spl_autoload_register(array($this, 'loadClass'), true);
	}

	
	public function unregister() {
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	
	public function loadClass($class) {
		if ($file = $this->findFile($class)) {
			include $file;
			return true;
		}
	}

	
	public function findFile($class) {
		if ('\\' === $class[0]) {
			$class = substr($class, 1);
		}

		if (isset($this->classMap[$class])) {
			return $this->classMap[$class];
		}
		elseif ($this->classMapAuthoratative) {
			return false;
		}

		$classPath = $this->getClassPath($class);

		foreach ($this->prefixes as $prefix => $dirs) {
			if (0 === strpos($class, $prefix)) {
				foreach ($dirs as $dir) {
					if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
						return $dir.DIRECTORY_SEPARATOR.$classPath;
					}
				}
			}
		}

		foreach ($this->fallbackDirs as $dir) {
			if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
				return $dir.DIRECTORY_SEPARATOR.$classPath;
			}
		}

		if ($this->useIncludePath && $file = self::resolveIncludePath($classPath)) {
			return $file;
		}

		return $this->classMap[$class] = false;
	}

	private function getClassPath($class) {
		if (false !== $pos = strrpos($class, '\\')) {
			// namespaced class name
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)).DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}
		else {
			// PEAR-like class name
			$classPath = null;
			$className = $class;
		}

		$className = str_replace('_', DIRECTORY_SEPARATOR, $className);

		// restore the prefix
		if ($this->allowUnderscore && DIRECTORY_SEPARATOR === $className[0]) {
			$className[0] = '_';
		}

		$classPath .= $className.'.php';

		return $classPath;
	}

	public static function resolveIncludePath($classPath) {
		$paths = explode(PATH_SEPARATOR, get_include_path());

		foreach ($paths as $path) {
			$path = rtrim($path, '/\\');

			if ($file = file_exists($path.DIRECTORY_SEPARATOR.$file)) {
				return $file;
			}
		}

		return false;
	}
}
