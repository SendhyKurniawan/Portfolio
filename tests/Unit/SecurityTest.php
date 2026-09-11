<?php

use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        putenv('ADMIN_USERNAME');
        putenv('ADMIN_PASSWORD_HASH');
    }

    public function testEscapeHandlesQuotesTagsAndNull(): void
    {
        $this->assertSame('&lt;a href=&quot;x&quot;&gt;it&#039;s&lt;/a&gt;', e('<a href="x">it\'s</a>'));
        $this->assertSame('', e(null));
    }

    public function testEscapeSubstitutesInvalidUtf8InsteadOfReturningEmpty(): void
    {
        $this->assertNotSame('', e("bad \xC3\x28 byte"));
    }

    public function testCsrfTokenIsStableWithinSession(): void
    {
        $first = csrf_token();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
        $this->assertSame($first, csrf_token());
    }

    public function testCsrfFieldEmbedsCurrentToken(): void
    {
        $field = csrf_field();

        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="' . csrf_token() . '"', $field);
    }

    public function testCsrfValidAcceptsOnlyTheSessionToken(): void
    {
        $token = csrf_token();

        $this->assertTrue(csrf_valid($token));
        $this->assertFalse(csrf_valid('forged'));
        $this->assertFalse(csrf_valid(''));
    }

    public function testCsrfValidFailsWhenSessionHasNoToken(): void
    {
        $this->assertFalse(csrf_valid(''));
        $this->assertFalse(csrf_valid('anything'));
    }

    public function testAdminCredentialsCheckUsernameAndHash(): void
    {
        putenv('ADMIN_USERNAME=admin');
        putenv('ADMIN_PASSWORD_HASH=' . password_hash('s3cret', PASSWORD_DEFAULT));

        $this->assertTrue(admin_credentials_valid('admin', 's3cret'));
        $this->assertFalse(admin_credentials_valid('admin', 'admin123'));
        $this->assertFalse(admin_credentials_valid('Admin', 's3cret'));
        $this->assertTrue(admin_password_valid('s3cret'));
    }

    public function testAdminLoginIsDisabledWhenEnvIsMissing(): void
    {
        putenv('ADMIN_PASSWORD_HASH=' . password_hash('s3cret', PASSWORD_DEFAULT));
        $this->assertFalse(admin_credentials_valid('admin', 's3cret'), 'no username configured');

        putenv('ADMIN_USERNAME=admin');
        putenv('ADMIN_PASSWORD_HASH');
        $this->assertFalse(admin_credentials_valid('admin', ''), 'no hash configured');
    }

    public function testIsAdminRequiresExactTrueFlag(): void
    {
        $this->assertFalse(is_admin());
        $_SESSION['admin_logged_in'] = 'yes';
        $this->assertFalse(is_admin());
        $_SESSION['admin_logged_in'] = true;
        $this->assertTrue(is_admin());
    }

    public function testFlashIsReadOnce(): void
    {
        flash_set('error', 'Oops', ['name' => 'Ann']);

        $this->assertSame(['type' => 'error', 'message' => 'Oops', 'old' => ['name' => 'Ann']], flash_take());
        $this->assertNull(flash_take());
    }
}
