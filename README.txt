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
Le programme n'a été testé que sur Linux, avec une version 7.2 de PHP (il faut le module
PHP 'curl').
Le code doit certainement fonctionner avec les versions plus récentes de PHP (en tout cas
de version majeure 7).
Un éventuel portage sur Windows doit être possible, mais ce n'est pas ma spécialité.
Les deux seuls éléments qui pourraient poser problème sous Windows sont :
* le chemin vers certains fichiers temporaires (utilisés pour les cookies)
* l'obtention du PID du programme, utilisé pour avoir un nom de fichier unique

Le programme doit être appelé depuis son répertoire de base.

* Créer un nouveau module
-------------------------

Pour ajouter un nouveau module il faut créer un fichier modules/mod_XXXX.php, où XXXX est
le nom du module. Ce nom ne doit contenir que des minuscules (pas d'espace, tiret, etc.).
Ce fichier doit obligatoirement contenir les deux fonctions suivantes :
* m_XXXX_init() : cette fonction est appelée pour initialiser le module. Elle peut faire
  tous les préparatifs nécessaires (si besoin), et doit impérativement déclarer le module
  en terminant par "return declare_module("XXXX", classif, externe, domaine);"
  Les paramètres sont : "XXXX" : le nom du module ; classif : true si le module peut générer
  une classification, false sinon ; externe : true si le module peut générer des liens
  externes ou autres données (noms vernaculaires…) ; domaine : true si le module traite tous
  les domaines du vivant, sinon une table des domaines couverts (exemple : ['animal','végétal'])
* m_XXXX_infos(&$struct, $classif) : fonction collectant les informations liées à XXXX et
  stockant les résultats dans la table $struct. Si $classif=TRUE le module est appelé pour
  générer (en plus des autres données) une classification. La fonction doit retourner TRUE si
  tout c'est bien passé, FALSE sinon. Utiliser la fonction 'logs("MESSAGE")' pour enregistrer
  des warnings ou des erreurs pour affichage ultérieur par le programme.
En sus un module fournira généralement les fonctions :
* m_XXXX_ext($struct) : cette fonction a pour rôle de retourner des éléments Bioref à insérer
  dans la section "Voir aussi". Si plusieurs doivent être insérés, retourner une table de
  chaînes, sinon une chaîne direct. Si rien n'est à retourner retourner FALSE.
  La fonction peut récupérer la date courante mise en forme (pour les modèles Bioref) via un
  appel à la fonction 'dates_recupere()'.
* m_XXXX_liens($struct) : retourne un lien (HTML) vers la cible de l'élément Bioref (pour
  affichage d'aide). Mêmes remarque que pour 'm_XXXX_ext()' pour le format du retour.

Un module peut utiliser diverses fonctions disponibles pour faciliter et regrouper les
traitements. En voici une liste non exhaustive :
* dates_recupere() : retourne une date formatée pour le champ "consulté le" des modèles
* get_config('NOM') : retourne la valeur de l'élément de configuration indiqué. Utile pour
  adapter le comportement du module (voir configuration.php pour les entrées existantes)
* wp_met_italiques($nom, $rang, $regne) : retourne une mise en forme (italiques) de nom du
  taxon selon son rang et son règne. Des paramètres non obligatoires sont présents aussi,
  pour mettre des wikiliens, etc. (voir dans wikipedia.php la fonction)
* get_data() / post_data() : génère une requête GET ou POST (possibilité d'insérer des
  données HEADER, gestion des cookies, etc.)
* logs() : permet d'enregistrer des messages (warning, error) pour affichage futur


