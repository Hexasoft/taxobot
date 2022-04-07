<?php

/*
  Module pour mycobank (non classification)
*/

// déclaration du module
function m_mycobank_init() {
  return declare_module("mycobank", true, true, ['champignon'], 997);
}

function mycobank_bioref($rang=null) {
  return "MycoBank";
}
// retourne le nom de la classification pour Taxobox début
function mycobank_classif() {
  return "mycobank";
}

// retourne le règne à partir de la donnée
function mycobank_cherche_regne($regne) {
  return 'champignon';
}

// mapping rang MycoBank → wikipédia
$m_mb_rangs = [
  "sp." => "espèce",
  "subsp." => "sous-espèce",
  "ssp." => "sous-espèce",
  "gen." => "genre",
  "subgen." => "sous-genre",
  "fam." => "famille",
  "subfam." => "sous-famille",
  "ordo" => "ordre",
  "subordo" => "sous-ordre",
  "subcl." => "sous-classe",
  "cl." => "classe",
  "subdiv." => "sous-division",
  "div." => "division",
  "subregn." => "sous-règne",
  "regn." => "règne",
  "var." => "variété",
  "f." => "forme",
];

// global pour simplifier les fonctions récursives
$m_mb_items = [
 "Taxon name" => [],
 "Rank" => [],
 "MycoBank #" => [],
 "Year of effective publication" => [],
 "Authors" => [],
 "Classification" => [],
 "Summary" => [],
 "Name status" => [],
 "Authors" => [],
];
$m_mb_mapping = [
  "Taxon name" => [ "*" => "nom" ],
  "Rank" => [ "*" => "rang" ],
  "MycoBank #" => [ "*" => "id" ],
  "Authors" => [ "*" => "auteur" ],
  "Year of effective publication" => [ "*" => "année" ],
  "Classification" => [ "Value" => "classification", "ChildValue" => "sous-taxons" ],
  "Summary" => [ "*" => "citation" ],
  "Name status" => [ "*" => "statut" ],
  "Authors" => [ "*" => "auteurs" ],
];
$m_mb_results = [];

// recherche de tous les identifiants (UserName) → stockés dans ...index[]
function m_mycobank_recurs0($el) {
  global $m_mb_items;
  if (!is_object($el) and !is_array($el)) {
    return; // rien à faire
  }
  if (is_array($el) and isset($el['UserName'])) {
    // trouvé
    if (isset($m_mb_items[$el['UserName']])) {
      $tmp = [];
      if (isset($el['FieldKey'])) {
        $tmp['field'] = $el['FieldKey'];
      }
      if (isset($el['TargetTableKey'])) {
        $tmp['table'] = $el['TargetTableKey'];
      }
      $m_mb_items[$el['UserName']] = $tmp;
    }
    return;
  }
  if (is_object($el) and isset($el->UserName)) {
    // trouvé
    if (isset($m_mb_items[$el->UserName])) {
      $tmp = [];
      if (isset($el->FieldKey)) {
        $tmp['field'] = $el->FieldKey;
      }
      if (isset($el->TargetTableKey)) {
        $tmp['table'] = $el->TargetTableKey;
      }
      $m_mb_items[$el->UserName] = $tmp;
    }
    return;
  }
  // sinon on boucle
  foreach($el as $e) {
    m_mycobank_recurs0($e);
  }
  return;
}
function m_mycobank_recurs($res) {
  global $m_mb_items;
  $m_mb_items = [
    "Taxon name" => [],
    "Rank" => [],
    "MycoBank #" => [],
    "Year of effective publication" => [],
    "Authors" => [],
    "Classification" => [],
    "Summary" => [],
    "Name status" => [],
    "Synonymy" => [],
    "Basionym" => [],
    "Authors" => [],
  ];
  if (!isset($res->Data->Template->Fields)) {
    return;
  }
  m_mycobank_recurs0($res->Data->Template->Fields);
}

