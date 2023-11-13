<?php

use OCA\Invitation\AppInfo\InvitationApp;

$appName = InvitationApp::APP_NAME;
// FIXME: decide whether the user display name is appropriate (as default)
$userDisplayName = \OC::$server->getUserSession()->getUser()->getDisplayName();

script($appName, 'invitation');
style($appName, 'pure-min-css-3.0.0');
style($appName, 'invitation');
?>
<div id="<?php p($appName); ?>" class="invitation-index pure-g">
    <div class="pure-u-1-1 create">
        <form class="pure-form">
            <fieldset>
                <legend>Create invitation</legend>
                <div class="pure-g">
                    <div class="pure-u-1-4">
                        <input id="create-invitation-email" type="email" placeholder="Receiver email" />
                        <button id="create-invitation" type="submit" class="pure-button pure-button-primary">Create</button>
                    </div>
                </div>
                <div class="pure-g">
                    <div class="pure-u-1-3">
                        <textarea id="create-invitation-message" placeholder="Your message (optional)"></textarea>
                    </div>
                </div>
            </fieldset>
        </form>
        <div id="invitation-error" class="error"><span></span></div>
    </div>
    <div class="pure-u-1-1 invites">
        <div class="pure-g">
            <div class="pure-u-1-2">
                <div class="pure-g">
                    <div class="pure-u-1-1 accepted">
                        <h2>Accepted invitations</h2>
                        <table class="pure-table">
                            <thead>
                                <tr>
                                    <th>Sent/Received</th>
                                    <th>Remote user name</th>
                                    <th>Remote user cloud ID</th>
                                    <th>Remote user email</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="pure-u-1-2">
                <div class="pure-g">
                    <div class="pure-u-1-1 open">
                        <h2>Open invitations</h2>
                        <table class="pure-table">
                            <thead>
                                <tr>
                                    <th>Sent/Received</th>
                                    <th>Token</th>
                                    <th>Remote user name</th>
                                    <th>Remote user cloud ID</th>
                                    <th>Remote user email</th>
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