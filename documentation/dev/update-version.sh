#!/bin/bash

# Récupérer la version à partir de /configuration.php
version=$(grep -oP "\$version = '\K[0-9]+\.[0-9]+\.[0-9]+" /configuration.php)

# Créer un nouveau tag de version
git tag -a v$version -m "Version $version"

# Poussez le nouveau tag vers le référentiel
git push origin v$version
