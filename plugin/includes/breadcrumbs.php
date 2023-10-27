
<?php
$cpSession = getenv('cp_security_token');
$crowdsecBreadcrumbs = <<<CS_PLUGIN_BREADCRUMBS
<div>
    <ol class="breadcrumb">
        <li><a href="$cpSession/scripts/command?PFILE=main">
                <span class="glyphicon glyphicon-home"></span>
            </a>
        </li>
        <li><a href="$cpSession/scripts/command?PFILE=Plugins">Plugins</a></li>
        <li class="active">
            <a href="$cpSession/cgi/crowdsec/doc.php">CrowdSec</a>
        </li>
    </ol>
</div>
CS_PLUGIN_BREADCRUMBS
?>