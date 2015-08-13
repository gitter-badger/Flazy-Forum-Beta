<?php
/**
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2015 Flazy.us
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package Flazy
 */


// Убедимся что никто не пытается запусть этот сценарий напрямую
if (!defined('FORUM'))
	die;

// START SUBST - <forum_about>
ob_start();

($hook = get_hook('ft_about_output_start')) ? eval($hook) : null;

$forum_page['copyright'] = sprintf($lang_common['Powered by'], '<a href="http://flazy.us/">Flazy Forum Software ©</a>'.($forum_config['o_show_version'] ? ' '.$forum_config['o_cur_version'] : ''));

($hook = get_hook('ft_about_pre_copyright')) ? eval($hook) : null;
	echo $forum_page['copyright'];
($hook = get_hook('ft_about_pre_quickjump')) ? eval($hook) : null;

// Display the "Jump to" drop list


$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<forum_about>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <forum_about>

($hook = get_hook('ft_about_end')) ? eval($hook) : null;

// START SUBST - <forum_debug>
if (defined('FORUM_DEBUG') || defined('FORUM_SHOW_QUERIES'))
{
	ob_start();

	($hook = get_hook('ft_debug_output_start')) ? eval($hook) : null;

	// Display debug info (if enabled/defined)
	if (defined('FORUM_DEBUG'))
	{
		/* Для dev версии
		$mem_usage = memory_get_usage(false); // true размер страницы false под переменные
		if ($mem_usage < 1024)
			$memory_usage =  $mem_usage.' байт';
		elseif ($mem_usage < 1048576)
			$memory_usage = round($mem_usage/1024,2).' кб';
		else
			$memory_usage = round($mem_usage/1048576,2).' мб';*/

		// Calculate script generation time
		$time_diff = sprintf('%.3f', get_microtime() - $forum_start);
		echo '<p id="querytime">[ '.sprintf($lang_common['Querytime'], $time_diff, forum_number_format($forum_db->get_num_queries())).' ]</p>'."\n";
	}

	if (defined('FORUM_SHOW_QUERIES'))
	{
		if (!defined('FORUM_FUNCTIONS_GET_SAVED_QUERIES'))
			require FORUM_ROOT.'include/functions/get_saved_queries.php';

		echo get_saved_queries();
	}

	($hook = get_hook('ft_debug_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_debug>', $tpl_temp, $tpl_main);
	ob_end_clean();
}
// END SUBST - <forum_debug>

($hook = get_hook('ft_forum_debug_end')) ? eval($hook) : null;


$gen_elements['<forum_js>'] = (isset($forum_js)) ? $forum_js->out() : '';
$gen_elements['<forum_ga>'] = (!empty($forum_config['o_google_analytics'])) ? "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga'); ga('create','".$forum_config['o_google_analytics']."', 'auto'); ga('send', 'pageview');</script>" : '';
$gen_elements['<forum_html_bottom>'] = ($forum_config['o_html_bottom'] && !defined('FORUM_DISABLE_HTML')) ? $forum_config['o_html_bottom_message'] : '';

($hook = get_hook('ft_gen_elements')) ? eval($hook) : null;

$tpl_main = str_replace(array_keys($gen_elements), array_values($gen_elements), $tpl_main);
unset($gen_elements);

// Last call!
($hook = get_hook('ft_end')) ? eval($hook) : null;

// End the transaction
$forum_db->end_transaction();

// Close the db connection (and free up any result data)
$forum_db->close();

// Spit out the page
die($tpl_main);
