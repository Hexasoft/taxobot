<?php

/*
  Module pour oepp (non classification)
*/

// déclaration du module
function m_oepp_init() {
  return declare_module("oepp", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_oepp_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $url = "https://gd.eppo.int/ajax/search?k=" . urlencode($taxon) . 
         "&s=1&m=1&t=0&l=";
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("OEPP: erreur réseau");
    return false;
  }
  
  $res = json_decode($ret);
  if ($res === null) {
    logs("OEPP: erreur d'accès aux données ou non trouvé");
    return false;
  }
  
  // on cherche l'identifiant de l'espèce
  $id = false;
  foreach($res as $r) {
    if ($r->f == $taxon) {
      $id = $r->e;
      break;
    }
  }
  if ($id === false) {
    logs("OEPP: taxon non trouvé");
    return false;
  }
  
  // on met les infos externes
  $struct['liens']['oepp']['id'] = $id;
  $struct['liens']['oepp']['nom'] = $taxon;
  $struct['liens']['oepp']['rang'] = $struct['taxon']['rang'];
  
  // on récupère la page du taxon
  $ret = file_get_contents("https://gd.eppo.int/taxon/" . $id);
  if ($ret === false) {
    logs("OEPP: impossible de récupérer les noms");
    return false;
  }
  $tbl = explode("\n", $ret);
  // on cherche l'autorité (si présente)
  $auteur = false;
  foreach($tbl as $idx => $ligne) {
    if (strpos($ligne, "Authority:") !== false) {
      $auteur = preg_replace(",^[ ]*(.*)[ ]*</li>.*$,", '$1', $tbl[$idx+1]);
      break;
    }
  }
  if ($auteur) {
    $struct['liens']['oepp']['auteur'] = trim($auteur);
  }
  // on cherche la langue française
  $noms = [];
  foreach($tbl as $idx => $ligne) {
    if (strpos($ligne, "French</td>") !== false) {
      $noms[] = trim(preg_replace(",^[ ]*<td>(.*)</td>.*$,", '$1', $tbl[$idx-1]));
    }
  }
  if (!empty($noms)) {
    $struct['vernaculaire']['OEPP'] = $noms;
  }
  
  if (!$classif) {
    return true;
  }
  
  // TODO : partie classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_oepp_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['oepp']['id'])) {
    $data = $struct['liens']['oepp'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $auteur = $data['auteur'];
    } else {
      $auteur = "";
    }
    return "{{OEPP | " . $data['id'] . " | " . $cible . " | $auteur | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_oepp_liens($struct) {
  if (isset($struct['liens']['oepp']['id'])) {
    return "<a href='https://gd.eppo.int/taxon/" . $struct['liens']['oepp']['id'] . "'>OEPP</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_oepp_fin($struct) {
  return false;
}

