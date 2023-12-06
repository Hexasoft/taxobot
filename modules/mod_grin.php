<?php

/*
  Module pour grin (non classification)
*/

// déclaration du module
function m_grin_init() {
  return declare_module("grin", false, true, true);
}

// teste les différentes recherches
function m_grin_cherche($nom) {
  // si plusieurs noms → espèce
  $tst = strpos($nom, " ");
  if ($tst === false) {
    $espece = false;
  } else {
    $espece = true;
  }

  // dans l'ordre : famille, sous-famille, tribue, sous-tribue, genre, espèce
  if (!$espece) {
    $url = "https://npgsweb.ars-grin.gov/gringlobal/taxon/taxonomysearch?t=family";
    $header = [
      'Content-Type: application/x-www-form-urlencoded',
      'Origin: https://npgsweb.ars-grin.gov',
      'Referer: https://npgsweb.ars-grin.gov/gringlobal/taxon/taxonomysearch?t=family',
      'Upgrade-Insecure-Requests: 1',
      'Sec-Fetch-Dest: document',
      'Sec-Fetch-Mode: navigate',
      'Sec-Fetch-Site: same-origin',
      'Sec-Fetch-User: ?1',
      'TE: trailers'
    ];
    $post = "__EVENTTARGET=&__EVENTARGUMENT=&__LASTFOCUS=&ctl00%24MainContent%24TabName=family" .
      "&ctl00%24MainContent%24txtBFamily=&ctl00%24MainContent%24txtBGenus=&ctl00%24MainContent%24txtBSpecies=" .
      "&ctl00%24MainContent%24txtBCommon=&ctl00%24MainContent%24chkSpecies=on&ctl00%24MainContent%24ddlOrder=" .
      "&ctl00%24MainContent%24ctrlFamily%24chkAccepted=on&ctl00%24MainContent%24ctrlFamily%24hFamily=&ctl00%24MainContent%24txtFamily=" .
      "&ctl00%24MainContent%24txtInfraFam=$nom&ctl00%24MainContent%24cbinfraf=on&ctl00%24MainContent%24cbpatho=on" .
      "&ctl00%24MainContent%24cbfern=on&ctl00%24MainContent%24cbgymn=on&ctl00%24MainContent%24cbangi=on" .
      "&ctl00%24MainContent%24btnFamily=Search&ctl00%24MainContent%24txtGenus=&ctl00%24MainContent%24txtInfraGen=" .
      "&ctl00%24MainContent%24txtCommonG=&ctl00%24MainContent%24txtSpecies=&ctl00%24MainContent%24txtInfraSpec=&ctl00%24MainContent%24txtCommon=";

    $res = post_date($url, $post, $header, false);
    if (!$res === false) {
      return false;
    }
  }

}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_grin_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // non implémenté pour le moment
  logs("GRIN: non implémenté");
  return false;

  // requête pour les cookies
  $ret = get_data("https://npgsweb.ars-grin.gov/gringlobal/taxon/taxonomysearch");

  $cible = m_grin_cherche($taxon);
  if ($cible === false) {
    logs("GRIN: taxon non trouvé");
    return false;
  }

  if (!$classif) {
    return true;
  }
  return false; // on ne fait pas la classification
}

// génération des liens externes (modèles dans Voir aussi)
function m_grin_ext($struct) {
  return false;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_grin_liens($struct) {
  return false;
}
