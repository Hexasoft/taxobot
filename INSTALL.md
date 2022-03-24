Installation de Taxobot.

# Sous Linux

Copiez les sources dans un répertoire. Il suffit ensuite de se placer dans le répertoire correspondant
et de lancer le programme : `php ./taxobot.php -taxon "Uroplatus fimbriatus"`

# Sous Windows

Le programme a été testé sous Windows et il fonctionne.
Voir la documentation sur Wikipédia pour plus de détails :
https://fr.wikipedia.org/wiki/Utilisateur:Hexasoft/Taxobot#Portage_sur_Windows

# Dépendances

Taxobox utilise uniquement PHP. Il a été testé avec PHP 7.2, 7.3 (mais devrait fonctionner avec toutes les
versions 7.x) et 8.0.

PHP doit avoir les modules suivants installés :
* CURL (php-curl sous Ubuntu)
* json (php-json)
* mbstring (php-mbstring)
* xml (php-xml)

Note : il existe un script SHELL, mais qui est juste un outil pour créer un *template* de module. Il ne
joue aucun rôle dans le fonctionnement de Taxobot.


