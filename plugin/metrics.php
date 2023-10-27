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
            <p>METRICS here</p>
        </div>
    </div>
</div>

<?php
// WHM Footer
WHM::footer();
?>