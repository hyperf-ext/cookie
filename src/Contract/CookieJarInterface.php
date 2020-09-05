<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/cookie.
 *
 * @link     https://github.com/hyperf-ext/cookie
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/cookie/blob/master/LICENSE
 */
namespace HyperfExt\Cookie\Contract;

use Hyperf\HttpMessage\Cookie\Cookie;

interface CookieJarInterface
{
    /**
     * Create a new cookie instance.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    public function make(string $name, string $value, int $minutes = 0, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null);

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    public function forever(string $name, string $value, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null);

    /**
     * Expire the given cookie.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    public function forget(string $name, ?string $path = null, ?string $domain = null);

    /**
     * Queue a cookie to send with the next response.
     */
    public function queue(Cookie $cookie): void;

    /**
     * Remove a cookie from the queue.
     */
    public function unqueue(string $name, ?string $path = null): void;

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie[]
     */
    public function getQueuedCookies(): array;
}