function m_mycobank_insert_valeur($val, $fk, $tk, $rc, $cat, $idx) {
  global $m_mb_results, $m_mb_mapping, $m_mb_items;

  // entrées "spéciales"
  if ($cat == "BasionymRecord") {
    $m_mb_results['basionyme'] = $val;
    return;
  }
  if ($cat == "ObligateSynonymRecords") {
    if (!isset($m_mb_results['synonymes'])) {
      $m_mb_results['synonymes'] = [];
    }
    $m_mb_results['synonymes'][] = $val;
    return;
  }
  if ($cat == "TaxonSynonymsRecords") {
    if (!isset($m_mb_results['synonymes'])) {
      $m_mb_results['synonymes'] = [];
    }
    $m_mb_results['synonymes'][] = $val;
    return;
  }
  if ($cat == "CurrentNameRecord") {
    $m_mb_results['actuel'][] = $val;
    return;
  }
  
  // on cherche si on match une entrée
  $trouve = false;
  $ntrouve = false;
  foreach($m_mb_items as $nom => $ent) {
    if (isset($ent['field']) and ($fk == $ent['field']) and
        ((isset($ent['table']) and ($tk == $ent['table'])) or !isset($ent['table']) ) ) {
      $trouve = $ent;
      $ntrouve = $nom;
      break;
    }
  }
  if ($trouve === false) {
    // pas trouvé, on laisse
    return;
  }
  if (!isset($m_mb_mapping[$ntrouve])) {
    return; // offset non demandé
  }
  $data = $m_mb_mapping[$ntrouve];
  $where = false;
  foreach($data as $type => $cible) {
    if ($type == "*") {
      $where = $cible;
    } else if ($type == $cat) {
      $where = $cible;
    }
  }
  if ($where === false) {
    return;
  }
  // on insert
  if (!isset($m_mb_results[$where])) {
    $m_mb_results[$where] = [];
  }
  $tmp = [];
  $tmp['valeur'] = $val;
  $tmp['id'] = $rc;
  $m_mb_results[$where][$idx] = $tmp;
}

function m_mycobank_recurs20($el, $fk, $tk, $rc, $cat, $idx, $parent) {
  if (!is_object($el) and !is_array($el)) {
    return; // rien à faire
  }
  if (is_array($el)) {
    if (isset($el['FieldKey'])) {
      $fk = $el['FieldKey'];
    } else {
      if (isset($el['FieldType']) and ($el['FieldType'] < 0)) {
        $fk = $el['FieldType'];
      }
    }
    if (isset($el['TableKey'])) {
      $tk = $el['TableKey'];
    }
    if (isset($el['RecordId'])) {
      $rc = $el['RecordId'];
    }
  }
  if (is_object($el)) {
    if (isset($el->FieldKey)) {
      $fk = $el->FieldKey;
    } else {
      if (isset($el->FieldType) and ($el->FieldType < 0)) {
        $fk = $el->FieldType;
      }
    }
    if (isset($el->TableKey)) {
      $tk = $el->TableKey;
    }
    if (isset($el->RecordId)) {
      $rc = $el->RecordId;
    }
  }
  if (is_array($el) and isset($el['Value']) and !is_array($el['Value']) and !is_object($el['Value'])) {
    // trouvé
    m_mycobank_insert_valeur($el['Value'], $fk, $tk, $rc, $cat, $idx);
    return;
  }
  if (is_object($el) and isset($el->Value) and !is_array($el->Value) and !is_object($el->Value)) {
    // trouvé
    m_mycobank_insert_valeur($el->Value, $fk, $tk, $rc, $cat, $idx);
    return;
  }
  // cas particulier des synonymes
  if (isset($el->RecordId) and isset($el->RecordName)) {
    m_mycobank_insert_valeur($el->RecordId, $fk, $tk, $rc, $parent, $idx);
    return;
  }
  
  // sinon on boucle
  if (is_array($el)) {
    foreach($el as $i => $e) {
      $idx = $i;
      m_mycobank_recurs20($e, $fk, $tk, $rc, $cat, $idx, $parent);
    }
  } else {
    foreach($el as $i => $e) {
      if (($i == "Value") or ($i == "ChildValue")) {
        $cat = $i;
      } else {
        $parent = $i;
      }
      m_mycobank_recurs20($e, $fk, $tk, $rc, $cat, $idx, $parent);
    }
  }
  return;
}
function m_mycobank_recurs2($res, $full=true) {
  global $m_mb_mapping, $m_mb_results;
  if ($full) {
    $m_mb_mapping = [
      "Taxon name" => [ "*" => "nom" ],
      "Rank" => [ "*" => "rang" ],
      "MycoBank #" => [ "*" => "id" ],
      "Authors" => [ "*" => "auteur" ],
      "Year of effective publication" => [ "*" => "année" ],
      "Classification" => [ "Value" => "classification", "ChildValue" => "sous-taxons" ],
      "Summary" => [ "*" => "citation" ],
      "Name status" => [ "*" => "statut" ],
      "Synonymy" => [ "*" => "synonymes" ],
      "Basionym" => [ "*" => "basionyme" ],
      "Authors" => [ "*" => "auteurs" ],
    ];
  } else {
    $m_mb_mapping = [
      "Taxon name" => [ "*" => "nom" ],
      "Rank" => [ "*" => "rang" ],
      "MycoBank #" => [ "*" => "id" ],
      "Authors" => [ "*" => "auteur" ],
      "Year of effective publication" => [ "*" => "année" ],
      "Summary" => [ "*" => "citation" ],
      "Name status" => [ "*" => "statut" ],
    ];
  }
  $m_mb_results = [];
  if (!isset($res->Data->RecordDetails)) {
    return;
  }
  m_mycobank_recurs20($res->Data->RecordDetails, "0", "0", "0", "?", "-1", "x");
}



