#!/bin/bash

if [ "$1" = "" -o "$2" = "" ]
then
  echo "Usage : $0 TYPE NOM-MODULE"
  echo "TYPE : classif ou nonclassif"
  exit 1
fi

TYPE="$1"
NOM="$2"

if [ -e ./modules/mod_"$NOM".php ]
then
  echo "La cible existe déjà"
  exit 1
fi

if [ "$TYPE" = "classif" ]
then
  SOURCE="mod_classif.php"
else
  SOURCE="non_nonclassif.php"
fi

cp ./modules/templates/"$SOURCE" ./modules/mod_"$NOM".php

sed -i -e "s/XXX/$NOM/g" ./modules/mod_"$NOM".php

