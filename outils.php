<?php

/*
  Regroupe les différentes fonctions-outils utilisées un peu partout
*/

/// fonctions dédiées aux interactions web (GET, POST, HEADER)

// fichier temporaire pour les cookies
$fichier_temp = "";

function init_outils() {
  global $fichier_temp;
  $fichier_temp = tempnam(sys_get_temp_dir(), 'taxobot');
}
function fini_outils() {
  global $fichier_temp;
  if (file_exists($fichier_temp)) {
    unlink($fichier_temp);
  }
}

// purge les cookies
function clean_data() {
  $pid = getmypid();
  if (file_exists("/tmp/taxobot.$pid.cookies")) {
    unlink("/tmp/taxobot.$pid.cookies");
  }
}

// purge les cookies
function get_clear() {
  global $fichier_temp;
  file_put_contents($fichier_temp, "");
}

// wrapper pour récupérer les données
function get_data($url, $header=false, $follow=true) {
  global $fichier_temp;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT,
              "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");
  curl_setopt($ch, CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  if ($follow) {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  } else {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  }
  if ($header !== false) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  $data = curl_exec($ch);
  if (curl_errno($ch)) {
    curl_close($ch);
    return FALSE;
  }
  curl_close($ch);
  return $data;
}

// wrapper pour récupérer le header (GET)
function get_data_header($url, $header=false, $follow=true) {
  global $fichier_temp;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT,
              "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");
  curl_setopt($ch, CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  //curl_setopt($ch, CURLOPT_NOBODY, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  if ($header !== false) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  if ($follow) {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  } else {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  }
  $data = curl_exec($ch);
  if (curl_errno($ch)) {
    curl_close($ch);
    return FALSE;
  }
  curl_close($ch);
  return $data;
}


// wrapper pour récupérer les données (POST)
function post_data($url, $post, $header=false) {
  global $fichier_temp;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT,
              "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");
  curl_setopt($ch, CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  if ($header !== false) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  $data = curl_exec($ch);
  if (curl_errno($ch)) {
    curl_close($ch);
    return FALSE;
  }
  curl_close($ch);
  return $data;
}

// wrapper pour récupérer le header (POST)
function post_data_header($url, $post, $header=false, $follow=true) {
  global $fichier_temp;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT,
              "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");
  curl_setopt($ch, CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_NOBODY, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  if ($header !== false) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  if ($follow) {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  } else {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  }
  $data = curl_exec($ch);
  if (curl_errno($ch)) {
    curl_close($ch);
    return FALSE;
  }
  curl_close($ch);
  return $data;
}


/// fonctions de gestion / mise en forme de données

// nettoyage du XML et convertion
function get_xml($t) {
  $t2 = preg_replace(",<[a-zA-Z0-9]*:,", "<", $t);
  $t3 = preg_replace(",</[a-zA-Z0-9]*:,", "</", $t2);
  $t4 = preg_replace(", [a-zA-Z0-9]*:,", " ", $t3);
  try {
    $x = new SimpleXMLElement($t3, LIBXML_NOERROR);
  } catch (Exception $e) {
    $x = null;
  }
  return $x;
}


/// fonctions de génération de code HTML (pour "affichage")

// entête HTML
function html_head($titre) {
  echo "<!DOCTYPE HTML>\n";
  echo "<html>\n<head>\n";
  echo "  <title>$titre</title>\n";
  echo "  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n";
  echo "  <link rel='stylesheet' type='text/css' href='style.css' />\n";
  echo "  <script type='text/javascript' src='style.js'></script>\n";
  echo "</head><body style='font-size:18px'>\n";
}
function html_end() {
  echo "  </body>\n</html>\n";
}

function html_error($msg) {
  html_head("Erreur");
  echo "<pre>\n";
  echo "$msg\n";
  echo "</pre>\n";
  html_end();
}

/// fonctions de logs, erreurs…

// génère un message d'erreur non fatal
function error($msg) {
  error_log($msg);
}

