#!/bin/bash

if [ "$1" = "" ]
then
  echo "Usage : $0 NOM-MODULE"
  exit 1
fi

if [ -e ./modules/mod_"$1".php ]
then
  echo "La cible existe déjà"
  exit 1
fi

cp ./templates/mod_XXX.php ./modules/mod_"$1".php

sed -i -e "s/XXX/$1/g" ./modules/mod_"$1".php

