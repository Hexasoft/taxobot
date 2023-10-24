<?php

/*
  Recherche des infos CITES
*/


function m_cites_init() {
  return declare_module("cites", false, true, true);
}

function m_cites_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $url = "https://www.speciesplus.net/api/v1/auto_complete_taxon_concepts?taxonomy=cites&taxon_concept_query=" .
         urlencode($taxon);
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("CITES: erreur réseau");
    return false;
  }
  $tmp = json_decode($ret);
  if ($tmp === null) {
    logs("CITES: erreur de décodage des informations (1)");
    return false;
  }
  if (!isset($tmp->auto_complete_taxon_concepts) or empty($tmp->auto_complete_taxon_concepts)) {
    logs("CITES: taxon non trouvé");
    return false;
  }
  $id = false;
  foreach($tmp->auto_complete_taxon_concepts as $e) {
    if (isset($e->full_name) and ($e->full_name == $taxon)) {
      $id = $e->id;
      break;
    }
  }
  if ($id === false) {
    logs("CITES: taxon non trouvé (possible synonyme)");
    return false;
  }
  
  // maintenant on récupère les infos pour cet ID
  $url = "https://www.speciesplus.net/api/v1/taxon_concepts/$id";
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("CITES: erreur réseau (2)");
    return false;
  }
  $tmp = json_decode($ret);
  if ($tmp === null) {
    logs("CITES: erreur de décodage des informations (2)");
    return false;
  }
  if (!isset($tmp->taxon_concept->cites_listings[0])) {
    logs("CITES: taxon sans classement ?");
    return false; // pas de classement ?!
  }
  
  $struct['liens']['cites']['nom'] = $tmp->taxon_concept->full_name;
  $struct['liens']['cites']['auteur'] = $tmp->taxon_concept->author_year;
  $struct['liens']['cites']['annexe'] = $tmp->taxon_concept->cites_listings[0]->species_listing_name;
  $struct['liens']['cites']['date'] = $tmp->taxon_concept->cites_listings[0]->effective_at_formatted;
  $struct['liens']['cites']['lien'] = $id;
  
  // noms en français
  $lst = [];
  if (isset($tmp->taxon_concept->common_names)) {
    foreach($tmp->taxon_concept->common_names as $cn) {
      if (isset($cn->lang) and ($cn->lang == "French")) {
        $x = explode(", ", $cn->names);
        foreach($x as $xx) {
          $lst[] = $xx;
        }
      }
    }
  }
  if (!empty($lst)) {
    $struct['vernaculaire']['CITES espèce'] = $lst;
  }

  if (!$classif) {
    return true;
  }
  
  // TODO : partie classification
  return false;
}


function m_cites_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['cites']['lien'])) {
    $data = $struct['liens']['cites'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne'], false, false);
    if (isset($data['auteur'])) {
      $auteur = $data['auteur'];
    } else {
      $auteur = "";
    }
    return "{{CITES species+ | " . $data['lien'] . " | " . $cible . " | " . $auteur . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

function m_cites_liens($struct) {
  if (isset($struct['liens']['cites']['lien'])) {
    return "<a href='https://www.speciesplus.net/#/taxon_concepts/" . $struct['liens']['cites']['lien'] .
           "/legal'>CITES species+</a>";
  } else {
    return false;
  }
}

