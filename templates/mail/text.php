<?php p($l->t('Hello,')); ?>

<?php if (!empty($_['inviteLink'])) : ?>
You have been invited to start sharing data in ResearchDrive with someone who is not part of your organization.
If you accept this invitation you can create federated (remote) shares with each other.

Please copy the link below into your browser and follow it to either accept or decline the invitation.
    <?php p($_['inviteLink']); ?>
<?php endif; ?>

<?php if (!empty($_['message'])) : ?>
The following message was send with the invitation:

    <?php p($_['message']); ?>

<?php endif; ?>

<?php print_unescaped($this->inc('plain.mail.footer', ['app' => 'core'])); ?>