<?php

/*
  Module pour biolib (non classification)
*/

// déclaration du module
function m_biolib_init() {
  return declare_module("biolib", false, true, true);
}

// récupère les données présentes sur la page
function m_biolib_recupere($id) {
  $out = false;

  $url = "https://www.biolib.cz/en/taxon/id$id/";
  $ret = get_data($url);
  if ($ret === false) {
    logs("Biolib: taxon identifié mais erreur de récupération de la page");
    return false;
  }
  // analyse du contenu
  $tbl = explode("\n", $ret);
  foreach($tbl as $ligne) {
    if (strpos($ligne, ">Text function<") !== FALSE) {
      $blb = explode("<br />", $ligne);
      $cnt = count($blb);
      if ($cnt <= 2) {
        logs("Biolib: taxon identifié mais échec de récupération des informations (1)");
        return false;
      }
      $tmp = $blb[$cnt-2];
      $tmp = strip_tags(html_entity_decode($tmp));
      // NS
      $x = explode("]", $tmp);
      if (!isset($x[0])) {
        logs("Biolib: taxon identifié mais échec de récupération des informations (1b)");
        return false;
      }
      $y = explode(";", $x[0]);
      if (!isset($y[1])) {
        logs("Biolib: taxon identifié mais échec de récupération des informations (1c)");
        return false;
      }
      $ns = $y[1];
      // auteur
      $x = explode("]", $tmp);
      if (!isset($x[2])) {
        logs("Biolib: taxon identifié mais échec de récupération des informations (1d)");
        return false;
      }
      $au = "";
      $i = 2;
      while(isset($x[$i])) {
        $au .= $x[$i++];
      }
      $au = trim($au);
      $out = [];
      $out['auteur'] = $au;
      $out['id'] = $id;
      $out['nom'] = $ns;
      break; // inutile de continuer
    }
  }
  return $out;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_biolib_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $url = 'https://www.biolib.cz/en/formsearch/?action=execute&searcharea=1&string=' . str_replace(" ", "+", $taxon);

  $ret = get_data_header($url, false, false);
  // erreur CURL
  if ($ret === false) {
    logs("Biolib: erreur réseau");
    return false;
  }
  // si redirection c'est qu'on a un résultat exact
  $tbl = explode("\n", $ret);
  $trouve = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, "location:") === 0) {
      $trouve = trim(str_replace("\r", " ", $ligne));
      break;
    }
  }
  if ($trouve) {
    // on a une réponse unique : récupération de l'ID
    $id = str_replace("location: https://www.biolib.cz/en/taxon/id", "", $trouve);
    $id = str_replace("/", "", $id);
    // on récupère la page pour trouver le nom, auteur, autre
    $ret = m_biolib_recupere($id);
    
    if ($ret === false) {
      // éléments non trouvés
      logs("Biolib: taxon identifié mais échec de récupération des informations (2)");
      return false;
    } else {
      $struct['liens']['biolib'] = $ret;
    }
  } else {
    // potentiellement plusieurs réponses : on parcours la réponse pour trouver la bonne
    $tbl = explode("\n", $ret);
    $trouve = false;
    $dedans = false;
    foreach($tbl as $ligne) {
      $ligne = trim(str_replace("\r", " ", $ligne));
      if (strpos($ligne, ">Vernacular names<") !== false) {
        break; // on a passé la zone des NS valides
      }
      if (strpos($ligne, ">Scientific names<") !== false) {
        $dedans = true;
      }
      if (!$dedans) {
        continue; // pas encore dans la zone des NS valides
      }
      $tst = strpos($ligne, '<a href="/en/taxon/id');
      if ($tst !== 0) {
        continue; // pas une ligne qui nous intéresse
      }
      $el = preg_replace("/ [<]small[>].*$/", "", $ligne);
      $el = strip_tags($el);
      if ($el == $taxon) {
        // trouvé : on récupère l'ID
        $tst = explode("/", $ligne);
        if (!isset($tst[3])) {
          logs("Biolib: taxon identifié mais échec de récupération des informations (2b)");
          return false;
        }
        $id = str_replace("id", "", $tst[3]);
        if (empty($id)) {
          logs("Biolib: taxon identifié mais échec de récupération des informations (2c)");
          return false;
        }
        $trouve = $id;
        break;
      }
    }
    if ($trouve === false) {
      logs("Biolib: taxon non trouvé");
      return false;
    }
    // récupération données taxon
    $ret = m_biolib_recupere($id);
    
    if ($ret === false) {
      // éléments non trouvés
      logs("Biolib: taxon identifié mais échec de récupération des informations (3)");
      return false;
    } else {
      $struct['liens']['biolib'] = $ret;
    }
  }

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_biolib_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['biolib']['id'])) {
    $data = $struct['liens']['biolib'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{Biolib | taxon | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_biolib_liens($struct) {
  return false;
}

