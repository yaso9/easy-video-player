<?php
/*
  Plugin Name: Easy Video Player
  Version: 2.0.0
  Plugin URI: http://noorsplugin.com/wordpress-video-plugin/
  Author: naa986
  Author URI: http://noorsplugin.com/
  Description: Easily embed videos into your WordPress blog
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('EASY_VIDEO_PLAYER')) {

    class EASY_VIDEO_PLAYER {

        var $plugin_version = '1.1.0';

        function __construct() {
            define('EASY_VIDEO_PLAYER_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array(&$this, 'easy_video_player_plugin_action_links'), 10, 2);
            }
            add_action('wp_enqueue_scripts', 'easy_video_player_enqueue_scripts');
            add_action('admin_menu', array(&$this, 'easy_video_player_add_options_menu'));
            add_action('wp_head', 'easy_video_player_header');
            add_shortcode('evp_embed_video', 'evp_embed_video_handler');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function easy_video_player_plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/easy-video-player.php')) {
                $links[] = '<a href="options-general.php?page=easy-video-player-settings">Settings</a>';
            }
            return $links;
        }

        function easy_video_player_add_options_menu() {
            if (is_admin()) {
                add_options_page('Easy Video Player Settings', 'Easy Video Player', 'manage_options', 'easy-video-player-settings', array(&$this, 'easy_video_player_options_page'));
            }
            add_action('admin_init', array(&$this, 'easy_video_player_add_settings'));
        }

        function easy_video_player_add_settings() {
            register_setting('easy-video-player-settings-group', 'evp_enable_jquery');
        }

        function easy_video_player_options_page() {
            ?>
            <div class="wrap">
                <div id="poststuff"><div id="post-body">

                        <h2>Easy Video Player - v<?php echo $this->plugin_version; ?></h2>
                        <div class="postbox">
                            <h3><label for="title">Setup Guide</label></h3>
                            <div class="inside">		
                                <p>For detailed documentation please visit the plugin homepage <a href="http://noorsplugin.com/wordpress-video-plugin/" target="_blank">here</a></p>
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

function easy_video_player_enqueue_scripts() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        $enable_jquery = get_option('evp_enable_jquery');
        if ($enable_jquery) {
            wp_enqueue_script('jquery');
        }
        wp_register_script('flowplayer-js', $plugin_url . '/lib/flowplayer.min.js');
        wp_enqueue_script('flowplayer-js');
        wp_register_style('flowplayer-css', $plugin_url . '/lib/skin/all-skins.css');
        wp_enqueue_style('flowplayer-css');
    }
}

function easy_video_player_header() {
    if (!is_admin()) {
        $fp_config = '<!-- This content is generated with the Easy Video Player plugin v' . EASY_VIDEO_PLAYER_VERSION . ' - http://noorsplugin.com/wordpress-video-plugin/ -->';
        $fp_config .= '<script>';
        $fp_config .= 'flowplayer.conf.embed = false;';
        $fp_config .= 'flowplayer.conf.keyboard = false;';
        $fp_config .= '</script>';
        $fp_config .= '<!-- Easy Video Player plugin -->';
        echo $fp_config;
    }
}

function evp_embed_video_handler($atts) {
    extract(shortcode_atts(array(
                'url' => '',
                'width' => '',
                'height' => '',
                'ratio' => '0.417',
                'autoplay' => 'false',
                'poster' => '',
                'loop' => '',
                'class' => '',
                'template' => '',
                    ), $atts));
    //check if mediaelement template is specified
    if($template=='mediaelement'){
        $attr = array();
        $attr['src'] = $url;
        if(is_numeric($width)){
            $attr['width'] = $width;
        }
        if(is_numeric($height)){
            $attr['height'] = $height;
        }
        if ($autoplay == "true"){
            $attr['autoplay'] = 'on';
        }
        if ($loop == "true"){
            $attr['loop'] = 'on';
        }
        if (!empty($poster)){
            $attr['poster'] = $poster;
        }
        return wp_video_shortcode($attr);
    }
    //
    if ($autoplay == "true") {
        $autoplay = " autoplay";
    } else {
        $autoplay = "";
    }
    if ($loop == "true") {
        $loop= " loop";
    }
    else{
        $loop= "";
    }
    $player = "fp" . uniqid();
    $color = '';
    if (!empty($poster)) {
        $color = 'background: #000 url('.$poster.') 0 0 no-repeat;background-size: 100%;';
    } else {
        $color = 'background-color: #000;';
    }
    $size_attr = "";
    if (!empty($width)) {
        $size_attr = "max-width: {$width}px;max-height: auto;";
    }
    $class_array = array('flowplayer', 'minimalist');
    if(!empty($class)){
        $shortcode_class_array = array_map('trim', explode(' ', $class));
        $shortcode_class_array = array_filter( $shortcode_class_array, 'strlen' );
        $shortcode_class_array = array_values($shortcode_class_array);
        if(in_array("functional", $shortcode_class_array) || in_array("playful", $shortcode_class_array)){
            $class_array = array_diff($class_array, array('minimalist'));
        }
        $class_array = array_merge($class_array, $shortcode_class_array);
        $class_array = array_unique($class_array);
        $class_array = array_values($class_array);
    }

    $classes = implode(" ", $class_array);
    $styles = <<<EOT
        <style>
            #$player {
                $size_attr
                $color
                display: grid;
            }
        </style>
EOT;

    $js = <<<EOT
        <script type="text/javascript">
            var player = document.getElementById('$player').getElementsByTagName('video')[0].getElementsByTagName('source')[0]
            var url = player.getAttribute('src')
            url = window.atob(url)
            player.setAttribute('src', url)
            document.getElementById('$player').getElementsByTagName('video')[0].load();
        </script>
EOT;
    
    $base64url = base64_encode($url);
    $output = <<<EOT
        <div id="$player" data-ratio="$ratio" class="{$classes}">
            <video{$autoplay}{$loop} controls>
               <source type="video/mp4" src="$base64url"/>
            </video>
        </div>
        $styles
        $js
EOT;
    return $output;
}
