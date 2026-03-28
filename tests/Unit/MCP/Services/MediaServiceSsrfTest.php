<?php
/**
 * Unit test: SSRF fix in MediaService::sideload_from_url().
 *
 * @package BricksMCP\Tests\Unit\MCP\Services
 */

declare(strict_types=1);

namespace BricksMCP\Tests\Unit\MCP\Services;

use BricksMCP\MCP\Services\MediaService;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that sideload_from_url rejects non-HTTP schemes and internal IPs.
 */
class MediaServiceSsrfTest extends TestCase {

	protected function tearDown(): void {
		unset(
			$GLOBALS['_bricks_mcp_test_wp_http_validate_url_return'],
			$GLOBALS['_bricks_mcp_test_download_url_return']
		);
	}

	/**
	 * @dataProvider invalidSchemeProvider
	 */
	public function test_rejects_non_http_schemes( string $url ): void {
		$service = new MediaService();
		$result  = $service->sideload_from_url( $url );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_scheme', $result->get_error_code() );
	}

	public static function invalidSchemeProvider(): array {
		return [
			'file scheme'   => [ 'file:///etc/passwd' ],
			'ftp scheme'    => [ 'ftp://example.com/image.jpg' ],
			'gopher scheme' => [ 'gopher://evil.com/' ],
			'no scheme'     => [ '//example.com/image.jpg' ],
		];
	}

	public function test_rejects_internal_ip_urls(): void {
		$GLOBALS['_bricks_mcp_test_wp_http_validate_url_return'] = false;

		$service = new MediaService();
		$result  = $service->sideload_from_url( 'http://169.254.169.254/latest/meta-data/' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_url', $result->get_error_code() );
	}

	public function test_rejects_localhost_url(): void {
		$GLOBALS['_bricks_mcp_test_wp_http_validate_url_return'] = false;

		$service = new MediaService();
		$result  = $service->sideload_from_url( 'http://127.0.0.1/admin' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_url', $result->get_error_code() );
	}
}
