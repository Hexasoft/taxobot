<?php

/*
  Module pour inpn (non classification)
*/

// déclaration du module
function m_inpn_init() {
  return declare_module("inpn", false, true, true);
}


/*
Changement :

récupérer la liste des espèces correspondant à la recherche :
https://odata-inpn.mnhn.fr/taxa/names?nameFragment=Fraxinus+excelsior&lteRank=SPECIES&embed=VALID_NAME&size=23

voir ce que fait lteRank

JSON :
_embedded
  taxonNames []
    taxrefId (int)
    binomialName (str)
    scientificName (str : bName + auth)
    rank (str = SPECIES / ?)
    valid (bool)


pour les noms en français : https://taxref.mnhn.fr/api/taxa/98921/vernacularNames

JSON :
_embedded
  vernacularNames []
    name (str, séparé par des virgules)
    langageId (str, "fra")
    locationName (str, "France, "Réunion"…)

*/
function m_inpn_infos_x(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = "https://odata-inpn.mnhn.fr/taxa/names?nameFragment=" .
         str_replace(" ", "+", $taxon) . "&lteRank=SPECIES&embed=VALID_NAME&size=1";
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
  if (!isset($res->page->totalElements) or ($res->page->totalElements == 0)) {
    logs("INPN: pas de réponse pour cette recherche");
    return false;
  }
  // requête complète
  $url = "https://odata-inpn.mnhn.fr/taxa/names?nameFragment=" .
         str_replace(" ", "+", $taxon) . "&lteRank=SPECIES&embed=VALID_NAME&size=" .
         $res->page->totalElements;
  $ret = get_data($url);
  if ($ret === false) {
    logs("INPN: échec de récupération réseau (2)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("INPN: échec de décodage des données (2)");
    return false;
  }

  // parcours
  $ok = false;
  $blob = [];
  foreach($res->_embedded->taxonNames as $r) {
    if (!$r->valid) {
      continue;
    }
    if ($r->binomialName != "$taxon") {
      continue;
    }
    $blob['nom'] = $taxon;
    $id = $blob['id'] = $r->taxrefId;
    $compl = $r->scientificName;
    $blob['auteur'] = str_replace($blob['id'] . " ", "", $compl);
    $ok = true;
    break;
  }
  if (!$ok) {
    logs("INPN: taxon non trouvé");
    return false;
  }
  
  // recherche noms vernaculaires
  $url = "https://taxref.mnhn.fr/api/taxa/$id/vernacularNames";
  $ret = get_data($url);
  if ($ret === false) {
    goto suite;
  }
  $res = json_decode($ret);
  if ($res === null) {
    goto suite;
  }
  foreach($res->xx as $r) {
  
  }

  // on l'ajoute
  $struct['liens']['inpn'] = $blob;
  
suite:
  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // pas de classification
  return false;
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_inpn_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = "https://inpn.mnhn.fr/inpn-web-services/autocomplete/especes/recherche?texte=" .
          str_replace(" ", "+", $taxon) . "&taxref_groupe1=&taxref_groupe2=&max_resultats=1";
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
  $max = $res->response->numFound;
  // on refait la recherche avec le bon nombre de résultats
  $url = "https://inpn.mnhn.fr/inpn-web-services/autocomplete/especes/recherche?texte=" .
          str_replace(" ", "+", $taxon) . "&taxref_groupe1=&taxref_groupe2=&max_resultats=$max";
  $ret = get_data($url);
  if ($ret === false) {
    logs("INPN: échec de récupération réseau (2)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("INPN: échec de décodage des données (2)");
    return false;
  }
  if (!isset($res->response->docs)) {
    echo "INPN: erreur pas response (2)\n";
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

