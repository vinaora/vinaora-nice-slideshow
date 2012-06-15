<?php
/**
 * @version		$Id: helper.php 2012-06-17 vinaora $
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

class modVtNiceSlideshowHelper{

	function __construct(){
	}
	
	public static function &validParams($params){

		$params->set('backMarginsLeft', 0);
		$params->set('backMarginsTop', 0);
		$params->set('backMarginsRight', 0);
		$params->set('backMarginsBottom', 0);

		$params->set('Border', 'none');

		$params->set('noFrame', 'true');
		$params->set('TooltipPos', 'top');
		$params->set('JSONList', 0);
		
		$params->set('ImageFillColor', '255,255,255');
		
		$param	= htmlspecialchars($params->get('prevCaption'), ENT_QUOTES);
		$params->set('prevCaption', $param);
		$param	= htmlspecialchars($params->get('nextCaption'), ENT_QUOTES);
		$params->set('nextCaption', $param);
		
		$param	= intval($params->get('ImageWidth'));
		$param	= (!$param) ? '640' : $param;
		$params->set('ImageWidth', $param);

		$param	= intval($params->get('ImageHeight'));
		$param	= (!$param) ? '480' : $param;
		$params->set('ImageHeight', $param);
		
		$param	= intval($params->get('SlideshowDuration', '500'));
		$params->set('SlideshowDuration', $param/100);
		
		$param	= intval($params->get('SlideshowDelay', '500'));
		$params->set('SlideshowDelay', $param/100);
		
		$param	= $params->get('Captions');
		if( $param == 'false' ) $params->set('item_description', '');
		
		self::_createConfigFolder($params);
		
		self::cutomStyle($params);
		
		return $params;
	}

	public static function makeConfig($params){
		if ( file_exists($params->get('configPath').DS.$params->get('lastedit').'.log') ) return;

		// Remove old files
		$files	= JFolder::files($params->get('configPath'), '.', false, true, array(), array('\.log$'));
		JFile::delete($files);
		
		// Make index.html
		$str = '<!DOCTYPE html><title></title>';
		JFile::write($params->get('configPath').DS.'index.html', $str);

		self::_makeImages($params);
		self::_makeCSS($params);
		self::_makeScript($params);

		// Make file log
		$str = mktime()."\n";
		$str .= var_export($params, true);
		JFile::write( $params->get('configPath').DS.$params->get('lastedit').'.log', $str);
	}
	
	/*
	 * Copy images from 'Common' directory and 'Themes' directory
	 */
	private static function _makeImages($params){
	
		// Check 'loading.gif' file exitst or not
		if ( file_exists($params->get('configPath').DS.'loading.gif') ) return;
		
		// Copy images for styles from 'Common' directory
		$src	= JPath::clean(JPATH_BASE.'/media/mod_vt_nice_slideshow/templates/common');
		$files	= JFolder::files($src, '[^\s]+(\.(?i)(jpg|png|gif|bmp))$', false, false);
		foreach($files as $file){
			JFile::copy($src.DS.$file, $params->get('configPath').DS.$file);
		}
		
		// Copy images for styles from 'Themes' directory
		$src	= JPath::clean(JPATH_BASE.'/media/mod_vt_nice_slideshow/templates/backgnd/'.$params->get('theme'));
		$files	= JFolder::files($src, '[^\s]+(\.(?i)(jpg|png|gif|bmp))$', false, false, array('thumbnail.png','.svn', 'CVS','.DS_Store','__MACOSX'));
		foreach($files as $file){
			JFile::copy($src.DS.$file, $params->get('configPath').DS.$file);
		}
	}

	/*
	 * Join files common/common.css and backgnd/[theme]/style*.css
	 */
	private static function _makeCSS($params){
		
		// Check CSS File exitst or not
		if ( file_exists($params->get('configPath').DS.'style.css') ) return;
		
		$css	= '';
		
		$path	= JPath::clean( JPATH_BASE.'/media/mod_vt_nice_slideshow/templates/common' );
		
		// Join Common Style
		$file	= $path.DS.'common.css';
		if( file_exists($file) ){
			$css	.= file_get_contents( $file );
		}
		
		$path	= JPath::clean( JPATH_BASE.'/media/mod_vt_nice_slideshow/templates/backgnd'.DS.$params->get('theme') );
		
		// Join Theme Style
		$file	= $path.DS.'style.css';
		if( file_exists($file) ){
			$css	.= "\n/* Theme Style */\n";
			$css	.= file_get_contents( $file );
		}

		// Join Tooltip Position Style
		$file	= $path.DS.'style-'.$params->get('TooltipPos').'.css';
		if( ($params->get('ShowTooltips') == 'true') && file_exists($file) ){
			$css	.= "\n/* Controls Position Style */\n";
			$css	.= file_get_contents( $file );
		}
		
		// Join Tooltip Style
		$file	= $path.DS.'style-tooltip.css';
		if( ($params->get('ShowTooltips') == 'true') && file_exists($file) ){
			$css	.= "\n/* Tooltips Style */\n";
			$css	.= file_get_contents( $file );
		}
		
		// Join Shadow Style
		$file	= $path.DS.'style-shadow.css';
		if( ($params->get('noFrame') == 'false') && file_exists($file) ){
			$css	.= "\n/* Shadow Style */\n";
			$css	.= file_get_contents( $file );
		}
		
		// Join Frame Style
		$file	= $path.DS.'style-frame.css';
		if( ($params->get('noFrame') == 'false') && file_exists($file) ){
			$css	.= "\n/* Frame Style */\n";
			$css	.= file_get_contents( $file );
		}
		
		// Replace CSS variables
		$css	= str_replace( '#wowslider-container1', '#wowslider-container$GallerySuffix$', $css );
		$css	= preg_replace( "/\\$(\w+)\\$/e", '$params->get("$1")', $css );
		$css	= str_replace( '#wowslider-container', '#vt_nice_slideshow', $css );
		
		// Add Timestamp log
		$css	.= "\n/* Vinaora Nice Slideshow >> http://vinaora.com/ */";
		$css 	.= "\n/* ".mktime()." */";

		// Make file CSS
		JFile::write( $params->get('configPath').DS.'style.css', $css);
	}

	/*
	 * Join Javascript files
	 */
	private static function _makeScript($params){

		// Check 'script.js' file exitst or not
		if ( file_exists($params->get('configPath').DS.'script.js') ) return;
		
		$script = '';
		$effect = $params->get('ImageEffect');

		// Additional scripts for some effects
		$path	= JPath::clean( JPATH_BASE.'/media/mod_vt_nice_slideshow/templates/effects/'.$effect );
		switch( $effect ){
			case 'squares':
				$script .= file_get_contents( $path.DS.'coin-slider.js' )."\n";
				break;
			
			case 'flip':
				$script .= file_get_contents( $path.DS.'jquery.2dtransform.js' )."\n";
				break;
			
			default:
				break;
		}

		// Join Main Image Effect Script
		$script .= file_get_contents( $path.DS.'script.js' )."\n";
		
		// Join Slideshow Config Script
		$path	= JPath::clean( JPATH_BASE.'/media/mod_vt_nice_slideshow/js' );
		$start	= file_get_contents( $path.DS.'script_start.js' )."\n";
		
		// Replace Javascript variables
		$start	= preg_replace( "/\\$(\w+)\\$/e", '$params->get("$1")', $start );
		$start	= str_replace( '#wowslider-container', '#vt_nice_slideshow', $start );
		
		$script .= $start;
		
		// Add Timestamp log
		$script	.= "\n/* Vinaora Nice Slideshow >> http://vinaora.com/ */";
		$script .= "\n/* ".mktime()." */";
		
		// Make file Javascript
		JFile::write( $params->get('configPath').DS.'script.js', $script);
	}
	
	/*
	 * Create Config Directory
	 */
	private static function _createConfigFolder($params){
		$path	= $params->get('configPath');
		
		// If the folder is exist then do nothing
		if ( is_dir($path) ) return false;

		// If the folder is not exist then create it
		JFolder::create($path);

		$str = '<!DOCTYPE html><title></title>';
		JFile::write($path.DS.'index.html', $str);

		return true;
	}
	
	/*
	 * Add jQuery Library to <head> tag
	 */
	public static function addjQuery($source='local', $version='latest'){
		$source = strtolower(trim($source));
		$version = trim($version);

		switch($source){
			case 'local':
				JHtml::script("media/mod_vt_nice_slideshow/js/jquery/$version/jquery.min.js");
				break;
			case 'google':
				JHtml::script("https://ajax.googleapis.com/ajax/libs/jquery/$version/jquery.min.js");
				break;
			default:
				return false;
		}
		return true;
	}
	
	/*
	 * Get a Parameter in a Parameters String which are separated by a specify symbol (default: vertical bar '|').
	 * Example: Parameters = "value1 | value2 | value3". Return "value2" if positon = 2
	 */
	public static function getParam($param, $position=1, $separator='|'){

		$position = intval($position);

		// Not found the separator in string
		if( strpos($param, $separator) === false ){
			if ( $position == 1 ) return $param;
		}
		// Found the separator in string
		else{
			$param = ($separator = "\n") ? str_replace(array("\r\n","\r"), "\n", $param) : $param;
			$items = explode($separator, $param);
			if ( ($position > 0) && ($position < count($items)+1) ) return $items[$position-1];
		}

		return '';
	}

	public static function getSlider($params, $separator = "\n"){
		$slider = array('images'=>'', 'bullets'=>'');

		$item_dir		= $params->get('item_dir');
		
		$links	= $params->get('item_url');
		$links	= str_replace("|", "\r\n", $links);

		$target = $params->get('item_target');
		
		$titles	= $params->get('item_title');
		$titles	= str_replace("|", "\r\n", $titles);

		$descriptions = $params->get('item_description');
		$descriptions = str_replace("|", "\r\n", $descriptions);
		
		// Get all images
		$items	= self::getItems($params);

		if (empty($items) || !count($items)){
			return '';
		}
		
		foreach($items as $i=>$path){
			$i++;
			
			$link	= self::getParam($links, $i, $separator);
			$link	= trim($link);
			$link	= htmlspecialchars($link, ENT_QUOTES);

			$title	= self::getParam($titles, $i, $separator);
			$title	= trim($title);
			$title	= htmlspecialchars($title, ENT_QUOTES);

			$desc	= self::getParam($descriptions, $i, $separator);
			$desc	= trim($desc);
			$desc	= htmlspecialchars($desc, ENT_QUOTES);
			
			$item	= "<img src=\"$path\" alt=\"$title\" title=\"$title\" id=\"wows6_$i\" />";
			$item 	= (!empty($link)) ? "<a href=\"$link\" target=\"$target\">" . $item . "</a>" : $item; 
			$item 	= "<li>$item$desc</li>";

			$slider['images']	.= $item;
			
			$item	= "<a href=\"#\" title=\"$title\">";
			if (!empty($thumb)){
				$item .= "<img src=\"$thumb\" alt=\"$title\"/>";
			}
			
			$item	.= $i."</a>";
			
			$slider['bullets']	.= $item;
		}
		
		return $slider;
	}
	
	/*
	 * Get the Paths of Items
	 */
	public static function getItems($params){

		$param	= $params->get('item_path');
		$param	= str_replace(array("\r\n","\r"), "\n", $param);
		$param	= explode("\n", $param);

		// Get Paths from invidual paths
		foreach($param as $key=>$value){
			$param[$key] = self::validPath($value);
		}
		// Remove empty element
		$param = array_filter($param);
		// Get Paths from directory
		if (empty($param)){
			$param	= $params->get('item_dir');
			if ($param == "-1") return null;

			$filter		= '([^\s]+(\.(?i)(jpg|png|gif|bmp))$)';
			$exclude	= array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.htaccess');
			$excludefilter = array();
			// array_push($excludefilter, $params->get('controlNavThumbsReplace'));

			$param	= JFolder::files(JPATH_BASE.DS.'images'.DS.$param, $filter, true, true, $exclude, $excludefilter);
			foreach($param as $key=>$value){
				$value = substr($value, strlen(JPATH_BASE.DS) - strlen($value));
				$param[$key] = self::validPath($value);
			}
		}

		// Reset keys
		$param = array_values($param);
		return $param;
	}
	
	/*
	 * Get the Valid Path of Item
	 */
	public static function validPath($path){
		$path = trim($path);

		// Check file type is image or not
		if( !preg_match('/[^\s]+(\.(?i)(jpg|png|gif|bmp))$/', $path) ) return '';

		// The path includes http(s) or not
		if( preg_match('/^(?i)(https?):\/\//', $path) ){
			$base = JURI::base(false);
			if (substr($path, 0, strlen($base)) == $base){
				$path = substr($path, strlen($base) - strlen($path));
			}
			else return $path;
		}

		$path = JPath::clean($path, DS);
		$path = ltrim($path, DS);
		if (!is_file(JPATH_BASE.DS.$path)) return '';

		// Convert it to url path
		$path = JPath::clean(JURI::base(true).'/'.$path, '/');
		
		return $path;
	}
	
	public static function &cutomStyle($params){
		$theme	= $params->get('theme');
		$imageW	= $params->get('ImageWidth');
		$imageH	= $params->get('ImageHeight');

		switch($theme){
			case 'aqua':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				$param	= ( $params->get('noFrame') ) ? 'none': '9px solid #FFFFFF';
				$params->set('Border', $param);
				break;
				
			case 'block':
				$params->set('prevCaption', 'prev');
				$params->set('nextCaption', 'next');
				break;

			case 'crystal':
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>5, 'right'=>5, 'bottom'=>39, 'left'=>5);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
					$params->set('BulletsBottom', '-24');
				}else{
					$params->set('BulletsBottom', '5');
				}
				
				$params->set('decorW', $imageW - 8*2);
				$params->set('decorH', $imageH - 8*2);
				break;
				
			case 'chrome':
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>25, 'right'=>25, 'bottom'=>26, 'left'=>25);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
					$params->set('Border', 'solid 1px white');
				}
				break;
				
			case 'digit':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>14, 'right'=>15, 'bottom'=>60, 'left'=>15);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
					$params->set('BulletsBottom', '11');
				}else{
					$params->set('BulletsBottom', '-5');
				}
				break;
				
			case 'elemental':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				break;
			
			case 'flux':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				$param = ( $params->get('noFrame') ) ? 'none' : '10px solid #FFFFFF';
				$params->set('Border', $param);
				break;
				
			case 'mac':
			case 'native':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>9, 'right'=>10, 'bottom'=>17, 'left'=>10);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
				}
				break;
			
			case 'mellow':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>11, 'right'=>11, 'bottom'=>11, 'left'=>11);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
				}
				break;
				
			case 'noble':
				break;
				
			case 'noir':
				$param	= $imageW + $params->get('backMarginsLeft') + $params->get('backMarginsRight');
				$params->set('ContaienerW', $param);
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$param	= round( $params->get('ContaienerW')*0.031 );
					$params->set('ShadowH', $param);
					
					$param	= round( 100*$params->get('ShadowH')/$params->get('ImageHeight') );
					$params->set('pShadowH', $param);
				}
				break;
				
			case 'numeric':
				$param	= $params->get('PageBgColor', '#d8d8d8');
				$params->set('PageBgColor', $param);
				
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>5, 'right'=>5, 'bottom'=>5, 'left'=>5);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
					$params->set('BulletsBottom', '0');
				}else{
					$params->set('BulletsBottom', '-5');
				}
				break;
				
			case 'pinboard':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>15, 'right'=>15, 'bottom'=>60, 'left'=>15);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
					
					$params->set('BulletsBottom', '20');
				}else{
					$params->set('BulletsBottom', '0');
				}
				break;
				
				
			case 'pulse':
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$border = array('top'=>15, 'right'=>15, 'bottom'=>15, 'left'=>15);
					$ContaienerW = $imageW + $border["left"] + $border["right"];
					$ContaienerH = $imageH + $border["top"] + $border["bottom"];
					$params->set('frameL', round( 100*100*$border["left"]/$imageW) / 100 );
					$params->set('frameT', round( 100*100*$border["top"]/$imageH) / 100 );
					$params->set('frameW', floor( 100*100*($imageW + $border["left"] + $border["right"]) /$imageW) / 100 );
					$params->set('frameH', floor( 100*100*($imageH + $border["top"] + $border["bottom"]) /$imageH) / 100 );
				}
				break;
			
			case 'shady':
				$param	= $imageW + $params->get('backMarginsLeft') + $params->get('backMarginsRight');
				$params->set('ContaienerW', $param);
				
				$param	= $imageH + $params->get('backMarginsBottom');
				$params->set('ContaienerH', $param);
				if( $params->get('noFrame') == 'false' ){
					// frame border+shadow
					$param	= round( $params->get('ContaienerW')*1.4 );
					$params->set('ShadowW', $param);
					
					$param	= round( $params->get('ContaienerH')/2.12 );
					$params->set('ShadowH', $param);
				}
				break;
				
			case 'terse':
				$param	= $params->get('PageBgColor', '#d7d7d7');
				$params->set('PageBgColor', $param);
				
				$param = ( $params->get('noFrame') ) ? 'none' : '1px solid #FFFFFF';
				$params->set('Border', $param);
				
				break;
		}
		if( $params->get('ShowTooltips') == 'true' ){
			$param = $params->get('ThumbWidth');
			$params->set('ThumbWidthHalf', round($param / 2));
		}
		return $params;
	}
}
