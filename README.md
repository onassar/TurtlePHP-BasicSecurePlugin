TurtlePHP-BasicSecurePlugin
======================

### Sample plugin loading:
``` php
require_once APP . '/plugins/TurtlePHP-BasePlugin/Base.class.php';
require_once APP . '/plugins/TurtlePHP-BasicSecurePlugin/BasicSecure.class.php';
$path = APP . '/config/plugins/basicSecure.inc.php';
Plugin\BasicSecure::setConfigPath($path);
Plugin\BasicSecure::init();
```
