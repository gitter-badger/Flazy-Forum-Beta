<?php
/**
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2015 Flazy.us
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package Flazy
 */


if (isset($_GET['action']))
	define('FORUM_QUIET_VISIT', 1);

if (!defined('FORUM_ROOT'))
	define('FORUM_ROOT', './');
require FORUM_ROOT.'include/common.php';

($hook = get_hook('li_start')) ? eval($hook) : null;

// Load the login.php language file
require FORUM_ROOT.'lang/'.$forum_user['language'].'/login.php';


$action = isset($_GET['action']) ? $_GET['action'] : null;
$errors = array();

// Login
if (!$action && isset($_POST['form_sent']))
{
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['login']));
	
	$form_username = forum_trim($_POST['req_username']);

	// Авторизация по e-mail'у
	if (!defined('FORUM_EMAIL_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/functions/email.php';

	if (is_valid_email($form_username))
	{
		$query = array(
			'SELECT'	=> 'username',
			'FROM'		=> 'users',
			'WHERE'		=> 'email=\''.$forum_db->escape($form_username).'\''
		);

		($hook = get_hook('li_login_qr_get_username')) ? eval($hook) : null;
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

		list($form_username) = $forum_db->fetch_row($result);
	}

	$form_password = forum_trim($_POST['req_password']);
	$save_pass = isset($_POST['save_pass']);

	($hook = get_hook('li_login_form_submitted')) ? eval($hook) : null;

	// Get user info matching login attempt
	$query = array(
		'SELECT'	=> 'u.id, u.group_id, u.password, u.salt, u.email, u.language, u.security_ip, u.user_agent, u.fasety_auth, u.fasety_last_auth, u.fasety_auth_mail',
		'FROM'		=> 'users AS u'
	);

	if ($db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'mysql_innodb' || $db_type == 'mysqli_innodb')
		$query['WHERE'] = 'username=\''.$forum_db->escape($form_username).'\'';
	else
		$query['WHERE'] = 'LOWER(username)=LOWER(\''.$forum_db->escape($form_username).'\')';

	($hook = get_hook('li_login_qr_get_login_data')) ? eval($hook) : null;
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
	list($user_id, $group_id, $db_password_hash, $salt, $user_email, $user_language, $security_ip, $user_agent, $fasety_auth, $fasety_last_auth, $fasety_auth_mail) = $forum_db->fetch_row($result);

	$authorized = false;
	if (!empty($db_password_hash))
	{
		$sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
		$form_password_hash = forum_hash($form_password, $salt);

		if ($sha1_in_db && $db_password_hash == $form_password_hash)
			$authorized = true;
		else if ((!$sha1_in_db && $db_password_hash == md5($form_password)) || ($sha1_in_db && $db_password_hash == sha1($form_password)))
		{
			$authorized = true;

			$salt = random_key(12);
			$form_password_hash = forum_hash($form_password, $salt);

			// There's an old MD5 hash or an unsalted SHA1 hash in the database, so we replace it
			// with a randomly generated salt and a new, salted SHA1 hash
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'password=\''.$form_password_hash.'\', salt=\''.$forum_db->escape($salt).'\'',
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_login_qr_update_user_hash')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}
	}

	($hook = get_hook('li_login_pre_auth_message')) ? eval($hook) : null;

	$now = time();
	if ($fasety_auth >= 3 && $fasety_last_auth > $now-600)
	{
		$errors[] = $lang_login['Blocking login'];

		if (!$fasety_auth_mail)
		{
			$user_language = (empty($user_language) ? $forum_config['o_default_lang'] : $user_language);
			$mail_tpl = forum_trim(file_get_contents(FORUM_ROOT.'lang/'.$user_language.'/mail_templates/fasety_auth.tpl'));

			// The first row contains the subject
			$first_crlf = strpos($mail_tpl, "\n");
			$mail_subject = forum_trim(substr($mail_tpl, 8, $first_crlf-8));
			$mail_message = forum_trim(substr($mail_tpl, $first_crlf));

			$mail_message = str_replace('<username>', $form_username, $mail_message);
			$mail_message = str_replace('<obtain_pass>', forum_link($forum_url['request_password']), $mail_message);
			$mail_message = str_replace('<board_mailer>', sprintf($lang_common['Forum mailer'], $forum_config['o_board_title']), $mail_message);

			($hook = get_hook('li_login_send_auth_mail')) ? eval($hook) : null;

			forum_mail($user_email, $mail_subject, $mail_message);

			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'fasety_auth_mail=1',
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_login_qr_update_user_fasety_auth_mail')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}
	}

	if (!$authorized && empty($errors))
	{
		$errors[] = sprintf($lang_login['Wrong user/pass']);

		if (!empty($db_password_hash))
		{
			if ($fasety_last_auth < $now-600)
				$fasety_auth = 0;

			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'fasety_auth='.($fasety_auth+1).', fasety_last_auth='.$now,
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_login_qr_update_user_fasety_auth')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}
	}

	// Did everything go according to plan?
	if (empty($errors))
	{
		$users_with_email = array();
		// Update the status if this is the first time the user logged in
		if ($group_id == FORUM_UNVERIFIED)
		{
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'group_id='.$forum_config['o_default_user_group'],
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_login_qr_update_user_group')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);

			// Regenerate cache
			if (!defined('FORUM_CACHE_STAT_USER_LOADED'))
				require FORUM_ROOT.'include/cache/stat_user.php';

			generate_stat_user_cache();
		}

		if ($fasety_auth_mail)
		{
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'fasety_auth_mail=0',
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_login_qr_update_user_unfasety_auth_mail')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}

		// Remove this user's guest entry from the online list
		$query = array(
			'DELETE'	=> 'online',
			'WHERE'		=> 'ident=\''.$forum_db->escape(get_remote_address()).'\''
		);

		($hook = get_hook('li_login_qr_delete_online_user')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);

		$ip = get_remote_address();
		if ($security_ip)
		{
			$security_enabled = (preg_match('/^([0-9]{1,3})\.([0-9]{1,3})$/', $security_ip) || preg_match('/^([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4})$/',  $security_ip)) ? true : false;
			if ($security_enabled)
				$ip = (preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', $ip, $matches) || preg_match('/^([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4}):([0-9A-Fa-f]{1,4})$/', $ip, $matches)) ? $matches[1].'.'.$matches[2] : '0';
		}

		$cur_user_agent = get_user_agent();
		if ($user_agent != $cur_user_agent)
		{
			$query = array(
				'UPDATE'	=> 'users',
				'SET'		=> 'user_agent=\''.$forum_db->escape($cur_user_agent).'\'',
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_fl_login_qr_update_user_agent')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}
		if ($security_ip != $ip)
		{
			$query = array(
				'UPDATE'	=> 'users',
				'SET' 		=> 'security_ip=\''.$forum_db->escape($ip).'\'',
				'WHERE'		=> 'id='.$user_id
			);

			($hook = get_hook('li_fl_login_qr_update_user_ip')) ? eval($hook) : null;
			$forum_db->query_build($query) or error(__FILE__, __LINE__);
		}

		$expire = ($save_pass) ? time() + 1209600 : time() + $forum_config['o_timeout_visit'];
		forum_setcookie($cookie_name, base64_encode($user_id.'|'.$form_password_hash.'|'.$expire.'|'.sha1($salt.$form_password_hash.forum_hash($expire, $salt))), $expire);

		($hook = get_hook('li_login_pre_redirect')) ? eval($hook) : null;

		redirect(forum_htmlencode($_POST['redirect_url']).((substr_count($_POST['redirect_url'], '?') == 1) ? '&amp;' : '?').'login=1', $lang_login['Login redirect']);
	}
}

// Logout
else if ($action == 'out')
{
	if ($forum_user['is_guest'] || !isset($_GET['id']) || $_GET['id'] != $forum_user['id'])
	{
		header('Location: '.forum_link($forum_url['index']));
		die;
	}
	
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['logout'], array($forum_user['id'], isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')));

	// We validate the CSRF token. If it's set in POST and we're at this point, the token is valid.
	// If it's in GET, we need to make sure it's valid.
	if (!isset($_POST['csrf_token']) && (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== generate_form_token('logout'.$forum_user['id'])))
		csrf_confirm_form();

	($hook = get_hook('li_logout_selected')) ? eval($hook) : null;

	// Remove user from "users online" list.
	$query = array(
		'DELETE'	=> 'online',
		'WHERE'		=> 'user_id='.$forum_user['id']
	);

	($hook = get_hook('li_logout_qr_delete_online_user')) ? eval($hook) : null;
	$forum_db->query_build($query) or error(__FILE__, __LINE__);

	// Update last_visit (make sure there's something to update it with)
	if (isset($forum_user['logged']))
	{
		$query = array(
			'UPDATE'	=> 'users',
			'SET'		=> 'last_visit='.$forum_user['logged'],
			'WHERE'		=> 'id='.$forum_user['id']
		);

		($hook = get_hook('li_logout_qr_update_last_visit')) ? eval($hook) : null;
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
	}

	$expire = time() + 1209600;
	forum_setcookie($cookie_name, base64_encode('1|'.random_key(8, false, true).'|'.$expire.'|'.random_key(8, false, true)), $expire);

	// Reset tracked topics
	set_tracked_topics(null);

	($hook = get_hook('li_logout_pre_redirect')) ? eval($hook) : null;

	redirect(forum_link($forum_url['index']), $lang_login['Logout redirect']);
}


// New password
else if ($action == 'forget')
{
	if (!$forum_user['is_guest'])
		header('Location: '.forum_link($forum_url['index']));
	
	// Check for use of incorrect URLs
	confirm_current_url(forum_link($forum_url['request_password']));

	($hook = get_hook('li_forgot_pass_selected')) ? eval($hook) : null;

	if (isset($_POST['form_sent']))
	{
		// User pressed the cancel button
		if (isset($_POST['cancel']))
			redirect(forum_link($forum_url['index']), $lang_login['Reset cancel redirect']);
		
		if (!defined('FORUM_EMAIL_FUNCTIONS_LOADED'))
			require FORUM_ROOT.'include/functions/email.php';

		// Validate the email-address
		$email = strtolower(forum_trim($_POST['req_email']));
		if (!is_valid_email($email))
			$errors[] = $lang_login['Invalid e-mail'];

		($hook = get_hook('li_forgot_pass_end_validation')) ? eval($hook) : null;

		// Did everything go according to plan?
		if (empty($errors))
		{
			// Fetch user matching $email
			$query = array(
				'SELECT'	=> 'u.id, u.group_id, u.username, u.salt, u.last_email_sent',
				'FROM'		=> 'users AS u',
				'WHERE'		=> 'u.email=\''.$forum_db->escape($email).'\''
			);

			($hook = get_hook('li_forgot_pass_qr_get_user_data')) ? eval($hook) : null;
			$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			while ($cur_user = $forum_db->fetch_assoc($result))
			{
				$users_with_email[] = $cur_user;
			}

			if (!empty($users_with_email))
			{
				($hook = get_hook('li_forgot_pass_pre_email')) ? eval($hook) : null;

				// Load the "activate password" template
				$mail_tpl = forum_trim(file_get_contents(FORUM_ROOT.'lang/'.$forum_user['language'].'/mail_templates/activate_password.tpl'));

				// The first row contains the subject
				$first_crlf = strpos($mail_tpl, "\n");
				$mail_subject = forum_trim(substr($mail_tpl, 8, $first_crlf-8));
				$mail_message = forum_trim(substr($mail_tpl, $first_crlf));

				// Do the generic replacements first (they apply to all e-mails sent out here)
				$mail_message = str_replace('<base_url>', $base_url.'/', $mail_message);
				$mail_message = str_replace('<board_mailer>', sprintf($lang_common['Forum mailer'], $forum_config['o_board_title']), $mail_message);

				($hook = get_hook('li_forgot_pass_new_general_replace_data')) ? eval($hook) : null;

				// Loop through users we found
				foreach ($users_with_email as $cur_hit)
				{
					$forgot_pass_timeout = 3600;

					($hook = get_hook('li_forgot_pass_pre_flood_check')) ? eval($hook) : null;
					
					if ($cur_hit['group_id'] == FORUM_ADMIN)
						message(sprintf($lang_login['Email important'], '<a href="mailto:'.forum_htmlencode($forum_config['o_admin_email']).'">'.forum_htmlencode($forum_config['o_admin_email']).'</a>'));

					if ($cur_hit['last_email_sent'] != '' && (time() - $cur_hit['last_email_sent']) < $forgot_pass_timeout && (time() - $cur_hit['last_email_sent']) >= 0)
						message(sprintf($lang_login['Email flood'], $forgot_pass_timeout));

					// Generate a new password activation key
					$new_password_key = random_key(8, true);

					$query = array(
						'UPDATE'	=> 'users',
						'SET'		=> 'activate_key=\''.$new_password_key.'\', last_email_sent = '.time(),
						'WHERE'		=> 'id='.$cur_hit['id']
					);

					($hook = get_hook('li_forgot_pass_qr_set_activate_key')) ? eval($hook) : null;
					$forum_db->query_build($query) or error(__FILE__, __LINE__);

					// Do the user specific replacements to the template
					$cur_mail_message = str_replace('<username>', $cur_hit['username'], $mail_message);
					$cur_mail_message = str_replace('<activation_url>', str_replace('&amp;', '&', forum_link($forum_url['change_password_key'], array($cur_hit['id'], $new_password_key))), $cur_mail_message);

					($hook = get_hook('li_forgot_pass_new_user_replace_data')) ? eval($hook) : null;

					forum_mail($email, $mail_subject, $cur_mail_message);
				}

				message(sprintf($lang_login['Forget mail'], '<a href="mailto:'.forum_htmlencode($forum_config['o_admin_email']).'">'.forum_htmlencode($forum_config['o_admin_email']).'</a>'));
			}
			else
				$errors[] = sprintf($lang_login['No e-mail match'], forum_htmlencode($email));
		}
	}

	// Setup form
	$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
	$forum_page['form_action'] = forum_link($forum_url['request_password']);

	// Setup breadcrumbs
	$forum_page['crumbs'] = array(
		array($forum_config['o_board_title'], forum_link($forum_url['index'])),
		$lang_login['New password request']
	);

	($hook = get_hook('li_forgot_pass_pre_header_load')) ? eval($hook) : null;

	define ('FORUM_PAGE', 'reqpass');
	require FORUM_ROOT.'header.php';

	// START SUBST - <forum_main>
	ob_start();

	($hook = get_hook('li_forgot_pass_output_start')) ? eval($hook) : null;

?>
<div class="chunk">
		<div class="ct-box info-box">
			<p class="important"><?php echo $lang_login['New password info'] ?></p>
		</div>
<?php
	// If there were any errors, show them
	if (!empty($errors))
	{
		$forum_page['errors'] = array();
		foreach ($errors as $cur_error)
			$forum_page['errors'][] = '<li class="warn"><span>'.$cur_error.'</span></li>';

		($hook = get_hook('li_forgot_pass_pre_new_password_errors')) ? eval($hook) : null;

?>
<div class="error"><p><?php echo $lang_login['New password errors'] ?><br><?php echo implode("\n\t\t\t\t", $forum_page['errors'])."\n" ?></p></div>
<?php
	}
?>
		<form id="afocus" method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">

		<div class="panel">
				<div id="req-msg" class="req-warn ct-box error-box">
			<p class="important"><?php printf($lang_common['Required warn'], '<em>'.$lang_common['Required'].'</em>') ?></p>
		</div>
			<div class="hidden">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="csrf_token" value="<?php echo generate_form_token($forum_page['form_action']) ?>" />
			</div>
			<div class="inner">
			<div class="content">
<?php ($hook = get_hook('li_forgot_pass_pre_group')) ? eval($hook) : null; ?>
				<fieldset class="group<?php echo ++$forum_page['group_count'] ?>">
<?php ($hook = get_hook('li_forgot_pass_pre_email')) ? eval($hook) : null; ?>
				<dl class="set<?php echo ++$forum_page['item_count'] ?>">
					<dt><label for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_login['E-mail address'] ?> <em><?php echo $lang_common['Required'] ?></em></label><br><span><?php echo $lang_login['E-mail address help'] ?></span></dt>
					<dd><input id="fld<?php echo $forum_page['fld_count'] ?>" class="inputbox narrow" type="text" name="req_email" value="<?php echo isset($_POST['req_email']) ? forum_htmlencode($_POST['req_email']) : '' ?>" size="35" maxlength="80" /></dd>
				</dl>
<?php ($hook = get_hook('li_forgot_pass_pre_group_end')) ? eval($hook) : null; ?>
				<dl>
					<dt>&nbsp;</dt>
					<dd>
						<input type="submit" name="cancel" value="<?php echo $lang_common['Cancel'] ?>" class="button2" />&nbsp;
						<input type="submit" name="request_pass"  class="button1" value="<?php echo $lang_login['Submit password request'] ?>" />
					</dd>
				</dl>
				</fieldset>
				<?php ($hook = get_hook('li_forgot_pass_group_end')) ? eval($hook) : null; ?>
			</div>
			</div>
		</div>
		</form>
	</div>
<?php

	($hook = get_hook('li_forgot_pass_end')) ? eval($hook) : null;

	$tpl_temp = forum_trim(ob_get_contents());
	$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
	ob_end_clean();
	// END SUBST - <forum_main>

	require FORUM_ROOT.'footer.php';
}

if (!$forum_user['is_guest'])
	header('Location: '.forum_link($forum_url['index']));

// Check for use of incorrect URLs
confirm_current_url(forum_link($forum_url['login']));

// Setup form
$forum_page['group_count'] = $forum_page['item_count'] = $forum_page['fld_count'] = 0;
$forum_page['form_action'] = forum_link($forum_url['login']);

$forum_page['hidden_fields'] = array(
	'form_sent'			=> '<input type="hidden" name="form_sent" value="1" />',
	'redirect_url'		=> '<input type="hidden" name="redirect_url" value="'.forum_htmlencode($forum_user['prev_url']).'" />',
	'csrf_token'		=> '<input type="hidden" name="csrf_token" value="'.generate_form_token($forum_page['form_action']).'" />'
);

// Setup breadcrumbs
$forum_page['crumbs'] = array(
	array($forum_config['o_board_title'], forum_link($forum_url['index'])),	
	array(sprintf($lang_login['Login info'], $forum_config['o_board_title']), forum_link($forum_url['login'])),
);

($hook = get_hook('li_login_pre_header_load')) ? eval($hook) : null;

define('FORUM_PAGE', 'login');
require FORUM_ROOT.'header.php';

// START SUBST - <forum_main>
ob_start();

($hook = get_hook('li_login_output_start')) ? eval($hook) : null;

?>
	<div class="chunk">
<?php

// If there were any errors, show them
if (!empty($errors))
{
	$forum_page['errors'] = array();
	foreach ($errors as $cur_error)
		$forum_page['errors'][] = '<li class="warn"><span>'.$cur_error.'</span></li>';

	($hook = get_hook('li_pre_login_errors')) ? eval($hook) : null;

?>
<div class="error"><p><?php echo $lang_login['Login errors'] ?><br><?php echo implode("\n\t\t\t\t", $forum_page['errors'])."\n" ?></p></div>
<?php

}

?>

		
		<form id="afocus"  method="post" accept-charset="utf-8" action="<?php echo $forum_page['form_action'] ?>">

		<div class="hidden">
				<?php echo implode("\n\t\t\t\t", $forum_page['hidden_fields'])."\n" ?>
			</div>
		<div class="panel">
					<div id="req-msg" class="req-warn ct-box error-box">
			<p class="important"><?php printf($lang_common['Required warn'], '<em>'.$lang_common['Required'].'</em>') ?></p>
		</div>
			<div class="inner">
			<div class="content">
<?php ($hook = get_hook('li_login_pre_login_group')) ? eval($hook) : null; ?>
				<fieldset class="fields1 group<?php echo ++$forum_page['group_count'] ?>">
<?php ($hook = get_hook('li_login_pre_username')) ? eval($hook) : null; ?>
				<dl class="set<?php echo ++$forum_page['item_count'] ?>">
					<dt><label for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_login['Username'] ?> <em><?php echo $lang_common['Required'] ?></em></label></dt>
					<dd><input type="text" tabindex="1" id="fld<?php echo $forum_page['fld_count'] ?>" name="req_username" class="inputbox autowidth" value="<?php echo isset($_POST['req_username']) ? forum_htmlencode($_POST['req_username']) : '' ?>" size="35" maxlength="25" /></dd>
				</dl>
<?php ($hook = get_hook('li_login_pre_pass')) ? eval($hook) : null; ?>
				<dl class="set<?php echo ++$forum_page['item_count'] ?>">
					<dt>
						<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_login['Password'] ?> <em><?php echo $lang_common['Required'] ?></em></label>
					</dt>
					<dd>
						<input type="password" tabindex="2" id="fld<?php echo $forum_page['fld_count'] ?>"  class="inputbox autowidth" name="req_password" value="<?php echo isset($_POST['req_password']) ? forum_htmlencode($_POST['req_password']) : '' ?>" size="35" />
					</dd>
					<dd>
						<a href="<?php echo $forum_url['request_password']?>"><?php echo $lang_login['Obtain pass'] ?></a>
					</dd>
				</dl>
<?php ($hook = get_hook('li_login_pre_remember_me_checkbox')) ? eval($hook) : null; ?>
				<dl class="set<?php echo ++$forum_page['item_count'] ?>">
					<dd>
						
<input type="checkbox" id="fld<?php echo ++$forum_page['fld_count'] ?>" tabindex="4" name="save_pass" value="1"<?php echo isset($_POST['save_pass']) ? 'checked="checked" ' : '' ?>/>
<label for="fld<?php echo ++$forum_page['fld_count'] ?>"><?php echo $lang_login['Remember me'] ?> <?php echo $lang_login['Persistent login'] ?></label>
					</dd>					
				</dl>
				<?php ($hook = get_hook('li_login_pre_group_end')) ? eval($hook) : null; ?>
				
				<dl>
					<dt>&nbsp;</dt>
<input type="submit" name="login" class="button1" value="<?php echo $lang_login['Login'] ?>" />
				</dl>
				</fieldset>
				<?php ($hook = get_hook('li_login_group_end')) ? eval($hook) : null; ?>
			</div>

						</div>
		</div>


		
		</form>

	</div>
<?php

($hook = get_hook('li_end')) ? eval($hook) : null;

$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<forum_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <forum_main>

require FORUM_ROOT.'footer.php';
