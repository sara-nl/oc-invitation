<html>

<head>
    <title><?php p($l->t("Select your institution to log in")); ?></title>
    <link rel="stylesheet" href="/apps/invitation/css/invitation.css">
    <link rel="stylesheet" href="/apps/invitation/css/pure-min-css-3.0.0.css">
</head>

<body>
    <div class="wayf-surf-logo">
        <img src="https://www.surf.nl/themes/surf/logo.svg" />
    </div>
    <div class="wayf">
        <div class="wayf-header">
            <h2><?php p($l->t("Select your institution to log in")); ?></h2>
        </div>
        <div class="text"><?php p($l->t('You will be redirected to the Research Drive page of your institution to log in.')); ?></div>
        <div class="institutes">
            <?php
            foreach ($_['wayfItems'] as $item) {
                print_r('<a href="' . $item['handleInviteUrl'] . '"><div class="institute"><div class="institute-logo"><img src="' . $item['logoUrl'] . '" /></div><div class="invite-link">' . $item['providerName'] . '</div></div></a>');
            }
            ?>
        </div>
    </div>
</body>

</html>