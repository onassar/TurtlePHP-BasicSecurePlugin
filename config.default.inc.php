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
        ),
        'exclude' => array()
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
