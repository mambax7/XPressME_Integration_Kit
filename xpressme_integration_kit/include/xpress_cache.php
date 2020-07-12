<?php
	
if(!function_exists("xpress_cache_found")):
    function xpress_cache_found($filename)
    {
		$cache_time = 0;
//        if (file_exists($filename) && ((time() - filemtime($filename)) < $cache_time)) {
        if (file_exists($filename)) {
            return true;
       } else {
			return false;
		}
    } 
endif;

if(!function_exists("xpress_cache_read")):
    function xpress_cache_read($mydirname,$collation_key)
    {
    	global $xoops_config;
    	if(defined('XOOPS_ROOT_PATH')){
    		$cache_dir = XOOPS_ROOT_PATH . '/cache/';
    	} else {
    		$cache_dir = $xoops_config->xoops_root_path . '/cache/';
    	}
        $filename = $cache_dir .$mydirname . '_' . $collation_key;
        if (xpress_cache_found($filename)) {
            return file_get_contents($filename);
       } else {
			return '';
		}
    } 
endif;

if(!function_exists("xpress_cache_write")):
    function xpress_cache_write($mydirname,$collation_key,$content)
    {
		global $xoops_config;
		$cache_dir = $xoops_config->xoops_root_path . '/cache/';
		$cache_time = 0;

        $filename = $cache_dir .$mydirname . '_' . $collation_key;
//        if ((time() - @filemtime($filename)) > $cache_time) {
            $fp = fopen($filename, "w");
            flock($fp, 2);
            fputs($fp, $content);
            fclose($fp);
 //       } 
    } 
endif;

if(!function_exists("xpress_block_cache_clear")):
    function xpress_cache_clear($mydirname)
    {
		global $xoops_config;
		$cache_dir = $xoops_config->xoops_root_path . '/cache/';
		$cache_time = 0;
        if ($dh = opendir($cache_dir)) {
            while (($file = readdir($dh)) !== false) {
                if (preg_match('/^' . preg_quote($mydirname) . '/', $file)) {
                    unlink($cache_dir.$file);
                } 
            } 
            closedir($dh);
        } 
    } 
endif;

?>