<?php
/**
 * XPressME - WordPress for XOOPS
 *
 * Adding multi-author features to XPress
 *
 * @copyright	toemon
 * @license		GNU public license
 * @author		"toemon ( http://ja.xpressme.info)"
 * @package		module::xpressme
 */
 
if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

if (!function_exists('wp_version_compare')){
	function wp_version_compare($wp_version , $operator='==',$comp_version){
		$inc_wp_version = str_replace("ME", "", $wp_version);
	 	return version_compare($inc_wp_version, $comp_version, $operator);
	}
}

if (!function_exists('mod_access_level')){
	function mod_access_level(){
		global $current_user;
		
		$level = @$current_user->user_level;
		$role = @$current_user->roles[0];
		switch ($role){
			case 'administrator':
				$role_level = 10;
				break;
			case 'editor':
				$role_level = 7;
				break;
			case 'author':
				$role_level = 2;
				break;		
			case 'contributor':
				$role_level = 1;
				break;
			default:
				$role_level = 0;
		}
		
		if ($level > $role_level){
			return $level;
		} else {
			return $role_level;
		}
	}
}
if (!function_exists('is_show_multi_blog_block')){
	function is_show_multi_blog_block($mydirname = ''){
		if(empty($mydirname)) return false;
		// Before loading xpressme. 
		// The multi blog is judged by the presence of the blogs table.		
		global $xoopsDB;
		$wp_prefix = preg_replace('/wordpress/','wp',$mydirname);
		$wp_blogs_tbl = $xoopsDB->prefix($wp_prefix) . '_blogs';
		$sql = "SELECT * FROM " . $wp_blogs_tbl;
		$result = $xoopsDB->query($sql, 0, 0);
		if ($xoopsDB->getRowsNum($result)) return true;
	 	return false;
	}
}

$mydirpath = dirname(__FILE__);
$mydirname = basename($mydirpath);

$lang = @$GLOBALS["xoopsConfig"]['language'];

// language file (modinfo.php)

if( file_exists( $mydirpath .'/language/'.$lang.'/modinfo.php' ) ) {
	include_once $mydirpath .'/language/'.$lang.'/modinfo.php' ;
} else if( file_exists(  $mydirpath .'/language/english/modinfo.php' ) ) {
	include_once $mydirpath .'/language/english/modinfo.php' ;
}
global $wp_db_version,$wp_version;

include $mydirpath .'/wp-includes/version.php' ;

$modversion['name'] = ucfirst($mydirname) . ' ' . constant('_MI_XP2_NAME') ;
$modversion['description'] = constant( '_MI_XP2_DESC');
$modversion['version'] = "2.31";
$modversion['credits'] = "Wordpress DEV (http://wordpress.org/) XPressME DEV Toemon) (http://ja.xpressme.info) ;";
$modversion['author'] = "toemon (http://ja.xpressme.info)";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 0 ;
$modversion['image'] =  'module_icon.php' ;
$modversion['dirname'] = $mydirname;

// status
$modversion['codename'] = "";

// onInstall, onUpdate, onUninstall
$modversion['onInstall'] = 'include/oninstall.php' ;
$modversion['onUpdate'] = 'include/onupdate.php' ;
$modversion['onUninstall'] = 'include/onuninstall.php' ;

// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
//$modversion['sqlfile']['mysql'] = "sql/mysql.sql";

$db_prefix = preg_replace('/wordpress/','wp',$mydirname);

/*
 * Table information is not described. 
 * 
 * The create of the table is do with oninstall.php. 
 * The drop of the table is do with onuninstall.php. 
 *
 * $modversion['tables'] = array( ,,,);
 */

	
// Search
$modversion['hasSearch'] = 1 ;
$modversion['search']['file'] = 'include/search.php' ;
$modversion['search']['func'] = $mydirname.'_global_search' ;
//Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

$modversion['hasMain'] = 1;

