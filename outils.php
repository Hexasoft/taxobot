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

// debug : affiche les cookies
function get_cookies() {
  global $fichier_temp;
  return file_get_contents($fichier_temp);
}

// ajoute une ligne aux cookies (attention : risqué !)
function add_cookies($txt) {
  global $fichier_temp;
  file_put_contents($fichier_temp, "$txt\n", FILE_APPEND);
}

/**
 * Récupère l'User Agent de l'utilisateur en utilisant différentes méthodes.
  * @return string User Agent
 */

function define_user_agent() {
  /** Gestion des erreurs
   * Désactive le PHP warning lié à browscap.ini
   */
  error_reporting(E_ERROR | E_PARSE);
  ini_set('display_errors', 'off');

  // User Agent par défaut au cas où toutes les méthodes échouent
  $default_agent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0";

  // Tentative de récupération de l'UA via la fonction get_browser (navigateur)
  try { 
      $user_agent = get_browser(null, true);
      if ($user_agent !== false) {
          return $user_agent;
      }
  } catch (Exception $e) {}

  // Tentative de récupération de l'UA via la variable $_SERVER (serveur)
  try { 
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      if ($user_agent !== null) {
          return $user_agent;
      }
  } catch (Exception $e) {}

  // Construction d'UAs prédéfinis en fonction de la famille du système d'exploitation
  try {
      $os_family = strtoupper(PHP_OS_FAMILY);
      if ($os_family !== 'UNKNOWN' ) {
        $validUA = [
            'WINDOWS' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
            'DARWIN' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'BSD' => 'Mozilla/5.0 (X11; FreeBSD amd64; rv:109.0) Gecko/20100101 Firefox/113.0',
            'SOLARIS' => 'Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.8.0.1) Gecko/20060206 Firefox/1.5.0.1',
            'LINUX' => 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0',
        ];
        $user_agent = $validUA[$os_family];
        return $user_agent;
      }
  } catch (Exception $e) {}

  // Construction d'un UA minimaliste via les informations système PHP
  try { 
      $uname_system = php_uname('s'); // system name
      $uname_version = php_uname('v'); // system version
      $uname_machine = php_uname('m'); // machine type
      $uname_release = php_uname('r'); // release version
      $user_agent = 'Mozilla/5.0 (s:' . $uname_system . '; v:' . $uname_version . '; m:' . $uname_machine . '; rv:' . $uname_release . ')';
      return $user_agent;
  } catch (Exception $e) {
    // En cas d'échec, retourner l'UA par défaut
      $user_agent = $default_agent;
      return $user_agent;
  }
}

function user_agent($ua = null, $duree = 86400) {
  $ua_cache = 'user_agent_cache.txt';
  $path_ua_cache = join(DIRECTORY_SEPARATOR, array(__DIR__, '.cache', $ua_cache));

  if (file_exists($path_ua_cache) && (time() - filemtime($path_ua_cache) < $duree && empty($ua))) {
      return file_get_contents($path_ua_cache);
  } else {
      // Permet de renseigner un UA, sinon il est défini.
      $user_agent = !empty($ua) ? $ua : define_user_agent();

      // Encodage
      $user_agent = mb_convert_encoding($user_agent, 'UTF-8', 'auto');

      // Stocker le nouvel User Agent dans le cache
      file_put_contents($path_ua_cache, $user_agent, LOCK_EX);

      // Retourner le nouvel User Agent
      return file_get_contents($path_ua_cache);
  }
}

/**
 * Effectue une requête cURL avec des options configurables.
 *
 * @param string $url     L'URL de la requête.
 * @param string $method  La méthode de la requête (GET, POST, HEAD).
 * @param bool   $head    Indique si les en-têtes doivent être inclus dans la réponse (par défaut à false).
 * @param mixed  $post    Les données à envoyer dans la requête en cas de méthode POST.
 * @param mixed  $header  Les en-têtes de la requête (facultatif).
 * @param bool   $follow  Indique si la redirection doit être suivie (par défaut à false).
 *
 * @return mixed          Les données récupérées de la requête ou false en cas d'erreur.
 */

function curl_request($url, $method, $post = null, $header = false, $head = false, $follow = true) {
  // Configuration des paramètres de cURL
  global $fichier_temp;
  $user_agent = user_agent();
  $ch = curl_init();

  // Paramètres généraux
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_NOPROGRESS, false);
  curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function () {});
  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // config : $timeout ? $curl_timeout ? if $timeout else $curl_timeout = 5 ?
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  // Paramètres spécifiques en fonction de la méthode de la requête
  $method = strtoupper($method);
  switch ($method) {
    case 'GET':
      $follow = true;
      if ($head) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // curl_setopt($ch, CURLOPT_NOBODY, 1);
      }
      break;

    case 'POST':
      $follow = true;
      if ($head) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
      }

      if (isset($post) && $post !== false) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      }
      break;

    default:
      echo 'Erreur $method attendu pour curl_request().\n';
      return false;
  }

  // Options supplémentaires
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow ? 1 : 0); // Si $follow est vrai, la valeur sera 1, sinon, la valeur sera 0.

  if (isset($header) && $header !== false) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }

  // Exécution de la requête cURL
  $data = curl_exec($ch);
 
  // Erreurs de réponses
  $http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if (curl_errno($ch) || empty($http_response) || $http_response >= 400) {
    curl_close($ch);
    return FALSE;
}

  // Fermeture de la session cURL
  curl_close($ch);

  // Retourne les données de la requête
  return $data;
}

