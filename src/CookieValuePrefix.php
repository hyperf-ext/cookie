<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
