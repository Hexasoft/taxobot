<?php


/*
  point d'entrée de Taxobot
*/

// message d'aide (-help)
$h_description = <<<EOT
Taxobot est un programme de génération de squelette d'articles sur des taxons.
Il utilise un certain nombre de sources (modules) pour extraire des données sur le
taxon, puis met ces données en forme pour proposer un squelette d'article qui
reprend ces informations et les pré-formate en suivant les conventions des
articles biologiques de wikipédia en français.

Taxobot prend en entrée le nom du taxon. Si le module à utiliser pour la classification
n'est pas spécifié il détermine lui-même la classification suivie (en filtrant
éventuellement par le domaine s'il est précisé). La classification retourne au
minimum : le nom+rang+auteur du taxon et sa classification supérieure. Elle peut aussi
retourner : les taxons inférieurs, les synonymes, le basionyme.
Une fois les données de classification extraites il parcours les modules qui
correspondent au domaine du taxon pour obtenir diverses informations additionnelles
(liens externes, noms vernaculaires…).
Quelques modules spécifiques retournent également des informations plus spécifiques :
catégorie, portail, ébauche, projets frères…
Au final la fonction de rendu utilise toutes ces informations pour construire un
article, en adaptant les différents textes et liens aux conventions biologiques.

EOT;


// les éléments nécessaires
require_once "outils.php";
require_once "configuration.php";
require_once "modules.php";
require_once "wikipedia.php";
require_once "rendu.php";

$web = false;

init_outils();

// fonction d'affichage d'une erreur, adapté au mode courant
function sortie_erreur($msg) {
  global $web;
  if ($web) {
    $msg .= "\n\nLogs :\n" . get_logs();
    html_error($msg);
  } else {
    echo $msg;
    echo "\n\nLogs :\n" . get_logs();
  }
}

// fonction d'affichage du résultat
function sortie_resultat($article, $liens) {
  global $web;
  $juste_article = get_config('article');

  if ($web) {
  
  } else {
    echo "$article";
    if (!$juste_article) {
      echo "\n-----\n";
      if (is_array($liens)) {
        foreach($liens as $lien) {
          if (is_array($lien)) {
            echo implode("\n", $lien) . "\n";
          } else {
            echo "$lien\n";
          }
        }
      } else {
        echo "$liens\n";
      }
      echo "\n-----\n";
      echo get_logs() . "\n";
    }
  }
}

// on récupère les éléments de configuration
$liste = list_config();
foreach($liste as $c => $data) {
  $type = $data[0];
  $ret = parametre($c, $type);
  if ($ret === null) {
    continue; // non indiqué, ou valeur fausse
  }
  // on enregistre la valeur
  set_config($c, $ret);
}

// est-ce un appel WEB ou ligne de commande ?
$web = est_web();

// usage ?
if (get_config('help')) {
  echo $h_description;
  echo "Options :\n";
  $lst = list_config();
  foreach($lst as $nom => $data) {
    $type = $data[0];
    $desc = $data[1];
    echo "  -$nom ($type) : $desc\n";
  }
  fini_outils();
  die();
}

// si on demande la liste des modules, on traite (et on quitte)
if (get_config('liste')) {
  $modules = cherche_modules();
  foreach($modules as $m) {
    require_once "./modules/$m";
  }
  $id_modules = noms_vers_identifiants($modules);
  foreach($id_modules as $id) {
    $f = "m_" . $id . "_init";
    $ret = $f();
    if ($ret == false) {
      logs("Échec d'initialisation du module '$id'");
    }
  }
  $ret = affiche_modules();
  echo "Nom Classification Liens-externes Domaines\n";
  echo implode("\n", $ret) . "\n";
  fini_outils();
  die(0);
}

// on applique les éventuels éléments de configuration autre
$tmp = get_config('off');
if (is_string($tmp) && !empty($tmp)) {
  desactive_modules($tmp);
}

