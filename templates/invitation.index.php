<?php

use OCA\Invitation\AppInfo\InvitationApp;

$appName = InvitationApp::APP_NAME;
script($appName, 'inv');
script($appName, 'invitation');
style($appName, 'pure-min-css-3.0.0');
style($appName, 'invitation');
?>
<div id="<?php p($appName); ?>" class="invitation-index pure-g">
    <input type="hidden" value="<?php echo \OC::$server->getConfig()->getAppValue($appName, InvitationApp::CONFIG_DEPLOY_MODE, ''); ?>" />
    <div class="pure-u-1-1 create">
        <form class="pure-form">
            <fieldset>
                <legend><?php p($l->t('Create invitation')); ?></legend>
                <div class="pure-g">
                    <div class="pure-u-1-4">
                        <input id="create-invitation-email" type="email" placeholder="<?php p($l->t('Recipient email')); ?>" />
                        <button id="create-invitation" type="submit" class="pure-button pure-button-primary"><?php p($l->t('Send')); ?></button>
                    </div>
                </div>
                <div class="pure-g">
                    <div class="pure-u-1-3">
                        <textarea id="create-invitation-message" placeholder="<?php p($l->t('Your message (optional)')); ?>"></textarea>
                    </div>
                </div>
            </fieldset>
        </form>
        <div id="invitation-message"><span class="message"></span><span class="error"></span></div>
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
                                    <th><?php p($l->t('Remote user email')); ?></th>
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