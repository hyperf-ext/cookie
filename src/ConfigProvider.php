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
                    'description' => 'The config for HyperfExt\Cookie.',
                    'source' => __DIR__ . '/../publish/ext-cookie.php',
                    'destination' => BASE_PATH . '/config/autoload/ext-cookie.php',
                ],
            ],
        ];
    }
}
