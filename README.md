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

