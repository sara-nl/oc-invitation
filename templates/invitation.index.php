<?php

use OCA\Collaboration\AppInfo\CollaborationApp;

$appName = CollaborationApp::APP_NAME;
script($appName, 'invitation-service');
script($appName, 'invitation-actions');
script($appName, 'invitation');
style($appName, 'pure-min-css-3.0.0');
style($appName, 'invitation');
?>
<div id="invitation" class="invitation-index pure-g">
    <input type="hidden" value="<?php echo \OC::$server->getConfig()->getAppValue($appName, CollaborationApp::CONFIG_DEPLOY_MODE, ''); ?>" />
    <div class="pure-u-1-1 create">
        <div class="pure-g">
            <div class="pure-u-1-8">
                <button id="open-invitation-form" type="submit" class="pure-button pure-button-primary"><?php p($l->t('Exchange cloud ID')); ?></button>
            </div>
            <div class="pure-u-7-8">
                <span class="information">
                </span>
            </div>
            <div class="pure-u-1-1" id="invitation-index-message">
                <span class="message"></span><span class="error"></span>
            </div>
        </div>
    </div>
    <div class="pure-u-1-1 invites">
        <div class="pure-g">
            <div class="pure-u-1-1">
                <div class="pure-g">
                    <div class="pure-u-1-1 open">
                        <h2><?php p($l->t('Open invitations')); ?></h2>
                        <table class="pure-table">
                            <thead>
                                <tr>
                                    <th><?php p($l->t('Sent/Received')); ?></th>
                                    <th><?php p($l->t('Remote user name')); ?></th>
                                    <th><?php p($l->t('Remote user institute')); ?></th>
                                    <th><?php p($l->t('Sent to')); ?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="pure-u-1-1">
                <div class="pure-g">
                    <div class="pure-u-1-1 accepted">
                        <h2><?php p($l->t('Accepted invitations')); ?></h2>
                        <table class="pure-table">
                            <thead>
                                <tr>
                                    <th><?php p($l->t('Sent/Received')); ?></th>
                                    <th><?php p($l->t('Remote user name')); ?></th>
                                    <th><?php p($l->t('Remote user institute')); ?></th>
                                    <th><?php p($l->t('Remote user email')); ?></th>
                                    <th><?php p($l->t('Remote user cloud ID')); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
