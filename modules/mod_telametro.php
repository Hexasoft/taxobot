<?php

/*
  Module pour telametro (non classification)
*/

// déclaration du module
function m_telametro_init() {
  return declare_module("telametro", false, true, ['végétal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_telametro_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // récupération des cookies
  $url = "https://www.tela-botanica.org/flore/france-metropolitaine/";
  $ret = get_data($url);
  if ($ret === false) {
    logs("TelaMétro: échec d'accès au site");
    return false;
  }
  // la requête
  $url = "https://yotvbfebjc-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=" .
          "Algolia%20for%20vanilla%20JavaScript%20(lite)%203.24.5%3B" .
          "instantsearch.js%202.4.1%3BJS%20Helper%202.23.0&x-algolia-application-id=" .
          "YOTVBFEBJC&x-algolia-api-key=843a36372facc0f1836f53d1d5968aa8";
  $post = '{"requests":[{"indexName":"Flore","params":"query=' .
          urlencode($taxon) . '&hitsPerPage=20&maxValuesPerFacet=10&page=0' .
          '&facetFilters=%5B%22referentiels%3Abdtfx%22%5D&' .
          'facets=%5B%22referentiels%22%5D&tagFilters="}]}';
  $ret = post_data($url, $post);
  if ($ret === false) {
    logs("TelaMétro: échec de recherche sur le site");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("TelaMétro: échec de recherche sur le site (2)");
    return false;
  }
  
  // parcours des résultats
  if (!isset($res->results[0])) {
    logs("TelaMétro: aucun résultat");
    return false;
  }
  $ok = false;
  foreach($res->results[0]->hits as $idx => $r) {
    if (!isset($r->bdtfx)) {
      continue;
    }
    $tmp = trim($r->bdtfx->scientific_name);
    if (isset($r->bdtfx->author) and !empty($r->bdtfx->author)) {
      $fin = $r->bdtfx->author;
      $nom = str_replace("$fin", "", $tmp);
    } else {
      $fin = "";
      $nom = $tmp;
    }
    $nom = trim($nom);
    if ($nom == $taxon) {
      // trouvé, on note les infos
      $struct['liens']['telametro']['id'] = $r->bdtfx->nomenclatural_number;
      if (isset($r->bdtfx->year) and !empty($r->bdtfx->year)) {
        $fin .= " " . $r->bdtfx->year;
      }
      $struct['liens']['telametro']['nom'] = $nom;
      $struct['liens']['telametro']['auteur'] = $fin;
      // si présent, les noms vernaculaires
      if (isset($r->bdtfx->common_name) and !empty($r->bdtfx->common_name)) {
        $tbl = explode(", ", $r->bdtfx->common_name);
        $struct["vernaculaire"]['Tela-métro'] = $tbl;
      }
      // terminé
      $ok = true;
      break;
    }
  }
  
  if (!$ok) {
    logs("TelaMétro: recherche non concordante");
    return false;
  }

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_telametro_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['telametro']['id'])) {
    $texte = "";
    if (isset($struct['liens']['telametro']['nom'])) {
      $texte = "''" . $struct['liens']['telametro']['nom'] . "''";
    }
    if (isset($struct['liens']['telametro']['auteur'])) {
      $texte .= " " . $struct['liens']['telametro']['auteur'];
    }
    $texte = trim($texte);
    return "{{Tela-métro | $texte | consulté le=$cdate }}"; 
  }
  return false;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_telametro_liens($struct) {
  if (isset($struct['liens']['telametro']['id'])) {
    return "<a href='https://www.tela-botanica.org/eflore/?referentiel=bdtfx" .
           "&module=fiche&action=fiche&num_nom=" .
           $struct['liens']['telametro']['id'] .
           "&onglet=synthese'>TelaMétro</a>";
  }
  return false;
}

