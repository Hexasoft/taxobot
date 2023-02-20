# Présentation

Taxobot est un programme de génération de squelette d'article biologique,
utilisant les diverses sources référencées par le projet Biologie.

Il est intégralement dédié à Wikipédia en français (même si toute la partie
extraction des méta-données pourrait facilement être réutilisée).

# Principe de fonctionnement

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

# Options

Taxobox a les options suivantes :
* `-taxon "NOM TAXON"` : obligatoire. Le nom scientifique du taxon à chercher
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
* `-liste` (flag) : Afficher la liste des modules
* `-help` (flag) : Afficher ce message d'aide
* `-version` (flag) : Afficher la version de Taxobot
* `-off` (string) : Liste de modules à désactiver (noms séparés par des virgules)
* `-selecteurs` (flag) : Autorise l'utilisation des fichiers de définition des ébauches/catégories/auteurs/…
* `-article` (flag) : Ne générer que la sortie de l'article et rien d'autre
* `-auteurs` (string) : Mode de traitement des auteurs. s→standard*, n→nouveau, n1→nouveau+ajout réponse unique

Ces options sont celles de la ligne de commande. En mode WEB ces options sont passées en
`GET` : l'option a le même nom (sans le tiret) et sa valeur est celle passée. Exemple :
`…&seuil-colonnes=30&…`

Il existe également l'option `-liste` qui retourne la liste des modules et leurs capacités,
et l'option `-help` qui affiche un message rappelant les diverses options.