// Effectue une requête GET pour récupérer des données.
function get_data($url, $header = false, $head = false, $follow = true) {
  return curl_request($url, 'GET', null, $header, $head, $follow);
}

// Effectue une requête GET pour récupérer les en-têtes.
function get_data_header($url, $header = false, $head = true, $follow = true) {
  return curl_request($url, 'GET', null, $header, $head, $follow);
}

// Effectue une requête POST pour envoyer des données.
function post_data($url, $post = null, $header = false, $head = false, $follow = true) {
  return curl_request($url, 'POST', $post, $header, $head, $follow);
}

// Effectue une requête POST pour envoyer des données et récupère les en-têtes.
function post_data_header($url, $post = null, $header = false, $head = false, $follow = true) {
  return curl_request($url, 'POST', $post, $header, true, $follow);
}

/// fonctions de gestion / mise en forme de données

/**
 * Vérifie si un XML est valide en utilisant XMLReader.
 *
 * @param string $xml Le contenu XML.
 * @return bool Retourne true si le XML est valide, sinon false.
 */
function est_xml_valide($xml) {
  if (!empty($xml)) {
    $reader = new XMLReader();
    $reader->xml($xml, null, LIBXML_NOERROR | LIBXML_NOWARNING);
    $reader->setParserProperty(XMLReader::VALIDATE, true);
    return $reader->isValid();
    } else { return False; }
}

/**
* Nettoie et simplifie du XML.
*
* @param string $xml Le contenu XML.
* @return SimpleXMLElement|null Retourne un objet SimpleXMLElement ou null si une erreur survient.
*/
function get_xml($xml) {
  // Validation
  if (!est_xml_valide($xml)) {
      return null;
  }

  // Nettoyage du XML
  $cleaned_xml = $xml;
  $cleaned_xml = preg_replace(",<[a-zA-Z0-9]*:,", "<", $cleaned_xml);
  $cleaned_xml = preg_replace(",</[a-zA-Z0-9]*:,", "</", $cleaned_xml);
  $cleaned_xml = preg_replace(", [a-zA-Z0-9]*:,", " ", $cleaned_xml);
  
  // SimpleXMLElement à partir du XML nettoyé
  try {
      $simple_xml = new SimpleXMLElement($cleaned_xml, LIBXML_NOERROR);
  } catch (Exception $e) {
      $simple_xml = null;
  }

  return $simple_xml;
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

/**
 * Valide voire convertit une valeur en fonction du type spécifié.
 *
 * @param mixed $valeur La valeur à valider ou convertir ('int', 'bool').
 * @param string $type Le type attendu ('string', 'int', 'bool', 'flag', 'array').
 * @return mixed|null Retourne la valeur validée ou null en cas d'erreur.
 */
function valide_parametre($valeur, $type) {
  // Vérification si la valeur est définie et non nulle
  if (!isset($valeur)) {
    error("valide_parametre: la valeur n'est pas définie ou est null");
    return null;
  }

  switch ($type) {
    case 'string':
      // Pas de conversion nécessaire, c'est déjà une chaîne
      return $valeur;

    case 'int':
      // Conversion en entier si la valeur est numérique
      if (!is_numeric($valeur)) {
        error("valide_parametre: la valeur '$valeur' n'est pas de type '$type'");
        return null;
      }
      return (int)$valeur;

    case 'bool':
      // Conversion en booléen en fonction de certaines valeurs acceptées
      $true_bool_values = ["oui", "yes", "1", "vrai", "true"];
      if (in_array(strtolower($valeur), $true_bool_values, true)) {
        return true;
      }
      $false_bool_values = ["non", "no", "0", "faux", "false"];
      if (in_array(strtolower($valeur), $false_bool_values, true)) {
        return false;
      }
      error("valide_parametre: la valeur '$valeur' n'est pas de type '$type'");
      return null;

    case 'flag':
      // Pour le type 'flag', la valeur est toujours vraie
      return true;

    case 'array':
      // Vérification si la valeur est un tableau
      if (!is_array($valeur)) {
        error("valide_parametre: la valeur n'est pas de type '$type'");
        return null;
      }
      return $valeur;

    default:
      // Par précaution, retourne la valeur telle quelle si le type n'est pas reconnu
      return $valeur;
  }
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
  $cdate .= $bck[0] . " ";
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
  // $taille_colonnes = get_config("taille-colonnes"); // à implémenter ?
  return "{{colonnes|taille=25|\n";
}
function colonnes_fin() {
  return "}}\n";
}
function colonnes_contenu($contenu) {
  return colonnes_debut() . $contenu . colonnes_fin();
}

/**
 * Évalue la similitude de deux mots après avoir ignoré la casse et enlevé les tirets.
 * @param string $mot_1, $mot_2 Les noms à comparer
 * @return bool Retourne true si les noms sont similaires, sinon false.
 */
function est_similaire($mot_1, $mot_2) {
  $mot_1 = mb_strtolower(str_replace("-", " ", $mot_1));
  $mot_2 = mb_strtolower(str_replace("-", " ", $mot_2));
  return $mot_1 == $mot_2;
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
