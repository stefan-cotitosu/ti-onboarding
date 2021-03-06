<?php
/**
 * Handles admin logic for the onboarding.
 *
 * Author:  Andrei Baicus <andrei@themeisle.com>
 * On:      21/06/2018
 *
 * @package    themeisle-onboarding
 * @soundtrack Smell the Roses - Roger Waters
 */

/**
 * Class Themeisle_OB_Admin
 *
 * @package themeisle-onboarding
 */
class Themeisle_OB_Admin {

	/**
	 * Initialize the Admin.
	 */
	public function init() {
		add_filter( 'query_vars', array( $this, 'add_onboarding_query_var' ) );
		add_filter( 'ti_about_config_filter', array( $this, 'add_demo_import_tab' ), 15 );
		add_action( 'after_switch_theme', array( $this, 'get_previous_theme' ) );
	}

	/**
	 * Memorize the previous theme to later display the import template for it.
	 */
	public function get_previous_theme() {
		$previous_theme = strtolower( get_option( 'theme_switched' ) );
		set_theme_mod( 'ti_prev_theme', $previous_theme );
	}

	/**
	 * Add our onboarding query var.
	 *
	 * @param array $vars_array the registered query vars.
	 *
	 * @return array
	 */
	public function add_onboarding_query_var( $vars_array ) {
		array_push( $vars_array, 'onboarding' );

		return $vars_array;
	}

	/**
	 * Add about page tab list item.
	 *
	 * @param array $config about page config.
	 *
	 * @return array
	 */
	public function add_demo_import_tab( $config ) {
		$config['custom_tabs']['sites_library'] = array(
			'title'           => __( 'Sites Library', 'textdomain' ),
			'render_callback' => array(
				$this,
				'add_demo_import_tab_content',
			),
		);

		return $config;
	}

	/**
	 * Add about page tab content.
	 */
	public function add_demo_import_tab_content() {
		?>
		<div id="<?php echo esc_attr( 'demo-import' ); ?>">
			<?php $this->render_site_library(); ?>
		</div>
		<?php
	}

	/**
	 * Render the sites library.
	 */
	public function render_site_library() {

		$this->enqueue();
		?>
		<div class="ti-sites-lib__wrap">
			<div id="ti-sites-library">
				<app></app>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue script and styles.
	 */
	public function enqueue() {

		wp_register_script( 'themeisle-site-lib', Themeisle_Onboarding::get_dir() . '/assets/js/bundle.js', array(), Themeisle_Onboarding::VERSION, true );

		wp_localize_script( 'themeisle-site-lib', 'themeisleSitesLibApi', $this->localize_sites_library() );

		wp_enqueue_script( 'themeisle-site-lib' );

		wp_enqueue_style( 'themeisle-site-lib', Themeisle_Onboarding::get_dir() . '/assets/css/style.css', array(), Themeisle_Onboarding::VERSION );
	}

	/**
	 * Localize the sites library.
	 *
	 * @return array
	 */
	private function localize_sites_library() {

		$theme = wp_get_theme();

		$api = array(
			'root'            => esc_url_raw( rest_url( Themeisle_Onboarding::API_ROOT ) ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'homeUrl'         => esc_url( home_url() ),
			'i18ln'           => $this->get_strings(),
			'onboarding'      => 'no',
			'contentImported' => $this->escape_bool_text( get_theme_mod( 'ti_content_imported', 'no' ) ),
			'aboutUrl'        => esc_url( admin_url( 'themes.php?page=' . $theme->__get( 'stylesheet' ) . '-welcome' ) ),
		);

		$is_onboarding = isset( $_GET['onboarding'] ) && $_GET['onboarding'] === 'yes';
		if ( $is_onboarding ) {
			$api['onboarding'] = 'yes';
		}

		return $api;
	}

	/**
	 * Get strings.
	 *
	 * @return array
	 */
	private function get_strings() {
		return array(
			'preview_btn'       => __( 'Preview', 'textdomain' ),
			'import_btn'        => __( 'Import', 'textdomain' ),
			'pro_btn'           => __( 'Get the PRO version!', 'textdomain' ),
			'importing'         => __( 'Importing', 'textdomain' ),
			'cancel_btn'        => __( 'Cancel', 'textdomain' ),
			'loading'           => __( 'Loading', 'textdomain' ),
			'go_to_site'        => __( 'View Website', 'textdomain' ),
			'edit_template'     => __( 'Add your own content', 'textdomain' ),
			'back'              => __( 'Back to Sites Library', 'textdomain' ),
			'note'              => __( 'Note', 'textdomain' ),
			'advanced_options'  => __( 'Advanced Options', 'textdomain' ),
			'plugins'           => __( 'Plugins', 'textdomain' ),
			'general'           => __( 'General', 'textdomain' ),
			'later'             => __( 'Keep current layout', 'textdomain' ),
			'search'			=> __( 'Search', 'textdomain' ),
			'content'           => __( 'Content', 'textdomain' ),
			'customizer'        => __( 'Customizer', 'textdomain' ),
			'widgets'           => __( 'Widgets', 'textdomain' ),
			'import_steps'      => array(
				'plugins'    => __( 'Installing Plugins', 'textdomain' ),
				'content'    => __( 'Importing Content', 'textdomain' ),
				'theme_mods' => __( 'Setting Up Customizer', 'textdomain' ),
				'widgets'    => __( 'Importing Widgets', 'textdomain' ),
			),
			'import_disclaimer' => __( 'We recommend you backup your website content before attempting a full site import.', 'textdomain' ),
			'import_done'       => __( 'Content was successfully imported. Enjoy your new site!', 'textdomain' ),
			'pro_demo'          => __( 'Available in the PRO version', 'textdomain' ),
		);
	}

	/**
	 * Escape settings that return 'yes', 'no'.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	private function escape_bool_text( $value ) {
		$allowed = array( 'yes', 'no' );

		if ( ! in_array( $value, $allowed ) ) {
			return 'no';
		}

		return esc_html( $value );
	}
}
