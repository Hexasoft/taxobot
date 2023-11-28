<?php
$extensions = [
    "curl" => "https://www.php.net/manual/fr/book.curl.php",
    "json" => "https://www.php.net/manual/fr/book.json.php",
    "mbstring" => "https://php.net/manual/fr/book.mbstring.php",
    "xml" => "https://www.php.net/manual/fr/book.xml.php",
    "pcntl" => "https://www.php.net/manual/fr/book.pcntl.php",
];

foreach ($extensions as $ext_id => $url) {
    if (!extension_loaded($ext_id)) {
        echo "$url\n";
    }
}
?>
