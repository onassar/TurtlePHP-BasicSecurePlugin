<?php

    // namespace
    namespace Plugin;

    /**
     * BasicSecure
     * 
     * Basic Secure plugin for TurtlePHP.
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     * @abstract
     * @extends Base
     */
    abstract class BasicSecure extends Base
    {
        /**
         * _configPath
         * 
         * @access  protected
         * @var     string (default: 'config.default.inc.php')
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _initiated
         * 
         * @access  protected
         * @var     bool (default: false)
         * @static
         */
        protected static $_initiated = false;

        /**
         * _autoSecureRequest
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _autoSecureRequest(): bool
        {
            $configData = static::_getConfigData();
            if ($configData['secure'] === false) {
                return false;
            }
            $isBypassedRequest = static::_isBypassedRequest();
            if ($isBypassedRequest === true) {
                return false;
            }
            $isBypassedPath = static::_isBypassedPath();
            if ($isBypassedPath === true) {
                return false;
            }
            static::_secureRequest();
            return true;
        }

        /**
         * _checkDependencies
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependencies(): void
        {
            static::_checkConfigPluginDependency();
        }

        /**
         * _getErrorViewMarkup
         * 
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getErrorViewMarkup(): string
        {
            $errorViewPath = CORE . '/error.inc.php';
            $content = file_get_contents($errorViewPath);
            return $content;
        }

        /**
         * _isBypassedPath
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _isBypassedPath(): bool
        {
            $configData = static::_getConfigData();
            $excludedPatterns = $configData['excludedPatterns'];
            foreach ($excludedPatterns as $excludedPattern) {
                if (preg_match($excludedPattern, $_SERVER['SCRIPT_URL']) > 0) {
                    return true;
                }
            }
            return false;
        }

        /**
         * _isBypassedRequest
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _isBypassedRequest(): bool
        {
            $configData = static::_getConfigData();
            $bypassKey = $configData['bypassKey'];
            $isBypassedRequest = isset($_GET[$bypassKey]) === true;
            return $isBypassedRequest;
        }

        /**
         * _loadErrorView
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _loadErrorView(): void
        {
            $content = static::_getErrorViewMarkup();
            echo $content;
            exit(0);
        }

        /**
         * _rejectFailedBasicSecureRequest
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _rejectFailedBasicSecureRequest(): void
        {
            static::_sendBasicSecureHeaders();
            static::_loadErrorView();
        }

        /**
         * _secureRequest
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _secureRequest(): bool
        {
            $userAgentUsername = $_SERVER['PHP_AUTH_USER'] ?? null;
            if ($userAgentUsername === null) {
                static::_rejectFailedBasicSecureRequest();
                return true;
            }
            $userAgentPassword = $_SERVER['PHP_AUTH_PW'] ?? null;
            if ($userAgentPassword === null) {
                static::_rejectFailedBasicSecureRequest();
                return true;
            }
            $configData = static::_getConfigData();
            $credentials = $configData['credentials'];
            $correspondingPassword = $credentials[$userAgentUsername] ?? null;
            if ($correspondingPassword === null) {
                static::_rejectFailedBasicSecureRequest();
                return true;
            }
            if ($correspondingPassword !== $userAgentPassword) {
                static::_rejectFailedBasicSecureRequest();
                return true;
            }
            return false;
        }

        /**
         * _sendBasicSecureHeaders
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _sendBasicSecureHeaders(): void
        {
            $value = 'WWW-Authenticate: Basic realm="Private Server"';
            static::_setHeader($value);
            $value = 'HTTP/1.0 401 Unauthorized';
            static::_setHeader($value);
        }

        /**
         * init
         * 
         * @access  public
         * @static
         * @return  bool
         */
        public static function init(): bool
        {
            if (static::$_initiated === true) {
                return false;
            }
            parent::init();
            static::_autoSecureRequest();
            return true;
        }

        /**
         * secure
         * 
         * @access  public
         * @static
         * @return  void
         */
        public static function secure(): void
        {
            static::_secureRequest();
        }
    }

    // Config path loading
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    \Plugin\Redirect::setConfigPath($configPath);
