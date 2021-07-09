Installation de Taxobot.

# Sous Linux

Copiez les sources dans un répertoire. Il suffit ensuite de se placer dans le répertoire correspondant
et de lancer le programme : `php ./taxobot.php -taxon "Uroplatus fimbriatus"`

# Sous Windows

Le programme n'a pas été testé sous Windows. Il devrait pouvoir fonctionner. Il y a toutefois deux éléments
qui peuvent poser des problèmes :

* l'utilisation de `getpid()` pour obtenir un identifiant unique
* les chemin de fichiers temporaires (utilisés pour les cookies)

# Dépendances

Taxobox utilise uniquement PHP. Il n'a été testé qu'avec PHP 7.2 mais devrait fonctionner avec toutes les
versions 7.x.

PHP doit avoir le module CURL installé.

Note : il existe un script SHELL, mais qui est juste un outil pour créer un *template* de module. Il ne
joue aucun rôle dans le fonctionnement de Taxobot.


