<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit84338ba5f12ed3d2052d60e4ad89863c
{
    public static $classMap = array (
        'Beast\\WooCommerce\\CreateShipment' => __DIR__ . '/../..' . '/includes/class-createshipment.php',
        'Beast\\WooCommerce\\Menu' => __DIR__ . '/../..' . '/includes/class-menu.php',
        'Beast\\WooCommerce\\MetaBox' => __DIR__ . '/../..' . '/includes/class-metabox.php',
        'Beast\\WooCommerce\\OrderList' => __DIR__ . '/../..' . '/includes/class-orderlist.php',
        'Beast\\WooCommerce\\Plugin' => __DIR__ . '/../..' . '/includes/class-plugin.php',
        'Beast\\WooCommerce\\Settings' => __DIR__ . '/../..' . '/includes/class-settings.php',
        'Beast\\WooCommerce\\Translations' => __DIR__ . '/../..' . '/includes/class-translations.php',
        'Beast\\WooCommerce\\Utils' => __DIR__ . '/../..' . '/includes/class-utils.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit84338ba5f12ed3d2052d60e4ad89863c::$classMap;

        }, null, ClassLoader::class);
    }
}
