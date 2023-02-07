<?php

/*
  Module pour lpsn (non classification)
*/

// traduction des rangs LPSN (pour WP et pour le modèle externe)
$lpsn_rangs = [
  "species" => "espèce", "genus" => "genre", "family" => "famille",
  "order" => "ordre", "class" => "classe", "phylum" => "phylum", "domain" => "domaine" ];


// déclaration du module (actuellement : pas classif ; liens externes ; tous les domaines
function m_lpsn_init() {
  return declare_module("lpsn", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_lpsn_infos(&$struct, $classif) {
  global $lpsn_rangs;

  $taxon = $struct['taxon']['nom'];

  // appel à vide pour les cookies
  $url = "https://lpsn.dsmz.de/advanced_search";
  $ret = get_data($url);
  if ($ret === false) {
    logs("LPSN: échec de connexion au site");
    return false;
  }
  
  // on fait la requête de recherche
  $url = "https://lpsn.dsmz.de/advanced_search?adv[taxon-name]=$taxon&adv[category]=" .
         "&adv[nomenclature]=&adv[valid-publ]=yes&adv[candidatus]=no" .
         "&adv[correct-name]=yes&adv[authority]=&adv[deposit]=" .
         "&adv[nomenclatural-status]=&adv[proposed-as]=&adv[etymology]=" .
         "&adv[gender]=&adv[date-option]=&adv[date]=" .
         "&adv[date-between]=&adv[riskgroup]=&adv[submit]=submit-adv#results";
  $ret = get_data($url);
  if ($ret === false) {
    logs("LPSN: échec de connexion au site (2)");
    return false;
  }
  
  $tbl = explode("\n", $ret);
  $in = false;
  $trouve = false;
  // on cherche les réponses (éventuelles)
  foreach($tbl as $ligne) {
    if (!$in) {
      if (strpos($ligne, '<div class="body">') !== false) {
        $in = true;
      }
      continue;
    }
    if (strpos($ligne, '<a href="') !== false) {
echo ">>$ligne\n";
      $px = explode('"', $ligne);
      if (isset($px[1])) {
        $py = explode("/", $px[1]);
        if (isset($py[1]) and isset($py[2])) {
          $rang0 = $py[1];
          $lien = $py[2];
          if (isset($lpsn_rangs[$rang0])) {
            $rang = $lpsn_rangs[$rang0];
            $lien2 = str_replace("-", " ", $lien);
            if (strcasecmp($taxon, $lien2) == 0) {
              $trouve = true;
              break;
            }
          }
        }
      }
    }
  }
  
  if (!$trouve) {
    logs("LPSN: taxon non trouvé");
    return false;
  }

  // on stocke les informations pour lien externe
  $blob['nom'] = $taxon;
  $blob['rang'] = $rang;
  $blob['rang-lpsn'] = $rang0;
  $blob['lien'] = $lien;
  // il faudrait aller chercher l'auteur, mais ça fait une requête de plus
  $struct['liens']['lpsn'] = $blob;

  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_lpsn_ext($struct) {
  if (isset($struct['liens']['lpsn']['lien'])) {
    $data = $struct['liens']['lpsn'];
    $cdate = dates_recupere();
    
    $rang = isset($data['rang'])?$data['rang']:$struct['taxon']['rang'];
    $nom = wp_met_italiques($data['nom'], $rang, 'animal');
    $id = $data['lien'];
    if (isset($data['auteur'])) {
      $nom .= " " . $data['auteur'];
    }
    return "{{LPSN | $rang | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_lpsn_liens($struct) {
  if (isset($struct['liens']['lpsn']['lien'])) {
    $data = $struct['liens']['lpsn'];
    return "<a href='https://lpsn.dsmz.de/" . $data['rang-lpsn'] . "/" . $data['lien'] . ">LPSN</a>";
  } else {
    return false;
  }
}