$o_debug = false;
// active le mode debug
function set_debug($mode=true) {
  global $o_debug;

  $o_debug = $mode;
}
// affiche un message de debug
function debug($msg) {
  global $o_debug;

  if ($o_debug) {
    error_log("debug: $msg");
  }
}

$o_debugc = false;
// active le mode debug classification
function set_debugc($mode=true) {
  global $o_debugc;

  $o_debugc = $mode;
}
// affiche un message de debug classification
function debugc($msg) {
  global $o_debugc;

  if ($o_debugc) {
    error_log("debug-classif: $msg");
  }
}

// ajoute un message au bilan
$o_logs = "";
function logs($msg) {
  global $o_logs;
  
  $o_logs .= "$msg\n";
}
function get_logs() {
  global $o_logs;
  return $o_logs;
}


/// fonctions de gestion des paramètres : regarde la ligne de commande puis les paramètres GET

// valide la valeur d'un paramètre en fonction de son type. Retourne NULL si invalide
function valide_parametre($val, $type) {
  if ($type == 'string') {
    return $val; // c'est déjà une chaîne
  }
  if ($type == 'int') {
    if (!is_numeric($val)) {
      error("valide_parametre: la valeur '$val' n'est pas de type '$type'");
      return null;
    }
    return (int)$val;
  }
  if ($type == 'bool') {
    if (($val == "oui") or ($val == "yes") or ($val == "1") or ($val == "vrai") or ($val == "true")) {
      return true;
    }
    if (($val == "non") or ($val == "no") or ($val == "0") or ($val == "faux") or ($val == "false")) {
      return false;
    }
    error("valide_parametre: la valeur '$val' n'est pas de type '$type'");
    return null;
  }
  if ($type == 'flag') {
    return true;
  }

  return $val; // on n'arrive jamais là, mais par précaution…
}


// vérifie s'il y a des paramètres inconnus sur la ligne de commande
function parametre_inconnu() {
  global $argv, $argc;

  if (!isset($argv)) {
    return false;
  }
  $lst = list_config();
  $bad = [];
  for($i=1; $i<$argc; $i++) {
    if (!isset($argv[$i])) {
      break; // ?!
    }
    $trouve = false;
    $inc = 0;
    foreach($lst as $nom => $det) {
      if ($argv[$i] == "-$nom") {
        // trouvé
        if ($det[0] != 'flag') {
          $inc = 1;
        }
        $trouve = true;
        break;
      }
    }
    if (!$trouve) {
      $bad[] = $argv[$i];
      continue;
    }
    $i += $inc; // on passe les éventuelles options
  }
  // si pas vide
  if (empty($bad)) {
    return false;
  }
  return $bad;
}


// retourne la valeur d'un paramètre ou NULL si non présent
// $type est le type attendu : int, bool, string
function parametre($nom, $type) {
  global $argv, $argc;

  // d'abord les paramètres via la ligne de commande
  if (isset($argv)) {
    // on parcours les paramètres
    for($i=1; $i<$argc; $i++) {
      if (!isset($argv[$i])) {
        break; // non trouvé
      }
      if ($argv[$i] == "-$nom") {
        if ($type == 'flag') {
          return true; // flag : true si présent
        } else {
          if (isset($argv[$i+1])) {
            return valide_parametre(trim($argv[$i+1]), $type); // trouvé
          }
        }
      }
    }
  }
  // pas trouvé, on regarde via $_GET
  if (isset($_GET[$nom])) {
    return valide_parametre(trim($_GET[$nom]), true); // trouvé
  }
  // pas trouvé
  return null;
}

// indique si il s'agit d'un appel WEB ou ligne de commande
function est_web() {
  $tmp = php_sapi_name();
  if ($tmp == 'cli') {
    return false;
  } else {
    return true;
  }
}

// date pour les "consulté le"
$cdate = "";

