<?php

namespace OCA\Invitation\Job;

use OC\BackgroundJob\TimedJob;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Federation\Invitation;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\ServiceException;
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
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
        }
    }
}
