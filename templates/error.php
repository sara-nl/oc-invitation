<?php

use OCA\RDMesh\AppInfo\RDMesh;

$appName = RDMesh::APP_NAME;

style($appName, 'pure-min-css-3.0.0');
style($appName, 'invitation');

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
    <?php p($l->t($_['message'])); ?>
    <p>
        <a href="<?php echo $urlGenerator->linkToRoute('files.view.index'); ?>">Click to continue</a>
    </p>
</div>