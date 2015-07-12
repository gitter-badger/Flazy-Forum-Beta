<?php
/**
 * first_geshi functions file
 *
 * @copyright Copyright (C) 2008 Flazy.ru
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package flazy_geshi
 */

if (!defined('FORUM'))
	die;

require_once $ext_info['path'].'/geshi.php';

function geshi($matches)
{
	global $lang_common;

	$language = forum_htmlencode(forum_trim($matches[1]));
	$source = forum_trim($matches[2]);

	$geshi = new GeSHi();
	//$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
	$geshi->set_source(html_entity_decode($source, ENT_QUOTES));
	$geshi->set_language($language);

	return '</p><div class="codebox"><strong>'.strtoupper($language).' '.$lang_common['Code'].':</strong><pre><code class="'.$language.'">'.$geshi->parse_code().'</code></pre></div><p>';
}
