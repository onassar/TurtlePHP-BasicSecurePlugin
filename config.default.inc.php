<?php

    /**
     * Namespace
     * 
     */
    namespace Plugin\BasicSecure;

    /**
     * Config settings
     * 
     */
    $config = array(
        'bypass' => 'bypass',
        'secure' => false,
        'credentials' => array(
            'username' => 'password'
        )
    );

    /**
     * Config storage
     * 
     */

    // Store
    \Plugin\Config::add(
        'TurtlePHP-BasicSecurePlugin',
        $config
    );
