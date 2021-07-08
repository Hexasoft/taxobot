<?php

/*
  Module pour mycobank (non classification)
*/

// déclaration du module
function m_mycobank_init() {
  return declare_module("mycobank", false, true, ['champignon']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_mycobank_infos(&$struct, $classif) {
  $tous_non_valides = get_config('inclure-invalides');
  $taxon = $struct['taxon']['nom'];

  $url = "https://webservices.bio-aware.com/cbsdatabase/api/Search/SearchForSummaryGrid";
  $post = '{"TableKey":"14682616000000067","Fields":["name","mycobanknr_",' . 
          '"authorsabbrev_","nameyear_","namestatus_"],"iDisplayLength":10,"iDisplayStart":0,' .
          '"ListOfConditionEntity":[{"Index":0,"FieldKey":"-100","NotOfCondition":false,' . 
          '"OperatorOfCondition":"","OperatorOfConditionUserName":"",' .
          '"OperatorOfField":"Starts with ...","OperatorOfFieldUserName":"Starts with ...",' .
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
      $tmp['id'] = $id;
      if (!empty($date)) {
        $auteur .= " $date";
      }
      if (!empty($type) and ($type != "Legitimate")) {
        if ($tous_non_valides) {
          // on l'accepte comme synonyme
          $tmp['synonyme'] = true;
        } else {
          // pas les "non légitimes"
          continue;
        }
      }
      if (!empty($auteur)) {
        $tmp['auteur'] = $auteur;
      }
      $struct['liens']['mycobank'][] = $tmp;
    }
  }
  
  if (empty($struct['liens']['mycobank'])) {
    unset($struct['liens']['mycobank']);
    logs("MycoBank: taxons trouvés mais non concordants");
    return false;
  }

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_mycobank_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['mycobank'])) {
    $tbl = [];
    $tblx = $struct['liens']['mycobank'];
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
    $tbl = [];
    $tblx = $struct['liens']['mycobank'];
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

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_mycobank_fin($struct) {
  return false;
}

