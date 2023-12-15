<?php

use OCA\Invitation\AppInfo\InvitationApp;

$appName = InvitationApp::APP_NAME;
$allowSharingWithInvitedUsersOnly = $_[InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY];
$remoteProviders = $_['remoteInvitationServiceProviders'];
script($appName, 'inv');
script($appName, 'settings');
style($appName, 'invitation'); ?>

<div class="section">
    <h2>Invitation Service</h2>
    <p>
        <input type="checkbox" name="auto_accept_trusted" id="allow-sharing-with-invited-users-only" class="checkbox" <?php p($allowSharingWithInvitedUsersOnly ? 'checked' : ' '); ?> value="<?php p($allowSharingWithInvitedUsersOnly ? 'true' : 'false'); ?>">
        <label for="allow-sharing-with-invited-users-only">
            Allow federated sharing with invited users only</label>
        <span id="allow-sharing-with-invited-users-only-error" class="settings-error"></span>
        <br>
    </p>
    <br>
    <p>
        <b>Invitation service endpoint</b>
    </p>
    <p>
        <input id="invitation-service-endpoint" class="invitation-endpoint" type="text" placeholder="endpoint" value="<?php p($_['endpoint']); ?>" />
        <button id="save-invitation-service-endpoint" type="submit" class="button">Save</button>
        <span id="invitation-service-settings-endpoint-success" class="invitation-service-settings-endpoint-success"></span>
        <span id="invitation-service-settings-endpoint-error" class="settings-error"></span>
    </p>
    <br>
    <p>
        <b>Remote Invitation Service Providers</b>
    </p>
    <p>
        <button id="invitation-add-remote-service-provider" type="submit" class="button">+ Add provider</button>
    <div><span id="invitation-remote-service-providers-error" class="settings-error"></span></div>
    <div id="invitation-add-remote-service-provider" class="hide">
        <input id="invitation-add-remote-service-provider-endpoint" class="invitation-endpoint" type="text" placeholder="endpoint" />
        <button id="invitation-remote-service-provider-save" type="submit" class="button">Save</button>
        <span id="invitation-remote-service-provider-save-error" class="settings-error"></span>
    </div>
    <br>
    <ul id="invitation-remote-service-providers" class="invitation-remote-service-providers">
    </ul>
    </p>
    <br>
    <br>
    <br>
</div>