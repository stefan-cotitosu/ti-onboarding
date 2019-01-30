<?php
/**
 * `loading` test.
 *
 * @package themeisle-onboarding
 */

/**
 * Test onboarding loading.
 */
class Onboarding_Rest_Test extends WP_UnitTestCase {
	public static $admin_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id = $factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function setUp() {
		parent::setUp();
		wp_set_current_user( self::$admin_id );
		add_theme_support( 'themeisle-demo-import', array(
			'editors' => array(
				'elementor'
			),
			'local'   => array(
				'elementor' => array(
					'neve-main' => array(
						'url'   => 'https://demo.themeisle.com/neve',
						'title' => 'Neve 2018',
					),
				),
			),
			'i18n'    => array(
				'templates_title'       => __( 'Ready to use pre-built websites with 1-click installation', 'neve' ),
				'templates_description' => __( 'With Neve, you can choose from multiple unique demos, specially designed for you, that can be installed with a single click. You just need to choose your favorite, and we will take care of everything else.', 'neve' ),
			),
		) );
		tests_add_filter( 'template_directory', function () {
			return dirname( __FILE__ ) . '/sample-theme';
		} );
		Themeisle_Onboarding::instance();
	}

	public function test_theme_support_loading() {
		$api = new Themeisle_OB_Rest_Server();
		$api->init();
		$templates = $api->init_library();

		$this->assertNotEmpty( $templates );
		$this->assertArrayHasKey( 'local', $templates );

		$this->assertNotEmpty( $templates['local'] );
	}

	/**
	 * @covers Themeisle_OB_Theme_Mods_Importer::import_theme_mods
	 *
	 */
	public function test_theme_mods_loading() {

		$api = new Themeisle_OB_Rest_Server();
		$api->init();
		$request = new WP_REST_Request();
		$json    = json_decode( file_get_contents( dirname( __FILE__ ) . '/sample-theme/onboarding/neve-main/data.json' ), true );

		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( array(
			'data' => array(
				'theme_mods' => $json['theme_mods'],
				'source_url' => 'https://demo.themeisle.com/neve-charity'
			)
		) ) );

		//ob_start();
		$response = $api->run_theme_mods_importer( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( $response->get_status(), 200 );
		$this->assertTrue( $response->get_data()['success'] );

	}

}
