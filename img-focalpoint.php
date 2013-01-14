<?php
/*
Plugin Name: Image Focal Point
Plugin URI: 
Description: Plugin to define image focal points for CSS consumption
Version: 1.0
Author: Deric Mendez
Author URI: http://www.dericmendez.com
*/

if (!defined('FOCALPOINT_PLUGIN_NAME')) {
  //all-in-one-favicon
  define('FOCALPOINT_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
}

if (!defined('FOCALPOINT_PLUGIN_URL')) {
  // http://www.domain.com/wordpress/wp-content/plugins/all-in-one-favicon
  define('FOCALPOINT_PLUGIN_URL', WP_PLUGIN_URL . '/' . FOCALPOINT_PLUGIN_NAME);
}

add_action('wp_ajax_image-focalpoint', 'fp_wp_ajax_image_focalpoint', 0);
add_action('wp_ajax_image-editor', 'fp_wp_ajax_image_editor', 0);
add_action('wp_ajax_query-attachments', 'wp_ajax_query-attachments');
add_filter('wp_get_attachment_image_attributes', 'fp_filter_image_focalpoint', 10, 2);
add_filter('get_image_tag_class', 'fp_get_image_tag_class', 10, 2);
add_filter('get_image_tag', 'fp_get_image_tag', 10, 2);

if ( is_admin() ) {      
  wp_register_script('image-focal-point', FOCALPOINT_PLUGIN_URL . '/js/image-focal-point.js', array('jquery'));
  wp_register_style('image-focal-point', FOCALPOINT_PLUGIN_URL . '/css/image-focal-point.css', null, 0.1, 'screen' );

  wp_enqueue_script('image-focal-point');
  wp_enqueue_style( 'image-focal-point' );
}

function fp_filter_image_focalpoint($attr, $attachment){  
  $focal_x = get_post_meta($attachment->ID, 'image_focal_x', true);
  $focal_y = get_post_meta($attachment->ID, 'image_focal_y', true);
  $metadata = wp_get_attachment_metadata($attachment->ID);
  $imagew = $metadata['width'];
  $imageh = $metadata['height'];

  if ( $imagew > $imageh ) {
    $attr['class'] .= ' landscape';
  } else {
    $attr['class'] .= ' portrait';
  }
  
  if ( $focal_x )
    $attr['class'] .= ' fpx-' . $focal_x;

  if ( $focal_y )
    $attr['class'] .= ' fpy-' . $focal_y;

  return $attr;
}

function fp_get_image_tag($html) {
  //return '<span class="wp-image-wrap">' . $html . '</span>';
  return $html;
}

function fp_get_image_tag_class($classname, $id) {
  $focal_x = get_post_meta($id, 'image_focal_x', true);
  $focal_y = get_post_meta($id, 'image_focal_y', true);
  $metadata = wp_get_attachment_metadata($id);
  $imagew = $metadata['width'];
  $imageh = $metadata['height'];

  if ( $imagew > $imageh ) {
    $classname .= ' landscape';
  } else {
    $classname .= ' portrait';
  }

  if ( $focal_x )
    $classname .= ' fpx-' . $focal_x;

  if ( $focal_y )
    $classname .= ' fpy-' . $focal_y;

  return $classname;
}

function fp_wp_ajax_image_focalpoint() {
  $attachment_id = intval($_GET['postid']);
  if ( empty($attachment_id) || !current_user_can('edit_post', $attachment_id) )
    wp_die( -1 );

    $meta_y = update_post_meta($_GET['postid'], "image_focal_y", $_GET['focal_y'] );
    $meta_x = update_post_meta($_GET['postid'], "image_focal_x", $_GET['focal_x'] );

    @header( 'Content-Type: application/x-javascript; charset=' . get_option( 'blog_charset' ) );

    echo '{ 
      "success": true,
      "metax": "' . $meta_x . '",
      "metay": "' . $meta_y . '"
    }';

    wp_die();
}

function fp_wp_ajax_image_editor() {
  $focal_x = get_post_meta($_POST['postid'], 'image_focal_x', true);
  $focal_y = get_post_meta($_POST['postid'], 'image_focal_y', true);
  ?>
    <script>
      new window.imageFocalGrid({
        postid: "<?php echo $_POST['postid']; ?>", 
        x: "<?php echo $focal_x; ?>", 
        y: "<?php echo $focal_y; ?>"
      });
    </script>
  <?php
}

?>