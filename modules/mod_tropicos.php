<?php

/*
  Module pour tropicos (non classification)
*/

// déclaration du module
function m_tropicos_init() {
  return declare_module("tropicos", false, true, ['végétal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_tropicos_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = "https://www.tropicos.org/api/Search/NameLookup?value=" .
         urlencode($taxon) . "&returnCount=10&lookupType=1";
  
  $ret = get_data($url, ["Authorization: Bearer RjRGNDA4RDgtOEY2NS00NzVGLUI3NDktRjk4MjE2Q0NCRTQ1",
                                        "Referer: https://www.tropicos.org/name/Search",]);
  if ($ret === false) {
    logs("Tropicos: echec de récupération réseau");
    return false;
  }
  $_res = json_decode($ret);
  if ($_res === null) {
    logs("Tropicos: echec de décodage des données");
    return false;
  }
  if (!isset($_res[0])) {
    logs("Tropicos: taxon non trouvé");
    return false;
  }
  $res = $_res[0];
  if (!isset($res->id)) {
    logs("Tropicos: taxon non trouvé (2)");
    return false;
  }
  $struct['liens']['tropicos']['id'] = $res->id;
  if (isset($res->fullName)) {
    $struct['liens']['tropicos']['nom'] = $res->fullName;
  } else {
    $struct['liens']['tropicos']['nom'] = $taxon;
  }
  if (isset($res->displayName)) {
    $lng = strlen($struct['liens']['tropicos']['nom']);
    $struct['liens']['tropicos']['auteur'] = substr($res->displayName, $lng+1);
  }

  if (!$classif) {
    return true;
  }
  
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_tropicos_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['tropicos']['id'])) {
    $data = $struct['liens']['tropicos'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne'], false, false);
    if (isset($data['auteur'])) {
      $auteur = " " . $data['auteur'];
    } else {
      $ateur = "";
    }
    if (isset($data['synonyme']) and $data['synonyme']) {
      return "{{Tropicos | " . $data['id'] . " | " . $cible . " | " . $auteur . " | nv | " . "consulté le=$cdate }}";
    } else {
      return "{{Tropicos | " . $data['id'] . " | " . $cible . " | " . $auteur . " | consulté le=$cdate }}";
    }
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_tropicos_liens($struct) {
  if (isset($struct['liens']['tropicos']['id'])) {
    return "<a href='https://tropicos.org/name/" . $struct['liens']['tropicos']['id'] .
           "'>Tropicos</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_tropicos_fin($struct) {
  return false;
}

