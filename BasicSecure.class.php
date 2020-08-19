<?php

    // namespace
    namespace TurtlePHP\Plugin;

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
         * _renderedErrorView
         * 
         * @access  protected
         * @var     null|string (default: null)
         * @static
         */
        protected static $_renderedErrorView = null;

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
            $errorViewPath = \TurtlePHP\Application::getErrorViewPath();
            $response = \TurtlePHP\Application::renderPath($errorViewPath);
            return $response;
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
            $excludePatterns = $configData['excludePatterns'];
            foreach ($excludePatterns as $excludePattern) {
                if (preg_match($excludePattern, $_SERVER['SCRIPT_URL']) > 0) {
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
            $content = static::$_renderedErrorView;
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
         * _renderErrorView
         * 
         * Renders the error view upon plugin init call to ensure that the
         * markup is available if and when it's needed.
         * 
         * This needs to be done _before_ any possible failure due to a header
         * flushing "race condition" (sort of) whereby:
         * 1. If the error view is simply loaded using file_get_contents, the
         *    proper rendering isn't completed: https://i.imgur.com/N62HtaB.png
         * 2. If the error view is rendered in the same flow that security
         *    header is sent to the browser, that header flushing prevents the
         *    actual username/password prompt from even showing up.
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _renderErrorView(): void
        {
            $markup = static::_getErrorViewMarkup();
            static::$_renderedErrorView = $markup;
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
         * @note    Ordered
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
            static::_renderErrorView();
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
    \TurtlePHP\Plugin\Redirect::setConfigPath($configPath);
