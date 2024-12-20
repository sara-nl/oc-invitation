<?php

/**
 * The invitation notifier
 */

namespace OCA\Collaboration\Service;

use OCA\Collaboration\AppInfo\CollaborationApp;
use OCA\Collaboration\Service\MeshRegistry\MeshRegistryService;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class InvitationNotifier implements INotifier
{
    private IL10N $il10n;
    private ILogger $logger;

    public function __construct(IL10N $il10n)
    {
        $this->il10n = $il10n;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Display the notification.
     */
    public function prepare(INotification $notification, $languageCode)
    {
        if ($notification->getApp() != CollaborationApp::APP_NAME) {
            $this->logger->error("Notification has been given the wrong app name '" . $notification->getApp() . "'");
            throw new \InvalidArgumentException("Wrong app");
        }

        switch ($notification->getSubject()) {
            case 'collaboration':
                $notification->setParsedSubject(
                    (string) $this->il10n->t(
                        "notification",
                        [
                            $notification->getSubjectParameters()[MeshRegistryService::PARAM_NAME_NAME],
                        ]
                    )
                );

                return $notification;
                break;

            default:
                // Unknown subject => Unknown notification => throw
                throw new \InvalidArgumentException();
        }
    }
}