// mois en français
$mois = [
  1 => 'janvier',
  2 => 'février',
  3 => 'mars',
  4 => 'avril',
  5 => 'mai',
  6 => 'juin',
  7 => 'juillet',
  8 => 'août',
  9 => 'septembre',
  10 => 'octobre',
  11 => 'novembre',
  12 => 'décembre',
];

// génère la date du jour
function dates_calcule() {
  global $cdate, $mois;

  $ddd = date("j n Y");
  $bck = explode(" ", $ddd);
  $cdate = "";
  if ($bck[0] == "1") {
    $cdate .= "{{1er}} ";
  } else {
    $cdate .= $bck[0] . " ";
  }
  $cdate .= $mois[$bck[1]] . " ";
  $cdate .= $bck[2];
}

// retourne la date calculée
function dates_recupere() {
  global $cdate;
  return $cdate;
}

// remplace et al. par le modèle, pour aide dans la zone liens externes
function rempl_et_al($txt) {
  if (strpos($txt, "{{et al.}}") !== false) {
    return $txt; // déjà fait, visiblement
  }
  return str_replace("et al.", "{{et al.}}", $txt);
}

// colonnes à activer ?
function est_colonnes($nombre) {
  $seuil_colonnes = get_config("seuil-colonnes");
  if ($seuil_colonnes == -1) {
    return false; // jamais
  } elseif ($seuil_colonnes == 0) {
    return true; // toujours
  } else {
    return ($nombre > $seuil_colonnes); // si on en a plus que la limite
  }
}

// fonctions de mise en colonnes
function colonnes_debut() {
  return "{{colonnes|taille=25|\n";
}
function colonnes_fin() {
  return "}}\n";
}
function colonnes_contenu($contenu) {
  return colonnes_debut() . $contenu . colonnes_fin();
}

// compare de façon "souple" deux noms
function est_similaire($nom1, $nom2) {
  $v1 = mb_strtolower($nom1);
  $v2 = mb_strtolower($nom2);
  $v1 = str_replace("-", " ", $v1);
  $v2 = str_replace("-", " ", $v2);
  if ($v1 == $v2) {
    return true;
  } else {
    return false;
  }
}

// vérification de la présence, ajout selon besoin
function ajoute_si_besoin(&$liste, $el, $src) {
  if (empty($liste)) {
    $liste[$el] = [ $src ];
    return;
  }
  // on parcours l'existant
  $trouve = false;
  foreach($liste as $nom => $source) {
    if  (est_similaire($el, $nom)) {
      // point d'insertion
      $trouve = $nom;
      break;
    }
  }
  // si pas trouvé on l'insert
  if ($trouve === false) {
    $liste[$el] = [ $src ];
    return;
  }
  // sinon on regarde si la source est déjà là
  if (in_array($src, $liste[$trouve])) {
    return; // déjà là
  }
  // on ajoute la source
  $liste[$trouve][] = $src;
}

// fonction de parcours, traitement, déduplication des noms en français
function conditionne_noms($struct, &$cnt) {
  $cdate = dates_recupere();
  if (!isset($struct['vernaculaire']) or empty($struct['vernaculaire'])) {
    return false;
  }
  $liste = [];
  foreach($struct['vernaculaire'] as $src => $lst) {
    foreach($lst as $el) {
      ajoute_si_besoin($liste, $el, $src);
    }
  }
  $cnt = count($liste);
  
  // on parcours le résultat pour mettre en forme (refs)
  $out = [];
  foreach($liste as $nom => $refs) {
    $tmp = $nom;
    $tbl = [];
    foreach($refs as $r) {
      $tbl[] = "{{Bioref|$r|$cdate|ref}}";
    }
    $tmp .= implode("{{,}}", $tbl);
    $out[] = $tmp;
  }
  $str = implode(", ", $out);
  
  return $str;
}

// retourne le bioref à partir du $struct
function bioref_de_struct($struct) {
  if (isset($struct['id-classification'])) {
    // on récupère la valeur
    return $struct['id-classification'];
  } else {
    return '???';
  }
}