// on récupère les éléments clés
$taxon = get_config("taxon");
$classification = get_config("classification");
$domaine = get_config("domaine");
$debug = get_config("debug");
set_debug($debug);

logs("Initial: taxon=$taxon ; classification=$classification ; domaine=$domaine");
if ($web) {
  logs("Appel via interface WEB");
} else {
  logs("Appel via ligne de commande");
}
if (empty($taxon)) {
  logs("Taxon manquant");
  sortie_erreur("Taxon manquant.");
  fini_outils();
  die(1);
}


// on récupère les modules, et on les initialise
$modules = cherche_modules();
if (($modules === false) or (empty($modules))) {
  logs("Aucun module trouvé");
  sortie_erreur("Aucun module trouvé.");
  fini_outils();
  die(1);
}
// liste des noms associés
$id_modules = noms_vers_identifiants($modules);
debug("Modules : " . print_r($id_modules, true));

// on charge tous les modules
foreach($modules as $m) {
  require_once "./modules/$m";
}

// on initialise tous les modules
foreach($id_modules as $id) {
  $f = "m_" . $id . "_init";
  $ret = $f();
  if ($ret == false) {
    logs("Échec d'initialisation du module '$id'");
    sortie_erreur("Échec d'initialisation du module '$id'.");
    fini_outils();
    die(1);
  }
}

// on récupère la liste des modules qui peuvent traiter le domaine
$possibles = modules_possibles($domaine);
debug("Possibles : " . print_r($possibles, true));
logs("Modules possibles (pour domaine '$domaine') : " . implode(", ", $possibles));

// si la classification n'est pas indiquée, on cherche la plus adapté
if (empty($classification)) {
  $classification = meilleure_classification($domaine);
  if ($classification === false) {
    logs("Meilleure_classification : non trouvé");
    sortie_erreur("Meilleure_classification : non trouvé.");
    fini_outils();
    die(1);
  }
  logs("Classification choisie : $classification");
} else {
  logs("Classification sélectionnée : $classification");
}

// on prépare les données
$struct = [];
$struct['taxon']['nom'] = $taxon;
$struct['classification'] = $classification;
$struct['domaine'] = $domaine;

// on récupère la date (pour les liens)
dates_calcule();

// on lance la classification
$class = "m_" . $classification . "_infos";
$ret = $class($struct, true);

if (!$ret) {
  logs("Taxon non récupéré pour la classification");
  sortie_erreur("Taxon non récupéré pour la classification.");
  fini_outils();
  die(1);
} else {
  logs("Classification récupérée via le module '$classification'");
}

// si le domaine est "*" on l'affine en fonction du résultat
if ($domaine == "*") {
  $domaine = $struct['regne'];
  // on regénère la liste des modules possibles à partir de ce domaine
  $possibles = modules_possibles($domaine);
  logs("Affinage domaine : '*' → '$domaine'");
  logs("Modules possibles (màj) : " . implode(", ", $possibles));
}

// on lance tous les autres modules (sauf celui de la classification)
foreach($possibles as $id) {
  if ($classification == $id) {
    continue;
  }
  $f = "m_" . $id . "_infos";
  $ret = $f($struct, false); // en mode données (pas classification)
  if ($ret == false) {
    logs("Échec de récupération d'informations du module '$id' (non classification)");
  } else {
    logs("Données non classification ajoutées (possiblement) via le module '$id'");
  }
}

// on lance la génération de toutes les URLs (partie "aide")
$aide = [];
foreach($possibles as $id) {
  $f = "m_" . $id . "_liens";
  if (function_exists($f)) {
    $ret = $f($struct);
  } else {
    $ret = false;
  }
  if ($ret !== false) {
    $aide[] = $ret;
  }
}

// on génère la sortie
$resu = rendu($struct);

// on affiche, selon le mode
sortie_resultat($resu, $aide);

// terminaison : affichage des logs
fini_outils();
die();

