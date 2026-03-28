<?php
/**
 * RateLimiter unit tests.
 *
 * @package BricksMCP
 * @license GPL-2.0-or-later
 */

declare(strict_types=1);

namespace BricksMCP\Tests\Unit\MCP;

use PHPUnit\Framework\TestCase;
use BricksMCP\MCP\RateLimiter;

/**
 * Fake in-process object cache used by stubs below.
 *
 * @var array<string, int>
 */
$GLOBALS['_bricks_mcp_test_cache'] = [];

/**
 * Fake bricks_mcp_settings option value, overridable per test.
 *
 * @var array<string, mixed>
 */
$GLOBALS['_bricks_mcp_test_settings'] = [];

/**
 * Controls which code path is exercised: true = persistent cache, false = transient fallback.
 *
 * @var bool
 */
$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;

/**
 * Fake transient store for the transient fallback path.
 *
 * @var array<string, mixed>
 */
$GLOBALS['_bricks_mcp_test_transients'] = [];

// ---------------------------------------------------------------------------
// WordPress function stubs — only defined when WordPress is NOT loaded.
// ---------------------------------------------------------------------------

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Stub for get_option().
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function get_option( string $option, mixed $default = false ): mixed {
		if ( 'bricks_mcp_settings' === $option ) {
			return $GLOBALS['_bricks_mcp_test_settings'];
		}
		return $default;
	}
}

if ( ! function_exists( 'wp_cache_add' ) ) {
	/**
	 * Stub for wp_cache_add() — sets the value only when the key does not exist.
	 *
	 * @param string $key    Cache key.
	 * @param mixed  $data   Value.
	 * @param string $group  Cache group (ignored in stub).
	 * @param int    $expire TTL in seconds (ignored in stub).
	 * @return bool True on success (key did not exist), false otherwise.
	 */
	function wp_cache_add( string $key, mixed $data, string $group = '', int $expire = 0 ): bool {
		$full_key = $group . ':' . $key;
		if ( array_key_exists( $full_key, $GLOBALS['_bricks_mcp_test_cache'] ) ) {
			return false;
		}
		$GLOBALS['_bricks_mcp_test_cache'][ $full_key ] = $data;
		return true;
	}
}

