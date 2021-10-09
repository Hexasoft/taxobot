Taxobot
=======

* Présentation
--------------

Taxobot est un programme de génération de squelette d'article biologique,
utilisant les diverses sources référencées par le projet Biologie.

Il est intégralement dédié à Wikipédia en français (même si toute la partie
extraction des méta-données pourrait facilement être réutilisée).

* Principe de fonctionnement
----------------------------

Le programme utilise une source de classification (qu'il sélectionne
lui-même ou qu'on lui indique) pour générer les méta-données de classification
du taxon indiqué : rang, auteur(s), rangs supérieurs, rangs inférieurs,
synonymes, basionyme, noms vernaculaires, etc. (selon la source et le taxon
tout ou partie de ces informations sont remontées, mais rang + auteurs +
rangs supérieurs sont obligatoires, sinon il y a erreur).

Ensuite le programme récupère les données externes de toutes les autres sources
(lien externe Bioref + d'autres données éventuelles comme noms vernaculaires…).

Enfin à partir de toutes ces méta-données il génère un squelette d'article :
* ébauche
* taxobox
* introduction minimale
* informations de systématique (basionyme, noms vernaculaires, synonymes…)
* taxons de rangs inférieurs
* section "Voir aussi" (liens Bioref + commons/species)
* références
* catégories et portails

* Utilisation
-------------

Cette partie s'appuie sur l'utilisation en ligne de commande. Les fonctionnalités
via l'interface WEB sont similaires (mais les paramètres sont saisis grâce à un
formulaire WEB).

L'option "-taxon" est obligatoire, c'est le nom (scientifique) du taxon cherché.
S'il contient un espace, il faut protéger le nom : … -taxon "Genre espece"

Il y a ensuite des options qui contrôlent le déroulement des traitements :
* -classification NOM : permet de forcer le choix d'une classification
* -domaine NOM : indique le domaine du vivant pour le taxon. Permet de restreindre
                 les modules qui seront appelés (y compris les sources de
                 classifications utilisables)
* -suivre-synonymes *oui/non : si le taxon est un synonyme, traiter le nom valide
* -inclure-invalides oui/*non : inclure dans les liens externes et autres les taxons
                                non valides (synonymes, etc.)
* -juste-ext oui/*non : ne fournir que les liens externes (pas de classification).
                        Note : sans classification, certaines informations peuvent
                        être omises ou mal présentées
* -liens-inf-sp oui/*non : mettre des wikiliens sur les taxons inférieurs à l'espèce
* -liens-synonymes *oui/non : mettre des wikiliens sur les synonymes
* -seuil-colonnes NOMBRE : seuil (nombre d'éléments) avant mise en multi-colonnes de
                           l'affichage (-2=défaut (25) ; -1=toujours ; 0=jamais)

* Résultat
----------

Si une erreur survient pour la classification, un message décrivant le problème est
affiché (typiquement "taxon non trouvé").

Lorsqu'il y a un résultat sont affichés :
* le squelette d'article générée
* la liste des liens vers chaque lien externe (pour les tester directement)
* la liste des logs de travail du programme (modules qui ont répondu, erreurs
  rencontrées, etc.)
* un résumé des options sélectionnées

* Fonctionnement détaillé
-------------------------

En premier lieu le programme récupère les paramètres.
Ensuite il va déterminer quelle source utiliser pour la classification :
* si elle est indiquée il utilise celle-là
* si un domaine (animal/végétal/…) est spécifié il va filtrer les sources possibles
* au final il sélectionne la source parmi les possibles (si plusieurs, en utilisant
  le statut de "par défaut" ou les priorités)

Le programme appelle alors le module associé à la source, qui va remplir les informations
nécessaires dans la structure des méta-données, plus les informations additionnelles
s'il y en a, ou retourner une erreur s'il n'est pas possible de les trouver.

Si la classification est trouvée, le programme va éventuellement affiner le domaine
(afin de réduire la liste des modules appelés − certains seraient appelés pour rien car
ne prenant pas en charge le domaine trouvé).

Il va ensuite appeler tous les autres modules (restant) en mode "données externes" (donc
pas sur les données de classification elles-mêmes) afin que chacun puisse insérer les
informations éventuelles (liens externes, noms vernaculaires, etc.).

Pour terminer il va appeler la fonction de rendu, qui va générer les différentes parties
de l'article en utilisant les méta-données enregistrées par l'ensemble des modules.
Il va également générer une liste de liens directs vers les ressources concernées, afin
d'aider le rédacteur en lui fournissant des liens directs lui permettant d'aller valider
les divers contenus ou d'accéder à des informations complémentaires pour étoffer l'article.

* Installation
--------------

Pour installer Taxobot sur sa machine il suffit de récupérer l'ensemble des fichiers.
Le programme a été testé sur Linux (Ubuntu 18.04, 20.04, 21.04), avec des version 7.x de PHP.
Il a été également testé sous Windows 10 (<insérer l'URL de la doc sur WP>).
Pour PHP il faut également les modules suivant :
  - php-curl ; php-json ; php-xml ; php-mbstring ; php-readline

Le programme doit être appelé depuis son répertoire de base.

