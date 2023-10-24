<?php

use OCA\RDMesh\AppInfo\RDMesh;

script(RDMesh::APP_NAME, 'invitation');
style(RDMesh::APP_NAME, 'pure-min-css-3.0.0');
style(RDMesh::APP_NAME, 'invitation');
?>
<div id="rd-mesh-invitation" class="invitation-index pure-g">
    <div class="pure-u-1-1 create">
        <h2>Create invitation</h2>
        <form class="pure-form">
            <fieldset>
                <input id="create-invitation-email" type="email" placeholder="Receiver email" />
                <input id="create-invitation-senderName" type="text" placeholder="Your name" />
                <button id="create-invitation" type="submit" class="pure-button pure-button-primary">Create</button>
            </fieldset>
        </form>
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
                                    <th>Send/Received</th>
                                    <th>Name</th>
                                    <th>Cloud ID</th>
                                    <th>email</th>
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
                                    <th>Send/Received</th>
                                    <th>Name</th>
                                    <th>Cloud ID</th>
                                    <th>email</th>
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