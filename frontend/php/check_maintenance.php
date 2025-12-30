<?php
$maintenanceFlagFile = __DIR__ . '/../../.maintenance';

if (file_exists($maintenanceFlagFile)) {
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Retry-After: 3600'); 


    include_once(__DIR__ . '/maintenance.html');

    exit();
}
?>

