<?php

/**
 * 
 */

namespace OCA\RDMesh\Service;

use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier
{

    protected $factory;

    public function __construct(\OCP\L10N\IFactory $factory)
    {
        $this->factory = $factory;
    }

    public function prepare(INotification $notification, $languageCode)
    {
        \OC::$server->getLogger()->debug(' - notification Notifier prepare() -');
        if ($notification->getApp() != 'notification-invite') {
            throw new \InvalidArgumentException("Wrong app");
        }

        $l = $this->factory->get('notification', $languageCode);

        switch ($notification->getSubject()) {
                // Deal with known subjects
            case 'invitation':
                $notification->setParsedSubject(
                    (string) $l->t(
                        'You received an invitation from "%s"',
                        $notification->getSubjectParameters()
                    )
                );

                // Deal with the actions for a known subject
                foreach ($notification->getActions() as $action) {
                    switch ($action->getLabel()) {
                        case 'accept':
                            $action->setParsedLabel(
                                (string) $l->t('Accept')
                            );
                            break;

                        case 'decline':
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
