<?php
/**
 * @version		$Id: default.php 2012-06-12 vinaora $
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
?>

<!-- Begin: Vinaora Nice Slideshow >> http://vinaora.com/ -->
<div class="vt_nice_slideshow<?php echo $moduleclass_sfx; ?>" style="background-color:#ccc; padding: 10px;">
<?php if ( !empty($slider) ){ ?>
	<div id="vt_nice_slideshow<?php echo $module_id;?>">
		<div class="ws_images"><ul><?php echo $slider["images"]; ?></ul></div>
<?php if( $params->get('ShowBullets')=='true' ){ ?>
		<div class="ws_bullets"><div><?php echo $slider["bullets"]; ?></div></div>
<?php }?>
<?php if( $params->get('noFrame')=='false' ){ ?>
		<a href="#" class="ws_frame"></a>
		<div class="ws_shadow"></div>
<?php }?>
	</div>
<?php }else{ ?>
		<div style="background-color:#ccc; color:#000;"> Image Not Found </div> 
<?php } ?>
</div>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<!-- End: Vinaora Nice Slideshow >> http://vinaora.com/ -->

