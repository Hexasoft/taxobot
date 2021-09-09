<?php

/*
  Module pour wrms (non classification)
*/

// déclaration du module
function m_wrms_init() {
  return declare_module("wrms", false, true, true);
}

// conversion rang WRMS / WP
$wrms_rangs = [
  'Clade' => 'clade',
  'Type' => 'type',
  'Group' => 'groupe',
  'Unspecified' => 'non-classé',
  'Subform' => 'sous-forme',
  'Form' => 'forme',
  'Variety' => 'variété',
  'Pathovar' => 'pathovar',
  'Cultivar' => 'cultivar',
  'Subspecies' => 'sous-espèce',
  'Hybrid' => 'hybride',
  'Species' => 'espèce',
  'Subserie' => 'sous-série',
  'Serie' => 'série',
  'Subsection' => 'sous-section',
  'Section' => 'section',
  'Subgenus' => 'sous-genre',
  'Genus' => 'genre',
  'Subtribe' => 'sous-tribu',
  'Tribe' => 'tribu',
  'Supertribe' => 'super-tribu',
  'Infratribe' => 'infra-tribu',
  'Subfamily' => 'sous-famille',
  'Family' => 'famille',
  'null2' => 'épifamille',
  'Superfamily' => 'super-famille',
  'Microorder' => 'micro-ordre',
  'Infraorder' => 'infra-ordre',
  'Suborder' => 'sous-ordre',
  'Order' => 'ordre',
  'Superorder' => 'super-ordre',
  'Subcohort' => 'sous-cohorte',
  'Cohort' => 'cohorte',
  'Supercohort' => 'super-cohorte',
  'null1' => 'subter-classe',
  'Infraclass' => 'infra-classe',
  'Subclass' => 'sous-classe',
  'Class' => 'classe',
  'Superclass' => 'super-classe',
  'Megaclass' => 'super-classe',
  'Microphylum' => 'micro-embranchement',
  'Infraphylum' => 'infra-embranchement',
  'Subphylum' => 'sous-embranchement',
  'Phylum' => 'embranchement',
  'Superphylum' => 'super-embranchement',
  'Infradivision' => 'infra-division',
  'Subdivision' => 'sous-division',
  'Division' => 'division',
  'Superdivision' => 'super-division',
  'Infrakingdom' => 'infra-règne',
  'null' => 'rameau',
  'Subkingdom' => 'sous-règne',
  'Kingdom' => 'règne',
  'Superkingdom' => 'super-règne',
  'Subdomain' => 'sous-domaine',
  'Domain' => 'domaine',
  'Superdomain' => 'super-domaine',
  'Empire' => 'empire',
  'Kingdom' => 'royaume',
  'Subkingdom' => 'sous-royaume',
];
function wrms_rang($rang) {
  global $wrms_rangs;
  if (isset($wrms_rangs[$rang])) {
    return $wrms_rangs[$rang];
  } else {
    return "NOTFOUND";
  }
}

