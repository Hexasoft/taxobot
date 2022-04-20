<?php

/*
  Module pour algaebase (non classification)
*/

$alg_rangs = [
  'clade' => 'clade',
  'type' => 'type',
  'group' => 'groupe',
  'unspecified' => 'non-classé',
  'subform' => 'sous-forme',
  'form' => 'forme',
  'variety' => 'variété',
  'pathovar' => 'pathovar',
  'cultivar' => 'cultivar',
  'subspecies' => 'sous-espèce',
  'hybrid' => 'hybride',
  'species' => 'espèce',
  'subserie' => 'sous-série',
  'serie' => 'série',
  'subsection' => 'sous-section',
  'section' => 'section',
  'subgenus' => 'sous-genre',
  'genus' => 'genre',
  'subtribe' => 'sous-tribu',
  'tribe' => 'tribu',
  'supertribe' => 'super-tribu',
  'infratribe' => 'infra-tribu',
  'subfamily' => 'sous-famille',
  'family' => 'famille',
  'null2' => 'épifamille',
  'superfamily' => 'super-famille',
  'microorder' => 'micro-ordre',
  'infraorder' => 'infra-ordre',
  'suborder' => 'sous-ordre',
  'order' => 'ordre',
  'superorder' => 'super-ordre',
  'subcohort' => 'sous-cohorte',
  'cohort' => 'cohorte',
  'supercohort' => 'super-cohorte',
  'subterclass' => 'subter-classe',
  'infraclass' => 'infra-classe',
  'subclass' => 'sous-classe',
  'class' => 'classe',
  'superclass' => 'super-classe',
  'megaclass' => 'super-classe',
  'microphylum' => 'micro-embranchement',
  'infraphylum' => 'infra-embranchement',
  'subphylum' => 'sous-embranchement',
  'phylum' => 'embranchement',
  'superphylum' => 'super-embranchement',
  'infradivision' => 'infra-division',
  'subdivision' => 'sous-division',
  'division' => 'division',
  'subphylum subdivision' => 'sous-division',
  'phylum division' => 'division',
  'superdivision' => 'super-division',
  'infrakingdom' => 'infra-règne',
  'null' => 'rameau',
  'subkingdom' => 'sous-règne',
  'kingdom' => 'règne',
  'superkingdom' => 'super-règne',
  'subdomain' => 'sous-domaine',
  'domain' => 'domaine',
  'superdomain' => 'super-domaine',
  'empire' => 'empire',
  'kingdom' => 'royaume',
  'subkingdom' => 'sous-royaume',
];

function alg_rang($rang) {
  global $alg_rangs;
  
  if (isset($alg_rangs[$rang])) {
    return $alg_rangs[$rang];
  }
  return "NOTFOUND-$rang";
}



// déclaration du module
function m_algaebase_init() {
  return declare_module("algaebase", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_algaebase_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // cookie
  $ret = get_data("https://www.algaebase.org/search/");
  // API key
  $url = "https://api2.algaebase.org/auth/";
  $header = [
    "Accept: */*",
    "Origin: https://www.algaebase.org",
    "DNT: 1",
    "Referer: https://www.algaebase.org/",
    "Sec-Fetch-Dest: empty",
    "Sec-Fetch-Mode: cors",
    "Sec-Fetch-Site: same-site",
    "TE: trailers",
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec d'API");
    return false;
  }
  $key = trim($ret);
  $offset = 0;
  $found = false;
  while(true) {
    // recherche
    $url = "https://api2.algaebase.org/v1.3/species?&scientificname=" . str_replace(" ", "%20", $taxon) . "&offset=0";
    $header = [
      "Accept: */*",
      "Origin: https://www.algaebase.org",
      "DNT: 1",
      "Referer: https://www.algaebase.org/",
      "Sec-Fetch-Dest: empty",
      "Sec-Fetch-Mode: cors",
      "Sec-Fetch-Site: same-site",
      "TE: trailers",
      "abapikey: $key",
    ];
    $ret = get_data($url, $header);
    if ($ret === false) {
      logs("AlgaeBase: échec de la recherche");
      break;
    }
    $res = json_decode($ret);
    if ($res === false) {
      logs("AlgaeBase: échec d'analyse de la recherche");
      break;
    }
    // on parcours les résultats pour voir si on a le bon
    foreach($res->result as $r) {
      $nom = $r->{"dwc:scientificName"};
      $nom = str_replace(" " . $r->{"dwc:scientificNameAuthorship"}, "", $nom);
      if ($nom == $taxon) {
        // trouvé
        $found = $r;
        break;
      }
    }
    // fin ?
    if ($offset >= $res->_pagination->_total_number_of_results) {
      break;
    } else {
      $offset += 50;
    }
  }

  // non trouvé
  if ($found === false) {
    logs("AlgaeBase: taxon non trouvé");
    return false;
  }
  
  // on prépare les infos de base
  $blob = [];
  $blob['auteur'] = $found->{"dwc:scientificNameAuthorship"};
  if (isset($found->{"dwc:namePublishedInYear"}) and !empty($found->{"dwc:namePublishedInYear"})) {
    $blob['auteur'] .= " " . $found->{"dwc:namePublishedInYear"};
  }
  $blob['rang'] = alg_rang($found->{"dwc:taxonRank"});
  $blob['nom'] = $taxon;
  $blob['id'] = $found->{"dwc:acceptedNameUsageID"};
  $struct['liens']['algaebase'] = $blob;

  if (!$classif) {
    return true;
  }
  
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_algaebase_ext($struct) {
  if (isset($struct['liens']['algaebase']['id'])) {
    $data = $struct['liens']['algaebase'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    $nom = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $nom .= " " . $data['auteur'];
    }
    return "{{AlgaeBASE espèce | $id | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_algaebase_liens($struct) {
  if (isset($struct['liens']['algaebase']['id'])) {
    return "<a href='https://www.algaebase.org/search/species/detail/?species_id=" .
           $struct['liens']['algaebase']['id'] . "'>AlgaeBase</a>";
  } else {
    return false;
  }
}

