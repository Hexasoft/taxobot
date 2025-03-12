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
function m_inpn_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = "https://odata-inpn.mnhn.fr/taxa/names?nameFragment=" .
         str_replace(" ", "+", $taxon) . "&lteRank=&embed=VALID_NAME&size=1";
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
         str_replace(" ", "+", $taxon) . "&embed=VALID_NAME&size=" .
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
    if ($r->binomialName != $taxon) {
      continue;
    }
    $blob['nom'] = $taxon;
    $id = $blob['id'] = $r->taxrefId;
    $compl = $r->scientificName;
    $blob['auteur'] = str_replace($blob['nom'] . " ", "", $compl);
    $ok = true;
    break;
  }

  // Vernaculaire
  foreach ($res->_embedded->taxonNames as $r2) {
    // Vérifier si des noms vernaculaires existent
    if (isset($r2->vernacularNames) && !empty($r2->vernacularNames)) {
        if (isset($r2->vernacularNames->fr) && !empty($r2->vernacularNames->fr)) {
            $ver = [];
            foreach ($r2->vernacularNames->fr as $vernacular) {
              $ver[] = $vernacular;
            }
            if (!empty($ver)) {
              $struct["vernaculaire"]['INPN'] = $ver;
            }
            break;
        }
    }
  }

  if (!$ok) {
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