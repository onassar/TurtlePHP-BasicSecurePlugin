<?php

    // namespace
    namespace Plugin;

    // dependency check
    if (class_exists('\\Plugin\\Config') === false) {
        throw new \Exception(
            '*Config* class required. Please see ' .
            'https://github.com/onassar/TurtlePHP-ConfigPlugin'
        );
    }

    /**
     * BasicSecure
     * 
     * HTTP basic secure plugin for TurtlePHP
     * 
     * @author   Oliver Nassar <onassar@gmail.com>
     * @abstract
     */
    abstract class BasicSecure
    {
        /**
         * _configPath
         *
         * @var    string
         * @access protected
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _initiated
         *
         * @var    boolean
         * @access protected
         * @static
         */
        protected static $_initiated = false;

        /**
         * _check
         * 
         * @access protected
         * @static
         * @return void
         */
        protected static function _check()
        {
            $config = \Plugin\Config::retrieve('TurtlePHP-BasicSecurePlugin');
            if ($config['secure'] === true) {;
                if (!isset($_GET[$config['bypass']])) {
                    if (
                        !isset($_SERVER['PHP_AUTH_USER'])
                        ||
                        !(
                            isset($config['credentials'][$_SERVER['PHP_AUTH_USER']])
                            && $config['credentials'][$_SERVER['PHP_AUTH_USER']] === $_SERVER['PHP_AUTH_PW']
                        )
                    ) {
                        header(
                            'WWW-Authenticate: Basic realm="Private Server"'
                        );
                        header('HTTP/1.0 401 Unauthorized');
                        echo file_get_contents(CORE . '/error.inc.php');
                        exit(0);
                    }
                }
            }
        }

        /**
         * init
         * 
         * @access public
         * @static
         * @return void
         */
        public static function init()
        {
            if (is_null(self::$_initiated) === false) {
                self::$_initiated = true;
                require_once self::$_configPath;
                self::_check();
            }
        }

        /**
         * setConfigPath
         * 
         * @access public
         * @param  string $path
         * @return void
         */
        public static function setConfigPath($path)
        {
            self::$_configPath = $path;
        }
    }

    // Config
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    if (is_file($configPath)) {
        BasicSecure::setConfigPath($configPath);
    }
