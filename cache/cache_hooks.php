<?php

define('FORUM_HOOKS_LOADED', 1);

$hooks = array (
  'ps_start' => 
  array (
    0 => '$GLOBALS[\'ext_info_stack\'][] = array(
\'id\'				=> \'flazy_geshi\',
\'path\'			=> FORUM_ROOT.\'extensions/flazy_geshi\',
\'url\'			=> $GLOBALS[\'base_url\'].\'/extensions/flazy_geshi\',
\'dependencies\'	=> array (
)
);
$ext_info = $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];

require_once $ext_info[\'path\'].\'/function.php\';

array_pop($GLOBALS[\'ext_info_stack\']);
$ext_info = empty($GLOBALS[\'ext_info_stack\']) ? array() : $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];
',
  ),
  'ps_preparse_bbcode_start' => 
  array (
    0 => '$GLOBALS[\'ext_info_stack\'][] = array(
\'id\'				=> \'flazy_geshi\',
\'path\'			=> FORUM_ROOT.\'extensions/flazy_geshi\',
\'url\'			=> $GLOBALS[\'base_url\'].\'/extensions/flazy_geshi\',
\'dependencies\'	=> array (
)
);
$ext_info = $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];

$text = preg_replace(\'#\\[code=([a-zA-Z0-9]+)\\]#s\', \'[code]%$1%\', $text);

array_pop($GLOBALS[\'ext_info_stack\']);
$ext_info = empty($GLOBALS[\'ext_info_stack\']) ? array() : $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];
',
  ),
  'ps_preparse_bbcode_end' => 
  array (
    0 => '$GLOBALS[\'ext_info_stack\'][] = array(
\'id\'				=> \'flazy_geshi\',
\'path\'			=> FORUM_ROOT.\'extensions/flazy_geshi\',
\'url\'			=> $GLOBALS[\'base_url\'].\'/extensions/flazy_geshi\',
\'dependencies\'	=> array (
)
);
$ext_info = $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];

$text = preg_replace(\'#\\[code\\]%([a-zA-Z0-9]+)%#s\', \'[code=$1]\', $text);

array_pop($GLOBALS[\'ext_info_stack\']);
$ext_info = empty($GLOBALS[\'ext_info_stack\']) ? array() : $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];
',
  ),
  'ps_parse_message_pre_split' => 
  array (
    0 => '$GLOBALS[\'ext_info_stack\'][] = array(
\'id\'				=> \'flazy_geshi\',
\'path\'			=> FORUM_ROOT.\'extensions/flazy_geshi\',
\'url\'			=> $GLOBALS[\'base_url\'].\'/extensions/flazy_geshi\',
\'dependencies\'	=> array (
)
);
$ext_info = $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];

$text = preg_replace_callback(\'#\\[code=([a-zA-Z0-9]+)\\](.*?)\\[/code\\]#s\', \'geshi\', $text);

array_pop($GLOBALS[\'ext_info_stack\']);
$ext_info = empty($GLOBALS[\'ext_info_stack\']) ? array() : $GLOBALS[\'ext_info_stack\'][count($GLOBALS[\'ext_info_stack\']) - 1];
',
  ),
);

?>