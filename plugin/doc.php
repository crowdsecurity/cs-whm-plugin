<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once '/usr/local/cpanel/php/WHM.php';
require_once 'includes/styles.php';
require_once 'includes/scripts.php';
require_once 'includes/navigation.php';
require_once 'includes/breadcrumbs.php';

$whmHeader = WHM::getHeaderString('CrowdSec for WHM');
$cpSession = getenv('cp_security_token');

?>

<?php
    // Header
    echo $whmHeader;
echo $crowdsecStyles;
echo $crowdsecScripts;
?>

<div class="before-content">
    <?php
        // Breadcrumbs
        echo $crowdsecBreadcrumbs;
// Navigation
echo $crowdsecNav;
?>
</div>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                labore
                et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
                ut
                aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
                culpa qui officia deserunt mollit anim id est laborum."</p>

            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                labore
                et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
                ut
                aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
                culpa qui officia deserunt mollit anim id est laborum."</p>

        </div>
    </div>
</div>


<?php
// WHM Footer
WHM::footer();
?>