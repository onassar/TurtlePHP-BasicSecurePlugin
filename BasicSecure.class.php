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
     * @author  Oliver Nassar <onassar@gmail.com>
     * @abstract
     */
    abstract class BasicSecure
    {
        /**
         * _configPath
         *
         * @var     string
         * @access  protected
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _initiated
         *
         * @var     boolean
         * @access  protected
         * @static
         */
        protected static $_initiated = false;

        /**
         * _autoSecure
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _autoSecure()
        {
            $config = \Plugin\Config::retrieve('TurtlePHP-BasicSecurePlugin');
            if ($config['secure'] === true) {
                if (isset($_GET[$config['bypass']]) === false) {
                    $secureRequest = true;
                    foreach ($config['exclude'] as $pattern) {
                        if (preg_match($pattern, $_SERVER['SCRIPT_URL']) > 0) {
                            $secureRequest = false;
                        }
                    }
                    if ($secureRequest === true) {
                        self::_secure();
                    }
                }
            }
        }

        /**
         * _secure
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _secure()
        {
            $config = \Plugin\Config::retrieve('TurtlePHP-BasicSecurePlugin');
            $credentials = $config['credentials'];
            if (
                isset($_SERVER['PHP_AUTH_USER']) === false
                || (
                    isset($credentials[$_SERVER['PHP_AUTH_USER']]) === true
                    && $credentials[$_SERVER['PHP_AUTH_USER']] === $_SERVER['PHP_AUTH_PW']
                ) === false
            ) {
                header('WWW-Authenticate: Basic realm="Private Server"');
                header('HTTP/1.0 401 Unauthorized');
                echo file_get_contents(CORE . '/error.inc.php');
                exit(0);
            }
        }

        /**
         * init
         * 
         * @access  public
         * @static
         * @return  void
         */
        public static function init()
        {
            if (self::$_initiated === false) {
                self::$_initiated = true;
                require_once self::$_configPath;
                self::_autoSecure();
            }
        }

        /**
         * secure
         * 
         * @access  public
         * @static
         * @return  void
         */
        public static function secure()
        {
            self::_secure();
        }

        /**
         * setConfigPath
         * 
         * @access  public
         * @param   string $path
         * @return  void
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
    if (is_file($configPath) === true) {
        BasicSecure::setConfigPath($configPath);
    }
