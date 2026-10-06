<?php
/**
 * Integrate the shared deckerweb updater with plugin-scoped safeguards.
 *
 * @package ConnectForShopware
 */

namespace Deckerweb\Shopware;

defined( 'ABSPATH' ) || exit;

/**
 * Adapt the shared updater with local artwork and package identity checks.
 */
final class GitHubUpdates {
	/** Public release repository; never taken from user input. */
	private const REPOSITORY = 'https://github.com/deckerweb/connect-for-shopware';

	/**
	 * Register the scoped routes or WordPress hooks for this component.
	 *
	 * @since 1.0.0
	 * @return void No return value; effects are described above.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'boot' ) );
	}

	/**
	 * Register the WordPress integration callbacks for this component.
	 *
	 * @since 1.0.0
	 * @return void No return value; effects are described above.
	 */
	public function boot(): void {
		if ( ! class_exists( '\Deckerweb\GitHubReleaseUpdater\V2\Updater' ) ) {
			require_once DW_SW_DIR . 'includes/deckerweb-github-release-updater-v2.php';
		}
		if ( ! defined( '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater::SUPPORTS_HOST_TRANSLATIONS' ) ) {
            add_action('admin_notices',static function(): void {
                $screen=function_exists('get_current_screen')?get_current_screen():null;
                if(current_user_can('update_plugins')&&$screen&&$screen->id==='settings_page_dw-sw')echo '<div class="notice notice-info"><p>'.esc_html__('Update the other active deckerweb plugins to use translated shared update messages.','connect-for-shopware').'</p></div>';
            });
        }
		try {
			$updater = new \Deckerweb\GitHubReleaseUpdater\V2\Updater(
				DW_SW_FILE,
				self::REPOSITORY,
				'Connect for Shopware',
				__( 'Read-only Shopware products for Gutenberg and native Bricks elements.', 'connect-for-shopware' ),
				$this->artwork(),
				defined( '\\Deckerweb\\GitHubReleaseUpdater\\V2\\Updater::SUPPORTS_HOST_TRANSLATIONS' ) ? [ 'translate' => require DW_SW_DIR . 'includes/updater-translations.php' ] : []
			);
		} catch ( \InvalidArgumentException $error ) {
			// An unsupported installation directory must not break product rendering.
			return;
		}
		$updater->register();
		add_filter( 'http_request_args', array( $this, 'request_limits' ), 20, 2 );
		add_filter( 'upgrader_source_selection', array( $this, 'validate_source' ), 30, 4 );
	}

	/**
	 * Return local translated icons and banners with versioned asset URLs.
	 *
	 * @since 1.0.0
	 * @return array Normalized result data for the documented operation.
	 */
	public function artwork(): array {
		$language = str_starts_with(determine_locale(), 'de') ? 'de-' : '';
		$asset = static fn( string $path ): string => add_query_arg( 'ver', DW_SW_VERSION, plugins_url( $path, DW_SW_FILE ) );
		return array(
			'icons'   => array(
				'svg' => $asset( 'assets/brand/icon.svg' ),
				'1x'  => $asset( 'assets/brand/icon-128x128.png' ),
				'2x'  => $asset( 'assets/brand/icon-256x256.png' ),
			),
			'banners' => array(
				'low'  => $asset( 'assets-github/banner-' . $language . '772x250.png' ),
				'high' => $asset( 'assets-github/banner-' . $language . '1544x500.png' ),
			),
		);
	}

	/**
	 * Apply HTTP limits only to this repository metadata endpoint.
	 *
	 * @since 1.0.0
	 * @param array $args WordPress HTTP request arguments.
	 * @param string $url Public HTTPS endpoint or repository metadata URL.
	 * @return array Normalized result data for the documented operation.
	 */
	public function request_limits( array $args, string $url ): array {
		if ( 'https://api.github.com/repos/deckerweb/connect-for-shopware/releases/latest' === $url ) {
			$args['limit_response_size'] = 512 * 1024;
			$args['timeout']             = 6;
			$args['redirection']         = 0;
			$args['sslverify']           = true;
			$args['reject_unsafe_urls']  = true;
		}
		return $args;
	}