if ( ! function_exists( 'wp_cache_incr' ) ) {
	/**
	 * Stub for wp_cache_incr() — increments the cached integer.
	 *
	 * @param string $key    Cache key.
	 * @param int    $offset Increment amount.
	 * @param string $group  Cache group (ignored in stub).
	 * @return int|false New value, or false if key does not exist.
	 */
	function wp_cache_incr( string $key, int $offset = 1, string $group = '' ): int|false {
		$full_key = $group . ':' . $key;
		if ( ! array_key_exists( $full_key, $GLOBALS['_bricks_mcp_test_cache'] ) ) {
			return false;
		}
		$GLOBALS['_bricks_mcp_test_cache'][ $full_key ] += $offset;
		return $GLOBALS['_bricks_mcp_test_cache'][ $full_key ];
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Stub for is_wp_error().
	 *
	 * @param mixed $thing Value to check.
	 * @return bool
	 */
	function is_wp_error( mixed $thing ): bool {
		return $thing instanceof \WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal WP_Error stub.
	 */
	class WP_Error {
		/** @var string */
		public string $code;
		/** @var string */
		public string $message;
		/** @var mixed */
		public mixed $data;

		/**
		 * Constructor.
		 *
		 * @param string $code    Error code.
		 * @param string $message Error message.
		 * @param mixed  $data    Additional data.
		 */
		public function __construct( string $code = '', string $message = '', mixed $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		/**
		 * Get the error code.
		 *
		 * @return string
		 */
		public function get_error_code(): string {
			return $this->code;
		}

		/**
		 * Get additional data for the error.
		 *
		 * @param string $code Error code (unused in stub).
		 * @return mixed
		 */
		public function get_error_data( string $code = '' ): mixed {
			return $this->data;
		}
	}
}

if ( ! function_exists( 'header' ) ) {
	/**
	 * Stub for header() — silently discards headers in unit tests.
	 *
	 * @param string $header     The header string.
	 * @param bool   $replace    Whether to replace a previous similar header.
	 * @param int    $response_code Optional HTTP response code.
	 * @return void
	 */
	function header( string $header, bool $replace = true, int $response_code = 0 ): void {
		// No-op in unit tests.
	}
}

if ( ! function_exists( 'wp_using_ext_object_cache' ) ) {
	/**
	 * Stub for wp_using_ext_object_cache().
	 *
	 * @return bool
	 */
	function wp_using_ext_object_cache(): bool {
		return (bool) $GLOBALS['_bricks_mcp_test_ext_object_cache'];
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Stub for get_transient().
	 *
	 * @param string $transient Transient key.
	 * @return mixed False if not set, stored value otherwise.
	 */
	function get_transient( string $transient ): mixed {
		return $GLOBALS['_bricks_mcp_test_transients'][ $transient ] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Stub for set_transient().
	 *
	 * @param string $transient  Transient key.
	 * @param mixed  $value      Value to store.
	 * @param int    $expiration TTL in seconds (ignored in stub).
	 * @return bool
	 */
	function set_transient( string $transient, mixed $value, int $expiration = 0 ): bool {
		$GLOBALS['_bricks_mcp_test_transients'][ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Stub for delete_transient().
	 *
	 * @param string $transient Transient key.
	 * @return bool
	 */
	function delete_transient( string $transient ): bool {
		unset( $GLOBALS['_bricks_mcp_test_transients'][ $transient ] );
		return true;
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Stub for __() translation function.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain (ignored in stub).
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string {
		return $text;
	}
}

/**
 * Tests for the RateLimiter class.
 */
final class RateLimiterTest extends TestCase {

	/**
	 * Reset fake cache and settings before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_bricks_mcp_test_cache']             = [];
		$GLOBALS['_bricks_mcp_test_settings']          = [];
		$GLOBALS['_bricks_mcp_test_ext_object_cache']  = true;
		$GLOBALS['_bricks_mcp_test_transients']        = [];
	}

	/**
	 * Test 1: check() with an IP-based identifier returns true when under limit.
	 *
	 * @return void
	 */
	public function test_check_ip_identifier_under_limit_returns_true(): void {
		$result = RateLimiter::check( 'ip_192.168.1.1' );

		$this->assertTrue( $result );
	}

	/**
	 * Test 2: check() with a user-based identifier returns true when under limit.
	 *
	 * @return void
	 */
	public function test_check_user_identifier_under_limit_returns_true(): void {
		$result = RateLimiter::check( 'user_42' );

		$this->assertTrue( $result );
	}

	/**
	 * Test 3: check() returns WP_Error with status 429 after exceeding rate_limit_rpm.
	 *
	 * @return void
	 */
	public function test_check_exceeds_limit_returns_wp_error_429(): void {
		// Set a very low limit.
		$GLOBALS['_bricks_mcp_test_settings'] = [ 'rate_limit_rpm' => 2 ];

		// First two calls should succeed.
		$this->assertTrue( RateLimiter::check( 'ip_10.0.0.1' ) );
		$this->assertTrue( RateLimiter::check( 'ip_10.0.0.1' ) );

		// Third call exceeds the limit.
		$result = RateLimiter::check( 'ip_10.0.0.1' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'bricks_mcp_rate_limit', $result->get_error_code() );
		$this->assertEquals( [ 'status' => 429 ], $result->get_error_data() );
	}

	/**
	 * Test 4: Different identifiers maintain independent counters.
	 *
	 * Hitting the limit for 'ip_X' must not affect 'user_Y'.
	 *
	 * @return void
	 */
	public function test_different_identifiers_have_independent_counters(): void {
		// Set a very low limit.
		$GLOBALS['_bricks_mcp_test_settings'] = [ 'rate_limit_rpm' => 1 ];

		// Exhaust ip_X's quota.
		RateLimiter::check( 'ip_1.2.3.4' );
		$ip_result = RateLimiter::check( 'ip_1.2.3.4' );
		$this->assertInstanceOf( \WP_Error::class, $ip_result, 'ip_1.2.3.4 should be rate-limited' );

		// user_Y's first request should still be allowed.
		$user_result = RateLimiter::check( 'user_99' );
		$this->assertTrue( $user_result, 'user_99 should not be affected by ip_1.2.3.4 limit' );
	}

	/**
	 * Test 5: Transient path — check() returns true when under limit.
	 *
	 * @return void
	 */
	public function test_transient_path_under_limit_returns_true(): void {
		$GLOBALS['_bricks_mcp_test_ext_object_cache'] = false;

		$result = RateLimiter::check( 'user_10' );

		$this->assertTrue( $result );
		$this->assertArrayHasKey( 'bricks_mcp_rl_user_10', $GLOBALS['_bricks_mcp_test_transients'] );
		$this->assertEquals( 1, $GLOBALS['_bricks_mcp_test_transients']['bricks_mcp_rl_user_10'] );
	}

	/**
	 * Test 6: Transient path — check() returns WP_Error 429 after exceeding limit.
	 *
	 * @return void
	 */
	public function test_transient_path_exceeds_limit_returns_wp_error_429(): void {
		$GLOBALS['_bricks_mcp_test_ext_object_cache'] = false;
		$GLOBALS['_bricks_mcp_test_settings']         = [ 'rate_limit_rpm' => 2 ];

		// First two calls should succeed.
		$this->assertTrue( RateLimiter::check( 'user_20' ) );
		$this->assertTrue( RateLimiter::check( 'user_20' ) );

		// Third call exceeds the limit.
		$result = RateLimiter::check( 'user_20' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'bricks_mcp_rate_limit', $result->get_error_code() );
		$this->assertEquals( [ 'status' => 429 ], $result->get_error_data() );
	}

	/**
	 * Test 7: Transient path — different identifiers maintain independent counters.
	 *
	 * @return void
	 */
	public function test_transient_path_independent_counters(): void {
		$GLOBALS['_bricks_mcp_test_ext_object_cache'] = false;
		$GLOBALS['_bricks_mcp_test_settings']         = [ 'rate_limit_rpm' => 1 ];

		// Exhaust identifier A's quota.
		RateLimiter::check( 'user_30' );
		$result_a = RateLimiter::check( 'user_30' );
		$this->assertInstanceOf( \WP_Error::class, $result_a, 'user_30 should be rate-limited' );

		// Identifier B's first request should still succeed.
		$result_b = RateLimiter::check( 'user_31' );
		$this->assertTrue( $result_b, 'user_31 should not be affected by user_30 limit' );
	}

	/**
	 * Test 8: Persistent cache path — transients are NOT written when ext object cache is active.
	 *
	 * @return void
	 */
	public function test_persistent_cache_path_does_not_write_transients(): void {
		$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;

		RateLimiter::check( 'user_50' );

		$this->assertEmpty(
			$GLOBALS['_bricks_mcp_test_transients'],
			'Transients must not be written when persistent object cache is active'
		);
	}
}
