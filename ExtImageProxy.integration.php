<?php

/**
 * @package "ExtImageProxy" Addon for Elkarte
 * @author tinoest
 * @license BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0.0
 *
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * integrate_buffer hook
 *
 * - Used to change http url to https via a external proxy
 */
function int_bufferExtImageProxy($buffer)
{
	global $modSettings;

	if(!empty($modSettings['extimageproxy_enabled'])) {
		if(!empty($buffer) && stripos( $buffer, 'http://' ) !== false) {
			$buffer = preg_replace_callback( "~<img([\w\W]+?)/>~",
			function($matches) use ($modSettings) {
				if(stripos( $matches[0], 'http://' ) !== false) {
				$matches[0] = preg_replace_callback( "~src\=(?:\"|\')(.+?)(?:\"|\')~",
				function($src) use ($modSettings) {
					if(stripos( $src[1], 'http://' ) !== false) {
						switch($modSettings['extimageproxy_type']) {
							case 'weserv':
								$url = str_replace('http://', '', $src[1]);
								$exturl = 'https://images.weserv.nl/?url=';
								if(!empty($modSettings['extimageproxy_custom_url'])) {
  									$exturl = 'https://'.$modSettings['extimageproxy_custom_url'].'/?url=';
								}
								return ' src="'.$exturl.urlencode( $url ).'"';
								break;
							case 'camo':
								$digest = hash_hmac('sha1', $src[1], $modSettings['extimageproxy_custom_key']);
								return ' src="https://'.$modSettings['extimageproxy_custom_url'].'/'.$digest.'/'.bin2hex($src[1]).'"';
								break;
							case 'nginx':
								return ' src="https://'.$modSettings['extimageproxy_custom_url'].'?'.$src[1].'"';
								break;
						}
					}
					else {
						return $src[0];
					}
				},
				$matches[0]);
				}
				return $matches[0];
			},
			$buffer );
		}
	}
	return $buffer;
}

/**
 * int_adminAreasExtImageProxy()
 *
 * - Admin Hook, integrate_admin_areas, called from Admin.php
 * - Used to add/modify admin menu areas
 *
 * @param mixed[] $admin_areas
 */
function int_adminAreasExtImageProxy(&$admin_areas)
{
	global $txt;
	loadLanguage('ExtImageProxy');
	$admin_areas['config']['areas']['addonsettings']['subsections']['extimageproxy'] = array($txt['extimageproxy_title']);
}

/**
 * int_adminExtImageProxy()
 *
 * - Admin Hook, integrate_sa_modify_modifications, called from AddonSettings.controller.php
 * - Used to add subactions to the addon area
 *
 * @param mixed[] $sub_actions
 */
function int_adminExtImageProxy(&$sub_actions)
{
	global $context, $txt;
	$sub_actions['extimageproxy'] = array(
		'dir' => SOURCEDIR,
		'file' => 'ExtImageProxy.integration.php',
		'function' => 'extimageproxy_settings',
		'permission' => 'admin_forum',
	);
	$context[$context['admin_menu_name']]['tab_data']['tabs']['extimageproxy']['description'] = $txt['extimageproxy_desc'];
}
/**
 * extimageproxy_settings()
 *
 * - Defines our settings array and uses our settings class to manage the data
 */
function extimageproxy_settings()
{
	global $txt, $context, $scripturl, $modSettings;
	loadLanguage('ExtImageProxy');
	// Lets build a settings form
	require_once(SUBSDIR . '/SettingsForm.class.php');
	// Instantiate the form
	$extImageProxySettings = new Settings_Form();
	// All the options, well at least some of them!
	$config_vars = array (
		array('check', 'extimageproxy_enabled', 'postinput' => $txt['extimageproxy_enabled_desc']),
		// Transition effects and speed
		array('title', 'extimageproxy_options'),
		array('select', 'extimageproxy_type',
			array (
				'weserv' 	=> $txt['extimageproxy_weserv'],
				'camo' 		=> $txt['extimageproxy_camo'],
				'nginx' 	=> $txt['extimageproxy_nginx'],
			)
		),
		array('text', 'extimageproxy_custom_url'),
		array('text', 'extimageproxy_custom_key'),
	);
	// Load the settings to the form class
	$extImageProxySettings->settings($config_vars);
	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		Settings_Form::save_db($config_vars);
		redirectexit('action=admin;area=addonsettings;sa=extimageproxy');
	}
	// Continue on to the settings template
	$context['settings_title'] = $txt['extimageproxy_title'];
	$context['page_title'] = $context['settings_title'] = $txt['extimageproxy_settings'];
	$context['post_url'] = $scripturl . '?action=admin;area=addonsettings;sa=extimageproxy;save';
	Settings_Form::prepare_db($config_vars);
}

?>
