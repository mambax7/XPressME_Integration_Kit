<?php
if(!defined('XPRESS_BLOCK_HEADER_FUNCTION_READ')){
	define('XPRESS_BLOCK_HEADER_FUNCTION_READ',1);
	require_once dirname( __FILE__ ) .'/xml.php' ;
	require_once dirname( __FILE__ ) .'/xpress_cache.php' ;
	global $xoops_config;
	
	if (!is_object($xoops_config)){ // is call other modules
		require_once dirname(dirname( __FILE__ )) .'/class/config_from_xoops.class.php' ;
		$xoops_config = new ConfigFromXoops;
	}
	
	function xpress_block_header_cash_write($mydirname,$block_header)
	{
			$xml = xpress_XML_serialize($block_header);
			$xml_name = 'block_header.xml';
			if (WPLANG == 'ja_EUC'){
				$xml = str_replace('<?xml version="1.0" ?>', '<?xml version="1.0" encoding="EUC-JP" ?>' , $xml);
			}
			xpress_cache_write($mydirname,$xml_name,$xml);
	}
	function xpress_block_header_cache_read($mydirname)
	{
		$xml_name = 'block_header.xml';
		$xml_data = xpress_cache_read($mydirname,$xml_name);
		
		$GLOBALS['DO_LIBXML_PATCH'] = get_xpress_mod_config($mydirname,'libxml_patch');
		
		// The character-code not treatable exists when 'XML_unserialize' of PHP5 processes EUC-JP. 
		// And, the result is returned by character-code UTF-8. 
		// Measures
		// After the character-code is converted into UTF-8, XML_unserialize will be processed. 
		if ( strstr($xml_data, '<?xml version="1.0" encoding="EUC-JP" ?>') !== false
			 && version_compare(PHP_VERSION, '5.0.0', '>') )
		{
			$xml_data = str_replace('<?xml version="1.0" encoding="EUC-JP" ?>', '<?xml version="1.0" encoding="UTF-8" ?>', $xml_data);
			$ans = mb_convert_variables('UTF-8' , 'EUC-JP', &$xml_data); //EUC-JP to UTF-8
			$ret = @xpress_XML_unserialize($xml_data);
			$ans = mb_convert_variables('EUC-JP' , 'UTF-8', &$ret); //UTF-8 to EUC-JP
		} else {
			$ret = xpress_XML_unserialize($xml_data);
		}
		return $ret;
	}
	function get_block_stylesheet_url($mydirname)
	{
		global $xoops_config;
		$mydirpath = $xoops_config->xoops_root_path . '/modules/' . $mydirname;
		$select_theme = get_xpress_theme_name($mydirname);
		$style_file = $mydirpath . '/wp-content/themes/' . $select_theme . '/blocks/block_style.css';
		if (file_exists($style_file))
			return $xoops_config->xoops_url . '/modules/' .$mydirname . '/wp-content/themes/' . $select_theme . '/blocks/block_style.css';
		else	
			return $xoops_config->xoops_url . '/modules/' .$mydirname . '/wp-content/themes/xpress_default/blocks/block_style.css';
	}

	function set_xpress_block_header($mydirname)
	{
		ob_start();	
			bloginfo('stylesheet_url');
			$stylesheet_link = "\t".'<link rel="stylesheet" href="' . ob_get_contents() . '" type="text/css" media="screen" />';
		ob_end_clean();
		$block_stylesheet_link = "\t".'<link rel="stylesheet" href="' . get_block_stylesheet_url($mydirname) . '" type="text/css" media="screen" />';

		ob_start();	
			wp_head();
			$header_str = ob_get_contents();
		ob_end_clean();
		$pattern = '<\s*link\s+rel\s*=[^>]*?>';
		$header_str = preg_replace("/".$pattern."/s" , '' , $header_str);
		$pattern = '<\s*meta\s+name\s*=[^>]*?>';
		$header_str = preg_replace("/".$pattern."/i" , '' , $header_str);
//		$pattern = "<style type.*<\/style>";
//		$header_str = preg_replace("/".$pattern."/s" , '' , $header_str);
		$pattern = "^\s*\n";
		$header_str = preg_replace("/".$pattern."/m" , '' , $header_str);
		$pattern = "^";
		$header_str = preg_replace("/".$pattern."/m" , "\t" , $header_str);
		ob_start();	
			wp_footer();
			$footer_str = ob_get_contents();
		ob_end_clean();
		$pattern = "^";
		$footer_str = preg_replace("/".$pattern."/m" , "\t" , $footer_str);
		
		$block_header  = "<!-- XPressME added block header -->\n";
		$block_header .= "\t<!-- from bloginfo('stylesheet_url') -->\n";
		$block_header .= $stylesheet_link ."\n";
		$block_header .= $block_stylesheet_link ."\n";
		$block_header .= "\t<!-- from wp_head() -->\n";
		$block_header .= $header_str ."\n";
		$block_header .= "\t<!-- from wp_footer() -->\n";
		$block_header .= $footer_str ."\n";
		$block_header .= "<!-- end of XPressME added block header -->\n";
		$data = array();
		$data['block_header']= $block_header;
		xpress_block_header_cash_write($mydirname,$data);
	}

}	
?>