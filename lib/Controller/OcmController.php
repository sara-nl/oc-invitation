<?php

/**
 * OCM controller
 */

namespace OCA\RDMesh\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * Class OcmController.
 * Enhances the existing federatedfilesharing app with the ocm endpoint '/invite-accepted'
 * 
 */
class OcmController extends Controller
{

    public function __construct($appName, IRequest $request)
    {
        parent::__construct($appName, $request);
    }

    /**
     * Inform the sender of the invite that it has been accepted by the recipient.
     * 
     * FIXME: use method parameters
     * 
     * @NoCSRFRequired
     * @PublicPage
     * @param string $recipientProvider
     * @param string $token the invite token
     * @param string $userID the recipient cloud ID
     * @param string $email the recipient email
     * @param string $name the recipient name
     * @return DataResponse
     */
    public function inviteAccepted(
        string $recipientProvider = '',
        string $token = '',
        string $userID = '',
        string $email = '',
        string $name = ''
    ): DataResponse {
        if ($recipientProvider == '') {
            return new DataResponse(
                ['error' => 'recipient provider missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($userID == '') {
            return new DataResponse(
                ['error' => 'recipient user ID missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($email == '') {
            return new DataResponse(
                ['error' => 'recipient email missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($name == '') {
            return new DataResponse(
                ['error' => 'recipient name missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        // FIXME: retrieve user info from the db based using the token as reference and do the necessary checks
        $dummyCloudID = 'maikel@rd-1.nl';
        $dummyEmailAddress = 'maikel@rd-1.nl';
        $dummyDisplayName = 'Maikel';

        return new DataResponse(
            [
                'userID' => $dummyCloudID,
                'email' => $dummyEmailAddress,
                'name' => $dummyDisplayName,
            ],
            Http::STATUS_OK
        );
    }
}
