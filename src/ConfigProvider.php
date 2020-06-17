<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace bvtvd\HyperfLogViewer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The condig of log viewer.',
                    'source' => __DIR__ . '/../publish/log_viewer.php',
                    'destination' => BASE_PATH . '/config/autoload/log_viewer.php',
                ],
                [
                    'id' => 'view',
                    'description' => 'The template of log viewer.',
                    'source' => __DIR__ . '/../publish/log.blade.php',
                    'destination' => BASE_PATH . '/storage/view/log.blade.php',
                ],
            ],
        ];
    }
}
