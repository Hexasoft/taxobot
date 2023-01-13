<?php

/*
  Tente de trouver le (ou les) portails
  Note : ne fait aucun rendu. Uniquement utilisé par rendu() et les fonctions associées
*/


function m_fin_init() {
  return declare_module("fin", false, false, true);
}

function m_fin_infos(&$struct, $classif) {
  // partie portails
  $portail = "biologie";  // par défaut
  
  // on affine par "règne"
  if ($struct['regne'] == 'animal') {
    $portail = "zoologie";
  } elseif ($struct['regne'] == 'végétal') {
    $portail = "botanique";
  } elseif ($struct['regne'] == 'champignon') {
    $portail = "mycologie";
  } elseif ($struct['regne'] == 'algue') {
    $portail = "phycologie";
  } elseif ($struct['regne'] == 'reptile') {
    $portail = "herpétologie";
  } elseif ($struct['regne'] == 'amphibien') {
    $portail = "herpétologie";
  } elseif ($struct['regne'] == 'virus') {
    $portail = "virologie";
  } elseif ($struct['regne'] == 'archaea') {
    $portail = "microbiologie";
  } elseif ($struct['regne'] == 'bactérie') {
    $portail = "microbiologie";
  } elseif ($struct['regne'] == 'protiste') {
    $portail = "microbiologie";
  } elseif ($struct['regne'] == 'champignon') {
    $portail = "mycologie";
  }
  
  $tmp = lien_pour_portail($portail, $struct);
  if ($tmp) {
    $struct['liens']['fin']['portails'] = $tmp;
  } else {
    $struct['liens']['fin']['portails'] = [ $portail ];
  }
  
  // partie catégories (catégorie de famille, ici c'est tout)
  $cats = [];
  if (isset($struct['rangs'])) {
    foreach($struct['rangs'] as $r) {
      if ($r['rang'] == 'famille') {
        $cats[] = $r['nom'];
      }
    }
  }
  $tmp = lien_pour_categorie($struct);
  if ($tmp) {
    $cats[] = $tmp;
  }
  $struct['liens']['fin']['categories'] = $cats;
  return true;
}


function m_fin_ext($struct) {
  return false;
}

function m_fin_liens($struct) {
  return false;
}

