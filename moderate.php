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

($hook = get_hook('mr_start')) ? eval($hook) : null;

// Load the misc.php language file
require FORUM_ROOT.'lang/'.$forum_user['language'].'/misc.php';


// This particular function doesn't require forum-based moderator access. It can be used
// by all moderators and admins.
if (isset($_GET['get_host']))
{
	if (!$forum_user['is_admmod'])
		message($lang_common['No permission']);

	($hook = get_hook('mr_view_ip_selected')) ? eval($hook) : null;

	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['get_host'], $_GET['get_host']));

	// Is get_host an IP address or a post ID?
	if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $_GET['get_host']) || preg_match('/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/', $_GET['get_host']))
		$ip = $_GET['get_host'];
	else
	{
		$get_host = intval($_GET['get_host']);
		if ($get_host < 1)
			message($lang_common['Bad request']);

		$query = array(
			'SELECT'	=> 'p.poster_ip',
			'FROM'		=> 'posts AS p',
			'WHERE'		=> 'p.id='.$get_host
		);

		($hook = get_hook('mr_view_ip_qr_get_poster_ip')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		if (!$forum_db->num_rows($result))
			message($lang_common['Bad request']);

		$ip = $forum_db->result($result);
	}

	($hook = get_hook('mr_view_ip_pre_output')) ? eval($hook) : null;

	message(sprintf($lang_misc['Hostname lookup'], forum_htmlencode($ip), @gethostbyaddr(forum_htmlencode($ip)), '<a href="'.forum_link('admin/users.php').'?show_users='.$ip.'">'.$lang_misc['Show more users'].'</a>'));
}


// All other functions require moderator/admin access
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
if ($fid < 1)
	message($lang_common['Bad request']);

// Get some info about the forum we're moderating
$query = array(
	'SELECT'	=> 'f.forum_name, f.redirect_url, f.num_topics, f.moderators, f.sort_by',
	'FROM'		=> 'forums AS f',
	'JOINS'		=> array(
		array(
			'LEFT JOIN'		=> 'forum_perms AS fp',
			'ON'			=> '(fp.forum_id=f.id AND fp.group_id='.$forum_user['g_id'].')'
		)
	),
	'WHERE'		=> '(fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$fid
);

($hook = get_hook('mr_qr_get_forum_data')) ? eval($hook) : null;
$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
if (!$forum_db->num_rows($result))
	message($lang_common['Bad request']);

$cur_forum = $forum_db->fetch_assoc($result);

// Make sure we're not trying to moderate a redirect forum
if ($cur_forum['redirect_url'] != '')
	message($lang_common['Bad request']);

// Setup the array of moderators
$mods_array = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

($hook = get_hook('mr_pre_permission_check')) ? eval($hook) : null;

if ($forum_user['g_id'] != FORUM_ADMIN && ($forum_user['g_moderator'] != '1' || !array_key_exists($forum_user['username'], $mods_array)))
	message($lang_common['No permission']);

// Get topic/forum tracking data
if (!$forum_user['is_guest'])
	$tracked_topics = get_tracked_topics();


