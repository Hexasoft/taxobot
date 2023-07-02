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
articles de biologie de wikipédia en français.

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
require_once "selecteurs.php";
require_once "wikipedia.php";
require_once "rendu.php";
require_once "data_pays.php";

$web = false;

$start_time = microtime(true);

// certains outils nécessitent une initialisation
init_outils();

// fonction d'affichage d'une erreur, adapté au mode courant
function sortie_erreur($msg) {
  global $web;
  if ($web) {
    $msg .= "<br/><br/>Logs :<br/>" . get_logs();
    html_error($msg);
  } else {
    echo $msg;
    echo "\n\nLogs :\n" . get_logs();
  }
}

// fonction d'affichage du résultat
function sortie_resultat($article, $liens, $taxon) {
  global $web, $version;
  $juste_article = get_config('article');
  if ($web) {
    html_head("Résultats pour $taxon − Taxobot v$version");
    echo "<table width='99%'>\n";
    echo "<tr><td width='80%' style='vertical-align: top;'>\n";
    echo "<i>Informations sur la requête : </i>";
    $tmp = print_config();
    foreach($tmp as $t) {
      echo "$t ; ";
    }
    echo "</br>\n";
    echo "<br/><hr><button id='copybutton' onclick='copyFunction()'>Copier le wikitexte</button><div id='wikitexte' width='99%'>";
    echo $article;
    echo "</div>";
    echo "</td><td style='vertical-align: top;'>Liens externes :<ul>";
    foreach($liens as $lien) {
      if (empty($lien)) {
        continue;
      }
      if (is_array($lien)) {
        foreach($lien as $l) {
          echo "<li>$l</li>\n";
        }
      } else {
        echo "<li>$lien</li>\n";
      }
    }
    echo "</ul></td></tr>\n";
    echo "</table>\n";
    echo "<hr>";
    echo "Infos et logs des traitements :<br/>\n";
    echo "<pre>\n";
    echo get_logs();
    echo "</pre>\n";
    html_end();
  } else {
    echo "$article";
    if (!$juste_article) {
      echo "\n-----\n";
      $tmp = "";
      if (is_array($liens)) {
        foreach($liens as $lien) {
          if (is_array($lien)) {
            $tmp .= implode("\n", $lien) . "\n";
          } else {
            $tmp .= "$lien\n";
          }
        }
      } else {
        $tmp .= "$liens\n";
      }
      $tmp = str_replace("\n\n", "\n", $tmp);
      $tmp = str_replace("\n\n", "\n", $tmp);
      echo $tmp;
      echo "\n-----\n";
      $tmp = print_config();
      echo "Configuration :\n";
      echo "Taxobot v$version\n";
      foreach($tmp as $t) {
        echo "$t ; ";
      }
      echo "\n";
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

if (get_config('version')) {
  global $version;
  echo "Taxobot version $version\n";
  fini_outils();
  die();
}

// on vérifie qu'il n'y a pas de paramètre inconnu
$ret = parametre_inconnu();
if ($ret !== false) {
  echo "Paramètre(s) inconnu(s) :\n";
  echo implode("\n", $ret);
  echo "\nUtilisez l'option '-help' pour la liste des options disponibles.\n";
  die(1);
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
$debugc = get_config("debugc");
set_debugc($debugc);

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
if ($possibles === false) {
  logs("Domaine '$domaine' non reconnu");
  sortie_erreur("Domaine '$domaine' non reconnu.");
  fini_outils();
  die(1);
}
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

// on vérifie que la classification existe (si elle vient des options)
$tmp = classif_modules();
if (!in_array($classification, $tmp)) {
  logs("Le module '$classification' n'existe pas où ne gère pas la classification.");
  sortie_erreur("Le module '$classification' n'existe pas où ne gère pas la classification.");
  fini_outils();
  die(1);
}

// on prépare les données
$struct = [];
$struct['taxon']['nom'] = $taxon;
$struct['classification'] = $classification;
$struct['domaine'] = $domaine;

// on récupère la date (pour les liens)
dates_calcule();

// est-ce qu'on ne veut que les liens externes
$justext = get_config('juste-ext');

// on lance la classification
if (!$justext) { // si juste-ext → rien coté classification
  debug("Lancement de la classification");
  $class = "m_" . $classification . "_infos";
  $elaps1 = microtime(true);
  $ret = $class($struct, true);
  $elaps2 = microtime(true);
  
  debug("Temps d'exécution du module (classification) $classification : " . number_format($elaps2-$elaps1, 2) . "s");
  logs("Temps d'exécution du module (classification) $classification : " . number_format($elaps2-$elaps1, 2) . "s");

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
    debug("Affinage du domaine");
    $domaine = $struct['regne'];
    // on regénère la liste des modules possibles à partir de ce domaine
    $possibles = modules_possibles($domaine);
    if ($possibles === false) {
      logs("Domaine '$domaine' non reconnu");
      sortie_erreur("Domaine '$domaine' non reconnu.");
      fini_outils();
      die(1);
    }
    logs("Affinage domaine : '*' → '$domaine'");
    logs("Modules possibles (màj) : " . implode(", ", $possibles));
  }
} else {
  // juste-ext → on met quelques infos pour éviter les erreurs
  $struct['juste-ext'] = true;
  $struct['taxon']['rang'] = 'espèce';
  $struct['regne'] = 'animal';
  // si indiqué, on "force" le règne
  if (get_config("force-regne") != "") {
    $struct['regne'] = get_config("force-regne");
  }
}

$timeout = get_config('timeout');
$timed = false;

if ($timeout > 0) {
  debug("Initialisation 'timer' ($timeout)");
  pcntl_async_signals(true);
  pcntl_signal(SIGALRM, function($signal) use (&$timed) {
    debug("Déclenchement du timeout");
    $timed = true;
  });
}

// on lance tous les autres modules (sauf celui de la classification)
foreach($possibles as $id) {
  if ($classification == $id) {
    if (!$justext) { // on traite tout si juste-ext
      continue;
    }
  }
  $f = "m_" . $id . "_infos";
  debug("Appel module $id (externe)");
  // on nettoie les précédents cookies (éventuels)
  get_clear();
  $elaps1 = microtime(true);
  $timed = false;
  if ($timeout > 0) {
    // si demandé on fixe le timeout
    debug("Armement timer [$timeout]");
    pcntl_alarm($timeout);
  }
  $ret = $f($struct, false); // en mode données (pas classification)
  $elaps2 = microtime(true);
  if (($timeout > 0) and (!$timed)) {
    // on coupe le timeout s'il n'a pas servi
    pcntl_alarm(0);
  }
  // si on a été coupé par timeout on l'indique
  if (($timeout > 0) and $timed) {
    logs("Module '$id' : timeout ($timeout sec.)");
    debug("Module '$id' : timeout ($timeout sec.)");
    $ret = false; // le module n'a pas fonctionné normalement
  }
  
  logs("Temps d'exécution du module (externe) $id : " . number_format($elaps2-$elaps1, 2) . "s");
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
  debug("Appel module $id (liens)");
  if (function_exists($f)) {
    $ret = $f($struct);
  } else {
    $ret = false;
  }
  if ($ret !== false) {
    $aide[] = $ret;
  }
}

$end_time = microtime(true);

// durée totale d'exécution (après ça ça se serait plus affiché)
logs("Durée totale d'exécution : " . number_format($end_time-$start_time, 2) . "s");

// on génère la sortie
if ($justext) {
  $resu = rendu($struct, true);
} else {
  $resu = rendu($struct);
}

logs("Disclaimer: attention, Taxobot n'a pas pour but de générer un article prêt à l'emploi");
logs("Même si tout est fait pour préparer du code le plus abouti possible, c'est au rédacteur");
logs("de s'assurer de la cohérence des informations, de l'absence d'erreurs, de typos, etc.");

// on affiche, selon le mode
sortie_resultat($resu, $aide, $struct['taxon']['nom']);

// terminaison
fini_outils();
die(0);

