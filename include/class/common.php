<?php
/**
 * Общие классы используемые на форуме.
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @modified Copyright (C) 2008 Flazy.ru
 * @license http://www.gnu.org/licenses/gpl.html GPL версии 2 или выше
 * @package Flazy
 */
($hook = get_hook('cls_fl_js_helper_start')) ? eval($hook) : null;
$js = array(
	'jquery'		=> '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
);

($hook = get_hook('cls_fl_pre_class_js_helper')) ? eval($hook) : null;


/**
 * Добавление java-script файлов в <head> форума.
 * @author Copyright (C) 2009 hcs
 * @modified Copyright (C) 2008 Flazy.ru
 */


class forum_js
{
	var $file = array();
	var $code = array();
	
	function forum_js()
	{
	}

	/**
	 * Проверка названия скрипта на соответсвие в массиве $js.
	 * @param string ссылка или название локального js скрипта.
	 * @return string ссылка на js скрипт.
	 */
	function check_path($path)
	{
		global $js;

		return $path = !empty($js[$path]) ? $js[$path] : $path;
	}

	function file($paths)
	{
		if (!is_array($paths))
		{
			if (!in_array($paths, $this->file))
				$this->file[] = $this->check_path($paths);
		}
		else
		{
			foreach ($paths as $path_num => $path)
				if (!in_array($paths, $this->file))
					$this->file[] = $this->check_path($path);
		}
	}

	function code($code)
	{
		if (!in_array($code, $this->code))
			$this->code[] = $code;
	}

	function out()
	{
		$str = '';
		foreach ($this->file as $file)
			$str .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";
		foreach ($this->code as $code)
			$str .= '<script type="text/javascript">'."\n".$code."\n".'</script>'."\n";
		return $str;
	}
}

($hook = get_hook('cls_fl_js_helper_end')) ? eval($hook) : null;

$forum_js = new forum_js();