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
  'unknown' => 'non-classé',
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
  return declare_module("algaebase", true, true,
        ['champignon','algue','végétal','algue','archaea','bactérie','protiste'], 990);
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
      // à faire : ajouter la détection de "non valide"
      // 1. choisir valide exact 2. si synonyme redir (selon) 3. invalide (lien ext)
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
  
  // pour classification on met en place les infos
  $struct['taxon'] = $struct['liens']['algaebase'];
  // charge de la classif
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  
  // publication originale
  if (isset($found->{"dcterms:bibliographicCitation"}) and !empty($found->{"dcterms:bibliographicCitation"})) {
    $struct['originale'] = $found->{"dcterms:bibliographicCitation"};
    $struct['originale'] = str_replace(['<i>','</i>'], "''", $struct['originale']);
    if (isset($found->pdfs[0]->pdf_url)) {
      $struct['originale'] .= " ([" . $found->pdfs[0]->pdf_url . " PDF])";
    }
  }
  // diverses infos
  $tbl = [];
  if (isset($found->{"dwc:isMarine"}) and $found->{"dwc:isMarine"}) {
    $tbl[] = "Cette espèce est marine";
  }
  if (isset($found->{"dwc:isFreshwater"}) and $found->{"dwc:isFreshwater"}) {
    $tbl[] = "Cette espèce vit en eau douce";
  }
  if (isset($found->{"dwc:isTerrestrial"}) and $found->{"dwc:isTerrestrial"}) {
    $tbl[] = "Cette espèce est terrestre";
  }
  if (isset($found->{"nameOrigin"}) and $found->{"nameOrigin"}) {
    $tbl[] = "Étymologie : ''" . preg_replace("/[.][ ]*$/", "", $found->{"nameOrigin"}) . "''";
  }
  if (!empty($tbl)) {
    $struct['description']['AlgaeBASE'] = $tbl;
  }
  
  // on récupère la classification
  $idgenre = $found->genusID;
  $url = "https://api2.algaebase.org/v1.3/genus/$idgenre";
  $header = [
    "Accept: application/json",
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
    logs("AlgaeBase: échec de récupération de la classification");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec de décodage de la classification");
    return false;
  }
  
  if (!isset($res->classification)) {
    logs("AlgaeBase: échec de d'extraction de la classification");
    return false;
  }
  $tbl = [];
  foreach($res->classification as $cl) {
    if ($cl->{"dwc:taxonRank"} == 'empire') {
      continue; // on n'utilise pas
    }
    $tmp = [];
    $tmp['rang'] = alg_rang($cl->{"dwc:taxonRank"});
    $nom = trim($cl->{"dwc:scientificName"});
    $tmp['id'] = $cl->{"dwc:taxonID"};
    $tmp['nom'] = trim(preg_replace("/" . $cl->{"dwc:taxonRank"} . "[ ]*/i", "", $nom));
    $tbl[] = $tmp;
  }
  $struct['rangs'] = array_reverse($tbl);
  
  // la charte
  $struct['regne'] = "algue"; //alg_regne($regne);
  
  return true;
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

