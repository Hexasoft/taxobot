import os
import re
from collections import defaultdict
"""
Script visant à conserver des conventions de nommage homogènes :
* Fournit des statistiques par casse (flatcase, camelCase, etc.)
* Fournit des informations pour les fichiers multi casses
* Fournit le nom des fonctions et variables
* Formate en wikitext pour exportation des résultats
"""

def parcourir_fichiers_php(dossier):
    fichiers_php = []
    for racine, _, fichiers in os.walk(dossier):
        fichiers_php.extend([os.path.join(racine, fichier) for fichier in fichiers if fichier.endswith(".php")])
    return fichiers_php


def extraire_variables_et_fonctions(chemin_fichier):
    with open(chemin_fichier, 'r', encoding='utf-8') as fichier:
        contenu = fichier.read()
        variables = set(re.findall(r'\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)', contenu))
        fonctions = set(re.findall(r'\bfunction\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(', contenu))
        return {'Variable': variables, 'Fonction': fonctions}


def nom_fichier_github(chemin_fichier):
    chemin_fichier = chemin_fichier.replace('\\', '/')
    match = re.search(r"(C:[/]GitHub[/]taxobot[/])(.*[/])?(.+\.php)", chemin_fichier)
    if match:
        chemin_prefixe = match.group(2) or ''
        nom_fichier = match.group(3)
        return f"[https://github.com/Hexasoft/taxobot/blob/main/{chemin_prefixe}{nom_fichier} {nom_fichier}]"
    else:
        return "-Lien non généré-"


def identifier_casse(entite):
    if re.match(r'^[a-z]*$', entite):
        return 'flatcase'
    elif re.match(r'^[a-z]*([-][a-z]*)+$', entite):
        return 'kebab-case'
    elif re.match(r'^[a-z]*[A-Z][a-z]*$', entite):
        return 'camelCase'
    elif re.match(r'^[A-Z][a-z]*[A-Z][a-z]*$', entite):
        return 'PascalCase'
    elif re.match(r'^[a-z]*([_][a-z]*)+$', entite):
        return 'snake_case'
    elif re.match(r'^[A-Z]*([_][A-Z]*)+$', entite):
        return 'CONSTANT_CASE'
    elif re.match(r'^[A-Z]*([-][A-Z]*)+$', entite):
        return 'COBOL-CASE'
    else:
        return '?'


def lister_casses(variables_fonctions_globales):
    statistiques = defaultdict(lambda: defaultdict(list))
    fichiers_multicasses = []

    for type_entite, entites_globales in variables_fonctions_globales.items():
        for fichier, entites in entites_globales.items():
            casse_par_fichier = defaultdict(list)

            for entite in entites:
                casse = identifier_casse(entite)
                casse_par_fichier[casse].append(entite)

            for casse, entites in casse_par_fichier.items():
                statistiques[type_entite][casse].extend(entites)

            if len(set(casse_par_fichier.keys())) > 1:
                fichiers_multicasses.append((fichier, dict(casse_par_fichier)))
    print("== Statistiques ==")
    for type_entite, cas_entites in statistiques.items():
        print(f"=== {type_entite} ===")
        for cas, entites in cas_entites.items():
            print(f"* {cas} ({len(entites)}) : {entites}")

    print("=== Fichiers ===")
    if fichiers_multicasses:
        print("Liste des fichiers ayant plusieurs casses différentes :")
        for fichier, casse_par_fichier in fichiers_multicasses:
            print(f"* {nom_fichier_github(fichier)}")
            for cas, entites in casse_par_fichier.items():
                print(f"** {cas} : {entites}")


if __name__ == "__main__":
    dossier = r'C:\GitHub\taxobot'
    fichiers_php = parcourir_fichiers_php(dossier)

    variables_fonctions_globales = {'Variable': defaultdict(set), 'Fonction': defaultdict(set)}

    for fichier_php in fichiers_php:
        resultats = extraire_variables_et_fonctions(fichier_php)
        variables_fonctions_globales['Variable'][fichier_php].update(resultats['Variable'])
        variables_fonctions_globales['Fonction'][fichier_php].update(resultats['Fonction'])

    lister_casses(variables_fonctions_globales)