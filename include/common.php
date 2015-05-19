<?php
/**
 * Общие данные, выполение различных функций, необходимых для правильной работы форума.
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2015 Flazy.us
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package Flazy
 */


if (!defined('FORUM_ROOT'))
{
	header('Content-type: text/html; charset=utf-8');
	die('Constant FORUM_ROOT need to be пределена и ссылаться на действующий корневой каталог Flazy.');
}
if (!defined('FORUM_ESSENTIALS_LOADED'))
	require FORUM_ROOT.'include/essentials.php';

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime() && !defined('FORUM_DISABLE_MAGIC_RUNTIME'))
	set_magic_quotes_runtime(0);

// Strip slashes from GET/POST/COOKIE (if magic_quotes_gpc is enabled)
if (get_magic_quotes_gpc() && !defined('FORUM_DISABLE_MAGIC_GPG'))
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
}

// Strip out "bad" UTF-8 characters
forum_remove_bad_characters();

// If a cookie name is not specified in config.php, we use the default (flazy_cookie)
if (empty($cookie_name))
	$cookie_name = 'flazy_cookie';

// Enable output buffering
if (!defined('FORUM_DISABLE_BUFFERING'))
{
	$_SERVER['HTTP_ACCEPT_ENCODING'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

	// Should we use gzip output compression?
	if (!empty($forum_config['o_gzip']) && extension_loaded('zlib') && !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
		ob_start('ob_gzhandler');
	else
		ob_start();
}

// Define standard date/time formats
$forum_time_formats = array($forum_config['o_time_format'], 'H:i:s', 'H:i', 'g:i:s a', 'g:i a');
$forum_date_formats = array($forum_config['o_date_format'], 'Y-m-d', 'Y-d-m', 'd-m-Y', 'm-d-Y', 'M j Y', 'jS M Y');

// Create forum_page array
$forum_page = array();

// Login and fetch user info
$forum_user = array();
cookie_login($forum_user);

// Attempt to load the common language file
if (file_exists(FORUM_ROOT.'lang/'.$forum_user['language'].'/common.php'))
	include FORUM_ROOT.'lang/'.$forum_user['language'].'/common.php';
else
	error('Установленный языковой пакет \''.forum_htmlencode($forum_user['language']).'\' неправильный. Пожалyйста переустановите его.');

// Setup the URL rewriting scheme
if (file_exists(FORUM_ROOT.'include/url/'.$forum_config['o_sef'].'/forum_urls.php'))
	require FORUM_ROOT.'include/url/'.$forum_config['o_sef'].'/forum_urls.php';
else
	require FORUM_ROOT.'include/url/Default/forum_urls.php';

// A good place to modify the URL scheme
($hook = get_hook('co_modify_url_scheme')) ? eval($hook) : null;


// Verify that we are running the proper database schema revision
if ((!isset($forum_config['o_database_revision']) || $forum_config['o_database_revision'] < FORUM_DB_REVISION || version_compare($forum_config['o_cur_version'], FORUM_VERSION, '<')) && $forum_user['g_id'] == FORUM_ADMIN)
	error('New update is avalible. Click here  <a href="'.$base_url.'/admin/db_update.php">to upgrade</a> .');

// Check if we are to display a maintenance message
if ($forum_config['o_maintenance'] && $forum_user['g_id'] > FORUM_ADMIN && !defined('FORUM_TURN_OFF_MAINT'))
{
	if (!defined('FORUM_FUNCTIONS_MAINTENANCE'))
		require FORUM_ROOT.'include/functions/maintenance.php';

	maintenance_message();
}


if ($forum_user['g_id'] == FORUM_ADMIN)
{
	if (file_exists(FORUM_CACHE_DIR.'cache_updates.php'))
		include FORUM_CACHE_DIR.'cache_updates.php';

	// Regenerate cache only if automatic updates are enabled and if the cache is more than 12 hours old
	$cached = time()-43200;

	if ($forum_config['o_check_for_updates'] && (!defined('FORUM_UPDATES_LOADED') || $forum_updates['cached'] < $cached))
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require FORUM_ROOT.'include/cache.php';

		generate_updates_cache();
		require FORUM_CACHE_DIR.'cache_updates.php';
	}
}

// Load cached bans
if (file_exists(FORUM_CACHE_DIR.'cache_bans.php'))
	include FORUM_CACHE_DIR.'cache_bans.php';

if (!defined('FORUM_BANS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/cache.php';

	generate_bans_cache();
	require FORUM_CACHE_DIR.'cache_bans.php';
}

// Check if current user is banned
check_bans();

// Update online list
update_users_online();

// Check to see if we logged in without a cookie being set
if ($forum_user['is_guest'] && isset($_GET['login']))
	message($lang_common['No cookie']);

// If we're an administrator or moderator, make sure the CSRF token in $_POST is valid (token in post.php is dealt with in post.php)
if (!empty($_POST) && (isset($_POST['confirm_cancel']) || (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== generate_form_token(get_current_url()))) && !defined('FORUM_SKIP_CSRF_CONFIRM'))
	csrf_confirm_form();

($hook = get_hook('co_common')) ? eval($hook) : null;
