<?php

namespace tests\util;

use Exception;

class HttpClient
{

    public function __construct()
    {
    }

    /**
     * Executes a POST request.
     *
     * @param string $url
     * @param array $params post fields
     * @param string $userName the user name to create a session user from
     * @return mixed returns an array in the standardized format:
     *  [
     *      'success' => true | false,
     *
     *      'data' => if success is true
     *          or
     *      'error_message' => if success is false
     *  ]
     * @throws HttpException
     */
    public function curlPost(string $url, array $params = [], string $userName = '')
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            if(!empty($userName)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["User: $userName"]);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_PRETTY_PRINT));
            $output = json_decode(curl_exec($ch));
            $info = curl_getinfo($ch);
            curl_close($ch);
            // print_r("\n" . "curl_getinfo: " . print_r($info, true) . "\n");
            if (!isset($output) || $output == false) {
                print_r("\n" . "curl_getinfo: " . print_r($info, true) . "\n");
                throw new Exception("empty or false output");
            }
            return (array)$output;
        } catch (Exception $e) {
            throw new Exception($e->getTraceAsString());
        }
    }

    /**
     * Executes a GET request.
     *
     * @param string $url
     * @param string $userName the user name to create a session user from
     * @return mixed returns an array in the standardized format:
     *  [
     *      'success' => true | false,
     *
     *      'data' => if success is true
     *          or
     *      'error_message' => if success is false
     *  ]
     * @throws HttpException
     */
    public function curlGet(string $url, string $userName = '')
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            if(!empty($userName)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["User: $userName"]);
            }
            $output = json_decode(curl_exec($ch));
            $info = curl_getinfo($ch);
            curl_close($ch);
            // print_r("\n" . "curl_getinfo: " . print_r($info, true) . "\n");
            if (!isset($output) || $output == false) {
                print_r("\n" . "curl_getinfo: " . print_r($info, true) . "\n");
                throw new Exception("empty or false output");
            }
            return (array)$output;
        } catch (Exception $e) {
            throw new Exception($e->getTraceAsString());
        }
    }
}
