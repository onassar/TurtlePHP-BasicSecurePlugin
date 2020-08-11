<?php

    /**
     * Plugin Config Data
     * 
     */
    $bypass = 'bypass';
    $credentials = array();
    $credentials['username'] = 'password';
    $exclude = array();
    $secure = false;
    $pluginConfigData = compact('bypass', 'credentials', 'exclude', 'secure');

    /**
     * Storage
     * 
     */
    $key = 'TurtlePHP-BasicSecurePlugin';
    TurtlePHP\Plugin\Config::set($key, $pluginConfigData);
