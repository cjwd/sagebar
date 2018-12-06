<?php
/**
 * Plugin Name: Sage Template Debugger
 * Description: Shows what Main and Base template a page is using for a Sage 8 theme
 * Author URI: https://chinarajames.com
 * Version: 1.0.0
 */

function sagebar_settings_init() {

    register_setting('sagebar','sagebar_enable', ['type' => 'boolean']);
    register_setting('sagebar','sagebar_bg_colour');
    register_setting('sagebar','sagebar_text_colour');


    add_settings_section(
        'sagebar_settings_section',
        __('Sagebar Settings', 'sagebar'),
        'sagebar_section_display',
        'sagebar'
    );


    add_settings_field(
        'sagebar_enable',
        __('Enable Sage Bar', 'sagebar'),
        'sagebar_enable_display',
        'sagebar',
        'sagebar_settings_section',
        [
          'label_for' =>  'sagebar_enable'
        ]
    );

    add_settings_field(
      'sagebar_bg_colour',
      __('Background Colour', 'sagebar'),
      'sagebar_bg_colorpicker_display',
      'sagebar',
      'sagebar_settings_section',
      [
        'label_for' =>  'sagebar_bg_colour'
      ]
    );

    add_settings_field(
      'sagebar_text_colour',
      __('Text Colour', 'sagebar'),
      'sagebar_text_colorpicker_display',
      'sagebar',
      'sagebar_settings_section',
      [
        'label_for' =>  'sagebar_text_colour'
      ]
    );
}
add_action('admin_init', 'sagebar_settings_init');

/**
 * Callback functions
 */

function sagebar_section_display() {
  _e('Turn Sage Template Debug Bar on and off and customise it\'s look.', 'sagebar');
}

function sagebar_enable_display($args) {
  $input_html_string = '<input type="checkbox" id="%s" name="%s" value="1" %s>';
  printf($input_html_string, $args['label_for'], $args['label_for'], checked(1, get_option('sagebar_enable'), false) );
}

function sagebar_bg_colorpicker_display($args) {
  $input_html_string = '<input class="color-picker" data-alpha="true" data-default-color="rgba(0,0,0,0.7)" type="text" id="%s" name="%s" value="%s">';
  printf($input_html_string, $args['label_for'], $args['label_for'], get_option('sagebar_bg_colour') );
}

function sagebar_text_colorpicker_display($args) {
  $input_html_string = '<input class="color-picker" data-alpha="true" data-default-color="rgba(255,255,255,1)" type="text" id="%s" name="%s" value="%s">';
  printf($input_html_string, $args['label_for'], $args['label_for'], get_option('sagebar_text_colour') );
}

# End Callback functions

function sagebar_add_settings_link( $links ) {
    $settings_link = '<a href="tools.php?page=sagebar">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ) , 'sagebar_add_settings_link' );

function sagebar_options_page() {
  add_management_page(
    'Sagebar Options',
    'Sagebar Options',
    'manage_options',
    'sagebar',
    'sagebar_options_page_html'
  );
}
add_action('admin_menu', 'sagebar_options_page');

function sagebar_options_page_html() {

  if (!current_user_can('manage_options')) {
    return;
  }

  // add error/update messages
  if( isset( $_GET['settings-updated'] ) ) {
    add_settings_error('sagebar_messages', 'sagebar_message', __('Settings Saved', 'sagebar'), 'Updated');
  }

  // show error/update messages
  settings_errors('sagebar_messages');
  ?>
  <div class="wrap">
    <h1><?= esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
      <?php
      settings_fields('sagebar');
      do_settings_sections('sagebar');
      submit_button('Save Settings');
      ?>
    </form>
  </div>
  <?php
}

/**
 * Wrapper function for var_dump
 *
 * @param mixed $var Variable to be inspected
 * @param boolean $die  Whether to kill execution of rest of the page
 * @return void
 */
function dump($var, $die = false) {
  if(defined('WP_DEBUG') && true === WP_DEBUG) {
    echo '<pre>';
      var_dump($var);
    echo '</pre>';

    if( true == $die ) {
      die();
    }
  }
}

function sagebar_is_enabled_notice() {
  if(!get_option('sagebar_enable')) {
    return;
  }
  ?>
  <div class="notice notice-warning is-dismissible">
    <p><?php _e('Sage Template Debug Bar is enabled. This should be disabled in live/production environment.'); ?></p>
  </div>
  <?php
}
add_action('admin_notices', 'sagebar_is_enabled_notice');

function sage_wrap_info() {
  if(!get_option('sagebar_enable')) {
    return;
  }

  $main = \Roots\Sage\Wrapper\SageWrapping::$main_template;
  global $template;
  $string = '<span>%s Template: %s</span>';
  $bgColour = get_option('sagebar_bg_colour');
  $textColour = get_option('sagebar_text_colour');
?>
  <div id="sagebar" style="background-color: <?= $bgColour; ?>; color: <?= $textColour; ?>;">
    <?php printf($string, 'Main', basename($main));  ?>
    <?= '-' ?>
    <?php printf($string, 'Base', basename($template)); ?>
  </div>
<?php
}
add_action( 'wp_footer', 'sage_wrap_info' );

function sagebar_frontend_scripts() {
  wp_enqueue_style( 'sagedebug', plugin_dir_url( __FILE__ ) . '/public/css/styles.css', array(), '1.0.0', 'all' );
}
add_action( 'wp_enqueue_scripts', 'sagebar_frontend_scripts' );

function sagebar_admin_scripts($hook) {
  if('settings_page_sagebar' !== $hook ) {
    // return;
  }

  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('wp-color-picker-alpha', plugin_dir_url( __FILE__) . 'admin/js/wp-color-picker-alpha.min.js', array('wp-color-picker'), '2.1.3', true);
}
add_action( 'admin_enqueue_scripts', 'sagebar_admin_scripts');