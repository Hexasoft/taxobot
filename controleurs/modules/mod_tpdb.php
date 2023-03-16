<?php

/*
  Module pour tpdb (non classification)
*/

// déclaration du module
function m_tpdb_init() {
  return declare_module("tpdb", false, true, true);
}

/*
curl 'https://paleobiodb.org/data1.2/combined/auto.json?show=countries&name=Cytisus&type=cls&_=1651839047714' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0' -H 'Accept: application/json, text/javascript; q=0.01' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'X-Requested-With: XMLHttpRequest' -H 'Connection: keep-alive' -H 'Referer: https://paleobiodb.org/classic' -H 'Cookie: _ga=GA1.2.546965016.1651838604; _gid=GA1.2.272562783.1651838604' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-origin'

→

{
	"elapsed_time": 0.00226,
	"records": [
		{
			"oid": "txn:448323",
			"nam": "Cytisus",
			"vid": "var:448323",
			"rnk": "genus",
			"htn": "Magnoliopsida",
			"noc": 0
		},
		{
			"oid": "txn:448324",
			"nam": "Cytisus florissantianus",
			"vid": "var:448324",
			"rnk": "species",
			"htn": "Magnoliopsida",
			"noc": 0
		},
		{
			"oid": "txn:451494",
			"nam": "Cytisus modestus",
			"vid": "var:451494",
			"rnk": "species",
			"tdf": "recombined as",
			"acn": "Ptelea modesta",
			"htn": "Dicotyledoneae",
			"noc": 0
		}
	]
}



*/


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_tpdb_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // on récupère la page de recherche (cookie)
  $url = "https://paleobiodb.org/classic/beginTaxonInfo";
  $ret = get_data($url);
  $url = "https://paleobiodb.org/data1.2/combined/auto.json?show=countries&name=" .
         str_replace(" ", "%20", $taxon) . "&type=cls&_=" . (time()*1000);
  $header = [ 'Referer: https://paleobiodb.org/classic',
              'Accept: application/json, text/javascript; q=0.01',
              'Sec-Fetch-Site: same-origin',
              'Sec-Fetch-Dest: empty',
              'Sec-Fetch-Mode: cors',
              'Upgrade-Insecure-Requests: 1'];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("TPDB: échec de la recherche");
    return false;
  }
  $res = json_decode($ret);
  if (($res === false) or (!isset($res->records))) {
    logs("TPDB: échec de l'analyse de la recherche");
    return false;
  }
  if (empty($res->records)) {
    logs("TPDB: taxon non trouvé");
    return false;
  }
  
  $id = false;
  $valid = true;
  foreach($res->records as $rec) {
    if ($rec->nam != $taxon) {
      continue;
    }
    $tmp = explode(":", $rec->vid);
    if (isset($tmp[1])) {
      $id = $tmp[1];
      if (isset($rec->acn)) {
        $valid = false;
      }
    }
  }
  
  if (!$id) {
    logs("TPDB: taxon non trouvé");
    return false;
  }
  
  // on enregistre l'identifiant
  $struct['liens']['tpdb']['id'] = $id;
  // utilisation du nom de la classification : le format de leur page est trop pourri pour
  // tenter d'extraire le nom scientifique (et l'auteur) proprement. Tant pis.
  $struct['liens']['tpdb']['nom'] = $taxon;
  if (!$valid) {
    $struct['liens']['tpdb']['nv'] = true; // le modèle ne le gère pas, mais tant pis
  }

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_tpdb_ext($struct) {
  if (isset($struct['liens']['tpdb']['id'])) {
    $data = $struct['liens']['tpdb'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $nom .= $data['auteur'];
    }
    return "{{TPDB | $id | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_tpdb_liens($struct) {
  if (isset($struct['liens']['tpdb']['id'])) {
    return "<a href='https://paleobiodb.org/classic/basicTaxonInfo?taxon_no=" .
           $struct['liens']['tpdb']['id'] . "'>TPDB</a>";
  } else {
    return false;
  }
}

