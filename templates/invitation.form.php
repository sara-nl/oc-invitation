<div class="invitation-form pure-g">
    <div class="pure-u-1-1 create">
        <form class="pure-form pure-form-aligned">
            <fieldset>
                <legend><?php p($l->t('Create invitation')); ?></legend>
                <div class="pure-g">
                    <div class="pure-u-1-8">
                        <label for="create-invitation-email">Email</label>
                    </div>
                    <div class="pure-u-7-8">
                        <input id="create-invitation-email" type="email" placeholder="<?php p($l->t('Recipient email')); ?>" />
                    </div>
                    <div class="pure-u-1-8">
                        <label for="create-invitation-senderName"><?php p($l->t('Sender name')); ?></label>
                    </div>
                    <div class="pure-u-7-8">
                        <input id="create-invitation-senderName" type="text" placeholder="<?php p($l->t('Your name')); ?>" value="<?php p($_['senderName']); ?>" />
                    </div>
                    <div class="pure-u-1-8">
                        <label for="create-invitation-message">&nbsp;</label>
                    </div>
                    <div class="pure-u-7-8">
                        <textarea id="create-invitation-message" placeholder="<?php p($l->t('Your message (optional)')); ?>"></textarea>
                    </div>
                    <div class="pure-u-1-1 invitation-form-button-row">
                        <button id="create-invitation" type="submit" class="pure-button pure-button-primary"><?php p($l->t('Send')); ?></button>
                    </div>
                </div>
            </fieldset>
        </form>
        <div id="invitation-message"><span class="message"></span><span class="error"></span></div>
    </div>
</div>
<script>
    (function(window) {
        window.INVITATION.initInvitationForm("<?php p($_['cloudID']); ?>");
    })(window);
</script>