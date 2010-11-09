<?php

namespace Core\Service\Loader;

use Core\Service\Exception;

/**
 * LoaderManager class
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class LoaderManager
{
    /**
     * @var array Subscribed Loaders
     */
    private static $LOADERS = array(
        'default'   => 'Core\Service\Loader\DefaultLoader',
        'singleton' => 'Core\Service\Loader\SingletonLoader'
    );

    /**
     * @var array Loaded loaders
     */
    private static $instances = array();

    /**
     * Retrieve the Loader
     *
     * @param string $name
     * @param Core\Service\ServiceLocator $locator
     * @return Core\Service\Loader\AbstractLoader
     */
    public static function getLoader($name, \Core\Service\ServiceLocator $locator)
    {
        $originalName = $name;
        $name = mb_strtolower($name);

        if ( ! isset(self::$instances[$name])) {
            // Loader is not yet loaded.
            if ( ! isset(self::$LOADERS[$name])) {
                throw new Exception\NameNotFoundException("Unable to find Loader entry '{$originalName}'.");
            }

            $loaderClass = self::$LOADERS[$name];
            $reflClass = new \ReflectionClass($loaderClass);
            
            if ( ! $reflClass->implementsInterface('Core\Service\Loader\Loader')) {
                throw new Exception\InvalidClassException(
                    "Loader '{$originalName}' points to '{$loaderClass}' class which does not implement Loader interface."
                );
            }

            self::$instances[$name] = new $loaderClass($locator);
        }

        return self::$instances[$name];
    }

    /**
     * Add a new Loader
     *
     * @param string $name
     * @param string $class
     * @return boolean
     */
    public static function addLoader($name, $class)
    {
        $originalName = $name;
        $name = mb_strtolower($name);

        if (isset(self::$LOADERS[$name])) {
            throw new Exception\NameCollisionException("Cannot override Loader entry '{$originalName}'.");
        }

        self::$LOADERS[$name] = $class;
    }

    /**
     * Override an existent Loader
     *
     * @param string $name
     * @param string $class
     */
    public static function overrideLoader($name, $class)
    {
        $name = mb_strtolower($name);
        self::$LOADERS[$name] = $class;
    }

    /**
     * Remove a subscribed loader
     *
     * @param string $name
     */
    public static function removeLoader($name)
    {
        $name = mb_strtolower($name);
        unset(self::$LOADERS[$name]);
    }
}