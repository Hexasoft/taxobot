<?php

/*
  Gestion de la configuration
*/

$version = "1.0.23";

// état de la configuration (= valeur par défaut)
$configuration = [
  "seuil-colonnes" => 25,
  "liens-synonymes" => true,
  "liens-inf-sp" => false,
  "suivre-synonymes" => true,
  "trier-synonymes" => true,
  "juste-ext" => false,
  "inclure-invalides" => false,
  "classification" => '',
  "timeout" => 0,
  "taxon" => '',
  "domaine" => '*',
  "off" => false,
  "selecteurs" => true,
  "debug" => false,
  "debugc" => false,
  "liste" => false,
  "help" => false,
  "version" => false,
  "article" => false,
  "auteurs" => 'n',
  "plan" => false,
  "limite-listes" => -1,
  "force-regne" => '',
  "force-rang" => '',
  "ua" => '',
];

// liste des éléments configurables
// nom-de-l'option => [ type, description ]
// type = string / int / bool / flag
$liste_configuration = [
"classification" => ['string', 'La classification à utiliser (vide pour laisser le programme choisir)'],
"taxon" => ['string', 'Le nom scientifique du taxon (obligatoire)'],
"domaine" => ['string', 'Le domaine du vivant du taxon. Utilisé pour filtrer les sources utilisées'],
"article" => ['flag', 'Ne générer que la sortie de l\'article et rien d\'autre'],
"auteurs" => ['string', 'Mode de traitement des auteurs. s→standard, n→nouveau*, n1→nouveau+ajout réponse unique'],
"debug" => ['flag', 'Activer ou pas le mode debug'],
"debugc" => ['flag', 'Activer ou pas le mode debug pour le module de classification'],
"force-rang" => ['string', 'Permet d\'indiquer le rang. Utile uniquement avec l\'option \'juste-ext\' où la classification n\'est pas utilisée'],
"force-regne" => ['string', 'Permet d\'indiquer le règne (charte). Utile uniquement avec l\'option \'juste-ext\' où la classification n\'est pas utilisée'],
"help" => ['flag', 'Afficher ce message d\'aide'],
"inclure-invalides" => ['bool', 'Inclure dans les liens externes les taxons invalides trouvés'],
"juste-ext" => ['flag', 'Ne déterminer que les liens externes. Les données peuvent être incohérentes'],
"liens-inf-sp" => ['bool', 'Ajout ou non de wikiliens pour les taxons inférieurs à l\'espèce'],
"liens-synonymes" => ['bool', 'Ajout ou non de wikiliens autour des synonymes'],
"limite-listes" => ['int', 'Nombre maximum d\'éléments dans les listes (sous-taxons, synonymes). <=0 : pas de limite [256]'],
"liste" => ['flag', 'Afficher la liste des modules'],
"off" => ['string', 'Liste de modules à désactiver (noms séparés par des virgules)'],
"plan" => ['flag', 'Générer un plan-type, même quand il n\'y a pas d\'information'],
"suivre-synonymes" => ['bool', 'Si la classification indique que le taxon demandé est un synonyme, traiter la cible du synonyme'],
"seuil-colonnes" => ['int', 'Nombre-seuil d\'éléments dans une liste avant mise en colonnes'],
"selecteurs" => ['bool', 'Autorise l\'utilisation des fichiers de définition des ébauches/catégories/auteurs/…'],
"timeout" => ['int', 'Durée max de fonctionnement d\'un module. 0=pas de timeout'],
"trier-synonymes" => ['bool', 'Trier les synonymes par ordre alphabétique, sinon garder l\'ordre de la source'],
"ua" => ['string', 'Permet de définir un User Agent pour les requêtes HTTP'],
"version" => ['flag', 'Afficher la version de Taxobot']
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

// retourne la configuration en cours sous forme affichable (table)
function print_config() {
  global $liste_configuration, $configuration;

  $result = [];
  foreach($liste_configuration as $nom => $data) {
    $type = $data[0];
    if (($type == "flag") or ($type == 'bool')) {
      if ($configuration[$nom]) {
        $add = "oui";
      } else {
        $add = "non";
      }
    } else {
      $add = "'" . $configuration[$nom] . "'";
    }
    $result[] = $nom . " : " . $add;
  }
  return $result;
}