if (function_exists('get_bloginfo')){
	//$add_url for wpmu multiblog
	$pattern = '/.*\/' . $mydirname . '/';
	$add_url = preg_replace($pattern,'',get_bloginfo('url'));
	if (!empty($add_url)){
	    $pattern = '/^\//';
	    $add_url = preg_replace($pattern,'',$add_url) . '/';
	}

	if(is_object($GLOBALS["xoopsUser"])){
		global $current_user , $xoops_config;
		if (mod_access_level() > 0) {
		$modversion['sub'][1]['name'] = constant( '_MI_XP2_MENU_POST_NEW');
		if (wp_version_compare($wp_version, '>=','2.1'))
			$modversion['sub'][1]['url'] = $add_url . "wp-admin/post-new.php";
		else
			$modversion['sub'][1]['url'] = $add_url . "wp-admin/post.php";
		$modversion['sub'][2]['name'] = constant( '_MI_XP2_MENU_EDIT');
		$modversion['sub'][2]['url'] = $add_url . "wp-admin/edit.php";
		$modversion['sub'][3]['name'] = constant( '_MI_XP2_MENU_ADMIN');
		$modversion['sub'][3]['url'] = $add_url . "wp-admin/";
		}
		if (mod_access_level() > 9) {
			$modversion['sub'][4]['name'] = constant( '_MI_XP2_MENU_XPRESS');
			$modversion['sub'][4]['url'] = $add_url . "wp-admin/admin.php?page=xpressme" . DIRECTORY_SEPARATOR . "xpressme.php";
		}
		if($GLOBALS["xoopsUserIsAdmin"]){
			$modversion['sub'][5]['name'] = constant( '_MI_XP2_MOD_ADMIN');
			$modversion['sub'][5]['url'] = "admin/index.php";
		}
		$create_new_blog = xpress_create_new_blog();
		if(!empty($create_new_blog)){
			$modversion['sub'][6]['name'] = $create_new_blog['title'];
			$modversion['sub'][6]['url'] = $create_new_blog['menu_url'];
		}
		$primaryw_blog = xpress_primary_blog_link();
		if(!empty($primaryw_blog)){
			$modversion['sub'][7]['name'] = $primaryw_blog['title'];
			$modversion['sub'][7]['url'] = $primaryw_blog['menu_url'];
		}
	}
}

// Use smarty
$modversion["use_smarty"] = 1;

/**
* Templates
*/
// All Templates can't be touched by modulesadmin.
$modversion['templates'] = array() ;

$modversion['hasconfig'] = 1;
$modversion['config'][] = array(
	'name'			=> 'libxml_patch' ,
	'title'			=>  '_MI_LIBXML_PATCH' ,
	'description'	=>  '_MI_LIBXML_PATCH_DESC' ,
	'formtype'		=> 'yesno' ,
	'valuetype'		=> 'int' ,
	'default'		=> 0 ,
);
$modversion['config'][] = array(
	'name'			=> 'memory_limit' ,
	'title'			=>  '_MI_MEMORY_LIMIT' ,
	'description'	=>  '_MI_MEMORY_LIMIT_DESC' ,
	'formtype'		=> 'textbox' ,
	'valuetype'		=> 'int' ,
	'default'		=> 64 ,
);

