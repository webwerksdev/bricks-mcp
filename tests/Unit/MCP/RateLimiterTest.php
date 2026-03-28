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

// ---------------------------------------------------------------------------
// WordPress function stubs — only defined when WordPress is NOT loaded.
// These allow the test suite to run with bootstrap-simple.php (no WP env).
// ---------------------------------------------------------------------------

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
 * Controls which code path is exercised when WordPress is NOT loaded.
 * When WordPress IS loaded, use wp_using_ext_object_cache() directly.
 *
 * @var bool
 */
$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;

/**
 * Fake transient store for the transient fallback path (no-WP stub only).
 *
 * @var array<string, mixed>
 */
$GLOBALS['_bricks_mcp_test_transients'] = [];

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

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Stub for update_option().
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Value.
	 * @return bool
	 */
	function update_option( string $option, mixed $value ): bool {
		if ( 'bricks_mcp_settings' === $option ) {
			$GLOBALS['_bricks_mcp_test_settings'] = $value;
		}
		return true;
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

if ( ! function_exists( 'wp_using_ext_object_cache' ) ) {
	/**
	 * Stub for wp_using_ext_object_cache().
	 *
	 * @param bool|null $using Optional new value to set.
	 * @return bool
	 */
	function wp_using_ext_object_cache( ?bool $using = null ): bool {
		if ( null !== $using ) {
			$GLOBALS['_bricks_mcp_test_ext_object_cache'] = $using;
		}
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
	 * @param string $header       The header string.
	 * @param bool   $replace      Whether to replace a previous similar header.
	 * @param int    $response_code Optional HTTP response code.
	 * @return void
	 */
	function header( string $header, bool $replace = true, int $response_code = 0 ): void {
		// No-op in unit tests.
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
	 * Whether WordPress is loaded (affects setUp/tearDown behavior).
	 *
	 * @var bool
	 */
	private bool $wp_loaded;

	/**
	 * Original ext object cache state (for restoration when WP is loaded).
	 *
	 * @var bool|null
	 */
	private ?bool $original_ext_cache_state = null;

	/**
	 * Unique identifier prefix to avoid transient collisions between tests.
	 *
	 * @var string
	 */
	private string $test_id;

	/**
	 * Reset state before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->wp_loaded = function_exists( 'wp_cache_flush' ) && defined( 'ABSPATH' ) && function_exists( 'get_option' ) && class_exists( 'wpdb' );
		$this->test_id   = uniqid( 'test_', true );

		if ( $this->wp_loaded ) {
			// Real WP environment: flush cache and store current ext cache state.
			$this->original_ext_cache_state = (bool) wp_using_ext_object_cache();
			wp_cache_flush();
			// Default to persistent cache path (mirrors production default for most tests).
			wp_using_ext_object_cache( true );
			update_option( 'bricks_mcp_settings', [] );
		} else {
			// Stub environment: reset fake stores.
			$GLOBALS['_bricks_mcp_test_cache']            = [];
			$GLOBALS['_bricks_mcp_test_settings']         = [];
			$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;
			$GLOBALS['_bricks_mcp_test_transients']       = [];
		}
	}

	/**
	 * Restore original state after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		parent::tearDown();

		if ( $this->wp_loaded ) {
			wp_using_ext_object_cache( $this->original_ext_cache_state ?? false );
			wp_cache_flush();
			// Delete any transients created during this test.
			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bricks_mcp_rl_%'" );
		} else {
			$GLOBALS['_bricks_mcp_test_cache']            = [];
			$GLOBALS['_bricks_mcp_test_settings']         = [];
			$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;
			$GLOBALS['_bricks_mcp_test_transients']       = [];
		}
	}

	/**
	 * Helper: set rate limit RPM (works in both WP and stub environments).
	 *
	 * @param int $rpm Requests per minute limit.
	 * @return void
	 */
	private function set_rate_limit( int $rpm ): void {
		if ( $this->wp_loaded ) {
			update_option( 'bricks_mcp_settings', [ 'rate_limit_rpm' => $rpm ] );
		} else {
			$GLOBALS['_bricks_mcp_test_settings'] = [ 'rate_limit_rpm' => $rpm ];
		}
	}

	/**
	 * Helper: force transient fallback path (works in both environments).
	 *
	 * @return void
	 */
	private function use_transient_path(): void {
		if ( $this->wp_loaded ) {
			wp_using_ext_object_cache( false );
		} else {
			$GLOBALS['_bricks_mcp_test_ext_object_cache'] = false;
		}
	}

	/**
	 * Helper: force persistent cache path (works in both environments).
	 *
	 * @return void
	 */
	private function use_cache_path(): void {
		if ( $this->wp_loaded ) {
			wp_using_ext_object_cache( true );
		} else {
			$GLOBALS['_bricks_mcp_test_ext_object_cache'] = true;
		}
	}

	/**
	 * Test 1: check() with an IP-based identifier returns true when under limit (persistent cache path).
	 *
	 * @return void
	 */
	public function test_check_ip_identifier_under_limit_returns_true(): void {
		$this->use_cache_path();

		$result = RateLimiter::check( 'ip_192.168.1.1_' . $this->test_id );

		$this->assertTrue( $result );
	}

	/**
	 * Test 2: check() with a user-based identifier returns true when under limit (persistent cache path).
	 *
	 * @return void
	 */
	public function test_check_user_identifier_under_limit_returns_true(): void {
		$this->use_cache_path();

		$result = RateLimiter::check( 'user_42_' . $this->test_id );

		$this->assertTrue( $result );
	}

	/**
	 * Test 3: check() returns WP_Error with status 429 after exceeding rate_limit_rpm (persistent cache path).
	 *
	 * @return void
	 */
	public function test_check_exceeds_limit_returns_wp_error_429(): void {
		$this->use_cache_path();
		$this->set_rate_limit( 2 );

		// First two calls should succeed.
		$this->assertTrue( RateLimiter::check( 'ip_10.0.0.1_' . $this->test_id ) );
		$this->assertTrue( RateLimiter::check( 'ip_10.0.0.1_' . $this->test_id ) );

		// Third call exceeds the limit.
		$result = RateLimiter::check( 'ip_10.0.0.1_' . $this->test_id );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'bricks_mcp_rate_limit', $result->get_error_code() );
		$this->assertEquals( [ 'status' => 429 ], $result->get_error_data() );
	}

	/**
	 * Test 4: Different identifiers maintain independent counters (persistent cache path).
	 *
	 * @return void
	 */
	public function test_different_identifiers_have_independent_counters(): void {
		$this->use_cache_path();
		$this->set_rate_limit( 1 );

		// Exhaust ip_X's quota.
		RateLimiter::check( 'ip_1.2.3.4_' . $this->test_id );
		$ip_result = RateLimiter::check( 'ip_1.2.3.4_' . $this->test_id );
		$this->assertInstanceOf( \WP_Error::class, $ip_result, 'ip_1.2.3.4 should be rate-limited' );

		// user_Y's first request should still be allowed.
		$user_result = RateLimiter::check( 'user_99_' . $this->test_id );
		$this->assertTrue( $user_result, 'user_99 should not be affected by ip_1.2.3.4 limit' );
	}

	/**
	 * Test 5: Transient path — check() returns true when under limit.
	 *
	 * @return void
	 */
	public function test_transient_path_under_limit_returns_true(): void {
		$this->use_transient_path();

		$identifier = 'user_10_' . $this->test_id;
		$result     = RateLimiter::check( $identifier );

		$this->assertTrue( $result );

		// Verify transient was written.
		$stored = get_transient( 'bricks_mcp_rl_' . $identifier );
		$this->assertEquals( 1, (int) $stored );
	}

	/**
	 * Test 6: Transient path — check() returns WP_Error 429 after exceeding limit.
	 *
	 * @return void
	 */
	public function test_transient_path_exceeds_limit_returns_wp_error_429(): void {
		$this->use_transient_path();
		$this->set_rate_limit( 2 );

		$identifier = 'user_20_' . $this->test_id;

		// First two calls should succeed.
		$this->assertTrue( RateLimiter::check( $identifier ) );
		$this->assertTrue( RateLimiter::check( $identifier ) );

		// Third call exceeds the limit.
		$result = RateLimiter::check( $identifier );

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
		$this->use_transient_path();
		$this->set_rate_limit( 1 );

		$id_a = 'user_30_' . $this->test_id;
		$id_b = 'user_31_' . $this->test_id;

		// Exhaust identifier A's quota.
		RateLimiter::check( $id_a );
		$result_a = RateLimiter::check( $id_a );
		$this->assertInstanceOf( \WP_Error::class, $result_a, 'user_30 should be rate-limited' );

		// Identifier B's first request should still succeed.
		$result_b = RateLimiter::check( $id_b );
		$this->assertTrue( $result_b, 'user_31 should not be affected by user_30 limit' );
	}

	/**
	 * Test 8: Persistent cache path — transients are NOT written when ext object cache is active.
	 *
	 * @return void
	 */
	public function test_persistent_cache_path_does_not_write_transients(): void {
		$this->use_cache_path();

		$identifier = 'user_50_' . $this->test_id;
		RateLimiter::check( $identifier );

		if ( $this->wp_loaded ) {
			// In real WP: verify no transient was stored for this identifier.
			$stored = get_transient( 'bricks_mcp_rl_' . $identifier );
			$this->assertFalse( $stored, 'Transient must not be written when persistent object cache is active' );
		} else {
			// In stub environment: verify fake transient store is empty.
			$this->assertEmpty(
				$GLOBALS['_bricks_mcp_test_transients'],
				'Transients must not be written when persistent object cache is active'
			);
		}
	}
}
