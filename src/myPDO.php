<?php

declare(strict_types=1);

class myPDO extends PDO {

    public function __construct()
    {
        if (!$settings = parse_ini_file('../.env')) {
            throw new Exception('Unable to get db settings');
        }
        
        $dns = $settings['DB_DRIVER'] .
        ':host=' . $settings['DB_HOST'] .
        ((!empty($settings['DB_PORT'])) ? (';port=' . $settings['DB_PORT']) : '') .
        ';dbname=' . $settings['DB_NAME'];
        
        parent::__construct($dns, $settings['DB_USER'], $settings['DB_PASSWORD']);
    }

}