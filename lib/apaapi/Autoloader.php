<?php
/**
 * Vendored Apaapi Autoloader (adapted for vendored layout).
 *
 * Original library: https://github.com/Jakiboy/apaapi (MIT, (c) Jihad Sinnaour)
 *
 * The upstream Autoloader expects the layout `<root>/src/<Class>.php` and
 * therefore resolves classes via `dirname(__DIR__) . "/src/{$class}.php"`.
 * In this plugin the library is vendored directly into `lib/apaapi/`, so
 * `lib/apaapi/Autoloader.php` lives next to the class folders. We therefore
 * resolve via `__DIR__ . "/{$class}.php"`.
 *
 * PSR-4 mapping:
 *   Apaapi\operations\SearchItems  ->  lib/apaapi/operations/SearchItems.php
 *   Apaapi\lib\Request             ->  lib/apaapi/lib/Request.php
 */

namespace Apaapi;

/**
 * Apaapi standalone autoloader (vendored variant).
 */
final class Autoloader
{
	/**
	 * @access private
	 * @var bool $initialized
	 */
	private static $initialized = false;

	/**
	 * Holds the singleton instance to keep the autoloader registered for the
	 * lifetime of the request. Without this, `new self;` in init() would be
	 * destroyed immediately and __destruct() would unregister spl_autoload.
	 *
	 * @access private
	 * @var self|null $instance
	 */
	private static $instance = null;

	/**
	 * Register autoloader.
	 *
	 * @access private
	 */
	private function __construct()
	{
		spl_autoload_register([__CLASS__, 'autoload']);
		static::$initialized = true;
	}

	/**
	 * Unregister autoloader.
	 */
	public function __destruct()
	{
		spl_autoload_unregister([__CLASS__, 'autoload']);
	}

	/**
	 * Autoloader method.
	 *
	 * @access private
	 * @param string $class
	 * @return void
	 */
	private static function autoload(string $class) : void
	{
		$namespace = __NAMESPACE__ . '\\';
		if ( strpos($class, $namespace) === 0 ) {
			$relative = substr($class, strlen($namespace));
			$path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
			if ( file_exists($path) ) {
				require_once $path;
			}
		}
	}

	/**
	 * Initialize autoloader.
	 *
	 * @access public
	 * @return void
	 */
	public static function init() : void
	{
		if ( !static::$initialized ) {
			static::$instance = new self;
		}
	}
}
