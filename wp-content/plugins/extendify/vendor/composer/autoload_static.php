<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc60890fe299ad8e6d6c61e7219ba2cb7
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Extendify\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Extendify\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc60890fe299ad8e6d6c61e7219ba2cb7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc60890fe299ad8e6d6c61e7219ba2cb7::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
