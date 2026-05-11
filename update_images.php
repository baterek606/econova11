<?php
$db = new PDO('sqlite:c:/xampp/htdocs/econova1/econova.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("UPDATE campaigns SET image_url = 'green cairo.png' WHERE title LIKE 'Green Cairo%'");
$db->exec("UPDATE campaigns SET image_url = 'alex beach.png' WHERE title LIKE 'Zero Waste Alexandria%'");
$db->exec("UPDATE campaigns SET image_url = 'sinai forest.png' WHERE title LIKE 'Save the Mangroves%'");

echo "Successfully updated campaign images!";
?>
