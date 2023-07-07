<?php
/**
 * Defines functions to support Tournamatch extensions.
 *
 * @link       https://www.tournamatch.com
 * @since      4.4.0
 *
 * @package    Tournamatch
 */

if ( ! function_exists( 'trn_check_for_plugin_update' ) ) {
	/**
	 * Evaluates Tournamatch.com for any available updates to plugins not hosted on
	 * WordPress.org.
	 *
	 * @since 4.4.0
	 *
	 * @param object $checked_data Plugin update information to store.
	 *
	 * @return object mixed Returns update information to store.
	 */
	function trn_check_for_plugin_update( $checked_data ) {
		global $wp_version;

		// Comment out these two lines during testing.
		if ( empty( $checked_data->checked ) ) {
			return $checked_data;
		}

		$license_status = trn_get_option( 'license_status', '' );

		if ( 'valid' === $license_status ) {
			$plugins = apply_filters( 'trn_filter_plugin_update_list', array() );

			if ( 0 === count( $plugins ) ) {
				return $checked_data;
			}

			$plugins = array_map(
				function( $slug ) use ( $checked_data ) {
					return array(
						'slug'    => $slug,
						'version' => $checked_data->checked[ $slug . '/' . $slug . '.php' ],
					);
				},
				$plugins
			);

			$arguments = array(
				'headers'    => trn_get_api_headers(),
				'timeout'    => 5,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				'body'       => wp_json_encode( $plugins ),
			);

			// Start checking for an update.
			$response      = wp_remote_post( trn_api_address( 'packages.php' ), $arguments );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( ! is_wp_error( $response ) && ( intval( $response_code ) === 200 ) ) {
				$response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( is_array( $response ) ) {
					// Feed the update data into WP updater.
					foreach ( $plugins as $plugin ) {
						if ( isset( $response[ $plugin['slug'] ] ) ) {
							$checked_data->response[ $plugin['slug'] . '/' . $plugin['slug'] . '.php' ] = (object) $response[ $plugin['slug'] ];
						}
					}
				}
			}
		}

		return $checked_data;
	}

	if ( defined( 'TOURNAMATCH_EXTENSIONS_ENABLED' ) ) {
		if ( true === TOURNAMATCH_EXTENSIONS_ENABLED ) {
			add_filter( 'pre_set_site_transient_update_plugins', 'trn_check_for_plugin_update' );
		}
	}
}

if ( ! function_exists( 'trn_plugin_api_call' ) ) {
	/**
	 * Evaluates Tournamatch.com for plugin information for plugins not hosted on WordPress.org.
	 *
	 * @since 4.4.0
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested from the plugin installation API.
	 * @param object             $args Plugin API arguments.
	 *
	 * @return mixed|WP_Error
	 */
	function trn_plugin_api_call( $result, $action, $args ) {
		global $api_url, $wp_version;

		$trn_plugins = apply_filters( 'trn_filter_plugin_update_list', array() );

		if ( ! isset( $args->slug ) || ( ! in_array( $args->slug, $trn_plugins, true ) ) ) {
			return $result;
		}

		// Get the current Tournamatch version.
		$plugin_info = get_site_transient( 'update_plugins' );
		$slug        = $args->slug . '/' . $args->slug . '.php';

		if ( isset( $plugin_info->response[ $slug ] ) ) {
			return $plugin_info->response[ $slug ];
		} else {
			$current_version = $plugin_info->checked[ $args->slug . '/' . $args->slug . '.php' ];
			$args->version   = $current_version;

			$request_string = array(
				'body'       => array(
					'action'  => $action,
					'request' => wp_json_encode( $args ),
					'api-key' => md5( get_bloginfo( 'url' ) ),
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			);

			$request = wp_remote_post( $api_url, $request_string );

			if ( is_wp_error( $request ) ) {
				$result = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
			} else {
				$result = json_decode( $request['body'] );

				if ( false === $result ) {
					$result = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred' ), $request['body'] );
				}
			}

			return $result;
		}
	}

	if ( defined( 'TOURNAMATCH_EXTENSIONS_ENABLED' ) ) {
		if ( true === TOURNAMATCH_EXTENSIONS_ENABLED ) {
			add_filter( 'plugins_api', 'trn_plugin_api_call', 10, 3 );
		}
	}
}

if ( ! function_exists( 'trn_get_api_headers' ) ) {
	/**
	 * Defines headers needed for Tournamatch REST API calls.
	 *
	 * @since 4.5.0
	 *
	 * @return array
	 */
	function trn_get_api_headers() {
		$license_key = trn_get_option( 'license_key', '' );
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		$headers = array(
			'Content-Type'    => 'application/json; charset=utf-8',
			'Accept'          => 'application/json; charset=utf-8',
			'Api-Version'     => TOURNAMATCH_API_VERSION,
			'Api-License-Key' => $license_key,
			'Api-Host'        => $http_host,
		);

		return $headers;
	}
}
