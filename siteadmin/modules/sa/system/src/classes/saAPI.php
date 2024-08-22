<?php
namespace sa\system;

/**
*  Site Administrator
*  (C) 2011 eLink Design, Inc.
*
*/

class saAPI
{


    protected $api_host = "http://sa.elinkdesign.com";
    protected $api_url = "api/api.new.php";
    protected $api_port = "80";

    function remoteCall($class, $method, $params, &$return)
    {
        $request = array("class"=>$class, "method"=>$method, "params"=>$params);

        $data = $this->executePostData($this->api_url, $request);

        return json_decode($data, true);

    }


    private function executePostData($endpoint, $body='') {

        $curl = curl_init($this->api_host .'/'.$endpoint);

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);

        $j = $result;

        return $j;

    }
}