<?php

/*
  Module pour mycobank (non classification)
*/

// déclaration du module
function m_mycobank_init() {
  return declare_module("mycobank", false, true, ['champignon']);
}


/*
 Requête :
 curl 'https://webservices.bio-aware.com/cbsdatabase/api/Search/SearchForSummaryGrid' -X POST
 -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0'
 -H 'Accept: application/json, text/plain, *_remove_/_remove_*'
 -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br'
 -H 'Referer: https://www.mycobank.org/' -H 'WebsiteId: 85' -H 'Content-Type: application/json' -H 'Origin: https://www.mycobank.org'
 -H 'Connection: keep-alive' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: cross-site'
 -H 'TE: trailers' --data-raw '{"TableKey":"14682616000000067","Fields":["-100","14682616000001548","14682616000001537","14682616000001538","14682616000001539"],"iDisplayLength":10,"iDisplayStart":0,"ListOfConditionEntity":[{"Index":0,"FieldKey":"-100","NotOfCondition":true,"OperatorOfCondition":"","OperatorOfConditionUserName":"","OperatorOfField":"Matches exactly","OperatorOfFieldUserName":"Matches exactly","FieldName":"Taxon name","Value":"Boletus edulis"}],"ComplexQuery":" Q0 ","SortColumn":"name","SortDirection":"asc","LoadOwnerRecord":false}'
*/
/*
Retourne :
{"Data":{"RecordEntityList":[{"Id":3200,"Name":"Boletus edulis","ListFields":[{"FieldValue":{"Value":356530,"UnknowValue":"9223372036854775806","FieldType":9,"IsEmpty":false,"DataToView":"356530","ReadOnly":false,"IsModified":false},"Name":"MycoBank #","Key":"14682616000001548","FieldKey":"14682616000001548","Type":9,"CFieldStateNumber":0,"IsMandatory":false,"IsEmpty":false,"MaxValue":0.0,"IsHTmlContent":false,"IsCssContent":false,"IsXmlContent":false,"SuggestionCount":0,"ListSubFields":[],"IsGroup":false},{"FieldValue":{"Value":"Bull.","FieldType":5,"IsEmpty":false,"DataToView":"Bull.","ReadOnly":false,"IsModified":false},"Name":"Authors (abbreviated)","Key":"14682616000001537","FieldKey":"14682616000001537","Type":5,"CFieldStateNumber":0,"IsMandatory":false,"IsEmpty":false,"MaxValue":0.0,"IsHTmlContent":false,"IsCssContent":false,"IsXmlContent":false,"SuggestionCount":0,"ListSubFields":[],"IsGroup":false},{"FieldValue":{"Value":"1782","FieldType":8,"IsEmpty":false,"DataToView":"1782","ReadOnly":false,"IsModified":false},"Name":"Year of effective publication","Key":"14682616000001538","FieldKey":"14682616000001538","Type":8,"CFieldStateNumber":0,"IsMandatory":false,"IsEmpty":false,"MaxValue":0.0,"IsHTmlContent":false,"IsCssContent":false,"IsXmlContent":false,"SuggestionCount":0,"ListSubFields":[],"IsGroup":false},{"FieldValue":{"Value":"Legitimate","FieldType":20,"IsEmpty":false,"DataToView":"Legitimate","ReadOnly":false,"IsModified":false},"Name":"Name status","Key":"14682616000001539","FieldKey":"14682616000001539","Type":20,"States":["Legitimate","Illegitimate","Invalid","Orthographic variant","Unavailable","Uncertain","Deleted","","","","","","","","",""],"CFieldStateNumber":0,"IsMandatory":false,"IsEmpty":false,"MaxValue":0.0,"IsHTmlContent":false,"IsCssContent":false,"IsXmlContent":false,"SuggestionCount":0,"ListSubFields":[],"IsGroup":false}],"ListSubFields":[],"CanWrite":false,"CanDelete":false,"OwnerEmail":"a.decock@cbs.knaw.nl","LastChangeDate":"07/04/2021 14:35:00","LastChangeUserEmail":"v.robert@cbs.knaw.nl","CreationDate":"01/01/2000"}],"TotalCount":1},"Success":true,"TitleMessage":"","Message":"","Answer":"","Url":""}

Il faut ensuite faire une requête pour avoir les détails du taxon via son ID :
curl 'https://webservices.bio-aware.com/cbsdatabase/api/Details/GetTemplateByIdAndRecordDetails?p_TemplateId=11&p_RecordId=3200&p_DesignMode=1'
-H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0'
-H 'Accept: application/json, text/plain, *_remove_/_remove_*' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3'
-H 'Accept-Encoding: gzip, deflate, br' -H 'Referer: https://www.mycobank.org/' -H 'WebsiteId: 85'
-H 'Origin: https://www.mycobank.org' -H 'Connection: keep-alive' -H 'Sec-Fetch-Dest: empty'
-H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: cross-site' -H 'TE: trailers'

*/



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


