import os
import re
"""
Script visant à conserver un « code propre » (clean code) :
* Supprime des caractères non essentiels (espace, nouvelle ligne).
* Ne supprime que les caractères qui ne nuisent pas au fonctionnement ou à la lisibilité scripts PHP.
"""

def parcourir_fichiers_php(dossier):
    """
    Parcourt récursivement les fichiers PHP dans le dossier spécifié.

    :param dossier: Le chemin du dossier à parcourir.
    :return: Liste des fichiers PHP dans le dossier et ses sous-dossiers.
    """
    fichiers_php = []

    for racine, _, fichiers in os.walk(dossier):
        fichiers_php.extend([os.path.join(racine, fichier) for fichier in fichiers if fichier.endswith(".php")])

    return fichiers_php

def traiter_fichier_php(chemin):
    """
    Effectue des modifications dans un fichier PHP spécifié.
    Remplace trois retours à la ligne par deux.
    S'assure que le fichier se termine par ';' ou '}'.
    Enregistre le fichier modifié.

    :param chemin: Le chemin du fichier PHP à traiter.
    """
    with open(chemin, 'r', encoding='utf-8') as fichier:
        contenu = fichier.read()

    # Remplacer toutes les occurrences de trois LF (new line) par deux
    contenu = re.sub(r'\n{3,}', '\n\n', contenu)

    # Remplacer toutes les occurrences de LF + espace(s) + LF par deux LF
    contenu = re.sub(r'\n[ ]+\n', '\n\n', contenu)

    # Recherche de la dernière ligne contenant ";" ou "}"
    dernier_index = len(contenu.splitlines()) - 1
    while dernier_index >= 0 and ";" not in contenu.splitlines()[dernier_index] and "}" not in contenu.splitlines()[
        dernier_index]:
        dernier_index -= 1

    # Reconstituer le contenu en excluant les lignes vides après la dernière occurrence de ";" ou "}"
    contenu = '\n'.join(
        contenu.splitlines()[:dernier_index + 1]).rstrip()

    with open(chemin, 'w', encoding='utf-8') as fichier:
        fichier.write(contenu)

    print(f"Le fichier {chemin} a été traité.")

def main():
    """
    Fonction principale pour exécuter le script.
    """
    dossier_courant = r'C:\GitHub\taxobot' # Chemin Windows par défaut

    fichiers_php = parcourir_fichiers_php(dossier_courant)

    for fichier_php in fichiers_php:
        traiter_fichier_php(fichier_php)

    print("Opération terminée.")

if __name__ == "__main__":
    main()