//BLOCKS
$b_no =1;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'recent_posts_content_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_CONTENT') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_content_show" ,
	'edit_func' 	=> "b_". $mydirname . "_content_edit" ,
	'template'		=> '' ,
	'options'		=> $mydirname. '||10|0|100||||0|0|0' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'recent_posts_list_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_POSTS') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_posts_show" ,
	'edit_func' 	=> "b_". $mydirname . "_posts_edit" ,
	'options'		=> $mydirname. '||10|1|7||||0' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'popular_posts_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_POPULAR') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_popular_show" ,
	'edit_func' 	=> "b_". $mydirname . "_popular_edit" ,
	'options'		=> $mydirname. '||10|0||||0' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'page_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_PAGE') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_page_show" ,
	'edit_func' 	=> "b_". $mydirname . "_page_edit" ,
	'options'		=> $mydirname. '||post_title|asc||||0|0|none||1||' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'recent_comments_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_COMMENTS') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_comments_show" ,
	'edit_func' 	=> "b_". $mydirname . "_comments_edit" ,
	'template'		=> '' ,
	'options'		=> $mydirname. '||10|30|||0' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'sidebar_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_SIDEBAR') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_sidebar_show" ,
	'edit_func' 	=> '' ,
	'options'		=> '' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'search_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_SEARCH') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_search_show" ,
	'edit_func' 	=> "b_". $mydirname . "_search_edit" ,
	'options'		=> $mydirname. '||18' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no ,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'calender_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_CALENDER') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_calender_show" ,
	'edit_func' 	=> "b_". $mydirname . "_calender_edit" ,
	'options'		=> $mydirname. '||#DB0000|#004D99' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no,
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'archives_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_ARCHIVE') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_archives_show" ,
	'edit_func' 	=> "b_". $mydirname . "_archives_edit" ,
	'options'		=> $mydirname. '||monthly|0|1|0' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'authors_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_AUTHORS') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_authors_show" ,
	'edit_func' 	=> "b_". $mydirname . "_authors_edit" ,
	'options'		=> $mydirname. '||0|1|0|1' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no,	
);
if (wp_version_compare($wp_version, '>=','2.3')){
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'tag_cloud_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_TAG') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_tag_cloud_show" ,
		'edit_func' 	=> "b_". $mydirname . "_tag_cloud_edit" ,
		'options'		=> $mydirname. '||8|22|pt|45|flat|name|ASC||' ,
		'can_clone'		=> false ,
		'func_num'		=> $b_no,	
	);
}
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'category_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_CATEGORY') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_category_show" ,
	'edit_func' 	=> "b_". $mydirname . "_category_edit" ,
	'options'		=> $mydirname. '||ALL|name|ASC|0|0|1|1|||1|0' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no,	
);
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'meta_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_META') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_meta_show" ,
	'edit_func' 	=> "b_". $mydirname . "_meta_edit" ,
	'options'		=> $mydirname. '||1|1|1|1|1|1|1|1' ,
	'can_clone'		=> false ,
	'func_num'		=> $b_no,	
);
if (wp_version_compare($wp_version, '>=','2.7')){
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'widget_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_WIDGET') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_widget_show" ,
		'edit_func' 	=> "b_". $mydirname . "_widget_edit" ,
		'options'		=> $mydirname. '||1|' ,
		'can_clone'		=> true ,
		'func_num'		=> $b_no,	
	);
}
$b_no++;
$modversion['blocks'][$b_no] = array(
	'file' 			=> 'enhanced_block.php' ,
	'name' 			=> constant('_MI_XP2_BLOCK_ENHANCED') ,
	'description'	=> '' ,
	'show_func' 	=> "b_". $mydirname . "_enhanced_show" ,
	'edit_func' 	=> "b_". $mydirname . "_enhanced_edit" ,
	'options'		=> $mydirname. '||' ,
	'can_clone'		=> true ,
	'func_num'		=> $b_no,	
);
if (wp_version_compare($wp_version, '>=','3.0-alpha') && is_show_multi_blog_block($mydirname)){
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'blog_list_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_BLOG_LIST') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_blog_list_show" ,
		'edit_func' 	=> "b_". $mydirname . "_blog_list_edit" ,
		'options'		=> $mydirname. '||name|ASC' ,
		'can_clone'		=> false ,
		'func_num'		=> $b_no,
	);
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'global_recent_posts_list_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_GLOBAL_POSTS') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_global_posts_show" ,
		'edit_func' 	=> "b_". $mydirname . "_global_posts_edit" ,
		'options'		=> $mydirname. '||10|1|7||' ,
		'can_clone'		=> true ,
		'func_num'		=> $b_no,	
	);
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'global_recent_comments_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_GLOBAL_COMM') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_global_comments_show" ,
		'edit_func' 	=> "b_". $mydirname . "_global_comments_edit" ,
		'template'		=> '' ,
		'options'		=> $mydirname. '||10|30|||0' ,
		'can_clone'		=> true ,
		'func_num'		=> $b_no,	
	);
	$b_no++;
	$modversion['blocks'][$b_no] = array(
		'file' 			=> 'global_popular_posts_block.php' ,
		'name' 			=> constant('_MI_XP2_BLOCK_GLOBAL_POPU') ,
		'description'	=> '' ,
		'show_func' 	=> "b_". $mydirname . "_global_popular_show" ,
		'edit_func' 	=> "b_". $mydirname . "_global_popular_edit" ,
		'options'		=> $mydirname. '||10|0||' ,
		'can_clone'		=> true ,
		'func_num'		=> $b_no,	
	);
}

