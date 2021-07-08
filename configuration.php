<?php

/*
  Gestion de la configuration
*/

// état de la configuration
$configuration = [
  "seuil-colonnes" => 25,
  "liens-synonymes" => true,
  "liens-inf-sp" => false,
  "suivre-synonymes" => true,
  "juste-ext" => false,
  "inclure-invalides" => false,
  "classification" => '',
  "taxon" => '',
  "domaine" => '*',
  "debug" => false,
  "liste" => false,
  "help" => false,
];

// liste des éléments configurables
$liste_configuration = [
  "classification" => 'string',
  "taxon" => 'string',
  "domaine" => 'string',
  "seuil-colonnes" => 'int',
  "liens-synonymes" => 'bool',
  "liens-inf-sp" => 'bool',
  "suivre-synonymes" => 'bool',
  "juste-ext" => 'bool',
  "inclure-invalides" => 'bool',
  "debug" => 'bool',
  "liste" => 'flag',
  "help" => 'flag',
];

// retourne la configuration
function get_all_config() {
  global $configuration;
  
  return $configuration;
}

// modifie une valeur de la configuration. Retourne false si l'élément de configuration n'existe pas
function set_config($nom, $val) {
  global $configuration;
  
  if (!isset($configuration[$nom])) {
    error("set_config: '$nom' non reconnu comme élément de configuration");
    return false;
  }
  $configuration[$nom] = $val;
  return true;
}

// retourne une valeur de la configuration. Retourne null si l'élément de configuration n'existe pas
function get_config($nom) {
  global $configuration;
  
  if (!isset($configuration[$nom])) {
    error("get_config: '$nom' non reconnu comme élément de configuration");
    return null;
  }
  return $configuration[$nom];
}

// retourne le nom de chaque élément de configuration
function list_config() {
  global $liste_configuration;
  
  return $liste_configuration;
}

