<?php

/*
  Module pour XXX  gérant une classification
  Ceci est un exemple de code
*/

// déclaration du module
function m_XXX_init() {
  // son nom est 'XXX', il génère une classification, il génère un lien externe,
  // il s'applique uniquement aux végétaux
  return declare_module("XXX", false, true, ['végétal']);
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

  /* si on est appelé sans classification, on s'arrête là et c'est ok */
  if (!$classif) {
    return true;
  }
  // maintenant on gère la partie classification
  $struct['taxon']['rang'] = $rang_du_taxon;
  $struct['taxon']['auteur'] = $auteur_du_taxon;
  // on indique que c'est nous
  $struct['classification'] = 'XXX';
  $struct['classification-taxobox'] = 'xxx'; // le nom du champ 'classification' de taxobox début

  // on instancie les rangs inférieurs s'il y en a
  if ($rangs_inf) {
    $struct['sous-taxons']['liste'] = [ $liste_de_taxons_avec_nom_auteur_rang ];
    $struct['sous-taxons']['source'] = 'XXX';  // le nom du modèle bioref associé
  }
  // on stocke le basionyme si présent
  if ($basionyme) {
    $struct['basionyme']['nom'] = $nom_basionyme;
    $struct['basionyme']['auteur'] = $auteur_basionyme;
    $struct['basionyme']['rang'] = $rang_basionyme;
    $struct['basionyme']['source'] = 'XXX';  // le nom du modèle bioref associé
  }
  // on stocke les synonymes si présent
  if ($synonymes) {
    $struct['synonymes']['liste'] = [ $liste_de_taxons_avec_nom_auteur_rang ];
    $struct['synonymes']['source'] = 'XXX';  // le nom du modèle bioref associé
  }
  // on a trouvé les infos, ok
  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_XXX_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['XXX'])) {
    $data = $struct['liens']['XXX'];
    // le nom (avec italiques si besoin) note : si $data contient un rang, l'utiliser en priorité
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