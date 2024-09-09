<?php
/**
 * @package lenguaje
 * @version 1.0.0
 */
/*
 * Plugin Name: show_gif
 * Plugin URI: https://epacartagena.gov.co/
 * Description: Plugin que permite mostrar un gif de acuerdo al elemento seleccionado y administrar una lista de videos personalizada.
 * Author: Juan López
 * Version: 1.0.0
 * Author URI: https://www.linkedin.com/in/juan-canchila/
 * Text Domain: show_gif
 */
if (!defined('ABSPATH')) {
    echo "get out!";
    // Echo JavaScript code for a 3-second delay and redirect
    echo '<script>
        setTimeout(function(){
            window.location.href = "https://www.policia.gov.co/";
        }, 3000);
      </script>';
    exit;
}

class show_gif
{
    public function __construct()
    {
        // Create custom post type
        add_action('init', array($this, 'create_post_type'));

        // Add meta box for custom fields
        add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));

        // Save custom field data
        add_action('save_post', array($this, 'save_custom_meta_box'));

        // Load shortcode
        add_shortcode('show_gif', array($this, 'load_shortcode'));

        // Load assets (js, css, etc)
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));

        // Register Rest API
        add_action('rest_api_init', array($this, 'register_rest_api'));

        // Add jQuery
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Apply filter to modify SSL verification settings for the specific request
        add_filter('http_request_args', array($this, 'modify_ssl_verification'), 10, 2);
    }

    public function create_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'labels' => array(
                'name' => 'Lista de Videos',
                'singular_name' => 'Video',
            ),
            'menu_icon' => 'dashicons-video-alt2',
        );

        register_post_type('lista_videos', $args);
    }

    public function add_custom_meta_box()
    {
        add_meta_box(
            'lista_videos_meta_box',
            'Información del Video',
            array($this, 'render_meta_box'),
            'lista_videos',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        // Retrieve current values for text and file path
        $video_name = get_post_meta($post->ID, '_video_name', true);
        $video_path = get_post_meta($post->ID, '_video_path', true);

        ?>
        <label for="video_name">Nombre del Video:</label>
        <input type="text" id="video_name" name="video_name" value="<?php echo esc_attr($video_name); ?>" class="widefat">
        <br><br>
        <label for="video_path">Ruta del Archivo:</label>
        <input type="text" id="video_path" name="video_path" value="<?php echo esc_attr($video_path); ?>" class="widefat">
        <?php
    }

    public function save_custom_meta_box($post_id)
    {
        // Check if the current user is authorized to save metadata
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save the video name
        if (isset($_POST['video_name'])) {
            update_post_meta($post_id, '_video_name', sanitize_text_field($_POST['video_name']));
        }

        // Save the video path
        if (isset($_POST['video_path'])) {
            update_post_meta($post_id, '_video_path', sanitize_text_field($_POST['video_path']));
        }
    }

    public function load_assets()
    {
        wp_enqueue_style('show_gif', plugin_dir_url(__FILE__) . 'assets/css/show_gif.css', array(), '1', 'all');
        wp_enqueue_script('show_gif', plugin_dir_url(__FILE__) . 'assets/js/show_gif.js', array('jquery'), '1', true);
    }

    public function load_shortcode() {
        ?>
        <div class="show_gif_form_css">
            <!-- Contenedor del GIF -->
            <div class="gif-container">
                <img id="dynamic-gif" src="" alt="GIF animado" style="max-width: 100%; max-height: 100%; opacity: 0.8;">
            </div>
        </div>
        <?php
    }



    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery');
    }

    public function register_rest_api()
    {
        error_log('Registrando la API REST...'); // Esto debería aparecer en el archivo de log de PHP

        register_rest_route('show-gif/v1', '/lista-videos/', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_lista_videos'),
        ));
    }


    public function get_lista_videos()
    {
        $args = array(
            'post_type' => 'lista_videos',
            'posts_per_page' => -1, // Obtener todos los videos
        );

        $query = new WP_Query($args);
        $videos = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $videos[] = array(
                    'title' => get_the_title(),
                    'video_name' => get_post_meta(get_the_ID(), '_video_name', true),
                    'video_path' => get_post_meta(get_the_ID(), '_video_path', true),
                );
            }
        }

        wp_reset_postdata();

        return $videos;
    }

    public function modify_ssl_verification($args, $url)
    {
        // Modificación de la verificación SSL si es necesario
        return $args;
    }
}

new show_gif();
