<?php
function upgrade_page()
{
	global $xoops_config,$xpress_config;
	
	$xpress_version = $xoops_config->module_version . $xoops_config->module_codename;
	$lang = WPLANG;

	$check_url = "http://ja.xpressme.info/version_check/index.php?version=$xpress_version&lang=$lang";

	echo	'<div class="wrap">'."\n";
	echo		'<div id="icon-options-general" class="icon32"><br /></div>'."\n";
	echo		'<h2>' . __('XPressME Upgrade', 'xpressme') . "</h2><br>\n";
	
	if(get_xpress_latest_version()){
		$latest = get_option('xpressme_latest_version');
		if ($latest) {
			$site_url=$latest['url'];
			$package=$latest['package'];
			$latest_version=$latest['latest_version'];
			$check_time=$latest['check_time'];
		}


		if (version_compare($xpress_version, $latest_version, '>')){
				echo '<h3 class="response">';
				printf(__('You are using a XPressME Integration Kit development version (%1$s). Cool! Please <a href="%2$s">stay updated</a>.', 'xpressme') , $xpress_version , $latest['develop_url']);
				echo '</h3>';

		} else if (version_compare($xpress_version, $latest_version, '<')) {
			echo	'<h3 class="response">'. __('There is a new version of XPressME Integration Kit available for upgrade', 'xpressme') . '</h3>';
			echo '<p>';
			printf(__('You can upgrade to version %s download the package and install it manually:', 'xpressme'),$latest_version);
			echo '</p>';
			echo '<a class="button" href="' . $package . '">';
			printf(__('Download %s', 'xpressme') , $latest_version);
			echo '</a>';
			
			if ($latest['diff_response'] == 'diff_exists'){
				echo '<p>';
				printf(__('You can download the differential file from version %s to %s and upgrade it manually:', 'xpressme'),$xpress_version,$latest['diff_latest_version']);
				echo '</p>';
				echo '<a class="button" href="' . $latest['diff_package'] . '">';
					printf(__('Download differential file for %s', 'xpressme') , $latest['diff_latest_version']);
				echo '</a>';
			}
		} else {
			echo	'<h3 class="response">'. __('You have the latest version of XPressME Integration Kit. You do not need to upgrade', 'xpressme') . '</h3>';
		}
		
		// develop
		if ($latest['develop_response'] == 'development_exists'
			&& !empty($latest['develop_package'])
			)
		{
			echo '<h3 class="response">';
			printf(__('You can use the development version %s download the package and install it manually:', 'xpressme'),$latest['develop_latest_version']);
			echo '</h3>';
			echo '<a class="button" href="' . $latest['develop_package'] . '">';
			printf(__('Download %s', 'xpressme') , $latest['develop_latest_version']);
			echo '</a>';
			// develop diff
			if ($latest['diff_develop_response'] == 'diff_develop_exists'
				&& !empty($latest['diff_develop_package'])
				)
			{
					echo '<p>';
					printf(__('You can download the differential file from version %s to %s and upgrade it manually:', 'xpressme'),$xpress_version,$latest['diff_develop_latest_version']);
					echo '</p>';
					echo '<a class="button" href="' . $latest['diff_develop_package'] . '">';
						printf(__('Download differential file for %s', 'xpressme') , $latest['diff_develop_latest_version']);
					echo '</a>';
			}
		}

	} else {
		echo '<h3 class="response">';
		printf(__('There is no response from <a href="%s">version check API</a> now. sorry, please confirm it after.', 'xpressme'),$check_url);
		echo	"</div>\n";
	}
}

function xp_remote_get($url, $headers = ""){
	global $xoops_config;
	$xpress_version = $xoops_config->module_version . $xoops_config->module_codename;

	require_once( $xoops_config->module_path . '/wp-includes/class-snoopy.php');

	// Snoopy is an HTTP client in PHP
	$client = new Snoopy();
	$client->agent = 'XPressME/' . $xpress_version;
	$client->read_timeout = 2;
	if (is_array($headers) ) {
		$client->rawheaders = $headers;
	}

	@$client->fetch($url);
	$response['response']['code'] = $client->status;
	$response['body'] = $client->results;
	return $response;
	return $client;

}

