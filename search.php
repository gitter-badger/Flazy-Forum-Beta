<?php
/**
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2015 Flazy.us
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package Flazy
 */


if (!defined('FORUM_ROOT'))
	define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';

($hook = get_hook('se_start')) ? eval($hook) : null;

// Load the search.php language file
require FORUM_ROOT.'lang/'.$forum_user['language'].'/search.php';

// Load the necessary search functions
require FORUM_ROOT.'include/functions/search.php';


if (!$forum_user['g_read_board'])
	message($lang_common['No view']);
else if (!$forum_user['g_search'])
	message($lang_search['No search permission']);


// If a search_id was supplied
if (isset($_GET['search_id']))
{
	$search_id = intval($_GET['search_id']);
	if ($search_id < 1)
		message($lang_common['Bad request']);

	// Generate the query to grab the cached results
	$query = generate_cached_search_query($search_id, $show_as, $keywords);

	$url_type = $forum_url['search_results'];
}
// We aren't just grabbing a cached search
else if (isset($_GET['action']))
{
	$action = $_GET['action'];

	// Validate action
	if (!validate_search_action($action))
		message($lang_common['Bad request']);

	// If it's a regular search (keywords and/or author)
	if ($action == 'search')
	{
		$keywords = (isset($_GET['keywords'])) ? forum_trim($_GET['keywords']) : null;
		$author = (isset($_GET['author'])) ? forum_trim($_GET['author']) : null;
		$sort_dir = (isset($_GET['sort_dir'])) ? (($_GET['sort_dir'] == 'DESC') ? 'DESC' : 'ASC') : 'DESC';
		$show_as = (isset($_GET['show_as'])) ? $_GET['show_as'] : 'posts';
		$sort_by = (isset($_GET['sort_by'])) ? intval($_GET['sort_by']) : null;
		$search_in = (!isset($_GET['search_in']) || $_GET['search_in'] == 'all') ? 0 : (($_GET['search_in'] == 'message') ? 1 : -1);
		$forum = (isset($_GET['forum']) && is_array($_GET['forum'])) ? array_map('intval', $_GET['forum']) : array(-1);

		if (preg_match('#^[\*%]+$#', $keywords))
			$keywords = '';

		if (preg_match('#^[\*%]+$#', $author))
			$author = '';

		if (!$keywords && !$author)
			message($lang_search['No terms']);

		// Create a cache of the results and redirect the user to the results
		create_search_cache($keywords, $author, $search_in, $forum, $show_as, $sort_by, $sort_dir);
	}
	// Its not a regular search, so its a quicksearch
	else
	{
		$value = null;
		// Get any additional variables for quicksearches
		if ($action == 'show_user_posts' || $action == 'show_user_topics' || $action == 'show_subscriptions')
		{
			$value = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
			if ($value < 2)
				message($lang_common['Bad request']);
		}
		else if ($action == 'show_recent')
			$value = (isset($_GET['value'])) ? intval($_GET['value']) : 86400;
		else if ($action == 'show_new')
			$value = (isset($_GET['forum'])) ? intval($_GET['forum']) : -1;

		($hook = get_hook('se_additional_quicksearch_variables')) ? eval($hook) : null;

		$search_id = $keywords = '';
		$show_as = 'topics';

		// Generate the query for the search
		$query = generate_action_search_query($action, $value, $search_id, $url_type, $show_as);
	}
}

($hook = get_hook('se_pre_search_query')) ? eval($hook) : null;

