<?php
/*
Plugin Name:  WPMU Custom CSS 
Plugin URI: http://wpwave.com/wordpress-mu-buddypress/
Description: WPMU Custom CSS is a plugin for wordpress mu that helps bloggers to change their theme style securely. Just like wordpress.com custom css paid upgrade.
Site admin can enable plugin for all of blogs or just for certain users (e.g. premium members)
Author: Hassan Jahangiry
Version: 1.06
Author URI: http://wpwave.com/wordpress-mu-buddypress/
*/

//To use Custom CSS with CMS Members enter plans ID below
global $allowed_plans;
$allowed_plans = (array)
$allowed_plans[]='PLANID';
$allowed_plans[]='AnotherPLANID';

function custom_css_setting( $id ) {
	$custom_css_setting = get_blog_option($id, "custom_css_setting"); 
	$custom_css_global = get_site_option("custom_css_global"); 
		
	if ($custom_css_global) { ?>
		<tr>
			<th><?php echo $id.$custom_css_setting; _e('Enable Custom CSS'); ?></th>
			<td><input type="hidden" name="option[custom_css_setting]" value=""  /><label><input type="checkbox" name="option[custom_css_setting]" value="1" <?php if ($custom_css_setting) echo 'checked="checked"'; ?> /> Enable Custom CSS for this blog</label></td>
		</tr>
		<?php
	}
}
add_action('wpmueditblogaction', 'custom_css_setting');



function update_css_global_setting() {
	if (isset($_POST['custom_css_global'])) 
		update_site_option( "custom_css_global", $_POST['custom_css_global']); 
}
add_action('update_wpmu_options','update_css_global_setting');


function custom_css_global_setting() { 
		$custom_css_global = get_site_option("custom_css_global"); 
?>
		<table class="form-table">
    		<tr valign="top"> 
				<th scope="row">Enable custom CSS for</th> 
				<td><label><input type='radio' id="custom_css_global" name="custom_css_global" value='' <?php if (!$custom_css_global) echo 'checked="checked"'; ?> /> All blogs</label><br />
				<label><input type='radio' id="custom_css_global" name="custom_css_global" value='1' <?php if ($custom_css_global) echo 'checked="checked"'; ?>/> Specific</label><br  />
			To enable Custom CSS for a specif blog go to blog edit page.</td>
			</tr>
		</table>

<?php
}
add_action('wpmu_options','custom_css_global_setting');


function custom_css_header() {
$custom_css=get_option('custom-css');
	if ($custom_css) {
	echo "\n <!-- Custom CSS : http://wpwave.com/ --> \n";
	?>
		<style type="text/css" media="screen">
            <?php echo $custom_css."\n"; ?>
        </style>
	<?php
	}
}


