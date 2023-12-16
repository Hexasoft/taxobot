<?php
// Specify the target URL
$target_url = "/web/index.php";

// Perform the redirection
header("Location: " . $target_url, true, 302);
exit;
?>