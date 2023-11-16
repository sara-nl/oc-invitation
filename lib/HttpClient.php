<?php

namespace OCA\Invitation;

use OCA\Invitation\AppInfo\InvitationApp;
use OCP\AppFramework\Http;

class HttpClient
{
    /**
     * @return mixed returns an array with in the following format:
     *  [
     *      'response' => the actual response in case of success, or a message string otherwise.
     *      'success' => 'true' | 'false'
     *  ]
     */
    public function curlPost(string $url, array $params = [])
    {
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
        if ($output == false || $info['http_code'] != Http::STATUS_OK) {
            $message = '';
            if (isset($output->error)) {
                $message .=  'Error: ' . $output->error . ". ";
            }
            if (isset($output->message)) {
                $message .=  'Message: ' . $output->message . ". ";
            }
            if ($message == '') {
                $message .= 'no error info';
            }
            \OC::$server->getLogger()->error("curlPost error: " . $message, ['app' => InvitationApp::APP_NAME]);
            return ['error_message' => $message, 'success' => false];
        } else {
            return ['response' => $output, 'success' => true];
        }
    }
}
