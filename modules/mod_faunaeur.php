<?php

/*
  Module pour faunaeur (non classification)
*/

// déclaration du module
function m_faunaeur_init() {
  return declare_module("faunaeur", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_faunaeur_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $url = "https://fauna-eu.org/cdm_dataportal/search/results/taxon?" .
         "ws=portal%2Ftaxon%2Ffind&query=" . str_replace(" ", "+", $taxon) .
         "&form_build_id=form-BQugWFJYRPGve8Fvc2jXOMpTh7kmZSU6PrTPchsHjOk" .
         "&form_id=cdm_dataportal_search_taxon_form" .
         "&search%5BdoTaxaByCommonNames%5D=&search%5BdoSynonyms%5D=" .
         "&search%5BdoTaxa%5D=1&search%5BpageSize%5D=25" .
         "&search%5BpageNumber%5D=0";
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("Faunaeur: erreur réseau");
    return false;
  }
  // parcours des lignes pour trouver les propositions
  $tbl = explode("\n", $ret);
  $ok = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, "TaxonName") === false) {
      continue;
    }
    $ok = $ligne;
    break;
  }
  if (!$ok) {
    logs("Faunaeur: taxon non trouvé");
    return false;
  }

  // on éclate la réponse, qui peut contenir plusieurs taxons
  $tmp = preg_replace("/cdm:Taxon/", "\n", $ok);
  $tbl = explode("\n", $tmp);
  $blob = [];
  foreach($tbl as $ligne) {
    if (strpos($ligne, "TaxonName") === false) {
      continue;
    }
    // extraction du nom du taxon
    $nom = preg_replace("/^.*TaxonName/", "<", $ligne);
    $nom = preg_replace("/class=\"authors.*$/", ">", $nom);
    $nom = preg_replace("/[<][^>]*[>]/", "", $nom);
    if ($nom != $struct['taxon']['nom']) {
      continue;
    }
    // extraction de l'identifiant
    $id = preg_replace("/^.* uuid:/", "", $ligne);
    $id = preg_replace("/ .*$/", "", $id);
    if ($id == "") {
      continue;
    }

    // extraction de l'auteur
    $auteur = preg_replace("/^.*authors\">/", "", $ligne);
    $auteur = preg_replace(",</span>.*$,", "", $auteur);
    $auteur = preg_replace("/^.*>/", "", $auteur);

    // construction
    $blob['nom'] = $nom;
    $blob['auteur'] = $auteur;
    $blob['id'] = $id;
    break; // trouvé, on arrête
  }

  if (empty($blob)) {
    logs("Faunaeur: taxon non trouvé (2)");
    return false;
  }
  // on met le lien externe
  $struct['liens']['faunaeur']['nom'] = $blob['nom'];
  $struct['liens']['faunaeur']['id'] = $blob['id'];
  $struct['liens']['faunaeur']['auteur'] = $blob['auteur'];

  if (!$classif) {
    return true;
  }

  // pas de gestion de la classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_faunaeur_ext($struct) {
  $cdate = dates_recupere();

  if (isset($struct['liens']['faunaeur']['id'])) {
    $data = $struct['liens']['faunaeur'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{Faunaeur2 | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_faunaeur_liens($struct) {
  if (isset($struct['liens']['faunaeur']['id'])) {
    return "<a href='https://fauna-eu.org/cdm_dataportal/taxon/" .
           $struct['liens']['faunaeur']['id'] . "'>Faunaeur</a>";
  } else {
    return false;
  }
}