// récupère les données JSON sur un taxon
function m_mycobank_get($id) {
  $url = "https://webservices.bio-aware.com/cbsdatabase/api/Details/getTemplateAndRecordDetailByCondition";
  $header = [
    'Referer: https://www.mycobank.org/',
    'WebsiteId: 85',
    'Accept: application/json',
    'Content-Type: application/json',
    'Origin: https://www.mycobank.org',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: cross-site',
    'TE: trailers'
  ];
  $post = '{"FieldName":"Mycobank #","Value":"' . $id . '","OperatorOfField":"=","TemplateId":11}';
  $ret = post_data($url, $post, $header);
  if ($ret === false) {
      logs("MycoBank: échec de récupération des infos détaillées sur le taxon");
      return false;
  }
file_put_contents("./content.json", $ret);
  $res = json_decode($ret);
  if ($res === null) {
    logs("MycoBank: échec de d'analyse des infos détaillées sur le taxon (2)");
    return false;
  }
  return $res;
}

https://webservices.bio-aware.com/cbsdatabase/api/Details/GetTemplateByIdAndRecordDetails?p_TemplateId=11&p_RecordId=92345&p_DesignMode=1

// récupère les données JSON sur un taxon (par son RecordId)
function m_mycobank_get_rec($id) {
  $url = "https://webservices.bio-aware.com/cbsdatabase/api/Details/GetTemplateByIdAndRecordDetails?p_TemplateId=11&p_RecordId=$id&p_DesignMode=1";
  $header = [
    'Referer: https://www.mycobank.org/',
    'WebsiteId: 85',
    'Accept: application/json',
    'Content-Type: application/json',
    'Origin: https://www.mycobank.org',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: cross-site',
    'TE: trailers'
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
      logs("MycoBank: échec de récupération (rec) des infos détaillées sur le taxon");
      return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("MycoBank: échec de d'analyse (rec) des infos détaillées sur le taxon (2)");
    return false;
  }
  return $res;
}

