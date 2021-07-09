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
    

# Ajouter un nouveau module

Un module est une unité qui s'occupe d'interagir avec une source d'informations afin de stocker les
méta-données associées dans la structure commune, et de générer des éléments de rendu.


