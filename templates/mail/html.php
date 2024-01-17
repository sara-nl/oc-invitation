<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td>
            <table cellspacing="0" cellpadding="0" border="0" width="600px">
                <tr>
                    <td bgcolor="<?php p($theme->getMailHeaderColor()); ?>" width="20px">&nbsp;</td>
                    <td bgcolor="<?php p($theme->getMailHeaderColor()); ?>">
                        <img src="<?php p(\OC::$server->getURLGenerator()->getAbsoluteURL(image_path('', 'logo-mail.gif'))); ?>" alt="<?php p($theme->getName()); ?>" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="20px">&nbsp;</td>
                    <td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">
                        <?php p($l->t('Hello,')); ?>
                        <br>
                        <?php if (!empty($_['inviteLink'])) : ?>
                            You have been invited to start sharing data in ResearchDrive with someone who is not part of your organization.<br>
                            If you accept this invitation you can create federated (remote) shares with each other.<br>
                            <br>
                            Please follow the link below to either accept or decline the invitation.<br>
                            <a href="<?php p($_['inviteLink']); ?>">Click to log into your ResearchDrive instance</a>
                            <br>
                        <?php endif; ?>
                        <?php if (!empty($_['message'])) : ?>
                            <p>
                                The following message was send with the invitation:<br>
                                <br>
                                <?php p($_['message']); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="20px">&nbsp;</td>
                    <td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">
                        <?php print_unescaped($this->inc('html.mail.footer', ['app' => 'core'])); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>