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
function ext_image_proxy(&$buffer)
{
	global $modSettings;

  $exturl = 'https://images.weserv.nl/?url=';

	if($modSettings['ext_proxy_enabled']) {
		if(!empty($buffer) && stripos( $buffer, 'http://' ) !== false) {
			$buffer = preg_replace_callback( "~<img([\w\W]+?)/>~",
			function($matches) use ($exturl) {
				if(stripos( $matches[0], 'http://' ) !== false) {
				$matches[0] = preg_replace_callback( "~src\=(?:\"|\')(.+?)(?:\"|\')~",
				function($src) use ($exturl) {
					if(stripos( $src[1], 'http://' ) !== false) {
						$url = str_replace('http://', '', $src[1]);
						return ' src="'.$exturl.urlencode( $url ).'"';
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

function ext_image_proxy_general_mod_settings(&$config_vars)
{
	global $txt;

	$txt['ext_proxy_enabled'] = 'Enable the External Image Proxy';

	$config_vars[] = array('check', 'ext_proxy_enabled');

}

?>