// récupère une partie "courte" à partir d'un ID MycoBank
function m_mycobank_get_id($id) {
  $url = "https://webservices.bio-aware.com/cbsdatabase/api/Search/SearchForSummaryGrid";
  $header = [
    'Referer: https://www.mycobank.org/',
    'WebsiteId: 85',
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: https://www.mycobank.org',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: cross-site',
    'TE: trailers'
  ];
  $post = '{"TableKey":"14682616000000067","Fields":' .
          '["-100","14682616000001548","14682616000001537","14682616000001538","14682616000001539"]' . 
          ',"iDisplayLength":50,"iDisplayStart":0,' .
          '"ListOfConditionEntity":[{"Index":0,"FieldKey":"14682616000001548","NotOfCondition":true,' . 
          '"OperatorOfCondition":"","OperatorOfConditionUserName":"",' .
          '"OperatorOfField":"=","OperatorOfFieldUserName":"=","FieldName":"MycoBank #",' .
          '"Value":"' . $id . '"}],"ComplexQuery":" Q0 ","SortColumn":"name",' .
          '"SortDirection":"asc","LoadOwnerRecord":false}';
  $ret = post_data($url, $post, $header);
  if ($ret === false) {
      logs("MycoBank: échec de récupération des infos auteurs sur le taxon");
      return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("MycoBank: échec de d'analyse des infos auteurs sur le taxon (2)");
    return false;
  }
  return $res;
}

