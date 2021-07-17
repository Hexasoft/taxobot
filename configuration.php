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
  "off" => false,
  "debug" => false,
  "liste" => false,
  "help" => false,
  "article" => false,
];

// liste des éléments configurables
$liste_configuration = [
  "classification" => ['string', 'La classification à utiliser (vide pour laisser le programme choisir)'],
  "taxon" => ['string', 'Le nom scientifique du taxon (obligatoire)'],
  "domaine" => ['string', 'Le domaine du vivant du taxon. Utilisé pour filtrer les sources utilisées'],
  "seuil-colonnes" => ['int', 'Nombre-seuil d\'éléments dans une liste avant mise en colonnes'],
  "liens-synonymes" => ['bool', 'Ajout ou non de wikiliens autour des synonymes'],
  "liens-inf-sp" => ['bool', 'Ajout ou non de wikiliens pour les taxons inférieurs à l\'espèce'],
  "suivre-synonymes" => ['bool', 'Si la classification indique que le taxon demandé est un synonyme, traiter la cible du synonyme'],
  "juste-ext" => ['bool', 'Ne déterminer que les liens externes. Les données peuvent être incohérentes'],
  "inclure-invalides" => ['bool', 'Inclure dans les liens externes les taxons invalides trouvés'],
  "debug" => ['flag', 'Activer ou pas le mode debug'],
  "liste" => ['flag', 'Afficher la liste des modules'],
  "help" => ['flag', 'Afficher ce message d\'aide'],
  "off" => ['string', 'Liste de modules à désactiver (noms séparés par des virgules)'],
  "article" => ['flag', 'Ne générer que la sortie de l\'article et rien d\'autre'],
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

