<?php
/**
 * Category management page
 *
 * Allows administrators to create, reposition, and remove categories.
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2008 Flazy.ru
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package Flazy
 */


if (!defined('FORUM_ROOT'))
	define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/functions/admin.php';

($hook = get_hook('acg_start')) ? eval($hook) : null;

if ($forum_user['g_id'] != FORUM_ADMIN)
	message($lang_common['No permission']);

// Load the admin.php language file
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin_common.php';
require FORUM_ROOT.'lang/'.$forum_user['language'].'/admin_categories.php';


// Add a new category
if (isset($_POST['add_cat']))
{
	$new_cat_name = forum_trim($_POST['new_cat_name']);
	if ($new_cat_name == '')
		message($lang_admin_categories['Must name category']);

	$new_cat_pos = intval($_POST['position']);

	($hook = get_hook('acg_add_cat_form_submitted')) ? eval($hook) : null;

	$query = array(
		'INSERT'	=> 'cat_name, disp_position',
		'INTO'		=> 'categories',
		'VALUES'	=> '\''.$forum_db->escape($new_cat_name).'\', '.$new_cat_pos
	);

	($hook = get_hook('acg_add_cat_qr_add_category')) ? eval($hook) : null;
	$forum_db->query_build($query) or error(__FILE__, __LINE__);

	($hook = get_hook('acg_add_cat_pre_redirect')) ? eval($hook) : null;

	redirect(forum_link('admin/categories.php'), $lang_admin_categories['Category added'].' '.$lang_admin_common['Redirect']);
}


