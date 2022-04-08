<?php

/*
  Module pour tpdb (non classification)
*/

// déclaration du module
function m_tpdb_init() {
  return declare_module("tpdb", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_tpdb_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // on récupère la page de recherche (cookie)
  $url = "https://paleobiodb.org/classic/beginTaxonInfo";
  $ret = get_data($url);
  // on cherche le taxon
  $url = "https://paleobiodb.org/classic";
  $post = "action=basicTaxonInfo&do_redirect=1&taxon_name=" . str_replace(" ", "+", $taxon) .
          "&common_name=&author=&pubyr=&validity=valid&taxon_rank=species&exclude_taxon=";
  $header = [ 'Referer: https://paleobiodb.org/classic/beginTaxonInfo',
              'Sec-Fetch-Dest: document',
              'Sec-Fetch-Mode: navigate',
              'Sec-Fetch-Site: same-origin',
              'Sec-Fetch-User: ?1',
              'Content-Type: application/x-www-form-urlencoded',
              'Origin: https://paleobiodb.org',
              'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
              'Upgrade-Insecure-Requests: 1'];
  $ret = post_data_header($url, $post, $header, false);
  if ($ret === false) {
    logs("TPDB: échec de la recherche");
    return false;
  }

  $tbl = explode("\n", $ret);
  $id = false;
  foreach($tbl as $l) {
    if (strpos($l, "taxon_no=") !== false) {
      $id = trim(preg_replace("/^.*taxon_no=/", "", $l));
      break;
    }
  }
  
  if (!$id) {
    logs("TPDB: taxon non trouvé");
    return false;
  }
  
  // on enregistre l'identifiant (TODO: extraire le nom et l'auteur selon TPDB)
  $struct['liens']['tpdb']['id'] = $id;
  $struct['liens']['tpdb']['nom'] = $taxon;

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_tpdb_ext($struct) {
  if (isset($struct['liens']['tpdb']['id'])) {
    $data = $struct['liens']['tpdb'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $nom .= $data['auteur'];
    }
    return "{{TPDB | $id | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_tpdb_liens($struct) {
  if (isset($struct['liens']['tpdb']['id'])) {
    return "<a href='https://paleobiodb.org/classic/basicTaxonInfo?taxon_no=" .
           $struct['liens']['tpdb']['id'] . "'>TPDB</a>";
  } else {
    return false;
  }
}

