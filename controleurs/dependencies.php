<?php
function check_dependencies()
{
    $dependencies = array(
        'CURL' => 'curl',
        'json' => 'json',
        'mbstring' => 'mbstring',
        'xml' => 'xml',
    );

    $missing_dependencies = array();

    foreach ($dependencies as $name => $extension) {
        if (!extension_loaded($extension)) {
            $missing_dependencies[] = $name;
        }
    }

    if (!empty($missing_dependencies)) {
        // Recommande à l'utilisateur d'installer les extensions manquantes
        echo '------------'. PHP_EOL;
        echo 'Taxobot n\'a pas pu s\'exécuter car une ou plusieurs extensions (dépendances) sont manquantes ou mal configurées.'. PHP_EOL;
        echo 'Les extensions manquantes sont : ' . implode(', ', $missing_dependencies) . '.' . PHP_EOL;

        // Ajoute les liens de téléchargement pour chaque dépendance manquante
        echo ''. PHP_EOL;
        echo 'Suivez les instructions ou téléchargez les dépendances manquantes depuis les sites officiels :' . PHP_EOL;
        foreach ($missing_dependencies as $name) {
            switch ($name) {
                case 'CURL':
                    echo 'CURL : https://curl.haxx.se/download.html' . PHP_EOL;
                    echo ''. PHP_EOL;
                    if (PHP_OS_FAMILY === 'Windows') {
                        echo "Sous Windows, nous recommandons d'utiliser GetComposer (Windows Installer) : https://getcomposer.org/download/ ". PHP_EOL;
                    }
                    break;
                case 'json':
                    echo 'json : https://www.php.net/manual/en/json.installation.php' . PHP_EOL;
                    break;
                case 'mbstring':
                    echo 'mbstring : https://www.php.net/manual/en/mbstring.installation.php' . PHP_EOL;
                    break;
                case 'xml':
                    echo 'xml : https://www.php.net/manual/en/xml.installation.php' . PHP_EOL;
                    break;
            }
        }

        // Vérifie la taille des pointeurs
        $arch = get_system_architecture();
        echo "". PHP_EOL;
        echo "L'installation manuelle requiert de connaître votre système d'exploitation.\nIl semble que vous utilisez " . PHP_OS_FAMILY . " ($arch).". PHP_EOL;
        echo "Après vérification, installez les dépendances manquantes." . PHP_EOL;
        die();
    }
}
