<?php

/**
 * 
 */

namespace OCA\RDMesh\Service;

use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class InvitationNotifier implements INotifier
{

    protected $factory;

    public function __construct(\OCP\L10N\IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Display the notification.
     */
    public function prepare(INotification $notification, $languageCode)
    {
        if ($notification->getApp() != 'notification-invite') {
            throw new \InvalidArgumentException("Wrong app");
        }

        $l = $this->factory->get('notification', $languageCode);

        switch ($notification->getSubject()) {
                // Deal with known subjects
            case 'invitation':
                $notification->setParsedSubject(
                    (string) $l->t(
                        'You received an invitation from "%s" (token: "%s").',
                        [
                            $notification->getSubjectParameters()[MeshService::PARAM_NAME_PROVIDER_DOMAIN],
                            $notification->getSubjectParameters()[MeshService::PARAM_NAME_TOKEN],
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
