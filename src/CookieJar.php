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

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Utils\Arr;
use Hyperf\Utils\InteractsWithTime;
use Hyperf\Macroable\Macroable;
use HyperfExt\Cookie\Contract\CookieJarInterface;

class CookieJar implements CookieJarInterface
{
    use InteractsWithTime;
    use Macroable;

    /**
     * The default path (if specified).
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The default domain (if specified).
     *
     * @var string
     */
    protected $domain = '';

    /**
     * The default secure setting (defaults to null).
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * The default SameSite option (defaults to lax).
     *
     * @var string
     */
    protected $sameSite = 'lax';

    /**
     * All of the cookies queued for sending.
     *
     * @var \Hyperf\HttpMessage\Cookie\Cookie[]
     */
    protected $queued = [];

    /**
     * Create a new cookie instance.
     */
    public function make(string $name, string $value, int $minutes = 0, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null): Cookie
    {
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);

        $time = ($minutes == 0) ? 0 : $this->availableAt($minutes * 60);

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Create a cookie that lasts "forever" (five years).
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    public function forever(string $name, string $value, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Expire the given cookie.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    public function forget(string $name, ?string $path = null, ?string $domain = null)
    {
        return $this->make($name, '', -2628000, $path, $domain);
    }

    /**
     * Determine if a cookie has been queued.
     */
    public function hasQueued(string $key, ?string $path = null): bool
    {
        return ! is_null($this->queued($key, null, $path));
    }

    /**
     * Get a queued cookie instance.
     *
     * @param mixed $default
     */
    public function queued(string $key, $default = null, ?string $path = null): ?Cookie
    {
        $queued = Arr::get($this->queued, $key, $default);

        if ($path === null) {
            return Arr::last((array) $queued, null, $default);
        }

        return Arr::get($queued, $path, $default);
    }

    /**
     * Queue a cookie to send with the next response.
     */
    public function queue(Cookie $cookie): void
    {
        if (! isset($this->queued[$cookie->getName()])) {
            $this->queued[$cookie->getName()] = [];
        }

        $this->queued[$cookie->getName()][$cookie->getPath()] = $cookie;
    }

    /**
     * Remove a cookie from the queue.
     */
    public function unqueue(string $name, ?string $path = null): void
    {
        if ($path === null) {
            unset($this->queued[$name]);

            return;
        }

        unset($this->queued[$name][$path]);

        if (empty($this->queued[$name])) {
            unset($this->queued[$name]);
        }
    }

    /**
     * Set the default path and domain for the jar.
     *
     * @return $this
     */
    public function setDefaultPathAndDomain(string $path, string $domain, bool $secure = false, ?string $sameSite = null)
    {
        [$this->path, $this->domain, $this->secure, $this->sameSite] = [$path, $domain, $secure, $sameSite];

        return $this;
    }

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie[]
     */
    public function getQueuedCookies(): array
    {
        return Arr::flatten($this->queued);
    }

    /**
     * Get the path and domain, or the default values.
     *
     * @return array
     */
    public function getPathAndDomain(?string $path = null, ?string $domain = null, ?bool $secure = null, ?string $sameSite = null)
    {
        return [$path ?: $this->path, $domain ?: $this->domain, is_bool($secure) ? $secure : $this->secure, $sameSite ?: $this->sameSite];
    }
}
