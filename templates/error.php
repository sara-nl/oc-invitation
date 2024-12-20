<?php

use OCA\Collaboration\AppInfo\CollaborationApp;

$appName = CollaborationApp::APP_NAME;

style($appName, 'pure-min-css-3.0.0');
style($appName, 'collaboration');

$urlGenerator = \OC::$server->getURLGenerator();
?>

<div class="error" style="
    padding: 2em;
    margin-left: auto;
    margin-right: auto;
    width: 20%;
    border: 1px solid gray;
    margin-top: 5%;
    background-color: aqua;
    font-weight: bold;
    padding-left: 5em;">
    <?php
    $_message = $_['message'];
    p($l->t("$_message", $_['param1']));
    ?>
    <p>
        <a href="<?php echo $urlGenerator->linkToRoute('files.view.index'); ?>"><?php p($l->t('Click to continue')); ?></a>
    </p>
</div>