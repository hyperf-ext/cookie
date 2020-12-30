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

use HyperfExt\Cookie\Contract\CookieJarInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                CookieJarInterface::class => CookieJarProxy::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for hyperf-ext/cookie.',
                    'source' => __DIR__ . '/../publish/cookie.php',
                    'destination' => BASE_PATH . '/config/autoload/cookie.php',
                ],
            ],
        ];
    }
}