// analyse les données d'un taxon
function m_mycobank_analyse_taxon($res, $full=true) {
  global $m_mb_results, $m_mb_rangs;
  // collecte des identifiants recherchés
  m_mycobank_recurs($res, $full);
  // collecte des infos
  m_mycobank_recurs2($res, $full);
  $tmp = $m_mb_results;

  $out = [];
  // on regarde les infos sur le taxon lui-même
  $taxon = [];
  if (isset($m_mb_results['nom'])) {
    $taxon['nom'] = $m_mb_results['nom'][-1]['valeur'];
  }
  if (isset($m_mb_results['id'])) {
    $taxon['id'] = $m_mb_results['id'][-1]['valeur'];
  }
  if (isset($m_mb_results['statut'])) {
    $taxon['statut'] = $m_mb_results['statut'][-1]['valeur'];
  }
  if (isset($m_mb_results['rang'])) {
    $x = $m_mb_results['rang'][0]['valeur'];
    if (isset($m_mb_rangs[$x])) {
      $taxon['rang'] = $m_mb_rangs[$x];
    } else {
      $taxon['rang'] = $x;
    }
  }

  // on fait une requête "summary" séparée pour extraire les auteurs sous leur bonne forme
  if (isset($taxon['id'])) {
    $res = m_mycobank_get_id($taxon['id']);
  }
  $trouve = false;
  if ($res !== false) {
    // si ok on cherche le bon taxon "Legitimate" pour récupérer l'info (+ date)
    if (isset($res->Data->RecordEntityList)) {
      foreach($res->Data->RecordEntityList as $el) {
        if (($el->Name == $taxon['nom']) and ($el->ListFields[3]->FieldValue->Value == "Legitimate")) {
          $taxon['auteur'] = $el->ListFields[1]->FieldValue->Value;
          $taxon['auteur-brut'] = $taxon['auteur'];
          if (isset($m_mb_results['année']) and !empty($m_mb_results['année'][-1]['valeur'])) {
            $taxon['auteur'] .= ", " . $m_mb_results['année'][-1]['valeur'];
          }
          $trouve = true;
          break;
        }
      }
    }
  }
  if (!$trouve) {
    // si pas trouvé on se rabat sur les trucs de merde…
    if (isset($m_mb_results['auteurs'][-1]['valeur'])) {
      $taxon['auteur'] = $m_mb_results['auteurs'][-1]['valeur'];
//echo $taxon['nom'] . " → '" . $taxon['auteurs'] . "'\n";
    }
    // en fait on passe plus ici : corriger, mais c'est pas supposé se produire, c'est 
    // un "fallback" de sécurité
    if (isset($m_mb_results['citation']) and isset($taxon['nom'])) {
      // problème : le champ 'auteurs' n'utilise aucune abréviation pour les noms d'auteurs
      // de son coté le champ citation contient plein de trucs après taxon+auteurs
      // soit on accepte d'avoir des noms non abrégés, soit il faut faire un découpage "à la con"
      // pour tenter d'extraire la partie intéressante, avec le risque de prendre trop ou pas
      // assez de la zone concernée
      $tmp = preg_replace("/^" . $taxon['nom'] . " /", "", $m_mb_results['citation'][-1]['valeur']);
      $x = explode(",", $tmp);
      $x = explode(":", $x[0]);
      $xx = $x[0];
      $xx = preg_replace("/[ ]*\[MB[^]]*\][ ]*/", "", $xx);
      $taxon['auteur'] = $xx;
      if (isset($m_mb_results['année']) and !empty($m_mb_results['année'][-1]['valeur'])) {
        $taxon['auteur'] .= ", " . $m_mb_results['année'][-1]['valeur'];
      }
    }
  }
  if (!empty($taxon)) {
    $out['taxon'] = $taxon;
  }
  
  // sous-taxons
    // $struct['sous-taxons']['liste'] = $liste;
    // $struct['sous-taxons']['source'] = gbif_bioref();
  if (isset($m_mb_results['sous-taxons'])) {
    $tbl = [];
    foreach($m_mb_results['sous-taxons'] as $el) {
      $nom = $el['valeur'];
      // on récupère les infos sur ce taxon (rang, auteur)
      $tmp = [];
      $tmp['nom'] = $el['valeur'];
      $tmp['id'] = $el['id'];
      $tmp['rang'] = 0;
      $tbl[] = $tmp;
    }
    $out['sous-taxons']['liste'] = $tbl;
    $out['sous-taxons']['source'] = mycobank_bioref();
  }
  // classification (sup)
  if (isset($m_mb_results['classification'])) {
    $tbl = [];
    foreach($m_mb_results['classification'] as $el) {
      $nom = $el['valeur'];
      // on récupère les infos sur ce taxon (rang, auteur)
      $tmp = [];
      $tmp['nom'] = $el['valeur'];
      $tmp['id'] = $el['id'];
      $tmp['rang'] = 0;
      $tbl[] = $tmp;
    }
    $out['rangs'] = $tbl;
  }
  
  // basionyme
  if (isset($m_mb_results['basionyme'])) {
    $out['basionyme']['id'] = $m_mb_results['basionyme'];
  }
  
  // synonymes
  if (isset($m_mb_results['synonymes'])) {
    $out['synonymes'] = $m_mb_results['synonymes'];
    // si le basionyme est différent du taxon courant on l'ajoute dans les synonymes
    if (isset($out['basionyme']['id']) and ($out['basionyme']['id'] != $out['taxon']['id'])) {
      array_unshift($out['synonymes'], $out['basionyme']['id']);
    }
  }
  
  if (isset($m_mb_results['actuel'])) {
    $out['actuel'] = $m_mb_results['actuel'];
  }
  
  if (isset($m_mb_results['citation'])) {
    $out['taxon']['citation'] = $m_mb_results['citation'][-1]['valeur'];
  }
  // extraction de la publication originale si possible
  if (isset($out['taxon']['citation']) and isset($out['taxon']['auteur-brut'])) {
    $tmp = str_replace($out['taxon']['nom'], "", $out['taxon']['citation']);
    $tmp = str_replace($out['taxon']['auteur-brut'], "", $tmp);
    $tmp = preg_replace("/\[MB#[^]]*\]/", "", $tmp);
    $tmp = str_replace("  ", " ", $tmp);
    $tmp = str_replace("  ", " ", $tmp);
    $tmp = trim(str_replace("  ", " ", $tmp));
    $tmp = trim(preg_replace("/^[,:][ ]*/", "", $tmp));
    if (!empty($tmp)) {
      $out['originale'] = $tmp;
    }
  }

    // $struct['rangs']
    
    // mycobank_cherche_regne($nom_regne)
  
  return $out;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_mycobank_infos(&$struct, $classif) {
  $tous_non_valides = get_config('inclure-invalides');
  $taxon = $struct['taxon']['nom'];

  $url = "https://webservices.bio-aware.com/cbsdatabase/api/Search/SearchForSummaryGrid";
  $post = '{"TableKey":"14682616000000067","Fields":["-100","14682616000001548","14682616000001537","14682616000001538","14682616000001539"]' . 
          ',"iDisplayLength":50,"iDisplayStart":0,' .
          '"ListOfConditionEntity":[{"Index":0,"FieldKey":"-100","NotOfCondition":true,' . 
          '"OperatorOfCondition":"","OperatorOfConditionUserName":"",' .
          '"OperatorOfField":"Matches exactly","OperatorOfFieldUserName":"Matches exactly",' .
          '"FieldName":"Taxon name","Value":"' .
          $taxon . '"}],"ComplexQuery":" Q0 ","SortColumn":"name",' .
          '"SortDirection":"asc","LoadOwnerRecord":false}';
  $header = [
    'Referer: https://www.mycobank.org/',
    'WebsiteId: 85',
    'Content-Type: application/json',
    'Origin: https://www.mycobank.org',
  ];
  $ret = post_data($url, $post, $header);
  if ($ret === false) {
    logs("MycoBank: échec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("MycoBank: échec de décodage des données");
    return false;
  }
  
  if (!isset($res->Data->RecordEntityList)) {
    logs("MycoBank: pas de réponse pour ce taxon");
    return false;
  }

  $struct['liens']['mycobank'] = [];
  // cette partie devrait sans doute être factorisée, mais bon ça fonctionne
  foreach($res->Data->RecordEntityList as $r) {
    if ($r->Name == $taxon) {
      $tmp['nom'] = $taxon;
      $id = false;
      $auteur = "";
      $date = "";
      $type = "";
      foreach($r->ListFields as $f) {
        if (isset($f->FieldValue->FieldType) and ($f->FieldValue->FieldType== 5)) {
          $auteur = $f->FieldValue->Value;
          continue;
        }
        if (isset($f->FieldValue->FieldType) and ($f->FieldValue->FieldType== 8)) {
          $date = $f->FieldValue->Value;
          continue;
        }
        if (isset($f->FieldValue->FieldType) and ($f->FieldValue->FieldType== 20)) {
          $type = $f->FieldValue->Value;
          continue;
        }
        if (isset($f->FieldValue->FieldType) and ($f->FieldValue->FieldType== 9)) {
          $id = $f->FieldValue->Value;
          continue;
        }
      }
      if ($id === false) {
        continue;
      }
      // seulement les noms légitimes
      if ($type != "Legitimate") {
        continue;
      }
      $tmp['id'] = $id;

      if (!empty($auteur)) {
        $tmp['auteur'] = $auteur;
      }
      $struct['liens']['mycobank'] = $tmp;
    }
  }
  
  if (empty($struct['liens']['mycobank'])) {
    unset($struct['liens']['mycobank']);
    logs("MycoBank: taxons trouvés mais non concordants (non légitime ?)");
    return false;
  }
  
  // on récupère l'enregistrement
  $res = m_mycobank_get($struct['liens']['mycobank']['id']);
  if ($res === false) {
    logs("MycoBank: échec de récupération des détails du taxon trouvé");
    return false;
  }

  // si c'est un synonyme
  if (isset($res->Data->RecordDetails->{"14682616000006675"}->SelectedRecord->RecordId) and
      isset($res->Data->RecordDetails->{"14682616000006675"}->CurrentNameRecord->RecordId) and
      ($res->Data->RecordDetails->{"14682616000006675"}->SelectedRecord->RecordId !=
       $res->Data->RecordDetails->{"14682616000006675"}->CurrentNameRecord->RecordId)) {
    // c'est un synonyme : si pas classif on l'indique
    if (!$classif) {
      // seulement si on les veut
      if ($tous_non_valides) {
        $struct['liens']['mycobank']['synonyme'] = true;
      } else {
        // si on ne les veut pas on le supprime
        unset($struct['liens']['mycobank']);
        logs("MycoBank: synonyme trouvé, mais configuré pour refuser les synonymes en liens externes");
        return false; // dans ce contexte on n'a rien trouvé
      }
    } else {
      // si on doit suivre les synonymes
      if (get_config("suivre-synonymes")) {
        // on se relance sur la cible du synonyme
        $res = m_mycobank_get_rec($res->Data->RecordDetails->{"14682616000006675"}->CurrentNameRecord->RecordId);
        if ($res === false) {
          logs("MycoBank : échec de récupération du synonyme, et suivi des synonymes demandé");
          return false;
        }
        $bla = m_mycobank_analyse_taxon($res);
        if (!isset($bla['taxon']['nom'])) {
          logs("MycoBank: échec de récupération du nom du synonyme");
          return false;
        }
        // on remplace le taxon
        unset($struct['taxon']);
        $struct['taxon'] = [];
        $struct['taxon']['nom'] = $bla['taxon']['nom'];
        // on se relance
        logs("MycoBank: suivi d'un synonyme");
        return m_mycobank_infos($struct, $classif);
      }
      // sinon on va traiter celui-ci
    }
  }
  
  // si pas plus loin, retour : on a fait le job
  if (!$classif) {
    return true;
  }
  
  // classification : il nous faut au moins les taxons supérieurs
  $id = $struct['liens']['mycobank']['id'];

  // on récupère la page détaillée du taxon
  $res = m_mycobank_get($id);
  if ($res === false) {
    logs("MycoBank: échec récupération infos détaillées sur le taxon");
    return false; // en mode classification c'est fatal
  }
  $out = m_mycobank_analyse_taxon($res);

  // il faut au moins la classification et le taxon
  if (!isset($out['taxon']) or !isset($out['rangs'])) {
    logs("MycoBank: données de classification manquantes");
    return false;
  }
  
  // on affine les infos présentes (données classif et sous-taxons : rang, auteur…)
  foreach($out['rangs'] as $idx => $rang) {
    $res = m_mycobank_get_rec($rang['id']);
    if ($res === false) {
      logs("MycoBank: échec récupération infos détaillées sur classification");
      return false;
    }
    $bla = m_mycobank_analyse_taxon($res);
    if (isset($bla['taxon'])) {
      if ($bla['taxon']['rang'] == 'règne') {
        $struct['regne'] = mycobank_cherche_regne($bla['taxon']['nom']);
        // on supprime ce rang
        unset($out['rangs'][$idx]);
        continue;
      }
      $out['rangs'][$idx]['nom'] = $bla['taxon']['nom'];
      $out['rangs'][$idx]['rang'] = $bla['taxon']['rang'];
      $out['rangs'][$idx]['auteur'] = $bla['taxon']['auteur'];
    }
  }
  
  // le taxon
  $struct['taxon'] = $out['taxon'];
  // on enregistre la classification dans le retour, en ordre inverse
  $struct['rangs'] = array_reverse($out['rangs']);

  // publication originale
  if (isset($out['originale'])) {
    $struct['originale'] = $out['originale'];
  }

  // taxons inférieurs
  if (isset($out['sous-taxons'])) {
    foreach($out['sous-taxons']['liste'] as $idx => $el) {
      $res = m_mycobank_get_rec($el['id']);
      if ($res === false) {
        continue;
      }
      $bla = m_mycobank_analyse_taxon($res);

      if (isset($bla['taxon'])) {
        if (isset($bla['taxon']['statut'])) {
          if ($bla['taxon']['statut'] != "Legitimate") {
            unset($out['sous-taxons']['liste'][$idx]);
            continue; // on ne prend que les taxons valides
          }
        }
        $out['sous-taxons']['liste'][$idx]['nom'] = $bla['taxon']['nom'];
        if (isset($bla['taxon']['rang'])) {
          $out['sous-taxons']['liste'][$idx]['rang'] = $bla['taxon']['rang'];
        } else {
          logs("MycoBank: rang non trouvé pour " . $bla['taxon']['nom'] . " recId=" . $el['id']);
        }
        if (isset($bla['taxon']['auteur'])) {
          $out['sous-taxons']['liste'][$idx]['auteur'] = $bla['taxon']['auteur'];
        }
      }
    }
    if (!empty($out['sous-taxons']['liste'])) {
      $struct['sous-taxons']['liste'] = $out['sous-taxons']['liste'];
      $struct['sous-taxons']['source'] = mycobank_bioref();
    }
  }
  
  // basionyme
  if (isset($out['basionyme']['id'])) {
    $res = m_mycobank_get_rec($out['basionyme']['id']);
    if ($res !== false) {
      $bla = m_mycobank_analyse_taxon($res);
      if ($bla['taxon']['nom'] != $struct['taxon']['nom']) {
        $struct['basionyme']['nom'] = $bla['taxon']['nom'];
        $struct['basionyme']['auteur'] = $bla['taxon']['auteur'];
        $struct['basionyme']['source'] = mycobank_bioref();
      }
    }
  }
  
  // synonymes
  if (isset($out['synonymes'])) {
    $tbl = [];
    foreach($out['synonymes'] as $syn) {
      $res = m_mycobank_get_rec($syn);
      if ($res !== false) {
        $bla = m_mycobank_analyse_taxon($res);
        $tmp = [];
        $tmp['nom'] = $bla['taxon']['nom'];
        $tmp['auteur'] = $bla['taxon']['auteur'];
        $tmp['source'] = mycobank_bioref();
        $tbl[] = $tmp;
      }
    }
    if (!empty($tbl)) {
      $struct['synonymes']['liste'] = $tbl;
      $struct['synonymes']['source'] = mycobank_bioref();
    }
  }
  
  $struct['classification'] = 'MycoBank';
  $struct['classification-taxobox'] = mycobank_classif();

  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_mycobank_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['mycobank'])) {
    $tbl = [];
    if (isset($struct['liens']['mycobank']['id'])) {
      $tblx = [ $struct['liens']['mycobank'] ];
    } else {
      $tblx = $struct['liens']['mycobank'];
    }
    foreach($tblx as $t) {
      if (isset($t['id'])) {
        $txt = $t['nom'];
        if (isset($t['auteur'])) {
          $auteur = " " . $t['auteur'];
        }
        if (isset($t['synonyme']) and $t['synonyme']) {
          $plus = " | nv";
        } else {
          $plus = "";
        }
        $tbl[] = "{{MycoBank | " . $t['id'] . " | " . $txt . " | " . $auteur . $plus . " | consulté le=$cdate }}";
        $tbl[] = "{{Fungorum espèce | " . $t['id'] . " | " . $txt . " | " . $auteur . $plus . " | consulté le=$cdate }}";
      }
    }
    return $tbl;
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_mycobank_liens($struct) {
 if (isset($struct['liens']['mycobank'])) {
    if (isset($struct['liens']['mycobank']['id'])) {
      $tblx = [ $struct['liens']['mycobank'] ];
    } else {
      $tblx = $struct['liens']['mycobank'];
    }
    $tmp = [];
    foreach($tblx as $t) {
      $tmp[] = "<a href='https://www.mycobank.org/MB/" . $t['id'] .
               "'>MycoBank</a>";
      $tmp[] = "<a href='http://www.speciesfungorum.org/Names/NamesRecord.asp?RecordID=" . $t['id'] .
               "'>Fungorum espèce</a>";
    }
    return $tmp;
  } else {
    return false;
  }
}


