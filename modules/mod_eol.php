<?php

/*
  Module pour EoL (non classification)
*/

// conversion rang/wp
$eol_wp = [
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
  'null1' => 'subter-classe',
  'infraclass' => 'infra-classe',
  'subclass' => 'sous-classe',
  'class' => 'classe',
  'superclass' => 'super-classe',
  'microphylum' => 'micro-embranchement',
  'infraphylum' => 'infra-embranchement',
  'subphylum' => 'sous-embranchement',
  'phylum' => 'embranchement',
  'superphylum' => 'super-embranchement',
  'infradivision' => 'infra-division',
  'subdivision' => 'sous-division',
  'division' => 'division',
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


// déclaration du module
function m_eol_init() {
  return declare_module("eol", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_eol_infos(&$struct, $classif) {
  global $eol_wp;
  $taxon = $struct['taxon']['nom'];

  $url = "https://eol.org/fr/autocomplete/" . urlencode($taxon);
  $ret = get_data($url);
  if ($ret === false) {
    logs("EoL: echec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("EoL: Echec de décodage des données");
    return false;
  }
  if (empty($res)) {
    logs("EoL: taxon non trouvé");
    return false;
  }
  // on parcours les entrées
  $found = false;
  foreach($res as $el) {
    if (isset($el->name) and ($el->name == $taxon)) {
      $found = $el;
      break;
    }
  }
  if ($found === false) {
    logs("EoL: taxon non trouvé");
    return false;
  }
  
  $struct['liens']['eol']['id'] = $found->id;
  $struct['liens']['eol']['nom'] = $found->name;
  
  // on tente de récupérer le rang et l'auteur
  $url = "https://eol.org/fr/pages/" . $found->id;
  $ret = get_data($url);
  if ($ret === false) {
    logs("EoL: echec de récupération de la page dédiée (ignoré)");
  } else {
    // on cherche l'auteur
    $tbl = explode("\n", $ret);
    $h1 = false;
    foreach($tbl as $ligne) {
      if (strpos($ligne, "<h2>") !== false) {
        $h1 = $ligne;
        break;
      }
      if (strpos($ligne, "<h1>") !== false) {
        $h1 = $ligne;
      }
    }
    if ($h1 !== false) {
      $h1 = trim($h1);
      $h1 = strip_tags($h1);
      $tmp = trim(str_replace("$taxon ", "", $h1));
      if (($tmp != "") and ($tmp != $h1)) {
        // on enregistre l'auteur associé
        $struct['liens']['eol']['auteur'] = $tmp;
      }
    }
    // on cherche le rang
    $l = false;
    foreach($tbl as $ligne) {
      if (strpos($ligne, "<p>") === 0) {
        $l = $ligne;
        break;
      }
    }
    if ($l !== false) {
      $tmp = preg_replace("/^.* est un[e]* /", "", $l);
      $tmp = preg_replace("/ d[e'].*$/", "", $tmp);
      $tmp = trim($tmp);
      if ($tmp != "") {
        $struct['liens']['eol']['rang'] = $tmp;
      }
    }
  }
  if (!$classif) {
    return true;
  }
  // on ne fait pas la classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_eol_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['eol']['id'])) {
    $data = $struct['liens']['eol'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{EOL | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_eol_liens($struct) {
  if (isset($struct['liens']['eol']['id'])) {
    return "<a href='https://eol.org/fr/pages/" . $struct['liens']['eol']['id'] .
           "'>EoL</a>";
  } else {
    return false;
  }
}

