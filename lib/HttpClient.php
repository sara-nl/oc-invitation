<?php

namespace OCA\Invitation;

use Exception;
use OCA\Invitation\AppInfo\AppError;
use OCP\ILogger;

class HttpClient
{
    private ILogger $logger;

    public function __construct()
    {
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Executes a POST request.
     *
     * @param string $url
     * @param array post fields
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
    public function curlPost(string $url, array $params = [])
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_PRETTY_PRINT));
            $output = json_decode(curl_exec($ch));
            $info = curl_getinfo($ch);
            curl_close($ch);
            $this->logger->debug('curl_getinfo: ' . print_r($info, true));
            if (!isset($output) || $output == false) {
                $this->logger->debug('curl_getinfo: ' . print_r($info, true));
                throw new HttpException(AppError::HTTP_POST_CURL_REQUEST_ERROR);
            }
            return (array)$output;
        } catch (HttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HttpException(AppError::HTTP_POST_CURL_REQUEST_ERROR);
        }
    }

    /**
     * Executes a GET request.
     *
     * @param string $url
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
    public function curlGet(string $url)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            $output = json_decode(curl_exec($ch));
            $info = curl_getinfo($ch);
            curl_close($ch);
            $this->logger->debug('curl_getinfo: ' . print_r($info, true));
            if (!isset($output) || $output == false) {
                $this->logger->debug('curl_getinfo: ' . print_r($info, true));
                throw new HttpException(AppError::HTTP_GET_CURL_REQUEST_ERROR);
            }
            return (array)$output;
        } catch (HttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HttpException(AppError::HTTP_POST_CURL_REQUEST_ERROR);
        }
    }
}
