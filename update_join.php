<?php
$file = 'c:/xampp/htdocs/econova1/campaigns.php';
$content = file_get_contents($file);
$content = str_replace("alert('You joined this campaign!');", "joinCampaign(<?php echo \$camp['id']; ?>);", $content);
file_put_contents($file, $content);
echo "Updated campaigns.php";
?>
