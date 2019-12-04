<?php
declare(strict_types=1);

namespace App\Controller;

use SimpleXMLElement;
use App\Decorator\DecoratorManager;

class LessonController
{
    public $isProdaction;
    public $host;
    public $user;
    public $password;
    public $cache;
    public $logger;

    /**
     * @param int $cat
     * @param string $rt
     */
    public function action($cat, $rt = null)
    {
        if (!preg_match('/[0-9]{5}/', $cat) || $cat <= 0) {
            echo "error";
            exit;
        }

        if (is_null($rt))
        {
            $rt = 'json';
        }

        $decorator_manager = new DecoratorManager($this->user, $this->password, $this->host, $this->logger);
        if ($this->isProdaction) {
            $decorator_manager->setCache($this->cache);
        }

        $data = $decorator_manager->getResponse(array("categoryId" => $cat, ''));
        if ($data != []) {
            if ($rt = 'xml') {
                $xml = new SimpleXMLElement('<root/>');
                array_walk_recursive($data, array ($xml, 'addChild'));
                echo $xml->asXML();
                exit;
            } elseif ($rt == 'json') {
                echo json_encode($data);
                exit;
            }
        }

        echo "error";
        exit;
    }
}