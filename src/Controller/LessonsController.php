<?php

namespace src\App\Controller;

use SimpleXMLElement;
use src\App\Decorator\DecoratorManager;

class LessonsController
{
    public $host;
    public $user;
    public $password;
    public $cache;
    public $logger;
    public $isProduction;

    /**
     * @param int $param1
     * @param string $param2
     */
    public function action($param1, $param2 = 'json')
    {
        if (!preg_match('/[0-9]/i', $param1)) {
            echo 'error';
            return;
        }

        $provider = new DecoratorManager($this->user, $this->password, $this->host, $this->logger);
        if ($this->isProduction) {
            $provider->setCache($this->cache);
        }

        $data = $provider->getResponse(array('categoryId' => $param1));
        if ($data != []) {
            if ($param2 = 'json') {
                echo $this->createJsonResponse($data);
                exit;
            } else {
                echo $this->createXmlResponse($data);
                exit;
            }
        }

        echo 'error';
        exit;
    }

    public function createJsonResponse(array $data) {
        return json_encode($data);
    }

    public function createXmlResponse(array $data)
    {
        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($data, array ($xml, 'addChild'));
        return $xml->asXML();
    }

}