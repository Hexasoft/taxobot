<?php

/*
  Module pour inpn (non classification)
*/

// déclaration du module
function m_inpn_init() {
  return declare_module("inpn", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_inpn_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $url = "https://inpn.mnhn.fr/inpn-web-services/autocomplete/especes/recherche?texte=" .
          str_replace(" ", "+", $taxon) . "&max_resultats=99";
  $ret = get_data($url);
  if ($ret === false) {
    logs("INPN: échec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("INPN: échec de décodage des données");
    return false;
  }

  if (!isset($res->response->numFound)) {
    logs("INPN: pas de réponse pour cette recherche");
    return false;
  }
  if ($res->response->numFound == 0) {
    logs("INPN: pas de réponse pour ce taxon");
    return false;
  }
  
  // parcours
  $ok = false;
  $blob = [];
  foreach($res->response->docs as $r) {
    if (isset($r->lb_nom_valide) and ($r->lb_nom_valide == "$taxon")) {
      $blob['nom'] = $taxon;
      $blob['id'] = $r->cd_ref;
      if (isset($r->lb_auteur_valide)) {
        $blob['auteur'] = $r->lb_auteur_valide;
      }
      // si présent on extrait les les noms vernaculaires
      if (isset($r->nom_vern) and !empty($r->nom_vern)) {
        $tbl = explode(", ", $r->nom_vern);
        $struct["vernaculaire"]['INPN'] = $tbl;
      }
      break;
    }
  }
  if (empty($blob)) {
    logs("INPN: taxon non trouvé");
    return false;
  }
  
  // on l'ajoute
  $struct['liens']['inpn'] = $blob;

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_inpn_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['inpn'])) {
    $data = $struct['liens']['inpn'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{INPN | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_inpn_liens($struct) {
  if (isset($struct['liens']['inpn']['id'])) {
    return "<a href='https://inpn.mnhn.fr/espece/cd_nom/" . $struct['liens']['inpn']['id'] . "'>INPN</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_inpn_fin($struct) {
  return false;
}

