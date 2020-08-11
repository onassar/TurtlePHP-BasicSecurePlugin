TurtlePHP-BasicSecurePlugin
======================

### Sample plugin loading:
``` php
require_once APP . '/plugins/TurtlePHP-BasePlugin/Base.class.php';
require_once APP . '/plugins/TurtlePHP-BasicSecurePlugin/BasicSecure.class.php';
$path = APP . '/config/plugins/basicSecure.inc.php';
TurtlePHP\Plugin\BasicSecure::setConfigPath($path);
TurtlePHP\Plugin\BasicSecure::init();
```
