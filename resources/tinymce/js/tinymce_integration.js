
	/* Version 1.1
	 * Author: Moxiecode Systems 
	* Slightly modified by Thierry Bézecourt, October 2005 (fixed the regexps)
	 */
	
	var tinyMCE_on = false;
	
	tinyMCE.init({
		mode : "exact",
		theme : "advanced",
		plugins : "image,link,emotions",
		theme_advanced_buttons1 : "bold,italic,underline,undo,redo,link,unlink,image,forecolor,styleselect,removeformat,cleanup,code",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "bottom",
		theme_advanced_toolbar_align : "center",
		theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
		valid_elements : "strong/b[class],em/i[class],u[class],a[href|target|title],img[class|src|border=0|alt|title|width|height|align|name],span[class],font[color|class],br",
		force_br_newlines : true,
		force_p_newlines : false,
		save_callback : "TinyMCESaving",
		add_form_submit_trigger : false,
		submit_patch : false,
		add_unload_trigger : false,
		entity_encoding : "raw",
		content_css : "style/tinymce.css",
		relative_urls : false,
		remove_script_host : false,
		convert_fonts_to_styles : false,
		file_browser_callback : "mcImageManager.filebrowserCallBack"
	});

	function TinyMCESaving() {
		if (tinyMCE_on == true)
			document.forms[0].req_message.value = convertFromMCEToBBCode(tinyMCE.getContent());
	}

	function convertFromMCEToBBCode(source) {

		// example: <strong> to [b]
		source = source.replace(/<\/strong>/gi,"[/b]");
		source = source.replace(/<strong>/gi,"[b]");

		source = source.replace(/<\/em>/gi,"[/i]");
		source = source.replace(/<em>/gi,"[i]");

		source = source.replace(/<\/u>/gi,"[/u]");
		source = source.replace(/<u>/gi,"[u]");

		source = source.replace(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
		source = source.replace(/<a.*?href=\'(.*?)\'.*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
		
		source = source.replace(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
		source = source.replace(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");

		source = source.replace(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
		source = source.replace(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");

		source = source.replace(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
		source = source.replace(/<font>(.*?)<\/font>/gi,"$1");
		source = source.replace(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
		source = source.replace(/<img.*?src=\'(.*?)\'.*?\/>/gi,"[img]$1[/img]");

		source = source.replace(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
		source = source.replace(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");

		source = source.replace(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
		source = source.replace(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");

		source = source.replace(/<em class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][i]$1[/i][/code]");
		source = source.replace(/<em class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][i]$1[/i][/quote]");

		source = source.replace(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
		source = source.replace(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");

		source = source.replace(/<br \/>/gi,"\n");
		source = source.replace(/<br\/>/gi,"\n");
		source = source.replace(/<br>/gi,"\n");

		source = source.replace(/&nbsp;/gi," ");
		source = source.replace(/&quot;/gi,"\"");
		source = source.replace(/&lt;/gi,"<");
		source = source.replace(/&gt;/gi,">");
		source = source.replace(/&amp;/gi,"&");

		source = source.replace(/<!--(.*?)-->/g, "");
		source = source.replace(/<.*?>/g, "")

		return source; 
	}

	function convertFromBBCodeToMCE(source) {
		// example: [b] to <strong>
		source = source.replace(/&/gi,"&amp;");
		source = source.replace(/\"/gi,"&quot;");
		source = source.replace(/</gi,"&lt;");
		source = source.replace(/>/gi,"&gt;");

		source = source.replace(/\n/gi,"<br />");

		source = source.replace(/\[b\]/gi,"<strong>");
		source = source.replace(/\[\/b\]/gi,"</strong>");

		source = source.replace(/\[i\]/gi,"<em>");
		source = source.replace(/\[\/i\]/gi,"</em>");

		source = source.replace(/\[u\]/gi,"<u>");
		source = source.replace(/\[\/u\]/gi,"</u>");

		source = source.replace(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
		source = source.replace(/\[url=(.*?)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
		source = source.replace(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");

		source = source.replace(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");

		source = source.replace(/\[code\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>&nbsp;");
		source = source.replace(/\[quote.*?\](.*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>&nbsp;");

		return source; 
	}

	function toggleTinyMCE() {
		if (tinyMCE_on == true) {
			// if mce is on, we toggle it off and convert to BBCode
			tinyMCE.execCommand("mceRemoveControl", false, "req_message");
			document.forms[0].req_message.value = convertFromMCEToBBCode(document.forms[0].req_message.value);
			tinyMCE_on = false;
			return;
		} else {
			// if mce is off, we toggle it on and convert to HTML code
			document.forms[0].req_message.value = convertFromBBCodeToMCE(document.forms[0].req_message.value);			
			tinyMCE.execCommand("mceAddControl", false, "req_message");
			tinyMCE_on = true;
			return;
		}
	}

	function submitForm(the_form) {
		TinyMCESaving();
		return process_form(the_form);
	}