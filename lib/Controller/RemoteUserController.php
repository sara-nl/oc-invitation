<?php

/**
 * RemoteUser controller.
 *
 */

namespace OCA\Invitation\Controller;

use Exception;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\RemoteUserService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\ILogger;
use OCP\IRequest;

class RemoteUserController extends Controller
{
    private RemoteUserService $remoteUserService;
    private ILogger $logger;

    public function __construct(
        $appName,
        IRequest $request,
        RemoteUserService $remoteUserService
    ) {
        parent::__construct($appName, $request);
        $this->remoteUserService = $remoteUserService;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getRemoteUser(string $cloudID = ''): DataResponse
    {
        try {
            $remoteUser = $this->remoteUserService->getRemoteUser($cloudID);
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $remoteUser->jsonSerialize()
                ],
                Http::STATUS_OK
            );
        } catch (NotFoundException $e) {
            return new DataResponse(
                [
                    'success' => false,
                ],
                Http::STATUS_NOT_FOUND
            );
        } catch (Exception $e) {
            return new DataResponse(
                [
                    'success' => false,
                ],
                Http::STATUS_NOT_FOUND
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function search(string $search = ''): DataResponse
    {
        $result = $this->remoteUserService->search($search);
        return new DataResponse(
            [
                'success' => true,
                'data' => $result
            ],
            Http::STATUS_OK
        );
    }
}