function custom_css_page() {
	
	$defaultmsg="/* Welcome to Custom CSS!
If you are familiar with CSS or you have a stylesheet ready to paste, you may delete these comments and get started.
CSS (Cascading Style Sheets) is a kind of code that tells the browser how to render a web page. Here's an example:
img { border: 1px solid red; }
That line basically means \"give images a red border one pixel thick.\"

We hope you enjoy developing your custom CSS. Here are a few things to keep in mind:
You can not edit the stylesheets of your theme. Your stylesheet will be loaded after the theme stylesheets, which means that your rules can take precedence and override the theme CSS rules.

CSS comments will be stripped from your stylesheet. */

/* This is a comment. Comments begin with /* and end with */

/*
Things we strip out include:
 * HTML code
 * @import rules
 * expressions
 * invalid and unsafe code
 * URLs not using the http: protocol

Things we encourage include:
 * @media blocks!
 * sharing your CSS!
 * testing in several browsers!
*/";
	$stylecontent=get_option('custom-css');
	if (!$stylecontent)
		$stylecontent=$defaultmsg;
	
	// Update
	if ( (isset($_POST['action']))&&($_POST['action']=='update')) {
	
		if (isset($_POST['restore'])) {
			update_option('custom-css','');
			$stylecontent=$defaultmsg;
		}elseif (isset($_POST['newcontent'])) {
			
			$stylecontent = stripslashes($_POST['newcontent']);
			
			//remove scripts and PHP
			$stylecontent = stripslashes(wp_filter_post_kses( $stylecontent ));
			
			//remove HTML
			$stylecontent=strip_tags($stylecontent);
			
			//remove comments
			$pattern2="/\/\*.*?\\*\//is";
			$stylecontent=preg_replace($pattern2,'',$stylecontent);
			
			
			//remove import and expressions with ()
			$stylecontent=preg_replace('/@import\\(.*?\\)/is','',$stylecontent);
			$stylecontent=preg_replace('/import\\(.*?\\)/is','',$stylecontent);
			$stylecontent=preg_replace('/expression\\(.*?\\)/is','',$stylecontent);
			
			$stylecontent=str_replace('important','replaceim',$stylecontent);
			
			//remove import and expressions
			$stylecontent=preg_replace('/@import/is','',$stylecontent);
			$stylecontent=preg_replace('/import/is','',$stylecontent);
			$stylecontent=preg_replace('/expression/is','',$stylecontent);
			
			$stylecontent=str_replace('replaceim','important',$stylecontent);
			
			//check urls
			$pattern="/\\(.*?\\)/is";	
			preg_match_all($pattern,$stylecontent,$matches);

					$rub=array(')','(','\'','"');
					
					foreach($matches[0] as $match) {
						$match=str_replace($rub,'',$match); //remove (' ("
						strtolower($match);
						
						
						//strRpos
						$pos[0]=strrpos($match,'.jpg');
						$pos[1]=strrpos($match,'.jpeg');
						$pos[2]=strrpos($match,'.gif');
						$pos[3]=strrpos($match,'.png');
						
						//.jpeg?w=1000... 25
						$acceptablepo=strlen($match)-25;
						$error=true;
						
						foreach($pos as $po) {
							if($po>$acceptablepo) {
								$error=false;
								continue;
							}
						}
						if($error)
							$msg.='Invalid URL: '.$match.'<br/>';
						
						if (substr(trim($match),0,7)!=='http://') {
							$error=true;
							$msg.='URL should start with http:// '.$match.'<br/>';
						}
						
					} //endforeach
		
		
			if(!$msg) {
				update_option('custom-css',$stylecontent);
				$msg='Stylesheet updated successfully.';
			}
		}//endelseif
	}//endif

	?>
    
	
	
	<?php
	 if ($msg) : ?>
 		<div id="message" class="updated fade"><p><?php echo _e($msg); ?></p></div>
	<?php endif; ?>
 
     <div class="wrap">
     <div id="icon-themes" class="icon32"><br /></div>
     <h2><?php _e('Custom CSS');?></h2>
    
    <form name="template" id="template" action="" method="post">
	
		 <div><textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1" class="large-text code"><?php echo $stylecontent; ?></textarea>
		 <input type="hidden" name="action" value="update" />
		 
		 </div>

		<div>
<p><? _e('Your theme stylesheet');?>: <a href="<?php bloginfo('stylesheet_url'); ?>">style.css</a></p>

			<p class="submit">
<?php
	echo "<input type='submit' name='submit' class='button-primary' value='" . __('Update') . "' tabindex='2' />";
	echo " | <input type='submit' name='restore' class='button' value='" . __('Restore Default') . "' tabindex='3' />";
?>
</p>
		</div>
	</form>
    

<div class="clear"> &nbsp; </div>
</div>
<?php
} //end func


function add_usertheme_menu() {
	global $allowed_plans, $user_ID;
	$custom_css_setting = get_option("custom_css_setting"); 
	$custom_css_global = get_site_option("custom_css_global"); 
	$planid=get_usermeta($user_ID, 'planid');
	
	if ( ($planid) && (in_array($planid, $allowed_plans)) ) 
		$val=true;
	else
		$val=false;
		
	if ( (!$custom_css_global) || ($custom_css_setting) || ($val) || (is_site_admin()) )
		add_submenu_page('themes.php',__('Custom CSS'), __('Custom CSS'), 8, basename(__FILE__), 'custom_css_page');
}
add_action('admin_menu', 'add_usertheme_menu');
add_action('wp_head', 'custom_css_header');
?>