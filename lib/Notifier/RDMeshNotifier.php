<?php

namespace OCA\RDMesh\Notifier;

use OCA\RDMesh\Service\RDMeshService;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class RDMeshNotifier implements INotifier {

    private $appName;
    private IFactory $factory;

    public function __construct($appName, IFactory $factory) {
        $this->appName = $appName;
        $this->factory = $factory;
        
    }

    /**
     * @param INotification $notification
     * @param string $languageCode The code of the language that should be used to prepare the notification
     */
    public function prepare(INotification $notification, $languageCode) {
        \OC::$server->getLogger()->debug(" --- prepare notification --- language: $languageCode");
        if ($notification->getApp() !== $this->appName) {
            // Not my app => throw
            throw new \InvalidArgumentException();
        }

        // Read the language from the notification
        $l = $this->factory->get($this->appName, $languageCode);

        switch ($notification->getSubject()) {
            // Deal with known subjects
            case 'invitation':
                $notification->setParsedSubject(
                    (string) $l->t(
                    'You received the remote share "%s"',
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

                        case 'reject':
                            $action->setParsedLabel(
                                (string) $l->t('Reject')
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