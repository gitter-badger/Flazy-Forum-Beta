<?php
/**
 * flazy_colored_usergroups css file
 *
 * @copyright (C) 2008-2009 PunBB
 * @modified Copyright (C) 2008 Flazy.ru
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package flazy_colored_usergroups
 */

header('Content-type: text/css; charset: UTF-8');
define('FORUM_ROOT', '../../');

require FORUM_ROOT.'include/essentials.php';
require_once FORUM_CACHE_DIR.'cache_flazy_coloured_usergroups.php';

echo $flazy_colored_usergroups_cache;