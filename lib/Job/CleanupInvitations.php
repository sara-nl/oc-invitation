<?php

namespace OCA\Collaboration\Job;

use OC\BackgroundJob\TimedJob;
use OCA\Collaboration\AppInfo\CollaborationApp;
use OCA\Collaboration\Federation\Invitation;
use OCA\Collaboration\Service\InvitationService;
use OCA\Collaboration\Service\ServiceException;
use OCP\ILogger;

class CleanupInvitations extends TimedJob
{
    private InvitationService $invitationService;
    private ILogger $logger;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
        $this->logger = \OC::$server->getLogger();
    }
    /**
     * Makes the background job do its work.
     *
     * @param $argument unused
     */
    protected function run($argument)
    {
        try {
            $this->invitationService->deleteForStatus([Invitation::STATUS_DECLINED, Invitation::STATUS_INVALID, Invitation::STATUS_REVOKED]);
            // 2592000 seconds is 30 days
            $this->invitationService->deleteExpiredOpenInvitation(2592000);
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage(), ['app' => CollaborationApp::APP_NAME]);
        }
    }
}