// extraction des infos de la page WRMS
function wrms_extraire($page, $id) {
  $out = [];
  $out['id'] = $id;
  $tbl = explode("\n", $page);
  $full_name = false;
  foreach($tbl as $idx => $ligne) {
    $ligne = trim($ligne);
    // rang
    if (strpos($ligne, '<div id="Rank"') !== false) {
      $out['rang'] = wrms_rang(trim($tbl[$idx+4]));
      continue;
    }
    // classification
    if (strpos($ligne, 'for="Classification">') !== false) {
      $out['classification'] = [];
      // on parcours les éléments
      $i = $idx + 6;
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $tmp = trim($tbl[$i]);
        if ($tmp == '</ol>') {
          break;
        }
        $p1 = preg_replace(',</a>.*$,', '', $tmp);
        $ns = strip_tags($p1);
        $p2 = preg_replace(',^.*</a>,', '', $tmp);
        $p2 = strip_tags($p2);
        $p2 = str_replace(['&nbsp;', '(', ')'], '', $p2);
        $blob = [];
        $blob['nom'] = $ns;
        $blob['rang'] = wrms_rang($p2);
        if ($blob['rang'] != 'royaume') {
          // on ne traite pas ce rang
          $out['classification'][] = $blob;
        }
        $der_nom = $ns;
        $der_rang = $p2;
        $i++;
      }
      // récupération de l'auteur
      if ($full_name) {
        $tmp = strip_tags($full_name);
        $auteur = str_replace($der_nom, '', $tmp);
        $out['auteur'] = trim($auteur);
        $out['nom'] = trim($der_nom);
      }
      // on vire le dernier (qui est égal au taxon)
      array_pop($out['classification']);
    }
    // nom + auteur
    if (strpos($ligne, '<b><i role="button" tabindex="0"') !== false) {
      $full_name = $ligne;
      continue;
    }
    // sous-taxons
    if (strpos($ligne, '>Direct children') !== false) {
      $i = $idx + 4;
      $out['sous-taxons'] = [];
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $blob = [];
        $tmp = trim($tbl[$i]);
        if ($tmp == "") {
          break;
        }
        $p1 = preg_replace(',<a .*$,', '', $tmp);
        $x = trim(strip_tags(trim($p1)));
        $blob['rang'] = wrms_rang($x);
        $p2 = preg_replace(',</a>.*$,', '', $tmp);
        $x = trim(strip_tags(trim($p2)));
        $blob['nom'] = $x;
        $out['sous-taxons'][] = $blob;
        $i++;
      }
    }
    // synonymes (et basionyme)
    if (strpos($ligne, '>Synonymised names') !== false) {
      $i = $idx + 5;
      $out['synonymes'] = [];
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $tmp = trim($tbl[$i]);
        if ($tmp == '</div>') {
          break;
        }
        
        $i++;
      }
    }
  }
  return $out;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_wrms_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // on récupère la page de recherche
  $url = "http://www.marinespecies.org/aphia.php?p=search";
  $ret = get_data($url);
  // on cherche le taxon
  $url = "http://www.marinespecies.org/aphia.php?p=taxlist";
  $post = "searchpar=0&tComp=begins&tName=" . str_replace(" ", "+", $taxon) .
	      "&action=search&rSkips=0&adv=0";
  $ret = post_data_header($url, $post);
  if ($ret === false) {
    logs("WRMS: échec de la recherche");
    return false;
  }
  $tbl = explode("\n", $ret);
  $trouve = false;
  foreach($tbl as $l) {
    if (strpos($l, "aphia.php?p=taxdetails&id=") !== false) {
      $trouve = trim(preg_replace('/^.*id=/', '', $l));
      $url = trim(preg_replace('/location: /', '', $l));
      break;
    }
  }
  if ($trouve === false) {
    logs("WRMS: taxon non trouvé");
    return false;
  }
  if (!is_numeric($trouve)) {
    logs("WRMS: taxon non trouvé");
    return false;
  }
  // on note l'identifiant
  $blob = [];
  $blob['id'] = $trouve;
  // on récupère la page pour avoir les autres infos
  $ret = get_data($url);
  if ($ret === false) {
    logs("WRMS: échec de récupération de la page d'informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = $struct['taxon']['auteur'];
    $struct['liens']['wrms'] = $blob;
    return false;
  }
  // extraction des infos
  $res = wrms_extraire($ret, $blob['id']);
  if ($res === false) {
    logs("WRMS: échec d'extraction des informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = $struct['taxon']['auteur'];
    $struct['liens']['wrms'] = $blob;
    return false;
  }

  $struct['liens']['wrms'] = $res;
  // on extrait la classification
  if (isset($struct['liens']['wrms']['classification'])) {
    $classification = $struct['liens']['wrms']['classification'];
    unset($struct['liens']['wrms']['classification']);
  } else {
    $classification = false;
  }
  if (isset($struct['liens']['wrms']['sous-taxons'])) {
    $st = $struct['liens']['wrms']['sous-taxons'];
    unset($struct['liens']['wrms']['sous-taxons']);
  } else {
    $st = false;
  }
  if (!$classif) {
    return true;
  }
  // on instancie les infos de classification
  $struct['classification'] = 'WRMS';
  $struct['classification-taxobox'] = 'wrms';
  if ($classification) {
    $struct['rangs'] = $classification;
  }
  if ($st) {
    $struct['sous-taxons'] = $st;
  }
  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_wrms_ext($struct) {
  if (isset($struct['liens']['wrms']['id'])) {
    $data = $struct['liens']['wrms'];
    $cdate = dates_recupere();
    
    $nom = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $auteur = $data['auteur'];
    } else {
      $auteur = '';
    }
    return "{{WRMS | $id | $nom | $auteur | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_wrms_liens($struct) {
  if (isset($struct['liens']['wrms']['id'])) {
    return "<a href='http://www.marinespecies.org/aphia.php?p=taxdetails&id=" .
           $struct['liens']['wrms']['id'] . "'>WRMS</a>";
  } else {
    return false;
  }
}

