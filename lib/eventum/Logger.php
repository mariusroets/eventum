<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

use Cascade\Cascade;
use Monolog\Registry;

/**
 * @method static Monolog\Logger app() Application log channel
 * @method static Monolog\Logger db() Database log channel
 * @method static Monolog\Logger auth() Auth log channel
 * @method static Monolog\Logger cli() CLI log channel
 */
class Logger extends Registry
{
    /**
     * Configure logging for Eventum application.
     *
     * This can be used like:
     *
     * Logger::api()->addError('Sent to $api Logger instance');
     * Logger::application()->addError('Sent to $application Logger instance');
     */
    public static function initialize()
    {
        // Configure it use Eventum timezone
        Monolog\Logger::setTimezone(new DateTimeZone(APP_DEFAULT_TIMEZONE));

        // configure your loggers
        Cascade::fileConfig(APP_CONFIG_PATH . '/logger.yml');

        // ensure those log channels are present
        static::createLogger('db');
        static::createLogger('auth');
        static::createLogger('cli');

        // attach php errorhandler to app logger
        Monolog\ErrorHandler::register(self::getInstance('app'));
    }

    /**
     * Helper to create named logger and add it to registry.
     * If handlers or processors not specified, they are taken from 'app' logger.
     *
     * This could be useful, say in LDAP Auth Adapter:
     *
     * $logger = Logger::createLogger('ldap');
     * $logger->error('ldap error')
     *
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @return \Monolog\Logger
     */
    public static function createLogger($name, $handlers = null, $processors = null)
    {
        if (self::hasLogger($name)) {
            return self::getInstance($name);
        }

        if ($handlers === null) {
            $handlers = self::getInstance('app')->getHandlers();
        }
        if ($processors === null) {
            $processors = self::getInstance('app')->getProcessors();
        }

        $logger = new Monolog\Logger($name, $handlers, $processors);

        self::addLogger($logger);

        return $logger;
    }
}
