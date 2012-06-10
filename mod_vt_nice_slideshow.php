<?php
/**
 * @version		$Id: mod_vt_nice_slideshow.php 2012-06-12 vinaora $
 * @package		VINAORA NICE SLIDESHOW
 * @subpackage	mod_vt_nice_slideshow
 * @copyright	Copyright (C) 2012 VINAORA. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website		http://vinaora.com
 * @twitter		http://twitter.com/vinaora
 * @facebook	http://facebook.com/vinaora
 * @google+		https://plus.google.com/111142324019789502653
 */

// no direct access
defined('_JEXEC') or die;

// Require the base helper class only once
require_once dirname(__FILE__).DS.'helper.php';

$module_id = $module->id;

// Initialize some variables 
$params->set('AppName', 'Vinaora Nice Slideshow');
$params->set('AppVersion', '2.5.0');

$params->set('GallerySuffix', $module_id);

// Get the Config Path on the server
$path	= JPath::clean( JPATH_BASE.'/media/mod_vt_nice_slideshow/config/'.$module_id );
$params->set('configPath', $path);

// Get the Config URL of the module
$base_url	= rtrim(JURI::base(true),'/');
$path		= $base_url.'/media/mod_vt_nice_slideshow/config/'.$module_id;
$params->set('ImgPath', $path);

modVtNiceSlideshowHelper::validParams($params);
modVtNiceSlideshowHelper::makeConfig($params);

$doc =& JFactory::getDocument();

// Add Module CSS to <head> tag
// JHtml::stylesheet( 'media/mod_vt_nice_slideshow/config/'.$module->id.'/style.css' );
$doc->addStyleSheet( $params->get('ImgPath').'/style.css' );

// Add jQuery library. Check jQuery loaded or not. See more details >> http://goo.gl/rK8Yr
$app = JFactory::getApplication();
$jqsource	= $params->get('jquery_source', 'local');
$jqversion	= $params->get('jquery_version', 'latest');
if($app->get('jquery') == false) {
	modVtNiceSlideshowHelper::addjQuery($jqsource, $jqversion);
	$app->set('jquery', true);
}

// Add Main script to <head> tag
$doc->addScript( $base_url.'/media/mod_vt_nice_slideshow/js/wowslider.js' );

// Path to config script. It'll be inserted inline, not to <head> tag
$script = $params->get('ImgPath').'/script.js';

$slider = modVtNiceSlideshowHelper::getSlider($params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_vt_nice_slideshow', $params->get('layout', 'default'));
