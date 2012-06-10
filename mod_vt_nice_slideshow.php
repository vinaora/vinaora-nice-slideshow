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

$params->set('AppName', 'Vinaora Nice Slideshow');
$params->set('AppVersion', '2.5.0');

$params->set('GallerySuffix', $module->id);
$params->set('Border', 'none');

$params->set('noFrame', false);

$params->set('module_code', $module->id);
$params->set('configPath', JPATH_BASE.DS.'media'.DS.'mod_vt_nice_slideshow'.DS.'config'.DS.$module->id);

$params->set('TooltipPos', 'top');
$params->set('JSONList', 0);

$base_url = rtrim(JURI::base(true),"/");
$config_path =  $base_url."/media/mod_vt_nice_slideshow/config/".$params->get('module_code');
$params->set('ImgPath', $config_path);

modVtNiceSlideshowHelper::validParams($params);

modVtNiceSlideshowHelper::makeConfig($params);

$slider = modVtNiceSlideshowHelper::getSlider($params);
// $slider = array();

$doc =& JFactory::getDocument();

$doc->addStyleSheet( $base_url."/media/mod_vt_nice_slideshow/config/".$params->get('module_code')."/style.css" );

$jqsource	= $params->get('jquery_source', 'local');
$jqversion	= $params->get('jquery_version', 'latest');

// Add jQuery library. Check jQuery loaded or not. See more details >> http://goo.gl/rK8Yr
$app = JFactory::getApplication();
if($app->get('jquery') == false) {
	modVtNiceSlideshowHelper::addjQuery($jqsource, $jqversion);
	$app->set('jquery', true);
}

$doc->addScript( $base_url."/media/mod_vt_nice_slideshow/js/wowslider.js" );

$script = $base_url."/media/mod_vt_nice_slideshow/config/".$params->get('module_code')."/script.js";
// $doc->addScript( $base_url."/media/mod_vt_nice_slideshow/config/".$params->get('module_code')."/script.js" );

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

// $folders	= JFolder::folders(JPATH_BASE.DS.'media'.DS.'mod_vt_nice_slideshow', '.', true, true);
// foreach($folders as $folder){
	// JFile::copy(JPATH_BASE.DS.'media'.DS.'index.html', $folder.DS.'index.html');
// } 


require JModuleHelper::getLayoutPath('mod_vt_nice_slideshow', $params->get('layout', 'default'));
