![GitHub tag (latest SemVer)](https://img.shields.io/github/v/tag/Hexasoft/taxobot?label=version&sort=semver&logo=github)
![GitHub last commit](https://img.shields.io/github/last-commit/Hexasoft/taxobot)
![PHP version](https://img.shields.io/badge/PHP-%3E%3D%207.x-8892BF.svg?logo=php)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
![Windows](https://img.shields.io/badge/Linux-lightgrey?logo=Linux)
![Linux](https://img.shields.io/badge/Windows-lightgrey?logo=windows)
![MacOS](https://img.shields.io/badge/macOS-lightgrey?logo=Apple)

# Présentation

Taxobot est un programme qui permet de générer le squelette d'un article de biologie pour un taxon donné. Il extrait les données de diverses sources de référence utilisées par le projet Biologie.

## Principe de fonctionnement

Pour générer le squelette d'un article, le programme utilise une source de classification qu'il sélectionne lui-même ou qu'on lui indique. Ensuite, il récupère les données externes de toutes les autres sources.

Ces sources sont designées au sein du projet en tant que **[modules](https://github.com/Hexasoft/taxobot/tree/main/modules)** nominatifs (adw, algaebase, etc.).

### Données
Pour un taxon indiqué, il extrait la classification biologique : rangs y compris supérieurs et inférieurs, auteur(s), synonyme(s), basionyme, nom(s) vernaculaire(s), etc.

Il recense également l'ensemble des sources externes utilisées qu'il affiche lors du débogage ou dans le contenu du squelette.

### Squelette

Le squelette généré suit les [recommandations wikipédiennes](https://fr.wikipedia.org/wiki/Projet:Biologie/Plan_%C3%A9bauche_taxon#Contenu_minimum_requis) et comprend les éléments suivants :
* Bandeau d'ébauche 
* Taxobox
* Introduction minimale
* Informations de systématique (basionyme, nom(s) vernaculaire(s), synonyme(s), etc.)
* Taxons de rangs inférieurs
* Section "Voir aussi" (liens Bioref, liens interprojets : commons et species)
* Références
* Catégories
* Portail

# Installation et utilisation
## Installation
Quel que soit votre système d'exploitation, vous pouvez utiliser Taxobot. Référez-vous aux informations fournies dans **[INSTALL.md](https://github.com/Hexasoft/taxobot/blob/main/INSTALL.md)** pour l'installation.

## Utilisation

Deux méthodes d'utilisation sont disponibles :
* « ligne de commande » : les commandes possibles sont décrites ci-dessous dans **Options**
* « WEB » : le point d'accès est **[index.php](https://github.com/Hexasoft/taxobot/blob/main/index.php)**

Exemples :
* root@xxxxx:~/taxobot# `php taxobot.php -taxon "Uroplatus fimbriatus" -classification gbif`
* https:~/taxobot.php?classification=gbif&taxon=Uroplatus+fimbriatus

## Options

Taxobox a les options suivantes :
* `-taxon "NOM TAXON"` : ***obligatoire***. Le nom scientifique du taxon à chercher
* `-classification NOM` : permet de forcer le choix d'une classification
* `-domaine NOM` : indique le domaine du vivant pour le taxon. Permet de restreindre
les modules qui seront appelés (y compris les sources de classifications utilisables)
* `-suivre-synonymes *oui/non` : si le taxon est un synonyme, traiter le nom valide
* `-inclure-invalides oui/*non` : inclure dans les liens externes et autres les taxons
non valides (synonymes, etc.)
* `-juste-ext oui/*non` : ne fournir que les liens externes (pas de classification).
Note : sans classification, certaines informations peuvent être omises ou mal présentées
* `-liens-inf-sp oui/*non` : mettre des wikiliens sur les taxons inférieurs à l'espèce
* `-liens-synonymes *oui/non` : mettre des wikiliens sur les synonymes
* `-seuil-colonnes NOMBRE` : seuil (nombre d'éléments) avant mise en multi-colonnes de l'affichage (-2=défaut (25) ; -1=toujours ; 0=jamais)
* `-debug` (flag) : Activer ou pas le mode debug
* `-liste` (flag) : Afficher la liste des modules et leurs capacités
* `-help` (flag) : Afficher ce message d'aide et rappelle les diverses options
* `-version` (flag) : Afficher la version de Taxobot
* `-off` (string) : Liste de modules à désactiver (noms séparés par des virgules)
* `-selecteurs` (bool) : Autorise l'utilisation des fichiers de définition des ébauches/catégories/auteurs/…
* `-article` (flag) : Ne générer que la sortie de l'article et rien d'autre
* `-auteurs` (string) : Mode de traitement des auteurs. s→standard*, n→nouveau, n1→nouveau+ajout réponse unique

En mode WEB ces options sont passées en `GET` : l'option a le même nom (sans le tiret) et sa valeur est celle passée. Exemple : `…&seuil-colonnes=30&…`

## Personnalisation
Plusieurs éléments sont personnalisables, référez-vous au dossier **[Documentation](https://github.com/Hexasoft/taxobot/tree/main/documentation)** pour plus d'informations.

# Licence et participation

Bien qu'intégralement dédié à Wikipédia en français, son code peut être réutilisé pour d'autres projets, tout ou partie, comme l'extraction des méta-données.

Il est placé sous licence ***GNU General Public License v3.0*** ([en savoir plus](https://github.com/Hexasoft/taxobot/blob/main/LICENSE)).

Si vous souhaitez participer à son amélioration, vous pouvez le télécharger, le tester, consulter le fichier à l'intention des [développeurs](https://github.com/Hexasoft/taxobot/blob/main/DEVEL.md) et signaler les bugs rencontrés ou faire part de suggestions en ouvrant une [pull request](https://github.com/Hexasoft/taxobot/pulls).
