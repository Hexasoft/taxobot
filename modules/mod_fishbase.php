<?php

/*
  Module pour fishbase (non classification)
*/

// déclaration du module
function m_fishbase_init() {
  return declare_module("fishbase", false, false, ['animal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_fishbase_infos(&$struct, $classif) {
  $url = "https://www.fishbase.se/Nomenclature/ScientificNameSearchList.php?";
  $header = [
    'Origin: https://www.fishbase.se', 'Connection: keep-alive', 'Referer: https://www.fishbase.se/search.php',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3',
    'Content-Type: application/x-www-form-urlencoded',
  ];
  $nom = str_replace(" ", "%2B", $struct['taxon']['nom']);
  $post = "Language=English&crit1_fieldname=SYNONYMS.SynGenus&crit1_fieldtype=CHAR&" .
	      "crit2_fieldname=SYNONYMS.SynSpecies&crit2_fieldtype=CHAR&crit1_operator=EQUAL&" .
	      "crit1_value=&crit2_operator=EQUAL&crit2_value=&group=summary&gs=$nom";
  $ret = post_data($url, $post, $header);
  if  ($ret === false) {
    logs("FishBase: problème réseau");
    return false;
  }
  $tmp = explode("\n", $ret);
  foreach($tmp as $ligne) {
    if (strpos($ligne, "NoRecord.php") !== false) {
      logs("FishBase: taxon non trouvé (espèce)");
      return false;
    }
  }
  // on cherche l'identifiant
  $id = false;
  foreach($tmp as $ligne) {
    if (strpos($ligne, "SpeciesSummary.php") !== false) {
      $id = preg_replace(",^.*SpeciesSummary[.]php[?]ID=([0-9]*).*$,", '$1', $ligne);
      break;
    }
  }
  if ($id === false) {
    logs("FishBase: recherche ok mais taxon non trouvé (espèce)");
    return false;
  }
  $struct['liens']['fishbase']['espece'] = $id;
  $struct['liens']['fishbase']['nom'] = $struct['taxon']['nom'];
  // TODO : recherche par genre
  
  if (!$classif) {
    return true;
  }
  // on ne fait pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_fishbase_ext($struct) {
  $cdate = dates_recupere();
  $ret = [];
  if (isset($struct['liens']['fishbase']['espece'])) {
    $txt = "";
    if (isset($struct['liens']['fishbase']['nom'])) {
      $txt .= wp_met_italiques($struct['liens']['fishbase']['nom'], $struct['taxon']['rang'], $struct['regne']);
    }
    if (isset($struct['liens']['fishbase']['auteur'])) {
      $txt .= " " . $struct['liens']['fishbase']['auteur'];
    }
    $ret[] = "{{FishBase espèce | " . $struct['liens']['fishbase']['espece'] . " | $txt | consulté le=" .
             $cdate . " }}";
  }
  // genre
  // famille
  
  if (empty($ret)) {
    return false;
  } else {
    return $ret;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_fishbase_liens($struct) {
  $ret = [];
  if (isset($struct['liens']['fishbase']['espece'])) {
    $ret[] = "<a href='http://www.fishbase.org/summary/" .
             $struct['liens']['fishbase']['espece'] . "'>Fishbase espèce</a>";
  }
  // genre
  // famille
  if (empty($ret)) {
    return false;
  } else {
    return $ret;
  }
}

