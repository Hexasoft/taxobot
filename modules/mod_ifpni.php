<?php

/*
  Module pour ifpni (non classification)
*/

// déclaration du module
function m_ifpni_init() {
  return declare_module("ifpni", false, true, true); // adapter la liste des cibles
}

// spécifique genres
function m_ifpni_infos_genre($struct) {
  $taxon = $struct['taxon']['nom'];
  
  $url='http://www.ifpni.org/genus.htm?formIndex=def&name=' . $taxon .
       '&isExtended=1&author=&originalSpelling=&yearFrom=&yearTo=&submitForm=Search&submitForm=Search';
  
  $ret = get_data($url);
  if ($ret === false) {
    logs("IFPNI: problème réseau");
    return false;
  }
  // parcours
  $tbl = explode("\n", $ret);
  $id = false;
  foreach($tbl as $idx => $l) {
    if (strpos($l, '"list-group-item"') !== false) {
      $valide = $tbl[$idx+3];
      $valide = preg_replace("/^[^>]*>/", "", $valide);
      $valide = preg_replace("/<.*$/", "", $valide);
      if ($valide != "valid") {
        continue;
      }
      $nom = $tbl[$idx+5];
      $nom = preg_replace("/^.*<span class=\"rank\"/", "", $nom);
      $nom = preg_replace(",^>[^>]*</span>,", "", $nom);
      $nom = preg_replace("/<.*$/", "", $nom);
      $nom = trim($nom);
      if ($nom != $taxon) {
        continue;
      }
      $id = $tbl[$idx+5];
      $id = preg_replace("/^.*id=/", "", $id);
      $id = preg_replace("/\".*$/", "", $id);
      if (empty($id)) {
        continue;
      }
      $auteur = false;
      if (strpos($tbl[$idx+5], '<span class="text-info"><span class="text-info">') !== false) {
        $auteur = $tbl[$idx+5];
        $auteur = preg_replace('/^.*<span class="text-info"><span class="text-info">/', "", $auteur);
echo ">>$auteur<<\n";
        $auteur = preg_replace(',</span>.*$,', "", $auteur);
echo ">>$auteur<<\n";
        $auteur = trim($auteur);
echo ">>$auteur<<\n";
      }
      break;
    }
  }
  if ($id === false) {
    return false;
  }
  $el['nom'] = $taxon;
  $el['rang'] = 'genre';
  $el['id'] = $id;
  if (isset($auteur) and ($auteur !== false)) {
    $el['auteur'] = $auteur;
  }

  return $el;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_ifpni_infos(&$struct, $classif) {
  // on teste chaque type de rang
  $rang = $struct['taxon']['rang'];
  if ($rang == "genre") {
    $ret = m_ifpni_infos_genre($struct);
    if ($ret !== false) {
      $struct['liens']['ifpni'] = $ret;
      goto suite;
    }
  }
  
  // autres rangs
  // TODO
  logs("IFPNI: taxon non trouvé");
  return false;
  
  // pour passer les divers rangs
suite:
  if (!$classif) {
    return true;
  }
  
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_ifpni_ext($struct) {
  if (isset($struct['liens']['ifpni']['id'])) {
    $data = $struct['liens']['ifpni'];
    $cdate = dates_recupere();
    $cible = wp_met_italiques($data['nom'], $data['rang'], $struct['regne'], false, true);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{IFPNI | " . $data['rang'] . " | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_ifpni_liens($struct) {
    if (isset($struct['liens']['ifpni']['id'])) {
    $data = $struct['liens']['ifpni'];
    if ($data['rang'] == 'genre') {
      return "<a href='http://www.ifpni.org/genus.htm?id=" . $data['id'] . "'>IFPNI</a>";
    } else {
      return "TODO " . $data['rang'] . " => " . $data['id'];
    }
  } else {
    return false;
  }
}

