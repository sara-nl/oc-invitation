<?php

use OCA\Invitation\AppInfo\InvitationApp;

$appName = InvitationApp::APP_NAME;
$allowSharingWithInvitedUsersOnly = $_[InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY];
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
        Invitation service endpoint
    </p>
    <p>
        <input id="invitation-service-endpoint" type="text" placeholder="endpoint" value="<?php p($_['endpoint']); ?>" />
        <button id="save-invitation-service-endpoint" type="submit" class="button">Save</button>
        <span id="invitation-service-settings-endpoint-success" class="invitation-service-settings-endpoint-success"></span>
        <span id="invitation-service-settings-endpoint-error" class="settings-error"></span>
    </p>
</div>