// Notification
$modversion['hasNotification'] = 1;
$modversion['notification'] = array(
	'lookup_file' => 'include/notification.inc.php' ,
	'lookup_func' => "xpress_notify" ,
	'category' => array(
		array(
			'name' => 'global' ,
			'title' => constant('_MI_XP2_NOTCAT_GLOBAL') ,
			'description' => constant('_MI_XP2_NOTCAT_GLOBALDSC') ,
			'subscribe_from' => 'index.php' ,
		) ,
		array(
			'name' => 'category' ,
			'title' => constant('_MI_XP2_NOTCAT_CAT') ,
			'description' => constant('_MI_XP2_NOTCAT_CATDSC') ,
			'subscribe_from' => 'index.php' ,
			'item_name' => 'cat' ,
			'allow_bookmark' => 1 ,
		) ,
		array(
			'name' => 'author' ,
			'title' => constant('_MI_XP2_NOTCAT_AUTHOR') ,
			'description' => constant('_MI_XP2_NOTCAT_AUTHORDSC') ,
			'subscribe_from' => 'index.php' ,
			'item_name' => 'author' ,
			'allow_bookmark' => 1 ,
		) ,
		array(
			'name' => 'post' ,
			'title' => constant('_MI_XP2_NOTCAT_POST') ,
			'description' => constant('_MI_XP2_NOTCAT_POSTDSC') ,
			'subscribe_from' => 'index.php' ,
			'item_name' => 'p' ,
			'allow_bookmark' => 1 ,
		) ,
	) ,
	'event' => array(
		array(
			'name' => 'waiting' ,
			'category' => 'global' ,
			'title' => constant('_MI_XP2_NOTIFY_GLOBAL_WAITING') ,
			'caption' => constant('_MI_XP2_NOTIFY_GLOBAL_WAITINGCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_GLOBAL_WAITINGCAP') ,
			'mail_template' => 'global_waiting' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_GLOBAL_WAITINGSBJ') ,
			'admin_only' => 1 ,
		) ,
		array(
			'name' => 'newpost' ,
			'category' => 'global' ,
			'title' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWPOST') ,
			'caption' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWPOSTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWPOSTCAP') ,
			'mail_template' => 'global_newpost' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWPOSTSBJ') ,
		) ,
		array(
			'name' => 'comment' ,
			'category' => 'global' ,
			'title' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWCOMMENT') ,
			'caption' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWCOMMENTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWCOMMENTCAP') ,
			'mail_template' => 'global_newcomment' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_GLOBAL_NEWCOMMENTSBJ') ,
		) ,
		
		array(
			'name' => 'newpost' ,
			'category' => 'category' ,
			'title' => constant('_MI_XP2_NOTIFY_CAT_NEWPOST') ,
			'caption' => constant('_MI_XP2_NOTIFY_CAT_NEWPOSTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_CAT_NEWPOSTCAP') ,
			'mail_template' => 'category_newpost' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_CAT_NEWPOSTSBJ') ,
		) ,
		array(
			'name' => 'comment' ,
			'category' => 'category' ,
			'title' => constant('_MI_XP2_NOTIFY_CAT_NEWCOMMENT') ,
			'caption' => constant('_MI_XP2_NOTIFY_CAT_NEWCOMMENTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_CAT_NEWCOMMENTCAP') ,
			'mail_template' => 'category_newcomment' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_CAT_NEWCOMMENTSBJ') ,
		) ,

		array(
			'name' => 'newpost' ,
			'category' => 'author' ,
			'title' => constant('_MI_XP2_NOTIFY_AUT_NEWPOST') ,
			'caption' => constant('_MI_XP2_NOTIFY_AUT_NEWPOSTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_AUT_NEWPOSTCAP') ,
			'mail_template' => 'author_newpost' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_AUT_NEWPOSTSBJ') ,
		) ,
		array(
			'name' => 'comment' ,
			'category' => 'author' ,
			'title' => constant('_MI_XP2_NOTIFY_AUT_NEWCOMMENT') ,
			'caption' => constant('_MI_XP2_NOTIFY_AUT_NEWCOMMENTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_AUT_NEWCOMMENTCAP') ,
			'mail_template' => 'author_newcomment' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_AUT_NEWCOMMENTSBJ') ,
		) ,

		array(
			'name' => 'comment' ,
			'category' => 'post' ,
			'title' => constant('_MI_XP2_NOTIFY_POST_NEWCOMMENT') ,
			'caption' => constant('_MI_XP2_NOTIFY_POST_NEWCOMMENTCAP') ,
			'description' => constant('_MI_XP2_NOTIFY_POST_NEWCOMMENTCAP') ,
			'mail_template' => 'post_newcomment' ,
			'mail_subject' => constant('_MI_XP2_NOTIFY_POST_NEWCOMMENTSBJ') ,
		) ,
	) ,
) ;


?>