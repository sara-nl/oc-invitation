<?php

/**
 * The invitation notifier
 */

namespace OCA\Invitation\Service;

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCP\ILogger;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class InvitationNotifier implements INotifier
{
    protected $factory;
    private ILogger $logger;

    public function __construct(\OCP\L10N\IFactory $factory)
    {
        $this->factory = $factory;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Display the notification.
     */
    public function prepare(INotification $notification, $languageCode)
    {
        if ($notification->getApp() != InvitationApp::APP_NAME) {
            $this->logger->error("Notification has been given the wrong app name '" . $notification->getApp() . "'");
            throw new \InvalidArgumentException("Wrong app");
        }

        $l = $this->factory->get('notification', $languageCode);

        switch ($notification->getSubject()) {
                // Deal with known subjects
            case 'invitation':
                $notification->setParsedSubject(
                    (string) $l->t(
                        'You received an invitation from "%s".',
                        [
                            $notification->getSubjectParameters()[MeshRegistryService::PARAM_NAME_NAME],
                        ]
                    )
                );

                foreach ($notification->getActions() as $action) {
                    // display the buttons
                    switch ($action->getLabel()) {
                        case 'accept':
                            // show the Accept label
                            $action->setParsedLabel(
                                (string) $l->t('Accept')
                            );
                            break;

                        case 'decline':
                            // show the Decline label
                            $action->setParsedLabel(
                                (string) $l->t('Decline')
                            );
                            break;
                    }

                    $notification->addParsedAction($action);
                }
                return $notification;
                break;

            default:
                // Unknown subject => Unknown notification => throw
                throw new \InvalidArgumentException();
        }
    }
}
