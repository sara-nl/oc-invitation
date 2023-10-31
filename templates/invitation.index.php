<?php

use OCA\RDMesh\AppInfo\RDMesh;

$appName = RDMesh::APP_NAME;
$userDisplayName = \OC::$server->getUserSession()->getUser()->getDisplayName();

script($appName, 'invitation');
style($appName, 'pure-min-css-3.0.0');
style($appName, 'invitation');
?>
<div id="<?php p($appName); ?>-invitation" class="invitation-index pure-g">
    <div class="pure-u-1-1 create">
        <h2>Create invitation</h2>
        <form class="pure-form">
            <fieldset>
                <input id="create-invitation-email" type="email" placeholder="Receiver email" />
                <input disabled id="create-invitation-senderName" type="text" value="<?php p($userDisplayName); ?>" placeholder="<?php p($userDisplayName); ?>" />
                <button id="create-invitation" type="submit" class="pure-button pure-button-primary">Create</button>
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