Éléments pour les développeurs.

# Description des méta-données

L'ensemble du programme utilise une structure de méta-données sur le taxon en cours d'analyse.

Certains éléments sont spécifiques à son module, d'autres sont communs à plusieurs sources, et utilisés
pour le rendu.

Ci-dessous la description des différents champs :
* taxon : les champs décrivant le taxon
 * nom : le nom scientifique du taxon. Cette valeur est toujours définie à l'appel d'un module
 * rang : rang du taxon (rang selon wikipédia)
 * auteur : zone auteur associée
* liens : une table pour les liens externes. Cette table doit être indexée par le nom du module. La fonction
`m_XXXX_ext()` sera appelée automatiquement pour générer les liens externes. Le contenu n'est pas contraint
* redirection : pour le module appelé pour classification, si un autre taxon est traité (option pour suivre
les synonymes) cette entrée doit contenir a minima un champ 'nom' avec le taxon initial, et le module doit
modifier le contenu de 'taxon'
* classification : le nom du module ayant généré la classification
* classification-taxobox : le nom du paramètre 'classification' du modèle Taxobox début
* regne : la charte à utiliser dans les modèles Taxobox (animal, végétal, etc.)
* basionyme : les informations sur le basionyme
 * nom
 * auteur
 * source : doit correspondre au modèle Bioref associé
 * rang : optionnel (si absent le rang du taxon est utilisé)
* sous-taxons : liste des taxons de rang inférieur
 * liste : une table de taxons
  * nom
  * auteur
  * rang
 * source : doit correspondre au modèle Bioref associé
* vernaculaire : une liste par source de noms vernaculaires. Chaque liste doit être indexée par le nom
du modèle Bioref associé.
* synonymes : une liste par source de synonymes. Chaque liste doit être indexée par le nom
du modèle Bioref associé. Chaque élément de la liste contient :
 * nom
 * auteur
 * rang

# Ajouter un nouveau module

Un module est une unité qui s'occupe d'interagir avec une source d'informations afin de stocker les
méta-données associées dans la structure commune, et de générer des éléments de rendu.

## Fichier PHP
Il faut créer un fichier nommé `modules/mod_XXXX.php`, où XXXX est le nom du module (uniquement composé
de minuscules).

## Initialisation
Le module doit obligatoirement contenir une fonction `m_XXXX_init()` qui peut faire toutes les initialisations
nécessaires, et doit obligatoirement retourner :
`return declare_module("XXXX", classif, ext, domaine, [poids], [défaut]);`

XXXX est bien sûr le nom du module. 'classif' vaut `true` si le module est capable de générer des classifications.
'ext' vaut `true` si le module peut générer des liens externes. 'domaine' liste les domaines auquels le module
peut s'appliquer. Mettre `true` pour tous les domaines, sinon une table de chaînes contenant les domaines (au
sens charte des taxobox : animal, végétal…). 'poids' indique l'ordre dans lequel sont appelés les modules, et
est aussi utilisé pour choisir lorsque plusieurs classifications sont possibles. Ne pas utiliser 'défaut'.

## Récupération de données
Le module doit également obligatoirement contenir une fonction `m_XXXX_infos(&$struct, $classif)`.
Cette fonction permet au module de récupérer les données de sa source cible et de les stocker dans la structure
passée (voir plus haut le format des méta-données).

Si `$classif` est vrai la fonction est appelée pour générer une classification. Dans tous les cas elle doit fournir
les données externes (si présentes).

Cette fonction doit retourner `true` si tout c'est bien passé, `false` sinon.

## Génération de liens externes
Le cas échéant le module doit fournir une fonction `m_XXXX_ext($struct)`. Son rôle est de retourner des liens
externes formatés. La fonction peut retourner `false` (pas de données), une chaîne (sans retour à la ligne) ou
une table de chaînes si plusieurs liens externes sont à insérer.

## Génération d'URL
Le cas échéant le module doit fournir une fonction `m_XXXX_liens($struct)`. Son rôle est le même que celle au
dessus (`m_XXXX_ext()`) mais elle retourne des liens au format HTML (utilisé dans la zone d'aide dans les
résultats, pour permettre un accès direct aux cibles des liens externes, sans devoir passer par une
prévisualisation).

## Fonctions d'aide
Il existe plusieurs fonctions permettant de faciliter le travail :

* `dates_recupere()` : retourne la date du jour, formatée pour le champ *consulté le* dans les modèles
* `wp_met_italiques(nom, rang, regne)` : retourne le *nom* scientifique avec ou sans italiques, selon le *rang*
et le *regne* associé. Il y a deux paramètres additionnels : voir `wikipedia.php` pour plus de détails
* `get_data(…)` et `post_data(…)` : effectuer une requête HTTP *GET* et *POST*. Voir `outils.php` pour plus
de détails
* `get_config(nom)` : retourne la valeur de l'élément de configuration *nom*. Voir `configuration.php` pour
plus de détails
* `logs(message)` : permet d'insérer un message dans la liste des messages (qui est affichée à la fin). Permet
aux modules d'informer sur les problèmes rencontrés, etc. En particulier lorsque la fonction `m_XXXX_infos()`
retourne `false` elle doit au préalable avoir ajouté un message expliquant la raison de cet échec.


