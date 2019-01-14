<?php
/**
 * Elementor Meta import handler.
 *
 * This is needed because by default, the importer breaks our JSON meta.
 *
 * @package    themeisle-onboarding
 * @soundtrack All Apologies (Live) - Nirvana
 */

/**
 * Class Themeisle_OB_Elementor_Meta_Handler
 *
 * @package themeisle-onboarding
 */
class Themeisle_OB_Elementor_Meta_Handler {
	/**
	 * Elementor meta key.
	 *
	 * @var string
	 */
	private $meta_key = '_elementor_data';

	/**
	 * Meta value.
	 *
	 * @var null
	 */
	private $value = null;

	/**
	 * A list of allowed mimes.
	 *
	 * @var array
	 */
	protected $extensions = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
		'svg'          => 'image/svg+xml',
	);

	/**
	 * Current site url.
	 *
	 * @var |null
	 */
	private $site_url = null;

	/**
	 * Themeisle_OB_Elementor_Meta_Handler constructor.
	 *
	 * @param string $unfiltered_value the unfiltered meta value.
	 */
	public function __construct( $unfiltered_value ) {
		$this->value    = $unfiltered_value;

		$site_url       = get_site_url();
		$site_url       = parse_url( $site_url );
		$this->site_url = $site_url['host'];
	}

	/**
	 * Filter the meta to allow escaped JSON values.
	 */
	public function filter_meta() {
		add_filter( 'sanitize_post_meta_' . $this->meta_key, array( $this, 'allow_escaped_json_meta' ), 10, 3 );
	}

	/**
	 * Allow JSON escaping.
	 *
	 * @param string $val  meta value.
	 * @param string $key  meta key.
	 * @param string $type meta type.
	 *
	 * @return array|string
	 */
	public function allow_escaped_json_meta( $val, $key, $type ) {
		if ( empty( $this->value ) ) {
			return $val;
		}

		$this->replace_urls();

		return $this->value;
	}

	/**
	 * Replace demo urls in meta with site urls.
	 */
	private function replace_urls() {
		$old_urls = $this->get_urls_to_replace();
		$urls     = array_combine( $old_urls, $old_urls );
		$urls     = array_map( 'wp_unslash', $urls );

		$urls = array_map( function ( $url ) {
			$parsed   = parse_url( $url );
			$old_site = $parsed['host'];

			return str_replace( $old_site, $this->site_url, $url );
		}, $urls );

		$this->value = str_replace( array_keys( $urls ), array_values( $urls ), $this->value );
	}

	/**
	 * Get url replace array.
	 *
	 * @return array
	 */
	private function get_urls_to_replace() {
		$regex = '/(?:http(?:s?):)(?:[\/\\\\\\\\|.|\w|\s|-])*\.(?:' . implode( '|', array_keys( $this->extensions ) ) . ')/m';
		preg_match_all( $regex, $this->value, $urls );

		$urls = array_map( function ( $value ) {
			return rtrim( html_entity_decode( $value ), '\\' );
		}, $urls[0] );

		$urls = array_unique( $urls );

		return array_values( $urls );
	}
}