// Did someone click a cancel button?
if (isset($_POST['cancel']))
	redirect(forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name']))), $lang_common['Cancel redirect']);


// All topic moderation features require a topic id in GET
if (isset($_GET['tid']))
{
	($hook = get_hook('mr_post_actions_selected')) ? eval($hook) : null;

	$tid = intval($_GET['tid']);
	if ($tid < 1)
		message($lang_common['Bad request']);

	$forum_page['page'] = (!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1) ? 1 : intval($_GET['p']);
	
	// Check for use of incorrect URLs
	confirm_current_url($forum_page['page'] == 1 ? forum_link($forum_url['moderate_topic'], array($fid, $tid)) : forum_sublink($forum_url['moderate_topic'], $forum_url['page'], $forum_page['page'], array($fid, $tid)));

	// Fetch some info about the topic
	$query = array(
		'SELECT'	=> 't.subject, t.poster, t.first_post_id, t.posted, t.num_replies',
		'FROM'		=> 'topics AS t',
		'WHERE'		=> 't.id='.$tid.' AND t.moved_to IS NULL'
	);

	($hook = get_hook('mr_post_actions_qr_get_topic_info')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
	if (!$forum_db->num_rows($result))
		message($lang_common['Bad request']);

	$cur_topic = $forum_db->fetch_assoc($result);

	// User pressed the cancel button
	if (isset($_POST['delete_posts_cancel']))
		redirect(forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject']))), $lang_common['Cancel redirect']);

	// Delete one or more posts
	if (isset($_POST['delete_posts']) || isset($_POST['delete_posts_comply']))
	{
		($hook = get_hook('mr_delete_posts_form_submitted')) ? eval($hook) : null;

		$posts = isset($_POST['posts']) && !empty($_POST['posts']) ? $_POST['posts'] : array();
		$posts = array_map('intval', (is_array($posts) ? $posts : explode(',', $posts)));

		if (empty($posts))
			message($lang_misc['No posts selected']);

		if (isset($_POST['delete_posts_comply']))
		{
			if (!isset($_POST['req_confirm']))
				redirect(forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject']))), $lang_common['No confirm redirect']);

			($hook = get_hook('mr_confirm_delete_posts_form_submitted')) ? eval($hook) : null;

			// Verify that the post IDs are valid
			$query = array(
				'SELECT'	=> 'COUNT(p.id)',
				'FROM'		=> 'posts AS p',
				'WHERE'		=> 'p.id IN('.implode(',', $posts).') AND p.id!='.$cur_topic['first_post_id'].' AND p.topic_id='.$tid
			);

			($hook = get_hook('mr_confirm_delete_posts_qr_verify_post_ids')) ? eval($hook) : null;
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			if ($forum_db->result($result) != count($posts))
				message($lang_common['Bad request']);

			// Delete the posts
			$query = array(
				'DELETE'	=> 'posts',
				'WHERE'		=> 'id IN('.implode(',', $posts).')'
			);

			($hook = get_hook('mr_confirm_delete_posts_qr_delete_posts')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);

			if (!defined('FORUM_SEARCH_IDX_FUNCTIONS_LOADED'))
				require FORUM_ROOT.'include/search_idx.php';

			strip_search_index($posts);

			if (!defined('FORUM_FUNCTIONS_SYNS'))
				require FORUM_ROOT.'include/functions/synchronize.php';

			sync_topic($tid);
			sync_forum($fid);

			($hook = get_hook('mr_confirm_delete_posts_pre_redirect')) ? eval($hook) : null;

			redirect(forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject']))), $lang_misc['Delete posts redirect']);
		}

		// Setup form
		$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
		$forum_page['form_action'] = forum_link($forum_url['moderate_topic'], array($fid, $tid));

		$forum_page['hidden_fields'] = array(
			'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
			'posts'			=> '<input type="hidden" name="posts" value="'.implode(',', $posts).'" />'
		);

		// Setup breadcrumbs
		$forum_page['crumbs'] = array(
			array($forum_config['o_board_title'], forum_link($forum_url['index'])),
			array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
			array($cur_topic['subject'], forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject'])))),
			$lang_misc['Delete posts']
		);

		($hook = get_hook('mr_confirm_delete_posts_pre_header_load')) ? eval($hook) : null;

		define('FORUM_PAGE', 'dialogue');
		require FORUM_ROOT.'header.php';

		// START SUBST - <forum_main>
		ob_start();

		($hook = get_hook('mr_confirm_delete_posts_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('mr_confirm_delete_posts_pre_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_misc['Delete posts'] ?></strong></legend>
<?php ($hook = get_hook('mr_confirm_delete_posts_pre_confirm_checkbox')) ? eval($hook) : null; ?>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="req_confirm" value="1" checked="checked" /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php echo $lang_common['Please confirm'] ?></span> <?php echo $lang_misc['Confirm post delete'] ?>.</label>
					</div>
				</div>
<?php ($hook = get_hook('mr_confirm_delete_posts_pre_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('mr_confirm_delete_posts_fieldset_end')) ? eval($hook) : null; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="delete_posts_comply" value="<?php echo $lang_common['Delete'] ?>" /></span>
				<span class="cancel"><input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

		$forum_id = $fid;

		($hook = get_hook('mr_confirm_delete_posts_end')) ? eval($hook) : null;

		$tpl_temp = forum_trim(ob_get_contents());
		$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
		ob_end_clean();
		// END SUBST - <forum_main>

		require FORUM_ROOT.'footer.php';
	}
	else if (isset($_POST['split_posts']) || isset($_POST['split_posts_comply']))
	{
		($hook = get_hook('mr_split_posts_form_submitted')) ? eval($hook) : null;

		$posts = isset($_POST['posts']) && !empty($_POST['posts']) ? $_POST['posts'] : array();
		$posts = array_map('intval', (is_array($posts) ? $posts : explode(',', $posts)));

		if (empty($posts))
			message($lang_misc['No posts selected']);

		if (isset($_POST['split_posts_comply']))
		{
			if (!isset($_POST['req_confirm']))
				redirect(forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject']))), $lang_common['No confirm redirect']);

			// Load the post.php language file
			require FORUM_ROOT.'lang/'.$forum_user['language'].'/post.php';

			($hook = get_hook('mr_confirm_split_posts_form_submitted')) ? eval($hook) : null;

			// Verify that the post IDs are valid
			$query = array(
				'SELECT'	=> 'COUNT(p.id)',
				'FROM'		=> 'posts AS p',
				'WHERE'		=> 'p.id IN('.implode(',', $posts).') AND p.id!='.$cur_topic['first_post_id'].' AND p.topic_id='.$tid
			);

			($hook = get_hook('mr_confirm_split_posts_qr_verify_post_ids')) ? eval($hook) : null;
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			if ($forum_db->result($result) != count($posts))
				message($lang_common['Bad request']);

			$new_subject = isset($_POST['new_subject']) ? forum_trim($_POST['new_subject']) : '';

			if ($new_subject == '')
				message($lang_post['No subject']);
			else if (utf8_strlen($new_subject) > 70)
				message($lang_post['Too long subject']);

			// Get data from the new first post
			$query = array(
				'SELECT'	=> 'p.id, p.poster, p.posted',
				'FROM'		=> 'posts AS p',
				'WHERE'		=> 'p.id IN('.implode(',', $posts).')',
				'ORDER BY'	=> 'p.id',
				'LIMIT'		=> '1'
			);

			($hook = get_hook('mr_confirm_split_posts_qr_get_first_post_data')) ? eval($hook) : null;
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			$first_post_data = $forum_db->fetch_assoc($result);

			// Create the new topic
			$query = array(
				'INSERT'	=> 'poster, subject, posted, first_post_id, forum_id',
				'INTO'		=> 'topics',
				'VALUES'	=> '\''.$forum_db->escape($first_post_data['poster']).'\', \''.$forum_db->escape($new_subject).'\', '.$first_post_data['posted'].', '.$first_post_data['id'].', '.$fid
			);

			($hook = get_hook('mr_confirm_split_posts_qr_add_topic')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
			$new_tid = $forum_db->insert_id();

			// Move the posts to the new topic
			$query = array(
				'UPDATE'	=> 'posts',
				'SET'		=> 'topic_id='.$new_tid,
				'WHERE'		=> 'id IN('.implode(',', $posts).')'
			);

			($hook = get_hook('mr_confirm_split_posts_qr_move_posts')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);

			// Sync last post data for the old topic, the new topic, and the forum itself
			if (!defined('FORUM_FUNCTIONS_SYNS'))
				require FORUM_ROOT.'include/functions/synchronize.php';

			sync_topic($new_tid);
			sync_topic($tid);
			sync_forum($fid);

			($hook = get_hook('mr_confirm_split_posts_pre_redirect')) ? eval($hook) : null;

			redirect(forum_link($forum_url['topic'], array($new_tid, sef_friendly($new_subject))), $lang_misc['Split posts redirect']);
		}

		// Setup form
		$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
		$forum_page['form_action'] = forum_link($forum_url['moderate_topic'], array($fid, $tid));

		$forum_page['hidden_fields'] = array(
			'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
			'posts'			=> '<input type="hidden" name="posts" value="'.implode(',', $posts).'" />'
		);

		// Setup breadcrumbs
		$forum_page['crumbs'] = array(
			array($forum_config['o_board_title'], forum_link($forum_url['index'])),
			array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
			array($cur_topic['subject'], forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject'])))),
			$lang_misc['Split posts']
		);

		($hook = get_hook('mr_confirm_split_posts_pre_header_load')) ? eval($hook) : null;

		define('FORUM_PAGE', 'dialogue');
		require FORUM_ROOT.'header.php';

		// START SUBST - <forum_main>
		ob_start();

		($hook = get_hook('mr_confirm_split_posts_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('mr_confirm_split_posts_pre_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_misc['Split posts'] ?></strong></legend>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
<?php ($hook = get_hook('mr_confirm_split_posts_pre_subject')) ? eval($hook) : null; ?>
					<div class="sf-box text required">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_misc['New subject'] ?> <em><?php echo $lang_common['Required'] ?></em></span></label><br />
						<span class="fld-input"><input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="new_subject" value="" size="80" maxlength="70" /></span>
					</div>
<?php ($hook = get_hook('mr_confirm_split_posts_pre_confirm_checkbox')) ? eval($hook) : null; ?>
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="req_confirm" value="1" checked="checked" /></span>
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_common['Please confirm'] ?></span> <?php echo $lang_misc['Confirm topic split'] ?>.</label>
					</div>
				</div>
<?php ($hook = get_hook('mr_confirm_split_posts_pre_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('mr_confirm_split_posts_fieldset_end')) ? eval($hook) : null; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="split_posts_comply" value="<?php echo $lang_common['Split'] ?>" /></span>
				<span class="cancel"><input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

		$forum_id = $fid;

		($hook = get_hook('mr_confirm_split_posts_end')) ? eval($hook) : null;

		$tpl_temp = forum_trim(ob_get_contents());
		$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
		ob_end_clean();
		// END SUBST - <forum_main>

		require FORUM_ROOT.'footer.php';
	}

	// Show the moderate topic view

	// Load the viewtopic.php language file
	require FORUM_ROOT.'lang/'.$forum_user['language'].'/topic.php';

	// Used to disable the Split and Delete buttons if there are no replies to this topic
	$forum_page['button_status'] = ($cur_topic['num_replies'] == 0) ? ' disabled="disabled"' : '';


	// Determine the post offset (based on $_GET['p'])
	$forum_page['num_pages'] = ceil(($cur_topic['num_replies'] + 1) / $forum_user['disp_posts']);
	$forum_page['page'] = (!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $forum_page['num_pages']) ? 1 : $_GET['p'];
	$forum_page['start_from'] = $forum_user['disp_posts'] * ($forum_page['page'] - 1);
	$forum_page['finish_at'] = min(($forum_page['start_from'] + $forum_user['disp_posts']), ($cur_topic['num_replies'] + 1));
	$forum_page['items_info'] = generate_items_info($lang_misc['Posts'], ($forum_page['start_from'] + 1), ($cur_topic['num_replies'] + 1));

	// Generate paging links
	$forum_page['page_post']['paging'] = '<p class="paging"><span class="pages">'.$lang_common['Pages'].'</span> '.paginate($forum_page['num_pages'], $forum_page['page'], $forum_url['moderate_topic'], $lang_common['Paging separator'], array($fid, $tid)).'</p>';

	// Navigation links for header and page numbering for title/meta description
	if ($forum_page['page'] < $forum_page['num_pages'])
	{
		$forum_page['nav']['last'] = '<link rel="last" href="'.forum_sublink($forum_url['moderate_topic'], $forum_url['page'], $forum_page['num_pages'], array($fid, $tid)).'" title="'.$lang_common['Page'].' '.$forum_page['num_pages'].'" />';
		$forum_page['nav']['next'] = '<link rel="next" href="'.forum_sublink($forum_url['moderate_topic'], $forum_url['page'], ($forum_page['page'] + 1), array($fid, $tid)).'" title="'.$lang_common['Page'].' '.($forum_page['page'] + 1).'" />';
	}
	if ($forum_page['page'] > 1)
	{
		$forum_page['nav']['prev'] = '<link rel="prev" href="'.forum_sublink($forum_url['moderate_topic'], $forum_url['page'], ($forum_page['page'] - 1), array($fid, $tid)).'" title="'.$lang_common['Page'].' '.($forum_page['page'] - 1).'" />';
		$forum_page['nav']['first'] = '<link rel="first" href="'.forum_link($forum_url['moderate_topic'], array($fid, $tid)).'" title="'.$lang_common['Page'].' 1" />';
	}

	if ($forum_config['o_censoring'])
		$cur_topic['subject'] = censor_words($cur_topic['subject']);

	// Setup form
	$forum_page['form_action'] = forum_link($forum_url['moderate_topic'], array($fid, $tid));

	// Setup breadcrumbs
	$forum_page['crumbs'] = array(
		array($forum_config['o_board_title'], forum_link($forum_url['index'])),
		array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
		array($cur_topic['subject'], forum_link($forum_url['topic'], array($tid, sef_friendly($cur_topic['subject'])))),
		$lang_topic['Moderate topic']
	);

	// Setup main heading
	$forum_page['main_title'] = sprintf($lang_misc['Moderate topic head'], forum_htmlencode($cur_topic['subject']));

	$forum_page['main_head_options']['select_all'] = '<span '.(empty($forum_page['main_head_options']) ? ' class="first-item"' : '').'><a href="#" onclick="return Forum.toggleCheckboxes(document.getElementById(\'mr-post-actions-form\'))">'.$lang_misc['Select all'].'</a></span>';
	$forum_page['main_foot_options']['select_all'] = '<span '.(empty($forum_page['main_foot_options']) ? ' class="first-item"' : '').'><a href="#" onclick="return Forum.toggleCheckboxes(document.getElementById(\'mr-post-actions-form\'))">'.$lang_misc['Select all'].'</a></span>';

	if ($forum_page['num_pages'] > 1)
		$forum_page['main_head_pages'] = sprintf($lang_common['Page info'], $forum_page['page'], $forum_page['num_pages']);

	($hook = get_hook('mr_post_actions_pre_header_load')) ? eval($hook) : null;

	define('FORUM_PAGE', 'modtopic');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('mr_post_actions_output_start')) ? eval($hook) : null;

?>
	<div class="main-head">
<?php

	if (!empty($forum_page['main_head_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_head_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
	<form id="mr-post-actions-form" class="newform" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
		<div class="main-content main-topic">
			<div class="hidden">
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token($forum_page['form_action']) ?>" />
			</div>

<?php

	if (!defined('FORUM_PARSER_LOADED'))
		require FORUM_ROOT.'include/parser.php';

	$forum_page['item_count'] = 0; // Keep track of post numbers

	// Retrieve the posts (and their respective poster)
	$query = array(
		'SELECT'	=> 'u.title, u.num_posts, g.g_id, g.g_user_title, p.id, p.poster, p.poster_id, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by',
		'FROM'		=> 'posts AS p',
		'JOINS'		=> array(
			array(
				'INNER JOIN'	=> 'users AS u',
				'ON'			=> 'u.id=p.poster_id'
			),
			array(
				'INNER JOIN'	=> 'groups AS g',
				'ON'			=> 'g.g_id=u.group_id'
			)
		),
		'WHERE'		=> 'p.topic_id='.$tid,
		'ORDER BY'	=> 'p.id',
		'LIMIT'		=> $forum_page['start_from'].','.$forum_user['disp_posts']
	);

	($hook = get_hook('mr_post_actions_qr_get_posts')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
	while ($cur_post = $forum_db->fetch_assoc($result))
	{
		($hook = get_hook('mr_post_actions_loop_start')) ? eval($hook) : null;

		++$forum_page['item_count'];

		$forum_page['post_ident'] = array();
		$forum_page['message'] = array();
		$forum_page['user_ident'] = array();
		$cur_post['username'] = $cur_post['poster'];

		// Generate the post heading
		$forum_page['post_ident']['num'] = '<span class="post-num">'.forum_number_format($forum_page['start_from'] + $forum_page['item_count']).'</span>';

		if ($cur_post['poster_id'] > 1)
			$forum_page['post_ident']['byline'] = '<span class="post-byline">'.sprintf((($cur_post['id'] == $cur_topic['first_post_id']) ? $lang_topic['Topic byline'] : $lang_topic['Reply byline']), (($forum_user['g_view_users']) ? '<a title="'.sprintf($lang_topic['Go to profile'], forum_htmlencode($cur_post['username'])).'" href="'.forum_link($forum_url['user'], $cur_post['poster_id']).'">'.forum_htmlencode($cur_post['username']).'</a>' : '<strong>'.forum_htmlencode($cur_post['username']).'</strong>')).'</span>';
		else
			$forum_page['post_ident']['byline'] = '<span class="post-byline">'.sprintf((($cur_post['id'] == $cur_topic['first_post_id']) ? $lang_topic['Topic byline'] : $lang_topic['Reply byline']), '<strong>'.forum_htmlencode($cur_post['username']).'</strong>').'</span>';

		$forum_page['post_ident']['link'] = '<span class="post-link"><a class="permalink" rel="bookmark" title="'.$lang_topic['Permalink post'].'" href="'.forum_link($forum_url['post'], $cur_post['id']).'">'.format_time($cur_post['posted']).'</a></span>';

		($hook = get_hook('mr_row_pre_item_ident_merge')) ? eval($hook) : null;

		// Generate the checkbox field
		if ($cur_post['id'] != $cur_topic['first_post_id'])
			$forum_page['item_select'] = '<p class="item-select"><input type="checkbox" id="fld'.$cur_post['id'].'" name="posts[]" value="'.$cur_post['id'].'" /> <label for="fld'.$cur_post['id'].'">'.$lang_misc['Select post'].' '.forum_number_format($forum_page['start_from'] + $forum_page['item_count']).'</label></p>';

		// Generate author identification
		$forum_page['author_ident']['username'] = '<li class="username">'.(($cur_post['poster_id'] > '1') ? '<a title="'.sprintf($lang_topic['Go to profile'], forum_htmlencode($cur_post['username'])).'" href="'.forum_link($forum_url['user'], $cur_post['poster_id']).'">'.forum_htmlencode($cur_post['username']).'</a>' : '<strong>'.forum_htmlencode($cur_post['username']).'</strong>').'</li>';
		$forum_page['author_ident']['usertitle'] = '<li class="usertitle"><span>'.get_title($cur_post).'</span></li>';

		// Give the post some class
		$forum_page['item_status'] = array(
			'post',
			//($forum_page['item_count'] % 2 != 0) ? 'odd' : 'even'
			($forum_page['item_count'] % 2 == 0) ? 'even' : 'odd'
		);

		if ($forum_page['item_count'] == 1)
			$forum_page['item_status']['firstpost'] = 'firstpost';

		if (($forum_page['start_from'] + $forum_page['item_count']) == $forum_page['finish_at'])
			$forum_page['item_status']['lastpost'] = 'lastpost';

		if ($cur_post['id'] == $cur_topic['first_post_id'])
			$forum_page['item_status']['topicpost'] = 'topicpost';
		else
			$forum_page['item_status']['replypost'] = 'replypost';

		// Generate the post title
		if ($cur_post['id'] == $cur_topic['first_post_id'])
			$forum_page['item_subject'] = sprintf($lang_topic['Topic title'], $cur_topic['subject']);
		else
			$forum_page['item_subject'] = sprintf($lang_topic['Reply title'], $cur_topic['subject']);

		$forum_page['item_subject'] = forum_htmlencode($forum_page['item_subject']);

		// Perform the main parsing of the message (BBCode, smilies, censor words etc)
		$forum_page['message']['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);

		if ($cur_post['edited'] != '')
			$forum_page['message']['edited'] = '<p class="lastedit"><em>'.sprintf($lang_topic['Last edited'], forum_htmlencode($cur_post['edited_by']), format_time($cur_post['edited'])).'</em></p>';

		($hook = get_hook('mr_post_actions_row_pre_display')) ? eval($hook) : null;

?>
			<div class="<?php echo implode(' ', $forum_page['item_status']) ?>">
				<div id="p<?php echo $cur_post['id'] ?>" class="posthead">
					<h3 class="hn post-ident"><?php echo implode(' ', $forum_page['post_ident']) ?></h3>
<?php ($hook = get_hook('mr_post_actions_pre_item_select')) ? eval($hook) : null; ?>
<?php if (isset($forum_page['item_select'])) echo "\t\t\t\t".$forum_page['item_select']."\n" ?>
<?php ($hook = get_hook('mr_post_actions_new_post_head_option')) ? eval($hook) : null; ?>
				</div>
				<div class="postbody">
					<div class="post-author">
						<ul class="author-ident">
							<?php echo implode("\n\t\t\t\t\t\t", $forum_page['author_ident'])."\n" ?>
						</ul>
<?php ($hook = get_hook('mr_post_actions_new_user_ident_data')) ? eval($hook) : null; ?>
					</div>
					<div class="post-entry">
						<h4 class="entry-title"><?php echo $forum_page['item_subject'] ?></h4>
						<div class="entry-content">
							<?php echo implode("\n\t\t\t\t\t\t\t", $forum_page['message'])."\n" ?>
						</div>
<?php ($hook = get_hook('mr_post_actions_new_post_entry_data')) ? eval($hook) : null; ?>
					</div>
				</div>
			</div>
<?php

	}

?>
	</div>
<?php

$forum_page['mod_options'] = array(
	'del_posts'		=> '<span class="submit first-item"><input type="submit" name="delete_posts" value="'.$lang_misc['Delete posts'].'" /></span>',
	'split_posts'	=> '<span class="submit"><input type="submit" name="split_posts" value="'.$lang_misc['Split posts'].'" /></span>',
	'del_topic'		=> '<span><a href="'.forum_link($forum_url['delete'], $cur_topic['first_post_id']).'">'.$lang_misc['Delete whole topic'].'</a></span>'
);

($hook = get_hook('mr_post_actions_pre_mod_options')) ? eval($hook) : null;

?>
		<div class="main-options mod-options gen-content">
			<p class="options"><?php echo implode(' ', $forum_page['mod_options']) ?></p>
		</div>
	</form>
	<div class="main-foot">
<?php

	if (!empty($forum_page['main_foot_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_foot_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
<?php

	$forum_id = $fid;

	($hook = get_hook('mr_post_actions_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}


// Move one or more topics
if (isset($_REQUEST['move_topics']) || isset($_POST['move_topics_to']))
{
	if (isset($_POST['move_topics_to']))
	{
		// Check for use of incorrect URLs
		confirm_current_url(forum_link($forum_url['moderate_forum'], $fid));
		
		($hook = get_hook('mr_confirm_move_topics_form_submitted')) ? eval($hook) : null;

		$topics = isset($_POST['topics']) && !empty($_POST['topics']) ? explode(',', $_POST['topics']) : array();
		$topics = array_map('intval', $topics);

		$move_to_forum = isset($_POST['move_to_forum']) ? intval($_POST['move_to_forum']) : 0;
		if (empty($topics) || $move_to_forum < 1)
			message($lang_common['Bad request']);

		// Fetch the forum name for the forum we're moving to
		$query = array(
			'SELECT'	=> 'f.forum_name',
			'FROM'		=> 'forums AS f',
			'WHERE'		=> 'f.id='.$move_to_forum
		);

		($hook = get_hook('mr_confirm_move_topics_qr_get_move_to_forum_name')) ? eval($hook) : null;

		if (!$forum_db->num_rows($result))
			message($lang_common['Bad request']);

		$move_to_forum_name = $forum_db->result($result);

		// Verify that the topic IDs are valid
		$query = array(
			'SELECT'	=> 'COUNT(t.id)',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.id IN('.implode(',', $topics).') AND t.forum_id='.$fid
		);

		($hook = get_hook('mr_confirm_move_topics_qr_verify_topic_ids')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		if ($forum_db->result($result) != count($topics))
			message($lang_common['Bad request']);

		// Delete any redirect topics if there are any (only if we moved/copied the topic back to where it where it was once moved from)
		$query = array(
			'DELETE'	=> 'topics',
			'WHERE'		=> 'forum_id='.$move_to_forum.' AND moved_to IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_move_topics_qr_delete_redirect_topics')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		// Move the topic(s)
		$query = array(
			'UPDATE'	=> 'topics',
			'SET'		=> 'forum_id='.$move_to_forum,
			'WHERE'		=> 'id IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_move_topics_qr_move_topics')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		// Should we create redirect topics?
		if (isset($_POST['with_redirect']))
		{
			foreach ($topics as $cur_topic)
			{
				// Fetch info for the redirect topic
				$query = array(
					'SELECT'	=> 't.poster, t.subject, t.posted, t.last_post',
					'FROM'		=> 'topics AS t',
					'WHERE'		=> 't.id='.$cur_topic
				);

				($hook = get_hook('mr_confirm_move_topics_qr_get_redirect_topic_data')) ? eval($hook) : null;
				$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
				$moved_to = $forum_db->fetch_assoc($result);

				// Create the redirect topic
				$query = array(
					'INSERT'	=> 'poster, subject, posted, last_post, moved_to, forum_id',
					'INTO'		=> 'topics',
					'VALUES'	=> '\''.$forum_db->escape($moved_to['poster']).'\', \''.$forum_db->escape($moved_to['subject']).'\', '.$moved_to['posted'].', '.$moved_to['last_post'].', '.$cur_topic.', '.$fid
				);

				($hook = get_hook('mr_confirm_move_topics_qr_add_redirect_topic')) ? eval($hook) : null;
				$forum_db->query_build($query) or error(__FILE__, __LINE__);

			}
		}

		if (!defined('FORUM_FUNCTIONS_SYNS'))
			require FORUM_ROOT.'include/functions/synchronize.php';

		sync_forum($fid); // Synchronize the forum FROM which the topic was moved
		sync_forum($move_to_forum); // Synchronize the forum TO which the topic was moved

		$forum_page['redirect_msg'] = (count($topics) > 1) ? $lang_misc['Move topics redirect'] : $lang_misc['Move topic redirect'];

		($hook = get_hook('mr_confirm_move_topics_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link($forum_url['forum'], array($move_to_forum, sef_friendly($move_to_forum_name))), $forum_page['redirect_msg']);
	}

	if (isset($_POST['move_topics']))
	{
		// Check for use of incorrect URLs
		confirm_current_url(forum_link($forum_url['moderate_forum'], $fid));
		
		$topics = isset($_POST['topics']) && is_array($_POST['topics']) ? $_POST['topics'] : array();
		$topics = array_map('intval', $topics);

		if (empty($topics))
			message($lang_misc['No topics selected']);

		if (count($topics) == 1)
		{
			$topics = $topics[0];
			$action = 'single';
		}
		else
			$action = 'multiple';
	}
	else
	{
		$topics = intval($_GET['move_topics']);
		if ($topics < 1)
			message($lang_common['Bad request']);

		$action = 'single';
		// Check for use of incorrect URLs
		confirm_current_url(forum_link($forum_url['move'], array($fid, $topics)));
	}
	
	if (is_array($topics))
	{
		$action = 'multi';
		$topics = implode(',', $topics);
	}
	else
		$action = 'single';

	if ($action == 'single')
	{

		// Fetch the topic subject
		$query = array(
			'SELECT'	=> 't.subject',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.id='.$topics
		);

		($hook = get_hook('mr_move_topics_qr_get_topic_to_move_subject')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

		if (!$forum_db->num_rows($result))
			message($lang_common['Bad request']);

		$subject = $forum_db->result($result);
	}

	// Get forums we can move the post into
	$query = array(
		'SELECT'	=> 'c.id AS cid, c.cat_name, f.id AS fid, f.forum_name',
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
		'WHERE'		=> '(fp.read_forum IS NULL OR fp.read_forum=1) AND f.redirect_url IS NULL AND f.id!='.$fid,
		'ORDER BY'	=> 'c.disp_position, c.id, f.disp_position'
	);

	($hook = get_hook('mr_move_topics_qr_get_target_forums')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	if (!$forum_db->num_rows($result))
		message($lang_misc['Nowhere to move']);

	$forum_list = array();
	while ($temp = $forum_db->fetch_assoc($result))
		$forum_list[] = $temp;

	// Setup form
	$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
	$forum_page['form_action'] = forum_link($forum_url['moderate_forum'], $fid);

	$forum_page['hidden_fields'] = array(
		'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
		'topics'	=> '<input type="hidden" name="topics" value="'.$topics.'" />'
	);

	// Setup breadcrumbs
	$forum_page['crumbs'][] = array($forum_config['o_board_title'], forum_link($forum_url['index']));
	$forum_page['crumbs'][] = array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name']))));
	if ($action == 'single')
		$forum_page['crumbs'][] = array($subject, forum_link($forum_url['topic'], array($topics, sef_friendly($subject))));
	else
		$forum_page['crumbs'][] = array($lang_misc['Moderate forum'], forum_link($forum_url['moderate_forum'], $fid));
	$forum_page['crumbs'][] = ($action == 'single') ? $lang_misc['Move topic'] : $lang_misc['Move topics'];

	($hook = get_hook('mr_move_topics_pre_header_load')) ? eval($hook) : null;

	define('FORUM_PAGE', 'dialogue');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('mr_move_topics_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('mr_move_topics_pre_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_misc['Move topic'] ?></strong></legend>
<?php ($hook = get_hook('mr_move_topics_pre_move_to_forum')) ? eval($hook) : null; ?>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box select">
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><span><?php echo $lang_misc['Move to'] ?></span></label><br />
						<span class="fld-input"><select id="fld<?php echo $forum_page['fld_count'] ?>" name="move_to_forum">
<?php

	$forum_page['cur_category'] = 0;
	foreach ($forum_list as $cur_forum)
	{
		if ($cur_forum['cid'] != $forum_page['cur_category']) // A new category since last iteration?
		{
			if ($forum_page['cur_category'])
				echo "\t\t\t\t".'</optgroup>'."\n";

			echo "\t\t\t\t".'<optgroup label="'.forum_htmlencode($cur_forum['cat_name']).'">'."\n";
			$forum_page['cur_category'] = $cur_forum['cid'];
		}

		if ($cur_forum['fid'] != $fid)
			echo "\t\t\t\t".'<option value="'.$cur_forum['fid'].'">'.forum_htmlencode($cur_forum['forum_name']).'</option>'."\n";
	}

?>
						</optgroup>
						</select></span>
					</div>
				</div>
<?php ($hook = get_hook('mr_move_topics_pre_redirect_checkbox')) ? eval($hook) : null; ?>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo (++$forum_page['fld_count']) ?>" name="with_redirect" value="1"<?php if ($action == 'single') echo ' checked="checked"' ?> /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php echo $lang_misc['Redirect topic'] ?></span> <?php echo ($action == 'single') ? $lang_misc['Leave redirect'] : $lang_misc['Leave redirects'] ?></label>
					</div>
				</div>
<?php ($hook = get_hook('mr_move_topics_pre_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('mr_move_topics_fieldset_end')) ? eval($hook) : null; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="move_topics_to" value="<?php echo $lang_misc['Move'] ?>" /></span>
				<span class="cancel"><input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

	$forum_id = $fid;

	($hook = get_hook('mr_move_topics_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}


// Merge topics
else if (isset($_POST['merge_topics']) || isset($_POST['merge_topics_comply']))
{
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['moderate_forum'], $fid));

	$topics = isset($_POST['topics']) && !empty($_POST['topics']) ? $_POST['topics'] : array();
	$topics = array_map('intval', (is_array($topics) ? $topics : explode(',', $topics)));

	if (empty($topics))
		message($lang_misc['No topics selected']);

	if (count($topics) == 1)
		message($lang_misc['Merge error']);

	$query = array(
		'SELECT'	=> 'question, read_unvote, revote, poll_created, days_count, votes_count',
		'FROM'		=> 'topics',
		'WHERE'		=> 'id IN('.implode(',', $topics).') AND question!=\'\''
	);

	($hook = get_hook('mr_confirm_merge_topics_qr_questions')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
	$num_polls = $forum_db->num_rows($result);

	if ($num_polls > 1)
		message($lang_misc['Merge poll error']);
	else if ($num_polls == 1)
		list($question, $read_unvote, $revote, $poll_created, $days_count, $votes_count) = $forum_db->fetch_row($result);

	if (isset($_POST['merge_topics_comply']))
	{
		($hook = get_hook('mr_confirm_merge_topics_form_submitted')) ? eval($hook) : null;

		// Verify that the topic IDs are valid
		$query = array(
			'SELECT'	=> 'COUNT(t.id), MIN(t.id)',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.id IN('.implode(',', $topics).') AND t.moved_to IS NULL AND t.forum_id='.$fid
		);

		($hook = get_hook('mr_confirm_merge_topics_qr_verify_topic_ids')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		list($num_topics, $merge_to_tid) = $forum_db->fetch_row($result);
		if ($num_topics != count($topics))
			message($lang_common['Bad request']);

		// Make any redirect topics point to our new, merged topic
		$query = array(
			'UPDATE'	=> 'topics',
			'SET'		=> 'moved_to='.$merge_to_tid,
			'WHERE'		=> 'moved_to IN('.implode(',', $topics).')'
		);

		// Should we create redirect topics?
		if (isset($_POST['with_redirect']))
			$query['WHERE'] .= ' OR (id IN('.implode(',', $topics).') AND id != '.$merge_to_tid.')';

		($hook = get_hook('mr_confirm_merge_topics_qr_fix_redirect_topics')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		// Merge the posts into the topic
		$query = array(
			'UPDATE'	=> 'posts',
			'SET'		=> 'topic_id='.$merge_to_tid,
			'WHERE'		=> 'topic_id IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_merge_topics_qr_merge_posts')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		if ($num_polls)
		{
			$query = array(
				'UPDATE'	=> 'topics',
				'SET'		=> 'question=\''.$forum_db->escape($question).'\', read_unvote='.$read_unvote.', revote='.$revote.', poll_created='.$revote.', days_count='.$days_count.', votes_count='.$votes_count,
				'WHERE'		=> 'id='.$merge_to_tid
			);

			($hook = get_hook('mr_confirm_merge_topics_qr_merge_question')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);

			$query = array(
				'UPDATE'	=> 'answers',
				'SET'		=> 'topic_id='.$merge_to_tid,
				'WHERE'		=> 'topic_id IN('.implode(',', $topics).')'
			);

			($hook = get_hook('mr_confirm_merge_topics_qr_merge_answers')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}
		// Delete any subscriptions
		$query = array(
			'DELETE'	=> 'subscriptions',
			'WHERE'		=> 'topic_id IN('.implode(',', $topics).') AND topic_id != '.$merge_to_tid
		);

		($hook = get_hook('mr_confirm_merge_topics_qr_delete_subscriptions')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		if (!isset($_POST['with_redirect']))
		{
			// Delete the topics that have been merged
			$query = array(
				'DELETE'	=> 'topics',
				'WHERE'		=> 'id IN('.implode(',', $topics).') AND id != '.$merge_to_tid
			);

			($hook = get_hook('mr_confirm_merge_topics_qr_delete_merged_topics')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}

		// Synchronize the topic we merged to and the forum where the topics were merged
		if (!defined('FORUM_FUNCTIONS_SYNS_TOPIC'))
			require FORUM_ROOT.'include/functions/synchronize.php';

		sync_topic($merge_to_tid);
		sync_forum($fid);

		($hook = get_hook('mr_confirm_merge_topics_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name']))), $lang_misc['Merge topics redirect']);
	}

	// Setup form
	$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
	$forum_page['form_action'] = forum_link($forum_url['moderate_forum'], $fid);

	$forum_page['hidden_fields'] = array(
		'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
		'topics'		=> '<input type="hidden" name="topics" value="'.implode(',', $topics).'" />'
	);

	// Setup breadcrumbs
	$forum_page['crumbs'] = array(
		array($forum_config['o_board_title'], forum_link($forum_url['index'])),
		array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
		array($lang_misc['Moderate forum'], forum_link($forum_url['moderate_forum'], $fid)),
		$lang_misc['Merge topics']
	);

	// Setup main heading
	$forum_page['main_title'] = end($forum_page['crumbs']);

	($hook = get_hook('mr_merge_topics_pre_header_load')) ? eval($hook) : null;

	define('FORUM_PAGE', 'dialogue');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('mr_merge_topics_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('mr_merge_topics_pre_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $lang_misc['Merge topics'] ?></strong></legend>
<?php ($hook = get_hook('mr_merge_topics_pre_redirect_checkbox')) ? eval($hook) : null; ?>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo (++$forum_page['fld_count']) ?>" name="with_redirect" value="1" /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php echo $lang_misc['Redirect topic'] ?></span> <?php echo $lang_misc['Leave merge redirects'] ?></label>
					</div>
				</div>
<?php ($hook = get_hook('mr_merge_topics_pre_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('mr_merge_topics_fieldset_end')) ? eval($hook) : null; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="merge_topics_comply" value="<?php echo $lang_misc['Merge'] ?>" /></span>
				<span class="cancel"><input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

	$forum_id = $fid;

	($hook = get_hook('mr_merge_topics_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}


// Delete one or more topics
else if (isset($_REQUEST['delete_topics']) || isset($_POST['delete_topics_comply']))
{
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['moderate_forum'], $fid));

	$topics = isset($_POST['topics']) && !empty($_POST['topics']) ? $_POST['topics'] : array();
	$topics = array_map('intval', (is_array($topics) ? $topics : explode(',', $topics)));

	if (empty($topics))
		message($lang_misc['No topics selected']);

	$multi = count($topics) > 1;

	if (isset($_POST['delete_topics_comply']))
	{
		if (!isset($_POST['req_confirm']))
			redirect(forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name']))), $lang_common['Cancel redirect']);

		($hook = get_hook('mr_confirm_delete_topics_form_submitted')) ? eval($hook) : null;

		// Verify that the topic IDs are valid
		$query = array(
			'SELECT'	=> 'COUNT(t.id)',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.id IN('.implode(',', $topics).') AND t.forum_id='.$fid
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_verify_topic_ids')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		if ($forum_db->result($result) != count($topics))
			message($lang_common['Bad request']);

		// Create an array of forum IDs that need to be synced
		$forum_ids = array($fid);
		$query = array(
			'SELECT'	=> 't.forum_id',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.moved_to IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_get_forums_to_sync')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		while ($row = $forum_db->fetch_row($result))
			$forum_ids[] = $row[0];

		// Delete the topics and any redirect topics
		$query = array(
			'DELETE'	=> 'topics',
			'WHERE'		=> 'id IN('.implode(',', $topics).') OR moved_to IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_delete_topics')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		$poll_topic_ids = isset($topic_ids) ? $topic_ids : implode(',', $topics);
		$query = array(
			'DELETE'	=> 'voting',
			'WHERE'		=> 'topic_id IN('.$poll_topic_ids.')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_delete_voting')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		$query = array(
			'DELETE'	=> 'answers',
			'WHERE'		=> 'topic_id IN('.$poll_topic_ids.')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_delete_answers')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
		unset($pun_poll_topic_ids);

		// Delete any subscriptions
		$query = array(
			'DELETE'	=> 'subscriptions',
			'WHERE'		=> 'topic_id IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_delete_subscriptions')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		// Create a list of the post ID's in the deleted topic and strip the search index
		$query = array(
			'SELECT'	=> 'p.id',
			'FROM'		=> 'posts AS p',
			'WHERE'		=> 'p.topic_id IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_get_deleted_posts')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

		$post_ids = array();
		while ($row = $forum_db->fetch_row($result))
			$post_ids[] = $row[0];

		// Strip the search index provided we're not just deleting redirect topics
		if (!empty($post_ids))
		{
			if (!defined('FORUM_SEARCH_IDX_FUNCTIONS_LOADED'))
				require FORUM_ROOT.'include/search_idx.php';

			strip_search_index($post_ids);
		}

		// Delete posts
		$query = array(
			'DELETE'	=> 'posts',
			'WHERE'		=> 'topic_id IN('.implode(',', $topics).')'
		);

		($hook = get_hook('mr_confirm_delete_topics_qr_delete_topic_posts')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		if (!defined('FORUM_FUNCTIONS_SYNS'))
			require FORUM_ROOT.'include/functions/synchronize.php';

		foreach ($forum_ids as $cur_forum_id)
			sync_forum($cur_forum_id);

		($hook = get_hook('mr_confirm_delete_topics_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name']))), $multi ? $lang_misc['Delete topics redirect'] : $lang_misc['Delete topic redirect']);
	}


	// Setup form
	$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] =0;
	$forum_page['form_action'] = forum_link($forum_url['moderate_forum'], $fid);

	$forum_page['hidden_fields'] = array(
		'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
		'topics'		=> '<input type="hidden" name="topics" value="'.implode(',', $topics).'" />'
	);

	// Setup breadcrumbs
	$forum_page['crumbs'] = array(
		array($forum_config['o_board_title'], forum_link($forum_url['index'])),
		array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
		array($lang_misc['Moderate forum'], forum_link($forum_url['moderate_forum'], $fid)),
		$multi ? $lang_misc['Delete topics'] : $lang_misc['Delete topic']
	);

	($hook = get_hook('mr_delete_topics_pre_header_load')) ? eval($hook) : null;

	define('FORUM_PAGE', 'dialogue');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('mr_delete_topics_output_start')) ? eval($hook) : null;

?>
	<div class="main-content main-frm">
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('mr_delete_topics_pre_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
				<legend class="group-legend"><strong><?php echo $multi ? $lang_misc['Delete topics'] : $lang_misc['Delete topics'] ?></strong></legend>
<?php ($hook = get_hook('mr_delete_topics_pre_confirm_checkbox')) ? eval($hook) : null; ?>
				<div class="sf-set set<?php echo ++$forum_page['item_count'] ?>">
					<div class="sf-box checkbox">
						<span class="fld-input"><input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" name="req_confirm" value="1" checked="checked" /></span>
						<label for="fld<?php echo $forum_page['fld_count'] ?>"><span><?php echo $lang_common['Please confirm'] ?></span> <?php echo $multi ? $lang_misc['Delete topics comply'] : $lang_misc['Delete topic comply'] ?></label>
					</div>
				</div>
<?php ($hook = get_hook('mr_delete_topics_pre_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('mr_delete_topics_fieldset_end')) ? eval($hook) : null; ?>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="delete_topics_comply" value="<?php echo $lang_common['Delete'] ?>" /></span>
				<span class="cancel"><input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

	$forum_id = $fid;

	($hook = get_hook('mr_delete_topics_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}


// Open or close one or more topics
else if (isset($_REQUEST['open']) || isset($_REQUEST['close']))
{
	$action = (isset($_REQUEST['open'])) ? 0 : 1;

	($hook = get_hook('mr_open_close_topic_selected')) ? eval($hook) : null;

	// There could be an array of topic ID's in $_POST
	if (isset($_POST['open']) || isset($_POST['close']))
	{
		// Check for use of incorrect URLs
		confirm_current_url(forum_link($forum_url['moderate_forum'], $fid));
		
		$topics = isset($_POST['topics']) && is_array($_POST['topics']) ? $_POST['topics'] : array();
		$topics = array_map('intval', $topics);

		if (empty($topics))
			message($lang_misc['No topics selected']);

		$query = array(
			'UPDATE'	=> 'topics',
			'SET'		=> 'closed='.$action,
			'WHERE'		=> 'id IN('.implode(',', $topics).') AND forum_id='.$fid
		);

		($hook = get_hook('mr_open_close_multi_topics_qr_open_close_topics')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		if (count($topics) == 1)
			$forum_page['redirect_msg'] = ($action) ? $lang_misc['Close topic redirect'] : $lang_misc['Open topic redirect'];
		else
			$forum_page['redirect_msg'] = ($action) ? $lang_misc['Close topics redirect'] : $lang_misc['Open topics redirect'];

		($hook = get_hook('mr_open_close_multi_topics_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link($forum_url['moderate_forum'], $fid), $forum_page['redirect_msg']);
	}
	// Or just one in $_GET
	else
	{
		$topic_id = ($action) ? intval($_GET['close']) : intval($_GET['open']);
		if ($topic_id < 1)
			message($lang_common['Bad request']);
		
		// Check for use of incorrect URLs
		confirm_current_url(forum_link($forum_url['mod'], array(($action ? 'close' : 'open'), $fid, $topic_id, isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')));

		// We validate the CSRF token. If it's set in POST and we're at this point, the token is valid.
		// If it's in GET, we need to make sure it's valid.
		if (!isset($_POST['csrf_token']) && (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token(($action ? 'close' : 'open').$topic_id)))
			csrf_confirm_form();

		// Get the topic subject
		$query = array(
			'SELECT'	=> 't.subject',
			'FROM'		=> 'topics AS t',
			'WHERE'		=> 't.id='.$topic_id.' AND forum_id='.$fid
		);

		($hook = get_hook('mr_open_close_single_topic_qr_get_subject')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

		if (!$forum_db->num_rows($result))
			message($lang_common['Bad request']);

		$subject = $forum_db->result($result);

		$query = array(
			'UPDATE'	=> 'topics',
			'SET'		=> 'closed='.$action,
			'WHERE'		=> 'id='.$topic_id.' AND forum_id='.$fid
		);

		($hook = get_hook('mr_open_close_single_topic_qr_open_close_topic')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		$forum_page['redirect_msg'] = ($action) ? $lang_misc['Close topic redirect'] : $lang_misc['Open topic redirect'];

		($hook = get_hook('mr_open_close_single_topic_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link($forum_url['topic'], array($topic_id, sef_friendly($subject))), $forum_page['redirect_msg']);
	}
}


// Stick a topic
else if (isset($_GET['stick']))
{
	$stick = intval($_GET['stick']);
	if ($stick < 1)
		message($lang_common['Bad request']);
	
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['mod'], array('stick', $fid, $stick, isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')));

	// We validate the CSRF token. If it's set in POST and we're at this point, the token is valid.
	// If it's in GET, we need to make sure it's valid.
	if (!isset($_POST['csrf_token']) && (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token('stick'.$stick)))
		csrf_confirm_form();

	($hook = get_hook('mr_stick_topic_selected')) ? eval($hook) : null;

	// Get the topic subject
	$query = array(
		'SELECT'	=> 't.subject',
		'FROM'		=> 'topics AS t',
		'WHERE'		=> 't.id='.$stick.' AND forum_id='.$fid
	);

	($hook = get_hook('mr_stick_topic_qr_get_subject')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	if (!$forum_db->num_rows($result))
		message($lang_common['Bad request']);

	$subject = $forum_db->result($result);

	$query = array(
		'UPDATE'	=> 'topics',
		'SET'		=> 'sticky=1',
		'WHERE'		=> 'id='.$stick.' AND forum_id='.$fid
	);

	($hook = get_hook('mr_stick_topic_qr_stick_topic')) ? eval($hook) : null;
	$forum_db->query_build($query) or error(__FILE__, __LINE__);

	($hook = get_hook('mr_stick_topic_pre_redirect')) ? eval($hook) : null;

	redirect(forum_link($forum_url['topic'], array($stick, sef_friendly($subject))), $lang_misc['Stick topic redirect']);
}


// Unstick a topic
else if (isset($_GET['unstick']))
{
	$unstick = intval($_GET['unstick']);
	if ($unstick < 1)
		message($lang_common['Bad request']);
	
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['mod'], array('unstick', $fid, $unstick, isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')));

	// We validate the CSRF token. If it's set in POST and we're at this point, the token is valid.
	// If it's in GET, we need to make sure it's valid.
	if (!isset($_POST['csrf_token']) && (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token('unstick'.$unstick)))
		csrf_confirm_form();

	($hook = get_hook('mr_unstick_topic_selected')) ? eval($hook) : null;

	// Get the topic subject
	$query = array(
		'SELECT'	=> 't.subject',
		'FROM'		=> 'topics AS t',
		'WHERE'		=> 't.id='.$unstick.' AND forum_id='.$fid
	);

	($hook = get_hook('mr_unstick_topic_qr_get_subject')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

	if (!$forum_db->num_rows($result))
		message($lang_common['Bad request']);

	$subject = $forum_db->result($result);

	$query = array(
		'UPDATE'	=> 'topics',
		'SET'		=> 'sticky=0',
		'WHERE'		=> 'id='.$unstick.' AND forum_id='.$fid
	);

	($hook = get_hook('mr_unstick_topic_qr_unstick_topic')) ? eval($hook) : null;
	$forum_db->query_build($query) or error(__FILE__, __LINE__);

	($hook = get_hook('mr_unstick_topic_pre_redirect')) ? eval($hook) : null;

	redirect(forum_link($forum_url['topic'], array($unstick, sef_friendly($subject))), $lang_misc['Unstick topic redirect']);
}

($hook = get_hook('mr_new_action')) ? eval($hook) : null;


// No specific forum moderation action was specified in the query string, so we'll display the moderate forum view

// If forum is empty
if ($cur_forum['num_topics'] == 0)
	message($lang_common['Bad request']);

// Load the viewforum.php language file
require FORUM_ROOT.'lang/'.$forum_user['language'].'/forum.php';

// Determine the topic offset (based on $_GET['p'])
$forum_page['num_pages'] = ceil($cur_forum['num_topics'] / $forum_user['disp_topics']);

$forum_page['page'] = (!isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $forum_page['num_pages']) ? 1 : $_GET['p'];
$forum_page['start_from'] = $forum_user['disp_topics'] * ($forum_page['page'] - 1);
$forum_page['finish_at'] = min(($forum_page['start_from'] + $forum_user['disp_topics']), ($cur_forum['num_topics']));
$forum_page['items_info'] = generate_items_info($lang_misc['Topics'], ($forum_page['start_from'] + 1), $cur_forum['num_topics']);

// Check for use of incorrect URLs
confirm_current_url($forum_page['page'] == 1 ? forum_link($forum_url['moderate_forum'], $fid) : forum_sublink($forum_url['moderate_forum'], $forum_url['page'], $forum_page['page'], $fid));

// Select topics
$query = array(
	'SELECT'	=> 't.id, t.poster, t.poster_id, t.subject, t.description, t.question, t.posted, t.last_post, t.last_post_id, t.last_poster, t.last_poster_id, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to',
	'FROM'		=> 'topics AS t',
	'WHERE'		=> 'forum_id='.$fid,
	'ORDER BY'	=> 't.sticky DESC, '.(($cur_forum['sort_by'] == '1') ? 't.posted' : 't.last_post').' DESC',
	'LIMIT'		=> $forum_page['start_from'].', '.$forum_user['disp_topics']
);

// With "has posted" indication
if (!$forum_user['is_guest'] && $forum_config['o_show_dot'])
{
	$subquery = array(
		'SELECT'	=> 'COUNT(p.id)',
		'FROM'		=> 'posts AS p',
		'WHERE'		=> 'p.poster_id='.$forum_user['id'].' AND p.topic_id=t.id'
	);

	($hook = get_hook('mr_qr_get_has_posted')) ? eval($hook) : null;
	$query['SELECT'] .= ', ('.$forum_db->query_build($subquery, true).') AS has_posted';
}

($hook = get_hook('mr_qr_get_topics')) ? eval($hook) : null;
$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

// Generate paging links
$forum_page['page_post']['paging'] = '<p class="paging"><span class="pages">'.$lang_common['Pages'].'</span> '.paginate($forum_page['num_pages'], $forum_page['page'], $forum_url['moderate_forum'], $lang_common['Paging separator'], $fid).'</p>';

// Navigation links for header and page numbering for title/meta description
if ($forum_page['page'] < $forum_page['num_pages'])
{
	$forum_page['nav']['last'] = '<link rel="last" href="'.forum_sublink($forum_url['moderate_forum'], $forum_url['page'], $forum_page['num_pages'], $fid).'" title="'.$lang_common['Page'].' '.$forum_page['num_pages'].'" />';
	$forum_page['nav']['next'] = '<link rel="next" href="'.forum_sublink($forum_url['moderate_forum'], $forum_url['page'], ($forum_page['page'] + 1), $fid).'" title="'.$lang_common['Page'].' '.($forum_page['page'] + 1).'" />';
}
if ($forum_page['page'] > 1)
{
	$forum_page['nav']['prev'] = '<link rel="prev" href="'.forum_sublink($forum_url['moderate_forum'], $forum_url['page'], ($forum_page['page'] - 1), $fid).'" title="'.$lang_common['Page'].' '.($forum_page['page'] - 1).'" />';
	$forum_page['nav']['first'] = '<link rel="first" href="'.forum_link($forum_url['moderate_forum'], $fid).'" title="'.$lang_common['Page'].' 1" />';
}

// Setup form
$forum_page['fld_count'] = 0;
$forum_page['form_action'] = forum_link($forum_url['moderate_forum'], $fid);

// Setup breadcrumbs
$forum_page['crumbs'] = array(
	array($forum_config['o_board_title'], forum_link($forum_url['index'])),
	array($cur_forum['forum_name'], forum_link($forum_url['forum'], array($fid, sef_friendly($cur_forum['forum_name'])))),
	sprintf($lang_misc['Moderate forum head'], forum_htmlencode($cur_forum['forum_name']))
);

// Setup main header
$forum_page['main_title'] = $lang_misc['Moderate forum head'];

// Setup main heading
if ($forum_page['num_pages'] > 1)
	$forum_page['main_head_pages'] = sprintf($lang_common['Page info'], $forum_page['page'], $forum_page['num_pages']);

$forum_page['main_head_options']['select_all'] = '<span '.(empty($forum_page['main_foot_options']) ? ' class="first-item"' : '').'><a href="#" onclick="return Forum.toggleCheckboxes(document.getElementById(\'mr-topic-actions-form\'))">'.$lang_misc['Select all'].'</a></span>';
$forum_page['main_foot_options']['select_all'] = '<span '.(empty($forum_page['main_foot_options']) ? ' class="first-item"' : '').'><a href="#" onclick="return Forum.toggleCheckboxes(document.getElementById(\'mr-topic-actions-form\'))">'.$lang_misc['Select all'].'</a></span>';

($hook = get_hook('mr_topic_actions_pre_header_load')) ? eval($hook) : null;

$forum_js->file(array('jquery', 'tooltip'));
$forum_js->code('$(function() {
		$(\'.item-subject a\').tooltip({ track: true, delay: 0, showURL: false, showBody: " - ", fade: 250 });
		$(\'#block\').click($.tooltip.block);
	});');

define('FORUM_PAGE', 'modforum');
require FORUM_ROOT.'header.php';

// START SUBST - <forum_main>
ob_start();

$forum_page['item_header'] = array();
$forum_page['item_header']['title'] = '<th class="item-subject" scope="col">'.$lang_forum['Topics'].'</th>';
$forum_page['item_header']['replies'] = '<th class="info-replies" scope="col">'.$lang_forum['Replies'].'</th>';

if ($forum_config['o_topic_views'])
	$forum_page['item_header']['views'] = '<th class="info-views" scope="col">'.$lang_forum['Views'].'</th>';

$forum_page['item_header']['lastpost'] = '<th class="info-lastpost" scope="col">'.$lang_forum['Last post'].'</th>';
$forum_page['item_header']['select'] = '<th class="info-select" scope="col"></th>';

($hook = get_hook('mr_topic_actions_output_start')) ? eval($hook) : null;

?>
	<div class="main-head">
<?php

	if (!empty($forum_page['main_head_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_head_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
	<form id="mr-topic-actions-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
	<div id="forum<?php echo $fid ?>" class="main-content main-forum">
		<div class="hidden">
			<input type="hidden" name="csrf_token" value="<?php echo generate_form_token($forum_page['form_action']) ?>" />
		</div>
		<table cellspacing="0">
		<thead>
			<tr>
				<?php echo implode("\n\t\t\t\t", $forum_page['item_header'])."\n" ?>
			</tr>
		</thead>
		<tbody>
<?php

	$forum_page['item_count'] = 0;

	while ($cur_topic = $forum_db->fetch_assoc($result))
	{
		($hook = get_hook('mr_topic_actions_row_loop_start')) ? eval($hook) : null;

		++$forum_page['item_count'];

		// Start from scratch
		$forum_page['item_subject'] = $forum_page['item_body'] = $forum_page['item_status'] = $forum_page['item_nav'] = $forum_page['item_title'] = $forum_page['item_title_status'] = array();

		if ($forum_config['o_censoring'])
			$cur_topic['subject'] = censor_words($cur_topic['subject']);

		$forum_page['item_subject']['starter'] = '<span class="item-starter">'.sprintf($lang_forum['Topic starter'], '<cite><a href="'.forum_link($forum_url['user'],$cur_topic['poster_id']).'">'.forum_htmlencode($cur_topic['poster']).'</a></cite>').'</span>';

		if ($cur_topic['moved_to'] != null)
		{
			$forum_page['item_title_status']['moved'] = '<em class="moved">'.$lang_forum['Moved'].'</em>';
			$forum_page['item_status']['moved'] = 'moved';

			$forum_page['item_title']['status'] = '<span class="item-status">'.sprintf($lang_forum['Item status'], implode(', ', $forum_page['item_title_status'])).'</span>';

			$forum_page['item_title']['link'] = '<strong><a href="'.forum_link($forum_url['topic'], array($cur_topic['moved_to'], sef_friendly($cur_topic['subject']))).'">'.forum_htmlencode($cur_topic['subject']).'</a></strong>';

			// Combine everything to produce the Topic heading
			$forum_page['item_body']['subject']['title'] = '<h3>'.implode(' ', $forum_page['item_title']).'</h3>';

			($hook = get_hook('vf_topic_loop_moved_topic_pre_item_subject_merge')) ? eval($hook) : null;

			$forum_page['item_body']['subject']['desc'] = implode(' ', $forum_page['item_subject']);

			$forum_page['item_body']['info']['replies'] = '<td class="info-replies"></td>';

			if ($forum_config['o_topic_views'])
				$forum_page['item_body']['info']['views'] = '<td class="info-views"></td>';

			$forum_page['item_body']['info']['replies'] = '<td class="info-replies"></td>';
			$forum_page['item_body']['info']['lastpost'] = '<td class="info-lastpost"></td>';
			$forum_page['item_body']['info']['select'] = '<td class="info-select"><input id="fld'.++$forum_page['fld_count'].'" type="checkbox" name="topics[]" value="'.$cur_topic['id'].'" /></td>';

			($hook = get_hook('mr_topic_actions_moved_row_pre_output')) ? eval($hook) : null;
		}
		else
		{
			$forum_page['ghost_topic'] = false;

			// First assemble the Topic heading

			// Should we display the dot or not? :)
			if (!$forum_user['is_guest'] && $forum_config['o_show_dot'] && $cur_topic['has_posted'] > 0)
			{
				$forum_page['item_title']['posted'] = '<span class="posted-mark">'.$lang_forum['You posted indicator'].'</span>';
				$forum_page['item_status']['posted'] = 'posted';
			}

			if ($cur_topic['sticky'])
			{
				$forum_page['item_title_status']['sticky'] = '<em class="sticky">'.$lang_forum['Sticky'].'</em>';
				$forum_page['item_status']['sticky'] = 'sticky';
			}
			else if ($cur_topic['closed'])
			{
				$forum_page['item_title_status']['closed'] = '<em class="closed">'.$lang_forum['Closed'].'</em>';
				$forum_page['item_status']['closed'] = 'closed';
			}
			else if ($cur_topic['question'] != '')
			{
				$forum_page['item_title_status']['poll'] = '<em class="poll">'.$lang_forum['Poll'].'</em>';
				$forum_page['item_status']['poll'] = 'poll';
			}

			($hook = get_hook('mr_topic_loop_normal_topic_pre_item_title_status_merge')) ? eval($hook) : null;

			if (!empty($forum_page['item_title_status']))
				$forum_page['item_title']['status'] = '<span class="item-status">'.sprintf($lang_forum['Item status'], implode(', ', $forum_page['item_title_status'])).'</span>';

			$topic_desc = array();
			if ($cur_topic['description'] != '')
				$topic_desc['description'] = ' - '.forum_htmlencode(forum_htmlencode($cur_topic['description']));
			if ($cur_topic['question'] != '')
				$topic_desc['question'] = ' - '.forum_htmlencode(forum_htmlencode($cur_topic['question']));

			$forum_page['item_title']['link'] = '<strong><a href="'.forum_link($forum_url['topic'], array($cur_topic['id'], sef_friendly($cur_topic['subject']))).'">'.forum_htmlencode($cur_topic['subject']).'</a></strong>';
			$forum_page['item_title']['link'] = '<strong><a href="'.forum_link($forum_url['topic'], array($cur_topic['id'], sef_friendly($cur_topic['subject']))).'"'.(!empty($topic_desc) ? ' title="'.$lang_forum['Description'].implode('', $topic_desc).'"' : '').'>'.forum_htmlencode($cur_topic['subject']).'</a></strong>';

			if (empty($forum_page['item_status']))
				$forum_page['item_status']['normal'] = 'normal';
			
			$forum_page['item_body']['subject']['title'] = '<h3 class="hn">'.implode(' ', $forum_page['item_title']).'</h3>';

			($hook = get_hook('mr_topic_actions_moved_row_pre_item_subject_status_merge')) ? eval($hook) : null;

			$forum_page['item_pages'] = ceil(($cur_topic['num_replies'] + 1) / $forum_user['disp_posts']);

			if ($forum_page['item_pages'] > 1)
				$forum_page['item_nav']['pages'] = '<span>'.$lang_forum['Pages'].'&#160;</span>'.paginate($forum_page['item_pages'], -1, $forum_url['topic'], $lang_common['Page separator'], array($cur_topic['id'], sef_friendly($cur_topic['subject'])));

			// Does this topic contain posts we haven't read? If so, tag it accordingly.
			if (!$forum_user['is_guest'] && $cur_topic['last_post'] > $forum_user['last_visit'] && (!isset($tracked_topics['topics'][$cur_topic['id']]) || $tracked_topics['topics'][$cur_topic['id']] < $cur_topic['last_post']) && (!isset($tracked_topics['forums'][$fid]) || $tracked_topics['forums'][$fid] < $cur_topic['last_post']))
			{
				$forum_page['item_nav']['new'] = '<em class="item-newposts"><a href="'.forum_link($forum_url['topic_new_posts'], array($cur_topic['id'], sef_friendly($cur_topic['subject']))).'">'.$lang_forum['New posts'].'</a></em>';
				$forum_page['item_status']['new'] = 'new';
			}

			if (!empty($forum_page['item_nav']))
				$forum_page['item_title']['nav'] = '<span class="item-nav">'.sprintf($lang_forum['Topic navigation'], implode('&#160;&#160;', $forum_page['item_nav'])).'</span>';

			($hook = get_hook('mr_topic_actions_moved_row_pre_item_title_merge')) ? eval($hook) : null;

			$forum_page['item_body']['subject']['desc'] = implode(' ', $forum_page['item_subject']);

			$forum_page['item_body']['info']['replies'] = '<td class="info-replies"><span class="'.item_size($cur_topic['num_replies']).'">'.forum_number_format($cur_topic['num_replies']).'</span></td>';

			if ($forum_config['o_topic_views'])
				$forum_page['item_body']['info']['views'] = '<td class="info-views"><span class="'.item_size($cur_topic['num_views']).'">'.forum_number_format($cur_topic['num_views']).'</span></li>';

			$forum_page['item_body']['info']['lastpost'] = '<td class="info-lastpost"><span><a href="'.forum_link($forum_url['post'], $cur_topic['last_post_id']).'">'.format_time($cur_topic['last_post']).'</a></span> <cite>'.sprintf($lang_forum['by poster'], '<a href="'.forum_link($forum_url['user'],$cur_topic['last_poster_id']).'">'.forum_htmlencode($cur_topic['last_poster']).'</a>').'</cite></td>';

			$forum_page['item_body']['info']['select'] = '<td class="info-select"><input id="fld'.++$forum_page['fld_count'].'" type="checkbox" name="topics[]" value="'.$cur_topic['id'].'" /></td>';

			($hook = get_hook('mr_topic_actions_normal_row_pre_output')) ? eval($hook) : null;
		}

		($hook = get_hook('mr_topic_actions_normal_row_pre_output')) ? eval($hook) : null;

		$forum_page['item_style'] = (($forum_page['item_count'] % 2 != 0) ? ' odd' : ' even').(($forum_page['item_count'] == 1) ? ' main-first-item' : '').((!empty($forum_page['item_status'])) ? ' '.implode(' ', $forum_page['item_status']) : '');

		($hook = get_hook('mr_topic_actions_row_pre_display')) ? eval($hook) : null;

?>
			<tr class="<?php echo implode(' ', $forum_page['item_status']) ?> <?php echo ($forum_page['item_count'] % 2 != 0) ? 'odd' : 'even' ?><?php echo ($forum_page['item_count'] == 1) ? ' row1' : '' ?>">
				<td class="item-subject">
					<div class="icon"><!-- --></div>
					<div class="tclcon">
						<?php echo implode("\n\t\t\t\t", $forum_page['item_body']['subject'])."\n" ?>
					</div>
				</td>
				<?php echo implode("\n\t\t\t\t", $forum_page['item_body']['info'])."\n" ?>
			</tr>
<?php

	}

?>
		</tbody>
		</table>
	</div>
<?php

	($hook = get_hook('mr_topic_actions_post_topic_list')) ? eval($hook) : null;

	// Setup moderator control buttons
	$forum_page['mod_options'] = array(
		'mod_move'		=> '<span class="submit first-item"><input type="submit" name="move_topics" value="'.$lang_misc['Move'].'" /></span>',
		'mod_delete'	=> '<span class="submit"><input type="submit" name="delete_topics" value="'.$lang_common['Delete'].'" /></span>',
		'mod_merge'		=> '<span class="submit"><input type="submit" name="merge_topics" value="'.$lang_misc['Merge'].'" /></span>',
		'mod_open'		=> '<span class="submit"><input type="submit" name="open" value="'.$lang_misc['Open'].'" /></span>',
		'mod_close'		=> '<span class="submit"><input type="submit" name="close" value="'.$lang_misc['Close'].'" /></span>'
	);

	($hook = get_hook('mr_topic_actions_pre_mod_option_output')) ? eval($hook) : null;

?>
		<div class="main-options mod-options gen-content">
			<p class="options"><?php echo implode(' ', $forum_page['mod_options']) ?></p>
		</div>
	</form>
	<div class="main-foot">
<?php

	if (!empty($forum_page['main_foot_options']))
		echo "\t\t".'<p class="options">'.implode(' ', $forum_page['main_foot_options']).'</p>'."\n";

?>
		<h2 class="hn"><span><?php echo $forum_page['items_info'] ?></span></h2>
	</div>
<?php

$forum_id = $fid;

($hook = get_hook('mr_end')) ? eval($hook) : null;

$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <forum_main>

require FORUM_ROOT.'footer.php';
