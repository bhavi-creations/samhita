<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 */

// Manually load Composer's autoloader
// require_once FCPATH . 'vendor/autoload.php';

// Load custom Paths config (modified to allow root-based index.php)
// require_once FCPATH . 'app/Config/Paths.php';

// $paths = new Paths();


require_once FCPATH . 'vendor/autoload.php';

// Use the fully qualified class name (autoloaded by Composer)
$paths = new \Config\Paths();


// Manually fix the system path if necessary
$systemDir = rtrim($paths->systemDirectory, '/\\');
if (!is_dir($systemDir)) {
    // Try to resolve relative to FCPATH
    $systemDir = realpath(FCPATH . 'system');
    if ($systemDir === false) {
        exit('System path not found.');
    }
    $paths->systemDirectory = $systemDir;
}

// Load the framework bootstrap file
require_once $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