// Delete a category
else if (isset($_POST['del_cat']) || isset($_POST['del_cat_comply']))
{
	$cat_to_delete = intval($_POST['cat_to_delete']);
	if ($cat_to_delete < 1)
		message($lang_common['Bad request']);

	// User pressed the cancel button
	if (isset($_POST['del_cat_cancel']))
		redirect(forum_link('admin/categories.php'), $lang_admin_common['Cancel redirect']);

	($hook = get_hook('acg_del_cat_form_submitted')) ? eval($hook) : null;

	if (isset($_POST['del_cat_comply'])) // Delete a category with all forums and posts
	{
		@set_time_limit(0);

		$query = array(
			'SELECT'	=> 'f.id',
			'FROM'		=> 'forums AS f',
			'WHERE'		=> 'cat_id='.$cat_to_delete
		);

		($hook = get_hook('acg_del_cat_qr_get_forums_to_delete')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		$num_forums = $forum_db->num_rows($result);

		for ($i = 0; $i < $num_forums; ++$i)
		{
			$cur_forum = $forum_db->result($result, $i);

			// Prune all posts and topics
			prune($cur_forum, 1, -1);

			// Delete the forum
			$query = array(
				'DELETE'	=> 'forums',
				'WHERE'		=> 'id='.$cur_forum
			);

			($hook = get_hook('acg_del_cat_qr_delete_forum')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}

		delete_orphans();

		// Delete the category
		$query = array(
			'DELETE'	=> 'categories',
			'WHERE'		=> 'id='.$cat_to_delete
		);

		($hook = get_hook('acg_del_cat_qr_delete_category')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		// Regenerate the quickjump cache
		if (!defined('FORUM_CACHE_QUICKJUMP_LOADED'))
			require FORUM_ROOT.'include/cache/quickjump.php';

		generate_quickjump_cache();

		($hook = get_hook('acg_del_cat_pre_redirect')) ? eval($hook) : null;

		redirect(forum_link('admin/categories.php'), $lang_admin_categories['Category deleted'].' '.$lang_admin_common['Redirect']);
	}
	else // If the user hasn't comfirmed the delete
	{
		$query = array(
			'SELECT'	=> 'c.cat_name',
			'FROM'		=> 'categories AS c',
			'WHERE'		=> 'c.id='.$cat_to_delete
		);

		($hook = get_hook('acg_del_cat_qr_get_category_name')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
		if (!$forum_db->num_rows($result))
			message($lang_common['Bad request']);

		$cat_name = $forum_db->result($result);

		// Setup the form
		$forum_page['form_action'] = forum_link('admin/categories.php');

		$forum_page['hidden_fields'] = array(
			'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />',
			'cat_to_delete'	=> '<input type="hidden" name="cat_to_delete" value="'.$cat_to_delete.'" />'
		);

		// Setup breadcrumbs
		$forum_page['crumbs'] = array(
			array($forum_config['o_board_title'], forum_link($forum_url['index'])),
			array($lang_admin_common['Forum administration'], forum_link('admin/admin.php')),
			array($lang_admin_common['Start'], forum_link('admin/admin.php')),
			array($lang_admin_common['Categories'], forum_link('admin/categories.php')),
			$lang_admin_categories['Delete category']
		);

		($hook = get_hook('acg_del_cat_pre_header_load')) ? eval($hook) : null;

		define('FORUM_PAGE_SECTION', 'start');
		define('FORUM_PAGE', 'admin-categories');
		require FORUM_ROOT.'header.php';

		// START SUBST - <forum_main>
		ob_start();

		($hook = get_hook('acg_del_cat_output_start')) ? eval($hook) : null;

?>
	<div class="main-subhead">
		<h2 class="hn"><span><?php printf($lang_admin_categories['Confirm delete cat'], forum_htmlencode($cat_name)) ?></span></h2>
	</div>
	<div class="main-content main-frm">
		<div class="ct-box warn-box">
			<p class="warn"><?php echo $lang_admin_categories['Delete category warning'] ?></p>
		</div>
		<form class="frm-form" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
			<div class="frm-buttons">
				<span class="submit"><input type="submit" name="del_cat_comply" value="<?php echo $lang_admin_categories['Delete category'] ?>" /></span>
				<span class="cancel"><input type="submit" name="del_cat_cancel" value="<?php echo $lang_admin_common['Cancel'] ?>" /></span>
			</div>
		</form>
	</div>
<?php

		($hook = get_hook('acg_del_cat_end')) ? eval($hook) : null;

		$tpl_temp = forum_trim(ob_get_contents());
		$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
		ob_end_clean();
		// END SUBST - <forum_main>

		require FORUM_ROOT.'footer.php';
	}
}


else if (isset($_POST['update'])) // Change position and name of the categories
{
	$cat_order = array_map('intval', $_POST['cat_order']);
	$cat_name = array_map('forum_trim', $_POST['cat_name']);

	($hook = get_hook('acg_update_cats_form_submitted')) ? eval($hook) : null;

	$query = array(
		'SELECT'	=> 'c.id, c.cat_name, c.disp_position',
		'FROM'		=> 'categories AS c',
		'ORDER BY'	=> 'c.id'
	);

	($hook = get_hook('acg_update_cats_qr_get_categories')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
	while ($cur_cat = $forum_db->fetch_assoc($result))
	{
		// If these aren't set, we're looking at a category that was added after
		// the admin started editing: we don't want to mess with it
		if (isset($cat_name[$cur_cat['id']]) && isset($cat_order[$cur_cat['id']]))
		{
			if ($cat_name[$cur_cat['id']] == '')
				message($lang_admin_categories['Must name category']);

			if ($cat_order[$cur_cat['id']] < 0)
				message($lang_admin_categories['Must be integer']);

			// We only want to update if we changed anything
			if ($cur_cat['cat_name'] != $cat_name[$cur_cat['id']] || $cur_cat['disp_position'] != $cat_order[$cur_cat['id']])
			{
				$query = array(
					'UPDATE'	=> 'categories',
					'SET'		=> 'cat_name=\''.$forum_db->escape($cat_name[$cur_cat['id']]).'\', disp_position='.$cat_order[$cur_cat['id']],
					'WHERE'		=> 'id='.$cur_cat['id']
				);

				($hook = get_hook('acg_update_cats_qr_update_category')) ? eval($hook) : null;
				$forum_db->query_build($query) or error(__FILE__, __LINE__);
			}
		}
	}

	// Regenerate the quickjump cache
	if (!defined('FORUM_CACHE_QUICKJUMP_LOADED'))
		require FORUM_ROOT.'include/cache/quickjump.php';

	generate_quickjump_cache();

	($hook = get_hook('acg_update_cats_pre_redirect')) ? eval($hook) : null;

	redirect(forum_link('admin/categories.php'), $lang_admin_categories['Categories updated'].' '.$lang_admin_common['Redirect']);
}


// Generate an array with all categories
$query = array(
	'SELECT'	=> 'c.id, c.cat_name, c.disp_position',
	'FROM'		=> 'categories AS c',
	'ORDER BY'	=> 'c.disp_position'
);

($hook = get_hook('acg_qr_get_categories')) ? eval($hook) : null;
$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

$cat_list = array();
while ($cur_category = $forum_db->fetch_assoc($result))
	$cat_list[] = $cur_category;

// Setup the form
$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
$forum_page['form_action'] = forum_link('admin/categories.php').'?action=foo';

$forum_page['hidden_fields'] = array(
	'csrf_token'	=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />'
);

// Setup breadcrumbs
$forum_page['crumbs'] = array(
	array($forum_config['o_board_title'], forum_link($forum_url['index'])),
	array($lang_admin_common['Forum administration'], forum_link('admin/admin.php')),
	array($lang_admin_common['Start'], forum_link('admin/admin.php')),
	array($lang_admin_common['Categories'], forum_link('admin/categories.php'))
);

($hook = get_hook('acg_pre_header_load')) ? eval($hook) : null;

define('FORUM_PAGE_SECTION', 'start');
define('FORUM_PAGE', 'admin-categories');
require FORUM_ROOT.'header.php';

// START SUBST - <forum_main>
ob_start();

($hook = get_hook('acg_main_output_start')) ? eval($hook) : null;

?>

          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?php echo $lang_admin_categories['Add category head'] ?></h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">

		<form class="form-horizontal" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
			<?php ($hook = get_hook('acg_pre_add_cat_fieldset')) ? eval($hook) : null; ?>
<fieldset class="gc<?php echo ++$forum_page['group_count'] ?>">
<div class="form-group">
  <label class="col-md-4 control-label" ><?php echo $lang_admin_categories['Add category legend'] ?></label>
  <div class="col-md-5 ic<?php echo ++$forum_page['item_count'] ?>">
<p><?php printf($lang_admin_categories['Add category info'], '<a href="'.forum_link('admin/forums.php').'">'.$lang_admin_categories['Add category info link text'].'</a>') ?></p>
    
  </div>
  <?php ($hook = get_hook('acg_pre_new_category_name')) ? eval($hook) : null; ?>
</div>

<div class="form-group set<?php echo ++$forum_page['item_count'] ?>">
  <label class="col-md-4 control-label" for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_admin_categories['New category label'] ?></label>
  <div class="col-md-5 ic<?php echo ++$forum_page['item_count'] ?>">
    <input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="new_cat_name" size="35" maxlength="80" class="form-control input-md" >
  </div>
</div>
<?php ($hook = get_hook('acg_pre_new_category_position')) ? eval($hook) : null; ?>
<div class="form-group set<?php echo ++$forum_page['item_count'] ?>">
  <label class="col-md-4 control-label" for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_admin_categories['Position label'] ?></label>
  <div class="col-md-5 ic<?php echo ++$forum_page['item_count'] ?>">
    <input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="position" size="3" maxlength="3" class="form-control input-md" >
  </div>
</div>
<?php ($hook = get_hook('acg_pre_add_cat_fieldset_end')) ? eval($hook) : null; ?>
</fieldset>
<?php ($hook = get_hook('acg_add_cat_fieldset_end')) ? eval($hook) : null; ?>
<!-- Button -->
<div class="form-group text-center">
 <input type="submit" name="add_cat" class="btn btn-info" value="<?php echo $lang_admin_categories['Add category'] ?>" />
</div>
</form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
<?php

($hook = get_hook('acg_post_add_cat_form')) ? eval($hook) : null;

// Reset counter
$forum_page['group_count'] = $forum_page['item_count'] = 0;

if (!empty($cat_list))
{

?>
	          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?php echo $lang_admin_categories['Del category head'] ?> <small> <?php echo $lang_admin_categories['Delete category'] ?></small></h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">

		<form class="form-horizontal" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php ($hook = get_hook('acg_pre_del_cat_fieldset')) ? eval($hook) : null; ?>
			<fieldset class="gc<?php echo ++$forum_page['group_count'] ?>">
<?php ($hook = get_hook('acg_pre_del_category_select')) ? eval($hook) : null; ?>
<div class="form-group set<?php echo ++$forum_page['item_count'] ?>">
  <label class="col-md-4 control-label" for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_admin_categories['Select category label'] ?></label>
  <div class="col-md-5">
<p><?php echo $lang_admin_common['Delete help'] ?></p>
    <select id="fld<?php echo $forum_page['fld_count'] ?>"  class="form-control" name="cat_to_delete">
    	<?php
		foreach ($cat_list as $cur_category)
			echo "\t\t\t\t\t\t\t" . '<option value="' . $cur_category['id'] . '">' . forum_htmlencode($cur_category['cat_name']) . '</option>' . "\n";
	?>
    </select>
  </div>
  </div>
<?php ($hook = get_hook('acg_pre_del_cat_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php ($hook = get_hook('acg_del_cat_fieldset_end')) ? eval($hook) : null; ?>
<!-- Button -->
<div class="form-group text-center">
 <input type="submit" name="del_cat" class="btn btn-info" value="<?php echo $lang_admin_categories['Delete category'] ?>" />
</div>
</form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
          



<?php

($hook = get_hook('acg_post_del_cat_form')) ? eval($hook) : null;

// Reset counter
$forum_page['group_count'] = $forum_page['item_count'] = 0;

?>

          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?php echo $lang_admin_categories['Edit categories head'] ?><small><?php printf($lang_admin_categories['Edit category legend'],  '<span class="hideme"> ('.forum_htmlencode($cur_category['cat_name']).')</span>') ?></small></h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">

		<form class="form-horizontal" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">
			<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
<?php

	($hook = get_hook('acg_edit_cat_fieldsets_start')) ? eval($hook) : null;

	foreach ($cat_list as $cur_category)
	{
		$forum_page['item_count'] = 0;

		($hook = get_hook('acg_pre_edit_cur_cat_fieldset')) ? eval($hook) : null;

?>
<fieldset class="gc<?php echo ++$forum_page['group_count'] ?>">
<div class="form-group set<?php echo ++$forum_page['item_count'] ?>">
	<?php ($hook = get_hook('acg_pre_edit_cat_name')) ? eval($hook) : null; ?>
  <label class="col-md-4 control-label" for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_admin_categories['Category name label'] ?></label>
  <div class="col-md-5 ic<?php echo ++$forum_page['item_count'] ?>">
    <input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="cat_name[<?php echo $cur_category['id'] ?>]" value="<?php echo forum_htmlencode($cur_category['cat_name']) ?>" size="35" maxlength="80" class="form-control input-md" >
  </div>
</div>
<?php ($hook = get_hook('acg_pre_edit_cat_position')) ? eval($hook) : null; ?>
<div class="form-group set<?php echo ++$forum_page['item_count'] ?>">
  <label class="col-md-4 control-label" for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_admin_categories['Position label'] ?></label>
  <div class="col-md-5 ic<?php echo ++$forum_page['item_count'] ?>">
    <input type="text" id="fld<?php echo $forum_page['fld_count'] ?>" name="cat_order[<?php echo $cur_category['id'] ?>]" value="<?php echo $cur_category['disp_position'] ?>" size="3" maxlength="3" class="form-control input-md" >
  </div>
</div>
<?php ($hook = get_hook('acg_pre_edit_cur_cat_fieldset_end')) ? eval($hook) : null; ?>
			</fieldset>
<?php

		($hook = get_hook('acg_edit_cur_cat_fieldset_end')) ? eval($hook) : null;
	}

	($hook = get_hook('acg_edit_cat_fieldsets_end')) ? eval($hook) : null;

?>
<!-- Button -->
<div class="form-group text-center">
 <input type="submit" name="update" class="btn btn-info" value="<?php echo $lang_admin_categories['Update all categories'] ?>" />
</div>
</form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
<?php

	($hook = get_hook('acg_post_edit_cat_form')) ? eval($hook) : null;
}

($hook = get_hook('acg_end')) ? eval($hook) : null;

$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <forum_main>

require FORUM_ROOT.'footer_adm.php';