// We have the query to get the results, lets get them!
if (isset($query))
{
	// No results?
	if (!$query)
		no_search_results();

	// Work out the settings for pagination
	$forum_page['per_page'] = ($show_as == 'posts') ? $forum_user['disp_posts'] : $forum_user['disp_topics'];

	// We now have a query that will give us our results in $query, lets get the data!
	$num_hits = get_search_results($query, $search_set);

	($hook = get_hook('se_post_results_fetched')) ? eval($hook) : null;

	// No search results?
	if ($num_hits == 0)
		no_search_results($action);

	//
	// Output the search results
	//
	$forum_page['main_title'] = $lang_common['Search'];

	// Setup breadcrumbs and results header and footer
	$forum_page['main_foot_options']['new_search'] = '<a class="user-option" href="'.forum_link($forum_url['search']).'">'.$lang_search['Perform new search'].'</a>';
	$forum_page['crumbs'][] = array($forum_config['o_board_title'], forum_link($forum_url['index']));
	$action = (isset($action)) ? $action : null;
	generate_search_crumbs($action);

	// Generate paging links
	$forum_page['page_post']['paging'] = '<div class="pagination">'. $forum_page['items_info'].' • <span class="pages">'.$lang_common['Pages'].'</span> '.paginate($forum_page['num_pages'], $forum_page['page'], $url_type, $lang_common['Paging separator'], $search_id).'</div>';

	// Get topic/forum tracking data
	if (!$forum_user['is_guest'])
		$tracked_topics = get_tracked_topics();

	// Navigation links for header and page numbering for title/meta description
	if ($forum_page['page'] < $forum_page['num_pages'])
	{
		$forum_page['nav']['last'] = '<link rel="last" href="'.forum_sublink($url_type, $forum_url['page'], $forum_page['num_pages'], $search_id).'" title="'.$lang_common['Page'].' '.$forum_page['num_pages'].'" />';
		$forum_page['nav']['next'] = '<link rel="next" href="'.forum_sublink($url_type, $forum_url['page'], ($forum_page['page'] + 1), $search_id).'" title="'.$lang_common['Page'].' '.($forum_page['page'] + 1).'" />';
	}
	if ($forum_page['page'] > 1)
	{
		$forum_page['nav']['prev'] = '<link rel="prev" href="'.forum_sublink($url_type, $forum_url['page'], ($forum_page['page'] - 1), $search_id).'" title="'.$lang_common['Page'].' '.($forum_page['page'] - 1).'" />';
		$forum_page['nav']['first'] = '<link rel="first" href="'.forum_link($url_type, $search_id).'" title="'.$lang_common['Page'].' 1" />';
	}

	// Setup main heading
	if ($forum_page['num_pages'] > 1)
		$forum_page['main_head_pages'] = sprintf($lang_common['Page info'], $forum_page['page'], $forum_page['num_pages']);

	// Setup main options header
	$forum_page['main_options_head'] = $lang_search['Search options'];


	($hook = get_hook('se_results_pre_header_load')) ? eval($hook) : null;

	$forum_js->file(array('jquery', 'tooltip'));
	$forum_js->code('$(function() {
		$(\'.hide-head\').toggle(
			function() {
			$(this).children().text(\''.$lang_common['Hidden text'].'\');
				$(this).next().show("slow");
			},
			function() {
				$(this).children().text(\''.$lang_common['Hidden show text'].'\');
				$(this).next().hide("slow");
			}
		);
		$(\'.item-subject a\').tooltip({ track: true, delay: 0, showURL: false, showBody: " - ", fade: 250 });
		$(\'#block\').click($.tooltip.block);
		});');

	define('FORUM_PAGE', $show_as == 'topics' ? 'searchtopics' : 'searchposts');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('se_results_output_start')) ? eval($hook) : null;

	if ($show_as == 'topics')
	{
		// Load the forum.php language file
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/forum.php';

		$forum_page['item_header'] = array();
		$forum_page['item_header']['title'] = '<dt><div class="list-inner">'.$lang_forum['Topics'].'</div></dt>';
		$forum_page['item_header']['forum'] = '<dd class="posts" scope="col">'.$lang_forum['Forum'].'</dd>';
		$forum_page['item_header']['replies'] = '<dd class="views" scope="col">'.$lang_forum['Replies'].'</dd>';
		$forum_page['item_header']['lastpost'] = '<dd class="lastpost" scope="col">'.$lang_forum['Last post'].'</dd>';

		($hook = get_hook('se_results_topics_pre_item_header_output')) ? eval($hook) : null;

?>
<div id="forumlist">
	<div id="forumlist-inner">
		<div class="forabg">
			<div class="inner">
				<ul class="topiclist">
					<li class="header">
						<dl class="icon">
								<?php echo implode("\n\t\t\t\t", $forum_page['item_header'])."\n" ?>
						</dl>
					</li>
				</ul>
<?php

	}
	else
	{
		// Load the topic.php language file
		require FORUM_ROOT.'lang/'.$forum_user['language'].'/topic.php';

?>
	<div class="main-head">
<?php

	if (!empty($forum_page['main_head_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_head_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
	<div class="main-content main-topic">
<?php

	}

	$forum_page['item_count'] = 0;

	if ($show_as == 'posts')
	{
		if (!defined('FORUM_PARSER_LOADED'))
			require FORUM_ROOT.'include/parser.php';
	}

	// Finally, lets loop through the results and output them
	foreach ($search_set as $cur_set)
	{
		($hook = get_hook('se_results_loop_start')) ? eval($hook) : null;

		++$forum_page['item_count'];

		if ($forum_config['o_censoring'])
		{
			$cur_set['subject'] = censor_words($cur_set['subject']);
			$cur_set['description'] = censor_words($cur_set['description']);
			$cur_set['question'] = censor_words($cur_set['question']);
		}

		if ($show_as == 'posts')
		{
			// Generate the result heading
			$forum_page['post_ident'] = array();
			$forum_page['post_ident']['num'] = '<span class="post-num">'.forum_number_format($forum_page['start_from'] + $forum_page['item_count']).'</span>';
			$forum_page['post_ident']['byline'] = '<span class="post-byline">'.sprintf((($cur_set['pid'] == $cur_set['first_post_id']) ? $lang_topic['Topic byline'] : $lang_topic['Reply byline']), '<strong>'.forum_htmlencode($cur_set['pposter']).'</strong>').'</span>';
			$forum_page['post_ident']['link'] = '<a class="permalink" rel="bookmark" title="'.$lang_topic['Permalink topic'].'" href="'.forum_link($forum_url['topic'], array($cur_set['tid'], sef_friendly($cur_set['subject']))).'">'.sprintf((($cur_set['pid'] == $cur_set['first_post_id']) ? $lang_topic['Topic title'] : $lang_topic['Reply title']), forum_htmlencode($cur_set['subject'])).'</a> <small>'.sprintf($lang_topic['Search replies'], forum_number_format($cur_set['num_replies']), '<a href="'.forum_link($forum_url['forum'], array($cur_set['forum_id'], sef_friendly($cur_set['forum_name']))).'">'.forum_htmlencode($cur_set['forum_name']).'</a>').'</small>';

			($hook = get_hook('se_results_posts_row_pre_item_ident_merge')) ? eval($hook) : null;

			// Generate author identification
			$forum_page['user_ident'] = ($cur_set['poster_id'] > 1 && $forum_user['g_view_users']) ? '<strong class="username"><a title="'.sprintf($lang_search['Go to profile'], forum_htmlencode($cur_set['pposter'])).'" href="'.forum_link($forum_url['user'], $cur_set['poster_id']).'">'.forum_htmlencode($cur_set['pposter']).'</a></strong>' : '<strong class="username">'.forum_htmlencode($cur_set['pposter']).'</strong>';

			$forum_page['post_contacts'] =array();
			$forum_page['post_contacts']['pposted'] = '<span><a class="permalink" rel="bookmark" title="'.$lang_topic['Permalink post'].'" href="'.forum_link($forum_url['post'], $cur_set['pid']).'">'.format_time($cur_set['pposted']).'</a></span>';

			// Generate the post actions links
			$forum_page['post_actions'] = array();
			$forum_page['post_actions']['forum'] = '<span class="forum-search"><a href="'.forum_link($forum_url['forum'], array($cur_set['forum_id'], sef_friendly($cur_set['forum_name']))).'">'.$lang_search['Go to forum'].'<span>: '.forum_htmlencode($cur_set['forum_name']).'</span></a></span>';

			if ($cur_set['pid'] != $cur_set['first_post_id'])
				$forum_page['post_actions']['topic'] = '<span class="topic-search"><a class="permalink" rel="bookmark" title="'.$lang_topic['Permalink topic'].'" href="'.forum_link($forum_url['topic'], array($cur_set['tid'], sef_friendly($cur_set['subject']))).'">'.$lang_search['Go to topic'].'<span>: '.forum_htmlencode($cur_set['subject']).'</span></a></span>';

			$forum_page['post_actions']['post'] = '<span class="post-search"><a class="permalink" rel="bookmark" title="'.$lang_topic['Permalink post'].'" href="'.forum_link($forum_url['post'], $cur_set['pid']).'">'.$lang_search['Go to post'].'<span> '.forum_number_format($forum_page['start_from'] + $forum_page['item_count']).'</span></a></span>';

			$forum_page['message'] = parse_message($cur_set['message'], $cur_set['hide_smilies']);

			if ($keywords != '')
			{
				$hilit_array = array_filter(explode('|', $keywords), 'strlen');
				foreach ($hilit_array as $key => $value)
				{
					$hilit_array[$key] = '/(?<=^|\s)('.str_replace('\*', '[[:punct:]а-яА-ЯёЁ\w]*?', preg_quote($value, '/')).')(?=$|\s)/iu';
					$hilit_array[$key] = preg_replace('#(^|\s)\\\\w\*\?(\s|$)#', '$1\w+?$2', $hilit_array[$key]);
				}

				$forum_page['message'] = utf8_substr(preg_replace($hilit_array, '<span class="posthilit">$1</span>', ' '.$forum_page['message'].' '), 1, -1);
			}

			// Give the post some class
			$forum_page['item_status'] = array(
				'post',
				(($forum_page['item_count'] % 2 != 0) ? 'odd' : 'even' )
			);

			if ($forum_page['item_count'] == 1)
				$forum_page['item_status']['firstpost'] = 'firstpost';

			if (($forum_page['start_from'] + $forum_page['item_count']) == $forum_page['finish_at'])
				$forum_page['item_status']['lastpost'] = 'lastpost';

			if ($cur_set['pid'] == $cur_set['first_post_id'])
				$forum_page['item_status']['topicpost'] = 'topicpost';


			($hook = get_hook('se_results_posts_row_pre_display')) ? eval($hook) : null;

?>
	<div class="<?php echo implode(' ', $forum_page['item_status']) ?> resultpost">
		<div class="posthead">
			<h3 class="hn post-ident"><?php echo implode(' ', $forum_page['post_ident']) ?></h3>
		</div>
		<div class="postbody">
			<div class="post-entry">
				<div class="entry-content">
					<?php echo $forum_page['message'] ?>
				</div>
<?php ($hook = get_hook('se_results_posts_row_new_post_entry_data')) ? eval($hook) : null; ?>
			</div>
		</div>
		<div class="postfoot">
			<div class="post-options">
				<p class="post-contacts"><?php echo implode(' ', $forum_page['post_contacts']) ?></p>
				<p class="post-actions"><?php echo implode(' ', $forum_page['post_actions']) ?></p>
			</div>
		</div>
	</div>
<?php

		}
		else
		{
			// Start from scratch
			$forum_page['item_subject'] = $forum_page['item_body'] = $forum_page['item_status'] = $forum_page['item_nav'] = $forum_page['item_title'] = $forum_page['item_title_status'] = array();

			// Assemble the Topic heading
			if (!$forum_user['is_guest'] && $forum_config['o_show_dot'] && $cur_set['has_posted'] > 0)
			{
				$forum_page['item_title']['posted'] = '<span class="posted-mark">'.$lang_forum['You posted indicator'].'</span>';
				$forum_page['item_status']['posted'] = 'posted';
			}

			if ($cur_set['sticky'])
			{
				$forum_page['item_title_status']['sticky'] = '<em class="sticky">'.$lang_forum['Sticky'].'</em>';
				$forum_page['item_status']['sticky'] = 'sticky';
			}
			else if ($cur_set['closed'])
			{
				$forum_page['item_title_status']['closed'] = '<em class="closed">'.$lang_forum['Closed'].'</em>';
				$forum_page['item_status']['closed'] = 'closed';
			}
			else if ($cur_set['question'] != '')
			{
				$forum_page['item_title_status']['poll'] = '<em class="poll">'.$lang_forum['Poll'].'</em>';
				$forum_page['item_status']['poll'] = 'poll';
			}

			($hook = get_hook('se_results_topics_row_pre_item_title_status_merge')) ? eval($hook) : null;

			if (!empty($forum_page['item_title_status']))
				$forum_page['item_title']['status'] = '<span class="item-status">'.sprintf($lang_forum['Item status'], implode(', ', $forum_page['item_title_status'])).'</span>';

			$topic_desc = array();
			if ($cur_set['description'] != '')
				$topic_desc['description'] = $lang_common['Title separator'].forum_htmlencode(forum_htmlencode($cur_set['description']));
			if ($cur_set['question'] != '')
				$topic_desc['question'] = $lang_common['Title separator'].forum_htmlencode(forum_htmlencode($cur_set['question']));

			$forum_page['item_title']['link'] = '<a class="forumtitle" href="'.forum_link($forum_url['topic'], array($cur_set['tid'], sef_friendly($cur_set['subject']))).'"'.(!empty($topic_desc) ? ' title="'.$lang_forum['Description'].implode('', $topic_desc).'"' : '').'>'.forum_htmlencode($cur_set['subject']).'</a><br>';

			($hook = get_hook('se_results_topic_loop_normal_topic_pre_item_title_merge')) ? eval($hook) : null;

			$forum_page['item_body']['subject']['title'] = implode(' ', $forum_page['item_title']);

			// Assemble the Topic subject
			$forum_page['item_subject']['starter'] = '<span class="item-starter">'.sprintf($lang_forum['Topic starter'], '<cite><a href="'.forum_link($forum_url['user'], $cur_set['poster_id']).'">'.forum_htmlencode($cur_set['poster']).'</a></cite>').'</span>';

			($hook = get_hook('se_results_topics_row_pre_item_subject_merge')) ? eval($hook) : null;

			if (empty($forum_page['item_status']))
				$forum_page['item_status']['normal'] = 'normal';

			($hook = get_hook('se_results_topics_pre_item_status_merge')) ? eval($hook) : null;

			$forum_page['item_pages'] = ceil(($cur_set['num_replies'] + 1) / $forum_user['disp_posts']);

			if ($forum_page['item_pages'] > 1)
				$forum_page['item_nav']['pages'] = '<span  class="pages">'.$lang_forum['Pages'].'&#160;</span>'.paginate($forum_page['item_pages'], -1, $forum_url['topic'], $lang_common['Page separator'], array($cur_set['tid'], sef_friendly($cur_set['subject'])));

			// Does this topic contain posts we haven't read? If so, tag it accordingly.
			if (!$forum_user['is_guest'] && $cur_set['last_post'] > $forum_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_set['tid']]) || $tracked_topics['topics'][$cur_set['tid']] < $cur_set['last_post']) && (!isset($tracked_topics['forums'][$cur_set['forum_id']]) || $tracked_topics['forums'][$cur_set['forum_id']] < $cur_set['last_post']))
			{
				$forum_page['item_nav']['new'] = '<small><a href="'.forum_link($forum_url['topic_new_posts'], array($cur_set['tid'], sef_friendly($cur_set['subject']))).'" title="'.$lang_forum['New posts info'].'">'.$lang_forum['New posts'].'</a></small>';
				$forum_page['item_status']['new'] = 'new';
			}

			($hook = get_hook('se_topic_loop_normal_topic_pre_item_nav_merge')) ? eval($hook) : null;

			if (!empty($forum_page['item_nav']))
				$forum_page['item_title']['nav'] = '<span class="item-nav">'.sprintf($lang_forum['Topic navigation'], implode('&#160;&#160;', $forum_page['item_nav'])).'</span>';

			$forum_page['item_body']['subject']['title'] = implode(' ', $forum_page['item_title']);

			$forum_page['item_body']['subject']['desc'] = implode(' ', $forum_page['item_subject']);

			($hook = get_hook('se_topic_loop_normal_topic_pre_item_subject_merge')) ? eval($hook) : null;

			$forum_page['item_body']['info']['forum'] = '<dd class="info-forum"><a href="'.forum_link($forum_url['forum'], array($cur_set['forum_id'], sef_friendly($cur_set['forum_name']))).'">'.$cur_set['forum_name'].'</a></td>';

			$forum_page['item_body']['info']['replies'] = '<dd class="info-replies"><span class="'.item_size($cur_set['num_replies']).'">'.forum_number_format($cur_set['num_replies']).'</span></td>';

			$forum_page['item_body']['info']['lastpost'] = '<dd class="info-lastpost"><span><a href="'.forum_link($forum_url['post'], $cur_set['last_post_id']).'">'.format_time($cur_set['last_post']).'</a></span> <cite>'.sprintf($lang_forum['by poster'], '<a href="'.forum_link($forum_url['user'],$cur_set['last_poster_id']).'">'.forum_htmlencode($cur_set['last_poster']).'</a>').'</cite></td>';

			($hook = get_hook('se_results_topics_row_pre_display')) ? eval($hook) : null;

			$forum_page['item_style'] = (($forum_page['item_count'] % 2 != 0) ? ' odd' : ' even').(($forum_page['item_count'] == 1) ? ' main-first-item' : '').((!empty($forum_page['item_status'])) ? ' '.implode(' ', $forum_page['item_status']) : '');

			($hook = get_hook('se_row_pre_display')) ? eval($hook) : null;

?>
		<ul class="topiclist forums">

										<li class="row">
											<dl class="icon <?php echo implode($forum_page['item_status']) ?> <?php echo ($forum_page['item_count'] % 2 != 0) ? 'forum_read' : 'forum_read' ?><?php echo ($forum_page['item_count'] == 1) ? '' : '' ?>">
												<dt>
													<div class="list-inner">
														
														<?php echo implode("\n\t\t\t\t", $forum_page['item_body']['subject'])."\n" ?>
													</div>
												</dt>
												<?php echo implode("\n\t\t\t\t", $forum_page['item_body']['info'])."\n" ?>
											</dl>
										</li>
									</ul>
<?php

		}
	}

?>
			</div>
		</div>
	</div>
</div>
	<div class="main-foot">
<?php

	if (!empty($forum_page['main_foot_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_foot_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
<?php

	($hook = get_hook('se_results_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}

//
// Display the search form
//

// Setup form information
$forum_page['frm-info'] = array(
	'search'	=> '<h4><span>'.$lang_search['Search info'].'</span><br>',
	'keywords'	=> '<span>'.$lang_search['Keywords info'].'</span><br>',
	'refine'	=> '<span>'.$lang_search['Refine info'].'</span><br>',
	'wildcard'	=> '<span>'.$lang_search['Wildcard info'].'</span><br>'
);

if ($forum_config['o_search_all_forums'] || $forum_user['is_admmod'])
	$forum_page['frm-info']['forums'] = '<span>'.$lang_search['Forum default info'].'</span>';
else
	$forum_page['frm-info']['forums'] = '<span>'.$lang_search['Forum require info'].'</span></h4>';

// Setup sort by options
$forum_page['frm-sorts'] = array(
	'post_time'		=> '<option value="0">'.$lang_search['Sort by post time'].'</option>',
	'author'		=> '<option value="1">'.$lang_search['Sort by author'].'</option>',
	'subject'		=> '<option value="2">'.$lang_search['Sort by subject'].'</option>',
	'forum_name'	=> '<option value="3">'.$lang_search['Sort by forum'].'</option>'
);

// Setup breadcrumbs
$forum_page['crumbs'] = array(
	array($forum_config['o_board_title'], forum_link($forum_url['index'])),
	array($lang_common['Search'], forum_link($forum_url['search'])) 
);

// Setup form
$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;

($hook = get_hook('se_pre_header_load')) ? eval($hook) : null;

define('FORUM_PAGE', 'search');
require FORUM_ROOT.'header.php';

// START SUBST - <forum_main>
ob_start();

($hook = get_hook('se_main_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<div class="alert alert-info">
				<?php echo implode("\n\t\t\t\t", $forum_page['frm-info'])."\n" ?>
		</div>
		<form id="afocus" class="frm-form" method="get" accept-charset="utf-8" action="<?php echo forum_link($forum_url['search']) ?>">
			<div class="hidden">
				<input type="hidden" name="action" value="search" />
			</div>
<?php ($hook = get_hook('se_pre_criteria_fieldset')) ? eval($hook) : null; ?>
			<div class="panel bg<?php echo ++$forum_page['group_count'] ?>">
				<div class="inner">
				<h3><?php echo $lang_search['Search legend'] ?></h3>
<?php ($hook = get_hook('se_pre_forum_fieldset')) ? eval($hook) : null; ?>
				<fieldset>
<?php ($hook = get_hook('se_pre_keywords')) ? eval($hook) : null; ?>
			<dl>
				<dt>
					<label for="keywords"><?php echo $lang_search['Keyword search'] ?></label>
				</dt>
				<dd>
					<input type="search" class="inputbox" id="fld<?php echo $forum_page['fld_count'] ?>" title="<?php echo $lang_search['Keyword search'] ?>" name="keywords" size="40" maxlength="100" />
				</dd>
<?php ($hook = get_hook('se_pre_search_in')) ? eval($hook) : null; ?>
				<div name="search_in">
					<dd>
						<label for="terms1">
							<input type="radio" name="terms" id="fld<?php echo $forum_page['fld_count'] ?>" value="all" checked="checked">
							<?php echo $lang_search['Message and subject'] ?></label>
					</dd>
					<dd>
						<label for="terms2">
							<input type="radio" name="terms" id="fld<?php echo $forum_page['fld_count'] ?>" value="message">
							<?php echo $lang_search['Message only'] ?></label>
					</dd>
					<dd>
						<label for="terms2">
							<input type="radio" name="terms" id="fld<?php echo $forum_page['fld_count'] ?>" value="topic">
							<?php echo $lang_search['Topic only'] ?></label>
					</dd>
				</div>
			</dl>

<?php ($hook = get_hook('se_pre_author')) ? eval($hook) : null; ?>
			<dl>
				<dt>
					<label for="author"><?php echo $lang_search['Author search'] ?></label>
				</dt>
				<dd>
					<input  id="fld<?php echo $forum_page['fld_count'] ?>" type="search" class="inputbox" name="author" size="25" maxlength="25" title="<?php echo $lang_search['Author search'] ?>">
				</dd>
			</dl>
							

				<dl>
					<dt>
						<label for="search_forum"><?php echo $lang_search['Forum search'] ?></label>
						<br>
							<span><?php echo ($forum_config['o_search_all_forums'] || $forum_user['is_admmod']) ? $lang_search['Forum search default'] : $lang_search['Forum search require'] ?></span>
					</dt>
<?php ($hook = get_hook('se_pre_forum_checklist')) ? eval($hook) : null; ?>
			<dd>
				<select name="fid[]" id="search_forum" multiple="multiple" size="8" title="Search in forums">
			
	
<?php

// Get the list of categories and forums
$query = array(
	'SELECT'	=> 'c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url',
	'FROM'		=> 'categories AS c',
	'JOINS'		=> array(
		array(
			'INNER JOIN'	=> 'forums AS f',
			'ON'			=> 'c.id=f.cat_id'
		),
		array(
			'LEFT JOIN'		=> 'forum_perms AS fp',
			'ON'			=> '(fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].')'
		)
	),
	'WHERE'		=> '(fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL',
	'ORDER BY'	=> 'c.disp_position, c.id, f.disp_position'
);

($hook = get_hook('se_qr_get_cats_and_forums')) ? eval($hook) : null;
$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

$cur_category = 0;
while ($cur_forum = $forum_db->fetch_assoc($result))
{
	($hook = get_hook('se_forum_loop_start')) ? eval($hook) : null;

	if ($cur_forum['cid'] != $cur_category) // A new category since last iteration?
	{
		if ($cur_category)
			echo "\t\t\t\t\t\t\t".'</option>'."\n";

		echo "\t\t\t\t\t\t\t".''."\n\t\t\t\t\t\t\t\t".'<option>'.forum_htmlencode($cur_forum['cat_name']).':'."\n";
		$cur_category = $cur_forum['cid'];
	}

	echo "\t\t\t\t\t\t\t\t".'<option id="fld'.(++$forum_page['fld_count']).'"  name="forum[]" value="'.$cur_forum['fid'].'" >&nbsp<label for="fld'.$forum_page['fld_count'].'">'.forum_htmlencode($cur_forum['forum_name']).'</label></option>'."\n";
}

?>
			</select>
		</dd>							

				</dl>
				</fieldset>
<?php ($hook = get_hook('se_pre_forum_fieldset_end')) ? eval($hook) : null; ?>	
				</div>
			</div>
<?php ($hook = get_hook('se_forum_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('se_criteria_fieldset_end')) ? eval($hook) : null; ?>
<?php $forum_page['item_count'] = 0; ?>
<?php ($hook = get_hook('se_pre_results_fieldset')) ? eval($hook) : null; ?>
		<div class="panel bg<?php echo ++$forum_page['group_count'] ?>">
			<div class="inner">
				<h3><?php echo $lang_search['Results legend'] ?></h3>
			<fieldset>
				<?php ($hook = get_hook('se_pre_display_choices_fieldset')) ? eval($hook) : null; ?>
<dl>
					<dt><label for="show_results1"><?php echo $lang_search['Display results'] ?></label></dt>
<?php ($hook = get_hook('se_pre_display_choices')) ? eval($hook) : null; ?>
				<dd>
						<label for="show_results1">
							
							<input type="radio" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="show_as" value="topics" checked="checked"/>
							<?php echo $lang_search['Show as topics'] ?>
						</label>
						<label for="show_results2">
							<input type="radio" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="show_as" value="posts" />
							<?php echo $lang_search['Show as posts'] ?>
						</label>
<?php ($hook = get_hook('se_new_display_choices')) ? eval($hook) : null; ?>
				</dd>
<?php ($hook = get_hook('se_pre_display_choices_fieldset_end')) ? eval($hook) : null; ?>
</dl>
<?php ($hook = get_hook('se_pre_results_fieldset_end')) ? eval($hook) : null; ?>
<?php ($hook = get_hook('se_pre_sort_by')) ? eval($hook) : null; ?>
<dl class="dl<?php echo ++$forum_page['item_count'] ?>">
	<dt><label for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_search['Sort by'] ?></label></dt>
	<dd>
		<div class="chosen-container chosen-container-single chosen-container-single-nosearch" style="width: auto;" title="" id="sk_chosen">
			<select id="fld<?php echo $forum_page['fld_count'] ?>" name="sort_by" class="chosen-single">
				<?php echo implode("\n\t\t\t\t\t\t", $forum_page['frm-sorts'])."\n" ?>
			</select>
		</div>
		<?php ($hook = get_hook('se_pre_sort_order_fieldset')) ? eval($hook) : null; ?>
		<?php ($hook = get_hook('se_pre_sort_order')) ? eval($hook) : null; ?>
		&nbsp; <label for="sa">
			<input type="radio" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="sort_dir" value="ASC" checked="checked">
			<?php echo $lang_search['Ascending'] ?></label>
		<label for="sd">
			<input type="radio" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="sort_dir" value="DESC" >
			<?php echo $lang_search['Descending'] ?></label>
		<?php ($hook = get_hook('se_pre_sort_order_fieldset_end')) ? eval($hook) : null; ?>
		
	</dd>
</dl>

			</fieldset>
		</div>
	</div>
<?php ($hook = get_hook('se_results_fieldset_end')) ? eval($hook) : null; ?>
<div class="panel bg3">
	<div class="inner">
		<fieldset class="submit-buttons">
			<input type="submit" name="submit" value="<?php echo $lang_search['Submit search'] ?>" class="button1">
		</fieldset>
	</div>
</div>

		</form>
	</div>
<?php

($hook = get_hook('se_end')) ? eval($hook) : null;

$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <forum_main>


require FORUM_ROOT.'footer.php';
