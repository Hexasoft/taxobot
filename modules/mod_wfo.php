<?php

/*
  Module pour wfo (non classification)
*/

// déclaration du module
function m_wfo_init() {
  return declare_module("wfo", false, true, true);
}

function wfo_donnees_entree($tbl, $idx) {
  $res = [];
  // récupération du code et du nom scientifique
  $code = preg_replace(",^.*/taxon/wfo-([0-9]*)[;].*$,", '$1', $tbl[$idx]);
  if (($code == "") or ($code == $tbl[$idx])) {
    return false;
  }
  $nom = preg_replace(",^.*<em>(.*)</em>.*$,", '$1', $tbl[$idx]);
  if (($nom == "") or ($nom == $tbl[$idx])) {
    return false;
  }
  // l'auteur
  $auteur = trim($tbl[$idx+1]);
  // le statut
  $l = trim($tbl[$idx+4]);
  $statut = preg_replace(',^.*entryStatus">([^<]*).*$,', '$1', $l);
  $statut = preg_replace(',#.*$,', '', $statut);
  
  $res['nom'] = $nom;
  $res['id'] = $code;
  $res['auteur'] = $auteur;
  $res['statut'] = $statut;
  return $res;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_wfo_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $url = "http://www.worldfloraonline.org/search?query=" .
         urlencode(str_replace(" ", "+", $taxon)) .
         "&limit=99&start=0&sort=&view=list";
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("WFO: erreur réseau");
    return false;
  }
  
  // parcours des lignes pour trouver les propositions
  $tbl = explode("\n", $ret);
  $ok = false;
  foreach($tbl as $idx => $ligne) {
    if (strpos($ligne, "h4Results") === false) {
      continue;
    }
    // on récupère les données associées
    $blob = wfo_donnees_entree($tbl, $idx);
    if ($blob === false) {
      continue;
    }
    if ($blob['nom'] != $taxon) {
      continue;
    }
    if ($blob['statut'] != "Accepted Name") {
      continue;
    }
    $ok = true;
    break;
  }

  if (!$ok) {
    logs("WFO: taxon non trouvé");
    return false;
  }
  
  // on met le lien externe
  $struct['liens']['wfo']['nom'] = $blob['nom'];
  $struct['liens']['wfo']['id'] = $blob['id'];
  $struct['liens']['wfo']['auteur'] = $blob['auteur'];
  
  if (!$classif) {
    return true;
  }
  
  // TODO : partie classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_wfo_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['wfo']['id'])) {
    $data = $struct['liens']['wfo'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne'], false, false);
    if (isset($data['auteur'])) {
      $auteur = $data['auteur'];
    } else {
      $auteur = "";
    }
    return "{{WFO | " . $data['id'] . " | " . $cible . " | " . $auteur . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_wfo_liens($struct) {
  if (isset($struct['liens']['wfo']['id'])) {
    return "<a href='http://www.worldfloraonline.org/taxon/wfo-" . $struct['liens']['wfo']['id'] . "'>WFO</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_wfo_fin($struct) {
  return false;
}

