<?php
declare(strict_types=1);

namespace App\Integration;

class DataProvider
{
    private $host;
    private $user;
    private $password;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     */
    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    public function get(array $request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . '?' . http_build_query($request));
        curl_setopt($ch, CURLOPT_USERPWD, $this->user . ":" . $this->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if(curl_errno($ch)){
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        //        // --- dump ---
        //        echo '<pre>';
        //        echo __FILE__ . chr(10);
        //        echo __METHOD__ . chr(10);
        //        var_dump($output);
        //        echo '</pre>';
        //        exit;
        //        // --- // ---

        return json_decode($output);
    }
}