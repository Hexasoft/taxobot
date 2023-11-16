<?php

/*
  Module pour photoark (non classification)
*/

// déclaration du module
function m_photoark_init() {
  return declare_module("photoark", false, false, false);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_photoark_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  return false; // désactivé pour le moment

  // ne s'applique qu'aux espèces
  if ($struct['taxon']['rang'] != "espèce") {
    return false;
  }
  return false; // le site a changé

  $t = strtolower($taxon);
  $t = str_replace(" ", "-", $t);
  $url = "https://www.nationalgeographic.org/projects/photo-ark/animal/$t/";
  $ret = get_data($url);
  if ($ret === false) {
    logs("ARK: echec de récupération réseau");
    return false;
  }
  $tst = strpos($ret, "Pardon me.");
  if ($tst !== false) {
    logs("ARK: taxon non trouvé");
    return false;
  }
  $tst = strpos($ret, "Page Not Found");
  if ($tst !== false) {
    logs("ARK: taxon non trouvé");
    return false;
  }

  // pas de gestion d'auteurs, ou même de nom
  $struct['liens']['photoark']['id'] = $t;
  $struct['liens']['photoark']['nom'] = $taxon;

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }

  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_photoark_ext($struct) {
  $cdate = dates_recupere();
  $data = $struct['liens']['photoark'];
  if (isset($data['id'])) {
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    return "{{ARK | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_photoark_liens($struct) {
  if (isset($struct['liens']['photoark']['id'])) {
    return "<a href='https://www.nationalgeographic.org/projects/photo-ark/animal/" .
           $struct['liens']['photoark']['id'] . "/'>Photo ARK</a>";
  } else {
    return false;
  }
}