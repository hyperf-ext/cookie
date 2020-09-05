<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/cookie.
 *
 * @link     https://github.com/hyperf-ext/cookie
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/cookie/blob/master/LICENSE
 */
namespace HyperfExt\Cookie;

class CookieValuePrefix
{
    /**
     * Create a new cookie value prefix for the given cookie name.
     */
    public static function create(string $cookieName, string $key): string
    {
        return hash_hmac('sha1', $cookieName . 'v2', $key) . '|';
    }

    /**
     * Remove the cookie value prefix.
     */
    public static function remove(string $cookieValue): string
    {
        return substr($cookieValue, 41);
    }
}
