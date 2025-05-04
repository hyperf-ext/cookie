<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/cookie.
 *
 * @link     https://github.com/hyperf-ext/cookie
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/cookie/blob/master/LICENSE
 */
namespace HyperfExt\Cookie\Middleware;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Collection\Arr;
use HyperfExt\Cookie\CookieValuePrefix;
use HyperfExt\Encryption\Contract\EncryptionInterface;
use HyperfExt\Encryption\Contract\SymmetricDriverInterface;
use HyperfExt\Encryption\Exception\DecryptException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EncryptCookieMiddleware
{
    /**
     * The encrypter instance.
     *
     * @var \HyperfExt\Encryption\Contract\AsymmetricDriverInterface|\HyperfExt\Encryption\Contract\SymmetricDriverInterface
     */
    protected $encrypter;

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Indicates if cookies should be serialized.
     *
     * @var bool
     */
    protected static $serialize = false;

    /**
     * Create a new CookieGuard instance.
     */
    public function __construct(EncryptionInterface $encrypter)
    {
        $this->encrypter = $encrypter->getDriver();
    }

    /**
     * Disable encryption for the given cookie name(s).
     *
     * @param array|string $name
     */
    public function disableFor($name)
    {
        $this->except = array_merge($this->except, (array) $name);
    }

    /**
     * Handle an incoming request.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->encrypt($handler->handle($this->decrypt($request)));
    }

    /**
     * Determine whether encryption has been disabled for the given cookie.
     */
    public function isDisabled(string $name): bool
    {
        return in_array($name, $this->except);
    }

    /**
     * Determine if the cookie contents should be serialized.
     */
    public static function serialized(string $name): bool
    {
        return static::$serialize;
    }

    /**
     * Decrypt the cookies on the request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function decrypt(ServerRequestInterface $request)
    {
        $cookies = [];

        foreach ($request->getCookieParams() as $key => $cookie) {
            if ($this->isDisabled($key)) {
                continue;
            }

            try {
                $value = $this->decryptCookie($key, $cookie);

                $hasValidPrefix = strpos($value, CookieValuePrefix::create(
                    $key,
                    $this->encrypter instanceof SymmetricDriverInterface
                        ? $this->encrypter->getKey()
                        : $this->encrypter->getPublicKey()
                )) === 0;

                $cookies[$key] = $hasValidPrefix ? CookieValuePrefix::remove($value) : null;
            } catch (DecryptException $e) {
                $cookies[$key] = null;
            }
        }

        return $request->withCookieParams($cookies);
    }

    /**
     * Decrypt the given cookie and return the value.
     *
     * @param array|string $cookie
     *
     * @return array|string
     */
    protected function decryptCookie(string $name, $cookie)
    {
        return is_array($cookie)
            ? $this->decryptArray($cookie)
            : $this->encrypter->decrypt($cookie, static::serialized($name));
    }

    /**
     * Decrypt an array based cookie.
     *
     * @return array
     */
    protected function decryptArray(array $cookie)
    {
        $decrypted = [];

        foreach ($cookie as $key => $value) {
            if (is_string($value)) {
                $decrypted[$key] = $this->encrypter->decrypt($value, static::serialized($key));
            }
        }

        return $decrypted;
    }

    /**
     * Encrypt the cookies on an outgoing response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function encrypt(ResponseInterface $response)
    {
        $cookies = Arr::flatten($response->getCookies());
        foreach ($cookies as $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }

            $response = $response->withCookie($this->duplicate(
                $cookie,
                $this->encrypter->encrypt(
                    CookieValuePrefix::create(
                        $cookie->getName(),
                        $this->encrypter instanceof SymmetricDriverInterface
                            ? $this->encrypter->getKey()
                            : $this->encrypter->getPublicKey()
                    ) . $cookie->getValue(),
                    static::serialized($cookie->getName())
                )
            ));
        }

        return $response;
    }

    /**
     * Duplicate a cookie with a new value.
     *
     * @return \Hyperf\HttpMessage\Cookie\Cookie
     */
    protected function duplicate(Cookie $cookie, string $value)
    {
        return new Cookie(
            $cookie->getName(),
            $value,
            $cookie->getExpiresTime(),
            $cookie->getPath(),
            $cookie->getDomain(),
            $cookie->isSecure(),
            $cookie->isHttpOnly(),
            $cookie->isRaw(),
            $cookie->getSameSite()
        );
    }
}
