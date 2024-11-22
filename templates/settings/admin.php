<?php

use OCA\Collaboration\AppInfo\CollaborationApp;

$appName = CollaborationApp::APP_NAME;
$allowSharingWithInvitedUsersOnly = $_[CollaborationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY];
script($appName, 'invitation-service');
script($appName, 'invitation-actions');
script($appName, 'collaboration');
script($appName, 'settings');
style($appName, 'collaboration'); ?>

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
        <b>This Invitation Service Provider</b>
    </p>
    <p>
        <label for="invitation-service-endpoint">Endpoint</label><input id="invitation-service-endpoint" data-invitation-service-provider-endpoint="<?php p($_['endpoint']); ?>" class="settings-isp-endpoint" type="text" placeholder="endpoint" value="<?php p($_['endpoint']); ?>" />
        <span id="invitation-service-settings-endpoint-error" class="settings-error"></span>
    </p>
    <label for="invitation-service-name">Name</label><input id="invitation-service-name" class="settings-isp-name" type="text" placeholder="name" value="<?php p($_['name']); ?>" />
    <span id="invitation-service-settings-name-error" class="settings-error"></span>
    <div>
        <button id="save-invitation-service-name" type="submit" class="button">Save</button>
        <span id="invitation-service-settings-success" class="invitation-service-settings-success"></span>
        <span id="invitation-service-settings-error" class="settings-error"></span>
    </div>
    <br>
    <p>
        <b>Remote Invitation Service Providers</b>
    </p>
    <p>
        <button id="invitation-add-remote-service-provider" type="submit" class="button">+ Add provider</button>
    <div><span id="invitation-remote-service-providers-error" class="settings-error"></span></div>
    <div id="invitation-add-remote-service-provider" class="hide">
        <input id="invitation-add-remote-service-provider-endpoint" class="settings-add-isp" type="text" placeholder="endpoint" />
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