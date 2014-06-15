<?php
/*
Plugin Name: Easy Video Player
Version: 1.0.3
Plugin URI: http://easywpguide.wordpress.com/?p=25
Author: naa986
Author URI: http://easywpguide.wordpress.com/
Description: Easily embed videos into your WordPress blog
*/

if(!defined('ABSPATH')) exit;
if(!class_exists('EASY_VIDEO_PLAYER'))
{
	class EASY_VIDEO_PLAYER 
	{
		var $plugin_version = '1.0.3';
		function __construct() 
		{
			define('EASY_VIDEO_PLAYER_VERSION', $this->plugin_version);
			$this->plugin_includes();
		}
		function plugin_includes()
		{
			if(is_admin( ) ) 
			{
				add_filter('plugin_action_links', array(&$this,'easy_video_player_plugin_action_links'), 10, 2 );
			}
                        add_action('wp_enqueue_scripts', 'easy_video_player_enqueue_scripts');
			add_action('admin_menu', array( &$this, 'easy_video_player_add_options_menu' ));
			add_shortcode('evp_embed_video','evp_embed_video_handler');	
		}
		function plugin_url() 
		{
			if($this->plugin_url) return $this->plugin_url;
			return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}
		function easy_video_player_plugin_action_links($links, $file) 
		{
			if ( $file == plugin_basename( dirname( __FILE__ ) . '/easy-video-player.php' ) ) 
			{
				$links[] = '<a href="options-general.php?page=easy-video-player-settings">Settings</a>';
			}
			return $links;
		}
		
		function easy_video_player_add_options_menu()
		{
			if(is_admin())
			{
				add_options_page('Easy Video Player Settings', 'Easy Video Player', 'manage_options', 'easy-video-player-settings', array(&$this, 'easy_video_player_options_page'));
			}
			add_action('admin_init', array(&$this, 'easy_video_player_add_settings'));		
		}
		function easy_video_player_add_settings()
		{ 
			register_setting('easy-video-player-settings-group', 'evp_enable_jquery');	
		}
		function easy_video_player_options_page()
		{
			
			?>
			<div class="wrap">
			<div id="poststuff"><div id="post-body">
			
			<h2>Easy Video Player - v<?php echo $this->plugin_version;?></h2>
                        <div class="postbox">
			<h3><label for="title">Setup Guide</label></h3>
			<div class="inside">		
                            <p>For detailed documentation please visit the plugin homepage <a href="http://easywpguide.wordpress.com/?p=25" target="_blank">here</a></p>
			</div></div>
			<div class="postbox">
			<h3><label for="title">General Settings</label></h3>
			<div class="inside">		
			<form method="post" action="options.php">
			    <?php settings_fields('easy-video-player-settings-group'); ?>
			    <table class="form-table">
			        <tr valign="top">
			        <th scope="row">Enable jQuery</th>
			        <td><input type="checkbox" id="evp_enable_jquery" name="evp_enable_jquery" value="1" <?php echo checked(1, get_option('evp_enable_jquery'), false) ?> /> 
			        <p><i>By default this option should always be checked.</i></p>
			        </td>
			        </tr>
			    </table>
			    
			    <p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			    </p>		
			</form>
			</div></div>
			
			</div></div>
			</div>
			<?php
		}
	}
	$GLOBALS['easy_video_player'] = new EASY_VIDEO_PLAYER();
}

function easy_video_player_enqueue_scripts()
{
    if (!is_admin()) 
    {
        $plugin_url = plugins_url('',__FILE__);
        $enable_jquery = get_option('evp_enable_jquery');
        if($enable_jquery)
        {
            wp_enqueue_script('jquery');
        }
    	wp_register_script('flowplayer-js', $plugin_url.'/lib/flowplayer.js');
	wp_enqueue_script('flowplayer-js');
    	wp_register_style('flowplayer-css', $plugin_url.'/lib/minimalist.css');
        wp_enqueue_style('flowplayer-css');	
    }	
}

function evp_embed_video_handler($atts)
{
	extract(shortcode_atts(array(
            'url' => '',
            'width' => '',
            'height' => '',
            'ratio' => '0.417',
            'autoplay' => 'false',
	), $atts));
    if($autoplay=="true"){
        $autoplay = " autoplay";
    }
    else{
        $autoplay = "";
    }
    $player = "fp".uniqid();
    $styles = "";
    if(!empty($width) && !empty($height)){
        $styles = <<<EOT
        <style>
            #$player {
                width: {$width}px;
                height: {$height}px;
            }
        </style>
EOT;
    }
	$output = <<<EOT
        <div id="$player" data-ratio="$ratio" class="flowplayer">
            <video{$autoplay}>
               <source type="video/mp4" src="$url"/>
            </video>
        </div>
        $styles
EOT;
	return $output; 
}
