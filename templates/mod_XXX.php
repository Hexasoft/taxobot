<?php

/*
  Module pour XXX (non classification)
*/

// déclaration du module
function m_XXX_init() {
  return declare_module("XXX", false, false, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_XXX_infos(&$struct, $classif) {
  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_XXX_ext($struct) {
  return false;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_XXX_liens($struct) {
  return false;
}

