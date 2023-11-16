<?php

/*
  Module pour XXX ne gérant pas la classification, mais générant un lien externe
  Ceci est un exemple de code
*/

// déclaration du module
function m_XXX_init() {
  // son nom est 'XXX', il ne génère pas de classification, il génère un lien externe,
  // il s'applique uniquement aux animaux
  return declare_module("XXX", false, true, ['animal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_XXX_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  /* dans cette partie on interroge la cible pour extraire les informations liées au
     taxon $struct['taxon']['nom'] */
  $url = "https://le-site-cible/la-requete-sur-le-taxon=" . urlencode($taxon);
  $ret = get_data($url);
  if ($ret == false) {
    // message d'explication
    logs("XXX: erreur de récupération des données");
    return false; // échec
  }
  /* ici on analyse ce qu'a retourné le site. Le format est libre */
  $struct['liens']['XXX']['identifiant'] = $identifiant_du_taxon;
  $struct['liens']['XXX']['nom'] = $nom_du_taxon;
  $struct['liens']['XXX']['auteur'] = $auteur_du_taxon;
  $struct['liens']['XXX']['non-valide'] = $non_validite_du_taxon;

  /* le cas échéant, on ajoute des noms vernaculaires
     note : l'index XXX correspond au nom du modèle Bioref associé */
  $struct['vernaculaire']['XXX'] = $liste_noms_vernaculaires;

  /* normalement puisqu'on est déclaré ne pas gérer la classification on ne doit pas être
     appelé avec $classif==true. Si $classif==false c'est OK */
  if (!$classif) {
    return true;
  }
  // ne doit pas se produire, mais c'est pas OK car on ne gère pas la classification */
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_XXX_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['XXX'])) {
    $data = $struct['liens']['XXX'];
    // le nom (avec italiques si besoin)
    $nom = wp_met_italiques($data['nom'],
                            isset($data['rang'])?$data['rang']:$struct['taxon']['rang'],
                            $struct['regne']);
    if (isset($data['auteur'])) {
      $nom .= " " . $data['auteur'];
    }
    // autres éléments éventuels
    if (isset($data['non-valide']) && $data['non-valide']) {
      $sup = " | nv";
    } else {
      $sup = "";
    }
    // on retourne le formatage du lien externe
    return "{{XXX | " . $data['identifiant'] . " | $nom$sup | consulté le=$cdate}}";
  } else {
    // rien à retourner
    return false;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_XXX_liens($struct) {
  // juste un lien HTML vers la page sur le site cible
  if isset($struct['liens']['XXX'])) {
    return "<a href='https://le-site-cible/?taxon=" . $struct['liens']['XXX']['identifiant'] . "'>XXX</a>";
  } else {
    // rien à retourner
    return false;
  }
}