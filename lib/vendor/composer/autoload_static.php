<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite431ac619939dd3535ee344697d67ca6
{
    public static $files = array (
        '565052bedb81aa25bc73fb41e6edd46f' => __DIR__ . '/../..' . '/enguzzlehttp/guzzle/src/functions_include.php',
        '186753722d9b6917628b195e3952a607' => __DIR__ . '/../..' . '/enguzzlehttp/psr7/src/functions_include.php',
        '71389d84173da81da22281bd934e5018' => __DIR__ . '/../..' . '/enguzzlehttp/promises/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'E' => 
        array (
            'Endcore\\' => 8,
            'EnGuzzleHttp\\Psr7\\' => 18,
            'EnGuzzleHttp\\Promise\\' => 21,
            'EnGuzzleHttp\\' => 13,
        ),
        'A' => 
        array (
            'Amazon\\ProductAdvertisingAPI\\v1\\' => 32,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Endcore\\' => 
        array (
            0 => __DIR__ . '/../..' . '/endcore',
        ),
        'EnGuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/../..' . '/enguzzlehttp/psr7/src',
        ),
        'EnGuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/../..' . '/enguzzlehttp/promises/src',
        ),
        'EnGuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/../..' . '/enguzzlehttp/guzzle/src',
        ),
        'Amazon\\ProductAdvertisingAPI\\v1\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite431ac619939dd3535ee344697d67ca6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite431ac619939dd3535ee344697d67ca6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
