<?php

/*
  Module pour ebird (non classification)
*/

// déclaration du module
function m_ebird_init() {
  return declare_module("ebird", false, true, ['oiseau']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_ebird_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $url = "https://ebird.org/explore";
  $ret = get_data($url);
  if ($ret === false) {
    logs("eBird: problème réseau");
    return false;
  }
  // on cherche l'URL d'accès aux "détails"
  $tbl = explode("\n", $ret);
  $url = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, "data-suggest-url") !== false) {
      $url = preg_replace(',^.*data-suggest-url=",', '', $ligne);
      $url = preg_replace(',%QUERY".*$,', '', $url);
      $url = str_replace('&amp;', '&', $url);
      break;
    }
  }
  if (!$url) {
    logs("eBird: impossible de trouver l'URL d'accès");
    return false;
  }
  $url = $url . str_replace(' ', '%20', $taxon);
  $url = str_replace('en_US', 'fr_FR', $url);
  $ret = get_data($url);
  if ($ret === false) {
    logs("eBird: problème réseau (2)");
    return false;
  }
  $obj = json_decode($ret);
  if ($obj === null) {
    logs("eBird: échec de décodage de la réponse");
    return false;
  }
  $trouve = false;
  foreach($obj as $o) {
    $code = $o->code;
    $nom = $o->name;
    $tmp = explode(" - ", $nom);
    if (count($tmp) == 1) {
      $nom = preg_replace('/^[ ]*/', '', $nom);
      $nom = preg_replace('/[ ]*$/', '', $nom);
      $vern = false;
    } else {
      $nom = preg_replace('/^[ ]*/', '', $tmp[1]);
      $nom = preg_replace('/[ ]*$/', '', $nom);
      $vern = preg_replace('/^[ ]*/', '', $tmp[0]);
      $vern = preg_replace('/[ ]*$/', '', $vern);
    }
    if ($nom == $taxon) {
      $trouve = true;
      break;
    }
  }
  if (!$trouve) {
    logs("eBird: taxon non trouvé");
    return false;
  }

  $blob = [];
  $blob['id'] = $code;
  $blob['nom'] = $nom;
  $struct['liens']['ebird'] = $blob;
  if ($vern) {
    $struct['vernacualire']['eBird'] = [ $vern ];
  }

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_ebird_ext($struct) {
  if (isset($struct['liens']['ebird']['id'])) {
    $data = $struct['liens']['ebird'];
    $cdate = dates_recupere();
    return "{{eBird | " . $data['id'] . " | ''" . $data['nom'] . "'' | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_ebird_liens($struct) {
  return false;
}