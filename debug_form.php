<?php
// Debug what the form is actually sending
if ($_POST) {
    echo "<h3>Raw POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Individual Values:</h3>";
    echo "Name: '" . ($_POST['name'] ?? 'NOT SET') . "'<br>";
    echo "IP Address: '" . ($_POST['ip_address'] ?? 'NOT SET') . "'<br>";
    echo "Device Group: '" . ($_POST['device_group'] ?? 'NOT SET') . "'<br>";
    echo "Monitor Type: '" . ($_POST['monitor_type'] ?? 'NOT SET') . "'<br>";
    echo "Port: '" . ($_POST['port'] ?? 'NOT SET') . "'<br>";
    echo "Location: '" . ($_POST['location'] ?? 'NOT SET') . "'<br>";
    echo "Description: '" . ($_POST['description'] ?? 'NOT SET') . "'<br>";
    echo "Critical: '" . (isset($_POST['critical_device']) ? 'YES' : 'NO') . "'<br>";
}
?>
 
<form method="POST">
    <p>Name: <input type="text" name="name" value="TestDevice"></p>
IP: <input type="text" name="ip_address" value="1.2.3.4"></p>
    <p>Group: <select name="device_group"><option value="routers">Routers</option></select></p>
    <p>Type: <select name="monitor_type"><option value="ping">Ping</option></select></p>
    <p>Port: <input type="text" name="port" value=""></p>
    <p>Location: <input type="text" name="location" value="Test Location"></p>
    <p>Description: <input type="text" name="description" value="Test Description"></p>
    <p><input type="checkbox" name="critical_device"> Critical</p>
    <p><input type="submit" value="Test Form"></p>
</form>