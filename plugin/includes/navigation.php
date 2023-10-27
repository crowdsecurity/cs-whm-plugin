
<?php
$cpSession = getenv('cp_security_token');
$crowdsecNav = <<<CS_PLUGIN_NAV
<div class="navigation">
    <ul>
        <li><a href="$cpSession/cgi/crowdsec/doc.php">Documentation</a></li>
        <li><a href="$cpSession/cgi/crowdsec/settings.php">Settings</a></li>
        <li><a href="$cpSession/cgi/crowdsec/status.php">Status</a></li>
        <li><a href="$cpSession/cgi/crowdsec/metrics.php">Metrics</a></li>
    </ul>
</div>
CS_PLUGIN_NAV
?>