	/**
	 * Validate package identity, offered version and platform requirements before replacement.
	 *
	 * @since 1.0.0
	 * @param string|\WP_Error $source Extracted package directory or an existing WordPress error.
	 * @param string $remote_source Core extraction root, retained for the filter contract.
	 * @param object|null $upgrader WordPress upgrader instance used to validate update context.
	 * @param array $hook_extra Core plugin/update context identifying the intended target.
	 * @return string|\WP_Error Checked package directory, or a localized WordPress error.
	 */
	public function validate_source( $source, $remote_source, $upgrader, array $hook_extra ) {
		if ( ( $hook_extra['plugin'] ?? '' ) !== plugin_basename( DW_SW_FILE ) || ( $hook_extra['type'] ?? '' ) !== 'plugin' || ( $hook_extra['action'] ?? '' ) !== 'update' ) {
			return $source;
		}
		if ( is_wp_error( $source ) ) {
			$messages = array(
				'ddw_ghru_filesystem' => __( 'The update filesystem is unavailable. Please try again.', 'connect-for-shopware' ),
				'ddw_ghru_archive'    => __( 'The GitHub package does not contain the Connect for Shopware plugin file.', 'connect-for-shopware' ),
				'ddw_ghru_rename'     => __( 'The GitHub package could not be prepared. The installed version has been kept.', 'connect-for-shopware' ),
			);
			$code     = $source->get_error_code();
			return isset( $messages[ $code ] ) ? new \WP_Error( $code, $messages[ $code ], $source->get_error_data( $code ) ) : $source;
		}
		global $wp_filesystem;
		if ( ! is_string( $source ) || ! $wp_filesystem ) {
			return new \WP_Error( 'dw_sw_update_source', __( 'The update package could not be checked.', 'connect-for-shopware' ) );
		}
		$main = trailingslashit( $source ) . basename( DW_SW_FILE );
		if ( ! $wp_filesystem->is_file( $main ) || $wp_filesystem->size( $main ) > 1024 * 1024 ) {
			return new \WP_Error( 'dw_sw_update_source', __( 'The update package could not be checked.', 'connect-for-shopware' ) );
		}
		$text = $wp_filesystem->get_contents( $main );
		if ( ! is_string( $text ) ) {
			return new \WP_Error( 'dw_sw_update_source', __( 'The update package could not be checked.', 'connect-for-shopware' ) );
		}
		$headers = array();
		foreach ( array( 'Plugin Name', 'Version', 'Update URI', 'Requires PHP', 'Requires at least' ) as $header ) {
			$headers[ $header ] = preg_match( '/^[ \t\/*#@]*' . preg_quote( $header, '/' ) . ':(.*)$/mi', str_replace( "\r", "\n", substr( $text, 0, 8192 ) ), $match ) ? trim( $match[1] ) : '';
		}
		if ( 'Connect for Shopware' !== $headers['Plugin Name'] || self::REPOSITORY !== $headers['Update URI'] || ! preg_match( '/^\d+\.\d+\.\d+$/D', $headers['Version'] ) || version_compare( $headers['Version'], DW_SW_VERSION, '<=' ) ) {
			return new \WP_Error( 'dw_sw_update_identity', __( 'The package identity or version does not match a newer Connect for Shopware release.', 'connect-for-shopware' ) );
		}
		$current  = get_site_transient( 'update_plugins' );
		$expected = is_object( $current ) ? ( $current->response[ plugin_basename( DW_SW_FILE ) ]->new_version ?? '' ) : '';
		if ( ! is_string( $expected ) || '' === $expected || $headers['Version'] !== $expected ) {
			return new \WP_Error( 'dw_sw_update_version', __( 'The package version differs from the offered update. Please check for updates again.', 'connect-for-shopware' ) );
		}
		foreach ( array( 'Requires PHP', 'Requires at least' ) as $header ) {
			if ( ! preg_match( '/^\d+\.\d+(?:\.\d+)?$/D', $headers[ $header ] ) ) {
				return new \WP_Error( 'dw_sw_update_requirements', __( 'The update package has missing or invalid WordPress/PHP requirements.', 'connect-for-shopware' ) );
			}
		}
		if ( ! is_php_version_compatible( $headers['Requires PHP'] ) || ! is_wp_version_compatible( $headers['Requires at least'] ) ) {
			return new \WP_Error( 'dw_sw_update_compatibility', __( 'This release requires a newer WordPress or PHP version. The installed version has been kept.', 'connect-for-shopware' ) );
		}
		return $source;
	}
}
