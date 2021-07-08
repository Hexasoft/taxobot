<?php

/*
  Tente de trouver le (ou les) portails
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

  $struct['liens']['fin']['portails'] = [ $portail ];
  
  // partie catégories
  $cats = [];
  foreach($struct['rangs'] as $r) {
    if ($r['rang'] == 'famille') {
      $cats[] = $r['nom'];
    }
  }
  $tmp = lien_pour_categorie($struct['regne']);
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

function m_fin_fin($struct) {
  $ret = "";
  if (!empty($struct['liens']['fin']['portails'])) {
    $ret = "{{Portail|" . implode("|", $struct['liens']['fin']['portails']) . "}}\n";
  }
  if (!empty($struct['liens']['fin']['categories'])) {
    if (!empty($ret)) {
      $ret .= "\n";
    }
    foreach($struct['liens']['fin']['categories'] as $c) {
      $ret .= "[[Catégorie:" . $c . "]]\n";
    }
  }
  if (!empty($ret)) {
    return $ret;
  }
  return false;
}

