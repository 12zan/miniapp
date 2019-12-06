<?php

class ClassLoader
{
	private $prefixLengthsPsr4 = [];
	private $prefixDirsPsr4    = [];
	private $fallbackDirsPsr4  = [];
	private $prefixesPsr0      = [];
	private $fallbackDirsPsr0  = [];
	private $classMap          = [];
	private $useIncludePath    = false;
	private $apcuPrefix        = null;

	public function getPrefixes()
	{
		if (!empty($this->prefixesPsr0)) {
			return call_user_func_array('array_merge', $this->prefixesPsr0);
		}
		return [];
	}

	public function getPrefixesPsr4()
	{
		return $this->prefixDirsPsr4;
	}

	public function getFallbackDirs()
	{
		return $this->fallbackDirsPsr0;
	}

	public function getFallbackDirsPsr4()
	{
		return $this->fallbackDirsPsr4;
	}

	public function getClassMap()
	{
		return $this->classMap;
	}

	public function addClassMap(array $classMap)
	{
		if ($this->classMap) {
			$this->classMap = array_merge($this->classMap, $classMap);
		} else {
			$this->classMap = $classMap;
		}
	}

	public function add($prefix, $paths, $prepend = false)
	{
		$paths = (array) $paths;

		if (!$prefix) {
			if ($prepend) {
				$this->fallbackDirsPsr0 = array_merge($paths, $this->fallbackDirsPsr0);
			} else {
				$this->fallbackDirsPsr0 = array_merge($this->fallbackDirsPsr0, $paths);
			}
			return;
		}

		$first = $prefix[0];

		if (!isset($this->prefixesPsr0[$first][$prefix])) {
			$this->prefixesPsr0[$first][$prefix] = $paths;
			return;
		}

		if ($prepend) {
			$this->prefixesPsr0[$first][$prefix] = array_merge($paths, $this->prefixesPsr0[$first][$prefix]);
		} else {
			$this->prefixesPsr0[$first][$prefix] = array_merge($this->prefixesPsr0[$first][$prefix], $paths);
		}
	}

	public function addPsr4($prefix, $paths, $prepend = false)
	{
		$paths = (array) $paths;

		if (!$prefix) {
			if ($prepend) {
				$this->fallbackDirsPsr4 = array_merge($paths, $this->fallbackDirsPsr4);
			} else {
				$this->fallbackDirsPsr4 = array_merge($this->fallbackDirsPsr4, $paths);
			}
		} elseif (!isset($this->prefixDirsPsr4[$prefix])) {
			$length = strlen($prefix);

			if ('\\' !== $prefix[$length - 1]) {
				throw new \InvalidArgumentException('A non-empty PSR-4 prefix must end with a namespace separator.');
			}

			$this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
			$this->prefixDirsPsr4[$prefix]                = $paths;
		} elseif ($prepend) {
			$this->prefixDirsPsr4[$prefix] = array_merge($paths, $this->prefixDirsPsr4[$prefix]);
		} else {
			$this->prefixDirsPsr4[$prefix] = array_merge($this->prefixDirsPsr4[$prefix], $paths);
		}
	}

	public function set($prefix, $paths)
	{
		$paths = (array) $paths;

		if (!$prefix) {
			$this->fallbackDirsPsr0 = $paths;
		} else {
			$this->prefixesPsr0[$prefix[0]][$prefix] = $paths;
		}
	}

	public function setPsr4($prefix, $paths)
	{
		$paths = (array) $paths;

		if (!$prefix) {
			$this->fallbackDirsPsr4 = $paths;
		} else {
			$length = strlen($prefix);

			if ('\\' !== $prefix[$length - 1]) {
				throw new \InvalidArgumentException('A non-empty PSR-4 prefix must end with a namespace separator.');
			}

			$this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
			$this->prefixDirsPsr4[$prefix]                = $paths;
		}
	}

	public function setUseIncludePath($useIncludePath)
	{
		$this->useIncludePath = $useIncludePath;
	}

	public function getUseIncludePath()
	{
		return $this->useIncludePath;
	}

	public function setApcuPrefix($apcuPrefix)
	{
		$this->apcuPrefix = function_exists('apcu_fetch') && ini_get('apc.enabled') ? $apcuPrefix : null;
	}

	public function getApcuPrefix()
	{
		return $this->apcuPrefix;
	}

	public function register($prepend = false)
	{
		spl_autoload_register([$this, 'loadClass'], true, $prepend);
	}

	public function unregister()
	{
		spl_autoload_unregister([$this, 'loadClass']);
	}

	public function loadClass($class)
	{
		if ($file = $this->findFile($class)) {
			include $file;
			return true;
		}
	}

	public function findFile($class)
	{
		if ('\\' == $class[0]) {
			$class = substr($class, 1);
		}

		if (isset($this->classMap[$class])) {
			return $this->classMap[$class];
		}

		if (null !== $this->apcuPrefix) {
			$file = apcu_fetch($this->apcuPrefix . $class, $hit);

			if ($hit && is_file($file)) {
				return $file;
			}
		}

		$file = $this->findFileWithExtension($class, '.php');

		if ($file === null) {
			return $this->classMap[$class] = false;
		}

		if (null !== $this->apcuPrefix) {
			apcu_store($this->apcuPrefix . $class, $file);
		}

		return $file;
	}

	private function findFileWithExtension($class, $ext)
	{
		$logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;
		$first           = $class[0];

		if (isset($this->prefixLengthsPsr4[$first])) {
			foreach ($this->prefixLengthsPsr4[$first] as $prefix => $length) {
				if (0 === strpos($class, $prefix)) {
					foreach ($this->prefixDirsPsr4[$prefix] as $dir) {
						if (is_file($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
							return $file;
						}
					}
				}
			}
		}

		foreach ($this->fallbackDirsPsr4 as $dir) {
			if (is_file($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
				return $file;
			}
		}

		if (false !== $pos = strrpos($class, '\\')) {
			$logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1) . substr($logicalPathPsr4, $pos + 1);
		} else {
			$logicalPathPsr0 = $class . $ext;
		}

		if (isset($this->prefixesPsr0[$first])) {
			foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
				if (0 === strpos($class, $prefix)) {
					foreach ($dirs as $dir) {
						if (is_file($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
							return $file;
						}
					}
				}
			}
		}

		foreach ($this->fallbackDirsPsr0 as $dir) {
			if (is_file($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
				return $file;
			}
		}

		if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
			return $file;
		}
	}
}
