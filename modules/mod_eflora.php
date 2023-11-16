<?php

/*
  Module pour eflora (non classification)
*/

// déclaration du module
function m_eflora_init() {
  return declare_module("eflora", false, true, ['végétal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_eflora_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = 'http://www.efloras.org/browse.aspx?flora_id=0&name_str=' . str_replace(" ", "+", $taxon);

  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("eFlora: erreur réseau");
    return false;
  }

  $ret = str_replace("\r", " ", $ret);
  $tbl = explode("\n", $ret);
  foreach($tbl as $ligne) {
    $ligne = trim($ligne);
    if (empty($ligne)) {
      continue;
    }
    if (strpos($ligne, "florataxon.aspx") === FALSE) {
      continue;
    }
    if (strpos($ligne, "flora_id") === FALSE) {
      continue;
    }
    if (strpos($ligne, "taxon_id") === FALSE) {
      continue;
    }
    // on récupère le flora_id et le taxon_id
    $tmp = explode("=", $ligne);
    $tmp = explode("&", $tmp[3]);
    $fid = $tmp[0];
    if (($fid != 1) and ($fid != 2) and ($fid != 5)) {
      continue;
    }
    $tmp = explode("=", $ligne);
    $tmp = explode("'", $tmp[4]);
    $tid = $tmp[0];
    // on met le lien externe
    $struct['liens']['eflora']['nom'] = $taxon;
    $struct['liens']['eflora']['id'][] = [ $fid, $tid ];
  }

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_eflora_ext($struct) {
  $cdate = dates_recupere();

  if (isset($struct['liens']['eflora']['id'])) {
    $nom = $struct['liens']['eflora']['nom'];
    $out = [];
    foreach($struct['liens']['eflora']['id'] as $id) {
      $out[] = "{{EFloras | " . $id[0] . " | " . $id[1] . " | $nom | consulté le=$cdate }}";
    }
    return $out;
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_eflora_liens($struct) {
  $cdate = dates_recupere();

  if (isset($struct['liens']['eflora']['id'])) {
    $nom = $struct['liens']['eflora']['nom'];
    $out = [];
    foreach($struct['liens']['eflora']['id'] as $id) {
      $out[] = "<a href='http://www.efloras.org/florataxon.aspx?flora_id=" . $id[0] . "&taxon_id=" . $id[1] . "'>eFlora (" . $id[0] . ")</a>";
    }
    return $out;
  } else {
    return false;
  }
}