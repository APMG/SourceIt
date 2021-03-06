<?php
/*
|---------------------------------------------------------------
| DEFINE APPLICATION CONSTANTS
|---------------------------------------------------------------
|
| EXT		- The file extension.  Typically ".php"
| SELF		- The name of THIS file (typically "index.php")
| FCPATH	- The full server path to THIS file
| BASEPATH	- The full server path to the "system" folder
| DOCPATH   - The full doctrine "lib" folder path
| APPPATH	- The full server path to the "application" folder
|
*/
define('EXT', '.php');
define('SELF', 'init.php');
define('FCPATH', str_replace(SELF, '', __FILE__));
define('BASEPATH', realpath(FCPATH.'../lib/codeigniter/system').'/');
define('DOCPATH', realpath(FCPATH.'../lib/doctrine/lib').'/');
define('APPPATH', realpath(FCPATH).'/');
define('IFDB_DOCROOT', realpath(FCPATH).'../public_html/');
define('IFDB_ENVIRONMENT', 'dev'); // possible values are 'prod' and 'dev'
define('PROFILES_INI_FILE', 'etc/profiles.ini');

//define('AUTH_TKT_NAME', 'ifdb_auth_tkt');
//define('AUTH_TKT_CONFIG_FILE', realpath(FCPATH).'/../etc/auth_tkt.conf');

$profile_name = trim(file_get_contents(realpath(FCPATH."../etc/my_profile")));
if (!$profile_name) {
    die("You must create a etc/my_profile file containing the name of a profile in etc/profiles.ini\n");
}
$profiles = parse_ini_file(realpath(FCPATH."../".PROFILES_INI_FILE), true);
if (!$profiles) {
    die("Invalid etc/profiles.ini");
}
$profile = $profiles[$profile_name];
if (!$profile) {
    var_export($profiles);
    die(sprintf("No such profile '%s' found in %s\n", $profile_name, PROFILES_INI_FILE));
}
if (isset($profile['fb_appId'])) {
    define('FB_APP_ID', $profile['fb_appId']);
}
if (isset($profile['fb_secret'])) {
    define('FB_SECRET', $profile['fb_secret']);
}
if (isset($profile['base_url'])) {
    define('BASE_URL', $profile['base_url']);
}
else {
    die(sprintf("Must define base_url in %s\n", PROFILES_INI_FILE));
}

/* set up include path */
$my_include_paths = array(
    APPPATH.'libraries',
    APPPATH.'models',
    APPPATH.'../lib',
    APPPATH.'../lib/shared'
);
set_include_path(implode(':', $my_include_paths));

require_once APPPATH.'config/app_constants.php';
require_once 'Carper.php';
require_once 'IFDB_Exception.php';
require_once 'IFDB_Utils.php';
