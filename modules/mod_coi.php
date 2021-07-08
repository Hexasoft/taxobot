<?php

/*
  Module de classification COI
*/


// déclaration du module
function m_coi_init() {
  // permet classification, liens externes et accepte tous les domaines
  return declare_module("coi", true, true, [ "oiseau" ], 10);
}

// récupère les données générales liées à COI. Si $classif=TRUE récupère aussi les données de classification
function m_coi_infos(&$struct, $classif) {
  return false;
}

// retourne les liens externes liés à COI (si présents)
function m_coi_ext($struct) {
  return false;
}

// retourne les liens HTTP directs liés à COI (si présents)
function m_coi_liens($struct) {
  return false;
}

function m_coi_fin($struct) {
  return false;
}