function get_xpress_latest_version(){
	global $wp_version, $wpdb, $wp_local_package;
	global $xoops_config;
	
	$xpress_version = $xoops_config->module_version . $xoops_config->module_codename;
	$lang = WPLANG;

	$check_url = "http://ja.xpressme.info/version_check/index.php?version=$xpress_version&lang=$lang";
	$request_options = array(
	'timeout' => 3,
	'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
	);

	if (! function_exists('wp_remote_get')) {
		$response = xp_remote_get($check_url);
		
		if (empty($response['body'])) return false;
	} else {
	
		$response = wp_remote_get($check_url, $request_options);
		
		if ( is_wp_error( $response ) )
			return false;
	}
	if ( 200 != $response['response']['code'] )
		return false;
	$body = trim( $response['body'] );
	$body = str_replace(array("\r\n", "\r"), "\n", $body);
	$returns = explode("\n", $body);

	if ( isset( $returns[0] ) ) $response = $returns[0]; else $response = '';
	if ( isset( $returns[1] ) ) $url = clean_url( $returns[1] ); else $url = '';
	if ( isset( $returns[2] ) ) $package = clean_url( $returns[2] ); else $package = '';
	if ( isset( $returns[3] ) ) $latest_version = $returns[3]; else  $latest_version = '';
	if ( isset( $returns[4] ) ) $lang = $returns[4]; else $lang = '';
	
	// diff 
	if ( isset( $returns[6] ) ) $diff_response = $returns[6]; else $diff_response = '';
	if ( isset( $returns[7] ) ) $diff_url = clean_url( $returns[7] ); else $diff_url = '';
	if ( isset( $returns[8] ) ) $diff_package = clean_url( $returns[8] ); else $diff_package = '';
	if ( isset( $returns[9] ) ) $diff_latest_version = $returns[9]; else  $diff_latest_version = '';
	if ( isset( $returns[10] ) ) $diff_lang = $returns[10]; else $diff_lang = '';

	// developer 
	if ( isset( $returns[12] ) ) $develop_response = $returns[12]; else $develop_response = '';
	if ( isset( $returns[13] ) ) $develop_url = clean_url( $returns[13] ); else $develop_url = '';
	if ( isset( $returns[14] ) ) $develop_package = clean_url( $returns[14] ); else $develop_package = '';
	if ( isset( $returns[15] ) ) $develop_latest_version = $returns[15]; else  $develop_latest_version = '';
	if ( isset( $returns[16] ) ) $develop_lang = $returns[16]; else $develop_lang = '';
	
	// developer diff
	if ( isset( $returns[18] ) ) $diff_develop_response = $returns[18]; else $diff_develop_response = '';
	if ( isset( $returns[19] ) ) $diff_develop_url = clean_url( $returns[19] ); else $diff_develop_url = '';
	if ( isset( $returns[20] ) ) $diff_develop_package = clean_url( $returns[20] ); else $diff_develop_package = '';
	if ( isset( $returns[21] ) ) $diff_develop_latest_version = $returns[21]; else  $diff_develop_latest_version = '';
	if ( isset( $returns[22] ) ) $diff_develop_lang = $returns[22]; else $diff_develop_lang = '';

	$write_options = array (
		'response' => $response ,
		'url' => $url ,
		'package' => $package ,
		'latest_version' => $latest_version ,
		'lang' => $lang ,
		'diff_response' => $diff_response ,
		'diff_url' => $diff_url ,
		'diff_package' => $diff_package ,
		'diff_latest_version' => $diff_latest_version ,
		'diff_lang' => $diff_lang ,
		'develop_response' => $develop_response ,
		'develop_url' => $develop_url ,
		'develop_package' => $develop_package ,
		'develop_latest_version' => $develop_latest_version ,
		'develop_lang' => $develop_lang ,
		'diff_develop_response' => $diff_develop_response ,
		'diff_develop_url' => $diff_develop_url ,
		'diff_develop_package' => $diff_develop_package ,
		'diff_develop_latest_version' => $diff_develop_latest_version ,
		'diff_develop_lang' => $diff_develop_lang ,
		'check_time' => time()
	);
	
	$latest_version = get_option('xpressme_latest_version');
	if (!$latest_version) {
		add_option('xpressme_latest_version', $write_options);
	} else {
		update_option('xpressme_latest_version', $write_options);
	}
	return true;
}

function xpress_update_check() {
	if ( defined('WP_INSTALLING') )
		return;
	global $pagenow;

	$php_query_string = $_SERVER['QUERY_STRING'];

	if ( 'admin.php' == $pagenow && 'page=upgrade_page' == $php_query_string)
		return;

	global $wp_version, $wpdb, $wp_local_package;
	global $xoops_config;

	$php_query_string = $_SERVER['QUERY_STRING'];
	$xpress_version = $xoops_config->module_version . $xoops_config->module_codename;

	$latest = get_option('xpressme_latest_version');
	if (!$latest ) {
		get_xpress_latest_version();
		$latest = get_option('xpressme_latest_version');
	}

	if ($latest) {
		$next_check = $latest['check_time'] + (60*60*24);
		$now_time = time();
		if ($next_check < $now_time ){
			get_xpress_latest_version();
			$latest = get_option('xpressme_latest_version');
		}
	}

	if ($latest) {
		$url=$latest['url'];
		$package=$latest['package'];
		$latest_version=$latest['latest_version'];
		$check_time=$latest['check_time'];
		$upgrade_page = $xoops_config->module_url . "/wp-admin/admin.php?page=upgrade_page";

		if (version_compare($xpress_version, $latest_version, '<')) {
			if ( current_user_can('manage_options') ){
				$msg = sprintf( __('XPressME Integration Kit Version %1$s is available! <a href="%2$s">Please update now</a>.', 'xpressme'), $latest_version, $upgrade_page );
			} else {
				$msg = sprintf( __('XPressME Integration Kit Version %1$s is available! Please notify the site administrator.', 'xpressme'), $latest_version );
			}
			echo "<div id='update-nag'>$msg </div>";
		}
	}
}
?>