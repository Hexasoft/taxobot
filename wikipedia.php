<?php

/*
 Fonctions et données liées à wikipédia
*/

$rangs = [
 'clade' => [ 'id' => 'clade', 'lien' => '[[Clade]]', 'lienm' => '[[clade]]', 'nom' => 'Clade',
   'nomm' => 'clade', 'nomp' => 'Clades', 'nommp' => 'clades', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'type' => [ 'id' => 'type', 'lien' => '[[Classification des virus|Type]]',
   'lienm' => '[[Classification des virus|type]]', 'nom' => 'Type', 'nomm' => 'type', 'nomp' => 'Types',
   'nommp' => 'types', 'un' => 'un', 'le' => '', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'groupe' => [ 'id' => 'groupe', 'lien' => '[[Classification des virus#Classification par type de génome|Groupe]]',
   'lienm' => '[[Classification des virus#Classification par type de génome|groupe]]', 'nom' => 'Groupe',
   'nomm' => 'groupe', 'nomp' => 'Groupes', 'nommp' => 'groupes', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'non-classé' => [ 'id' => 'non-classé', 'lien' => '— non-classé —', 'lienm' => '— non-classé —',
   'nom' => 'non-classé', 'nomm' => 'non-classé', 'nomp' => 'non-classé', 'nommp' => 'non-classé',
   'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-forme' => [ 'id' => 'sous-forme', 'lien' => '[[Forme (botanique)|Sous-forme]]',
   'lienm' => '[[Forme (botanique)|sous-forme]]', 'nom' => 'Sous-forme', 'nomm' => 'sous-forme',
   'nomp' => 'Sous-formes', 'nommp' => 'sous-formes', 'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'forme' => [ 'id' => 'forme', 'lien' => '[[Forme (botanique)|Forme]]', 'lienm' => '[[Forme (botanique)|forme]]',
   'nom' => 'Forme', 'nomm' => 'forme', 'nomp' => 'Formes', 'nommp' => 'formes', 'un' => 'une ', 'le' => 'la ',
   'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'variété' => [ 'id' => 'variété', 'lien' => '[[Variété (botanique)|Variété]]',
   'lienm' => '[[Variété (botanique)|variété]]', 'nom' => 'Variété', 'nomm' => 'variété',
   'nomp' => 'Variétés', 'nommp' => 'variétés', 'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'pathovar' => [ 'id' => 'pathovar', 'lien' => '[[Pathovar]]', 'lienm' => '[[pathovar]]', 'nom' => 'Pathovar',
   'nomm' => 'pathovar', 'nomp' => 'Pathovars', 'nommp' => 'pathovars', 'un' => 'un ', 'le' => 'le ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'cultivar' => [ 'id' => 'cultivar', 'lien' => '[[Cultivar]]', 'lienm' => '[[cultivar]]', 'nom' => 'Cultivar',
   'nomm' => 'cultivar', 'nomp' => 'Cultivars', 'nommp' => 'cultivars', 'un' => 'un ', 'le' => 'le ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-espèce' => [ 'id' => 'sous-espèce', 'lien' => '[[Sous-espèce]]', 'lienm' => '[[sous-espèce]]',
   'nom' => 'Sous-espèce', 'nomm' => 'sous-espèce', 'nomp' => 'Sous-espèces', 'nommp' => 'sous-espèces',
   'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'hybride' => [ 'id' => 'hybride', 'lien' => '[[Hybride]]', 'lienm' => '[[hybride]]', 'nom' => 'Hybride',
   'nomm' => 'hybride', 'nomp' => 'Hybrides', 'nommp' => 'hybrides', 'un' => 'un ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'espèce' => [ 'id' => 'espèce', 'lien' => '[[Espèce]]', 'lienm' => '[[espèce]]', 'nom' => 'Espèce',
   'nomm' => 'espèce', 'nomp' => 'Espèces', 'nommp' => 'espèces', 'un' => 'une ', 'le' => 'l\'', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-série' => [ 'id' => 'sous-série', 'lien' => '[[Série (biologie)|Sous-série]]',
   'lienm' => '[[Série (biologie)|sous-série]]', 'nom' => 'Sous-série', 'nomm' => 'sous-série',
   'nomp' => 'Sous-séries', 'nommp' => 'sous-séries', 'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'série' => [ 'id' => 'série', 'lien' => '[[Série (biologie)|Série]]', 'lienm' => '[[Série (biologie)|série]]',
   'nom' => 'Série', 'nomm' => 'série', 'nomp' => 'Séries', 'nommp' => 'séries', 'un' => 'une ', 'le' => 'la ',
   'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-section' => [ 'id' => 'sous-section', 'lien' => '[[Section (biologie)|Sous-section]]',
   'lienm' => '[[Section (biologie)|sous-section]]', 'nom' => 'Sous-section', 'nomm' => 'sous-section',
   'nomp' => 'Sous-sections', 'nommp' => 'sous-sections', 'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'section' => [ 'id' => 'section', 'lien' => '[[Section (biologie)|Section]]',
   'lienm' => '[[Section (biologie)|section]]', 'nom' => 'Section', 'nomm' => 'section', 'nomp' => 'Sections',
   'nommp' => 'sections', 'un' => 'une ', 'le' => 'la ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-genre' => [ 'id' => 'sous-genre', 'lien' => '[[Sous-genre (biologie)|Sous-genre]]',
   'lienm' => '[[Sous-genre (biologie)|sous-genre]]', 'nom' => 'Sous-genre', 'nomm' => 'sous-genre',
   'nomp' => 'Sous-genres', 'nommp' => 'sous-genres', 'un' => 'un ', 'le' => 'le ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'genre' => [ 'id' => 'genre', 'lien' => '[[Genre (biologie)|Genre]]', 'lienm' => '[[Genre (biologie)|genre]]',
   'nom' => 'Genre', 'nomm' => 'genre', 'nomp' => 'Genres', 'nommp' => 'genres', 'un' => 'un ',
   'le' => 'le ', 'inf' => true,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-tribu' => [ 'id' => 'sous-tribu', 'lien' => '[[Tribu (biologie)|Sous-tribu]]',
   'lienm' => '[[Tribu (biologie)|sous-tribu]]', 'nom' => 'Sous-tribu', 'nomm' => 'sous-tribu',
   'nomp' => 'sous-tribus', 'nommp' => 'Sous-tribus', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'tribu' => [ 'id' => 'tribu', 'lien' => '[[Tribu (biologie)|Tribu]]', 'lienm' => '[[Tribu (biologie)|tribu]]',
   'nom' => 'Tribu', 'nomm' => 'tribu', 'nomp' => 'Tribus', 'nommp' => 'tribus', 'un' => 'une ',
   'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'super-tribu' => [ 'id' => 'super-tribu', 'lien' => '[[Tribu (biologie)|Super-tribu]]',
   'lienm' => '[[Tribu (biologie)|super-tribu]]', 'nom' => 'Super-tribu', 'nomm' => 'super-tribu',
   'nomp' => 'Super-tribu', 'nommp' => 'super-tribu', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'infra-tribu' => [ 'id' => 'infra-tribu', 'lien' => '[[Tribu (biologie)|Infra-tribu]]',
   'lienm' => '[[Tribu (biologie)|infra-tribu]]', 'nom' => 'Infra-tribu', 'nomm' => 'infra-tribu',
   'nomp' => 'Infra-tribus', 'nommp' => 'infra-tribus', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-famille' => [ 'id' => 'sous-famille', 'lien' => '[[Sous-famille (biologie)|Sous-famille]]',
   'lienm' => '[[Sous-famille (biologie)|sous-famille]]', 'nom' => 'Sous-famille', 'nomm' => 'sous-famille',
   'nomp' => 'Sous-familles', 'nommp' => 'sous-familles', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'famille' => [ 'id' => 'famille', 'lien' => '[[Famille (biologie)|Famille]]',
   'lienm' => '[[Famille (biologie)|famille]]', 'nom' => 'Famille', 'nomm' => 'famille', 'nomp' => 'Familles',
   'nommp' => 'familles', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'épifamille' => [ 'id' => 'épifamille', 'lien' => '[[Famille (biologie)|Épifamille]]',
   'lienm' => '[[Famille (biologie)|épifamille]]', 'nom' => 'Épifamille', 'nomm' => 'épifamille',
   'nomp' => 'Épifamilles', 'nommp' => 'épifamilles', 'un' => 'une ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'super-famille' => [ 'id' => 'super-famille', 'lien' => '[[Super-famille (biologie)|Super-famille]]',
   'lienm' => '[[Super-famille (biologie)|super-famille]]', 'nom' => 'Super-famille', 'nomm' => 'super-famille',
   'nomp' => 'Super-familles', 'nommp' => 'super-familles', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'micro-ordre' => [ 'id' => 'micro-ordre', 'lien' => '[[Micro-ordre]]', 'lienm' => '[[micro-ordre]]',
   'nom' => 'Micro-ordre', 'nomm' => 'micro-ordre', 'nomp' => 'Micro-ordres', 'nommp' => 'micro-ordres',
   'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'infra-ordre' => [ 'id' => 'infra-ordre', 'lien' => '[[Infra-ordre]]', 'lienm' => '[[infra-ordre]]',
   'nom' => 'Infra-ordre', 'nomm' => 'infra-ordre', 'nomp' => 'Infra-ordres', 'nommp' => 'infra-ordres',
   'un' => 'un ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-ordre' => [ 'id' => 'sous-ordre', 'lien' => '[[Sous-ordre]]', 'lienm' => '[[sous-ordre]]',
   'nom' => 'Sous-ordre', 'nomm' => 'sous-ordre', 'nomp' => 'Sous-ordres', 'nommp' => 'sous-ordres',
   'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'ordre' => [ 'id' => 'ordre', 'lien' => '[[Ordre (biologie)|Ordre]]', 'lienm' => '[[Ordre (biologie)|ordre]]',
   'nom' => 'Ordre', 'nomm' => 'ordre', 'nomp' => 'Ordres', 'nommp' => 'ordres', 'un' => 'un ',
   'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'super-ordre' => [ 'id' => 'super-ordre', 'lien' => '[[Super-ordre (biologie)|Super-ordre]]',
   'lienm' => '[[Super-ordre (biologie)|super-ordre]]', 'nom' => 'Super-ordre', 'nomm' => 'super-ordre',
   'nomp' => 'Super-ordres', 'nommp' => 'super-ordres', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-cohorte' => [ 'id' => 'sous-cohorte', 'lien' => '[[Cohorte (biologie)|Sous-cohorte]]',
   'lienm' => '[[Cohorte (biologie)|sous-cohorte]]', 'nom' => 'Sous-cohorte', 'nomm' => 'sous-cohorte',
   'nomp' => 'Sous-cohortes', 'nommp' => 'sous-cohortes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'cohorte' => [ 'id' => 'cohorte', 'lien' => '[[Cohorte (biologie)|Cohorte]]',
   'lienm' => '[[Cohorte (biologie)|cohorte]]', 'nom' => 'Cohorte', 'nomm' => 'cohorte', 'nomp' => 'Cohortes',
   'nommp' => 'cohortes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'super-cohorte' => [ 'id' => 'super-cohorte', 'lien' => '[[Cohorte (biologie)|Super-cohorte]]',
   'lienm' => '[[Cohorte (biologie)|super-cohorte]]', 'nom' => 'Super-cohorte', 'nomm' => 'super-cohorte',
   'nomp' => 'Super-cohortes', 'nommp' => 'super-cohortes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'subter-classe' => [ 'id' => 'subter-classe', 'lien' => '[[Subter-classe]]', 'lienm' => '[[subter-classe]]',
   'nom' => 'Subter-classe', 'nomm' => 'subter-classe', 'nomp' => 'Subter-classes',
   'nommp' => 'subter-classes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'infra-classe' => [ 'id' => 'infra-classe', 'lien' => '[[Infra-classe]]', 'lienm' => '[[infra-classe]]',
   'nom' => 'Infra-classe', 'nomm' => 'infra-classe', 'nomp' => 'Infra-classes', 'nommp' => 'infra-classes',
   'un' => 'une ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-classe' => [ 'id' => 'sous-classe', 'lien' => '[[Sous-classe (biologie)|Sous-classe]]',
   'lienm' => '[[Sous-classe (biologie)|sous-classe]]', 'nom' => 'Sous-classe', 'nomm' => 'sous-classe',
   'nomp' => 'Sous-classes', 'nommp' => 'sous-classes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'classe' => [ 'id' => 'classe', 'lien' => '[[Classe (biologie)|Classe]]',
   'lienm' => '[[Classe (biologie)|classe]]', 'nom' => 'Classe', 'nomm' => 'classe', 'nomp' => 'Classes',
   'nommp' => 'classes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'super-classe' => [ 'id' => 'super-classe', 'lien' => '[[Super-classe (biologie)|Super-classe]]',
   'lienm' => '[[Super-classe (biologie)|super-classe]]', 'nom' => 'Super-classe', 'nomm' => 'super-classe',
   'nomp' => 'Super-classes', 'nommp' => 'super-classes', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'micro-embranchement' => [ 'id' => 'micro-embranchement', 'lien' => '[[Micro-embranchement]]',
   'lienm' => '[[micro-embranchement]]', 'nom' => 'Micro-embranchement', 'nomm' => 'micro-embranchement',
   'nomp' => 'Micro-embranchements', 'nommp' => 'micro-embranchements', 'un' => 'un ', 'le' => 'le ',
   'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'infra-embranchement' => [ 'id' => 'infra-embranchement', 'lien' => '[[Infra-embranchement]]',
   'lienm' => '[[infra-embranchement]]', 'nom' => 'Infra-embranchement', 'nomm' => 'infra-embranchement',
   'nomp' => 'Infra-embranchements', 'nommp' => 'infra-embranchements', 'un' => 'un ', 'l\'' => 'le ',
   'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-embranchement' => [ 'id' => 'sous-embranchement', 'lien' => '[[Sous-embranchement]]',
   'lienm' => '[[sous-embranchement]]', 'nom' => 'Sous-embranchement', 'nomm' => 'sous-embranchement',
   'nomp' => 'Sous-embranchements', 'nommp' => 'sous-embranchements', 'un' => 'un ', 'le' => 'le ',
   'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'embranchement' => [ 'id' => 'embranchement', 'lien' => '[[Embranchement (biologie)|Embranchement]]',
   'lienm' => '[[Embranchement (biologie)|embranchement]]', 'nom' => 'Embranchement', 'nomm' => 'embranchement',
   'nomp' => 'Embranchements', 'nommp' => 'embranchements', 'un' => 'un ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'super-embranchement' => [ 'id' => 'super-embranchement', 'lien' => '[[Super-embranchement]]',
   'lienm' => '[[super-embranchement]]', 'nom' => 'Super-embranchement', 'nomm' => 'super-embranchement',
   'nomp' => 'Super-embranchements', 'nommp' => 'super-embranchements', 'un' => 'un ', 'le' => 'le ',
   'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'infra-division' => [ 'id' => 'infra-division', 'lien' => '[[Division (biologie)|Infra-division]]',
   'lienm' => '[[Division (biologie)|infra-division]]', 'nom' => 'Infra-division', 'nomm' => 'infra-division',
   'nomp' => 'Infra-divisions', 'nommp' => 'infra-divisions', 'un' => 'une ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'sous-division' => [ 'id' => 'sous-division', 'lien' => '[[Sous-division]]', 'lienm' => '[[sous-division]]',
   'nom' => 'Sous-division', 'nomm' => 'sous-division', 'nomp' => 'Sous-divisions', 'nommp' => 'sous-divisions',
   'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'division' => [ 'id' => 'division', 'lien' => '[[Division (biologie)|Division]]',
   'lienm' => '[[Division (biologie)|division]]', 'nom' => 'Division', 'nomm' => 'division', 'nomp' => 'Divisions',
   'nommp' => 'divisions', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'super-division' => [ 'id' => 'super-division', 'lien' => '[[Division (biologie)|Super-division]]',
   'lienm' => '[[Division (biologie)|super-division]]', 'nom' => 'Super-division', 'nomm' => 'super-division',
   'nomp' => 'Super-divisions', 'nommp' => 'super-divisions', 'un' => 'une ', 'le' => 'la ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteinte]]' ],
 'infra-règne' => [ 'id' => 'infra-règne', 'lien' => '[[Infra-règne]]', 'lienm' => '[[infra-règne]]',
   'nom' => 'Infra-règne', 'nomm' => 'infra-règne', 'nomp' => 'Infra-règnes', 'nommp' => 'infra-règnes',
   'un' => 'un ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'rameau' => [ 'id' => 'rameau', 'lien' => '[[Rameau (biologie)|Rameau]]', 'lienm' => '[[Rameau (biologie)|rameau]]',
   'nom' => 'Rameau', 'nomm' => 'rameau', 'nomp' => 'Rameaux', 'nommp' => 'rameaux', 'un' => 'un ',
   'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-règne' => [ 'id' => 'sous-règne', 'lien' => '[[Sous-règne]]', 'lienm' => '[[sous-règne]]',
   'nom' => 'Sous-règne', 'nomm' => 'sous-règne', 'nomp' => 'Sous-règnes', 'nommp' => 'sous-règnes',
   'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'règne' => [ 'id' => 'règne', 'lien' => '[[Règne (biologie)|Règne]]', 'lienm' => '[[Règne (biologie)|règne]]',
   'nom' => 'Règne', 'nomm' => 'règne', 'nomp' => 'Règnes', 'nommp' => 'règnes', 'un' => 'un ', 'le' => 'le ',
   'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'super-règne' => [ 'id' => 'super-règne', 'lien' => '[[Règne (biologie)|Super-règne]]',
   'lienm' => '[[Règne (biologie)|super-règne]]', 'nom' => 'Super-règne', 'nomm' => 'super-règne',
   'nomp' => 'Super-règnes', 'nommp' => 'super-règnes', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-domaine' => [ 'id' => 'sous-domaine', 'lien' => '[[Sous-domaine (biologie)|Sous-domaine]]',
   'lienm' => '[[Sous-domaine (biologie)|sous-domaine]]', 'nom' => 'Sous-domaine', 'nomm' => 'sous-domaine',
   'nomp' => 'Sous-domaines', 'nommp' => 'sous-domaines', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'domaine' => [ 'id' => 'domaine', 'lien' => '[[Domaine (biologie)|Domaine]]',
   'lienm' => '[[Domaine (biologie)|domaine]]', 'nom' => 'Domaine', 'nomm' => 'domaine', 'nomp' => 'Domaines',
   'nommp' => 'domaines', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'super-domaine' => [ 'id' => 'super-domaine', 'lien' => '[[Domaine (biologie)|Super-domaine]]',
   'lienm' => '[[Domaine (biologie)|super-domaine]]', 'nom' => 'Super-domaine', 'nomm' => 'super-domaine',
   'nomp' => 'Super-domaines', 'nommp' => 'super-domaines', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'empire' => [ 'id' => 'empire', 'lien' => '[[Domaine (biologie)|Empire]]',
   'lienm' => '[[Domaine (biologie)|empire]]', 'nom' => 'Empire', 'nomm' => 'empire', 'nomp' => 'Empires',
   'nommp' => 'empires', 'un' => 'un ', 'le' => 'l\'', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'royaume' => [ 'id' => 'royaume', 'lien' => '[[Royaume (virologie)|Royaume]]',
   'lienm' => '[[Royaume (virologie)|royaume]]', 'nom' => 'Royaume', 'nomm' => 'royaume', 'nomp' => 'Royaumes',
   'nommp' => 'royaumes', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'sous-royaume' => [ 'id' => 'sous-royaume', 'lien' => '[[Royaume (virologie)|Sous-royaume]]',
   'lienm' => '[[Royaume (virologie)|sous-royaume]]', 'nom' => 'Sous-royaume', 'nomm' => 'sous-royaume',
   'nomp' => 'Sous-royaumes', 'nommp' => 'sous-royaumes', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
  'phylum' => [ 'id' => 'phylum', 'lien' => '[[phylum|Phylum]]',
   'lienm' => '[[phylum|phylum]]', 'nom' => 'Phylum', 'nomm' => 'phylum',
   'nomp' => 'phylum', 'nommp' => 'phylum', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
  'kingdom' => [ 'id' => 'kingdom', 'lien' => '[[Kingdom (biologie)|Kingdom]]',
   'lienm' => '[[Kingdom (biologie)|kingdom]]', 'nom' => 'Kingdom', 'nomm' => 'kingdom',
   'nomp' => 'kingdom', 'nommp' => 'kingdom', 'un' => 'un ', 'le' => 'le ', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
 'NOTFOUND' => [ 'id' => 'NOTFOUND', 'lien' => 'NOTFOUND',
   'lienm' => 'NOTFOUND', 'nom' => 'NOTFOUND', 'nomm' => 'NOTFOUND',
   'nomp' => 'NOTFOUND', 'nommp' => 'NOTFOUND', 'un' => 'NOTFOUND', 'le' => 'NOTFOUND', 'inf' => false,
   'ex' => '[[Extinction des espèces|éteint]]' ],
];

// table des italiques (true) partout
$italiques = [
  'algue' => true,
  'animal' => false,
  'reptile' => false,
  'amphibien' => false,
  'protiste' => false,
  'eucaryote' => false,
  'archaea' => true,
  'bactérie' => true,
  'champignon' => true,
  'végétal' => true,
  'virus' => true,
  'procaryote' => true,
  'neutre' => true,
  'NOTFOUND' => true,
];

// liste des ébauches
$ebauches = [
  'algue' => 'algue',
  'animal' => 'zoologie',
  'reptile' => 'reptile',
  'amphibien' => 'amphibien',
  'archaea' => 'biologie',
  'bactérie' => 'bactérie',
  'champignon' => 'champignon',
  'protiste' => 'protiste',
  'végétal' => 'botanique',
  'virus' => 'virus',
  'neutre' => 'biologie',
  'eucaryote' => 'biologie',
  'procaryote' => 'biologie',
];

// si inférieur à espèce
function est_inf_espece($rang) {
  $inf_espece = [
    'sous-forme', 'forme', 'variété', 'pathovar', 'cultivar', 'sous-espèce',
    'sous-série', 'série', 'sous-section', 'section',
  ];
  if (in_array($rang, $inf_espece)) {
    return true;
  }
  return false;
}

// retourne l'ébauche la plus adaptée (ne supporte qu'une seule ébauche, pas de tableau en sortie)
function wp_ebauche($struct) {
  global $ebauches;

  if (get_config("selecteurs")) {
    $ret = sel_evalue("selecteurs/ebauches.lst.local", $struct);
    if ($ret !== false) {
      return $ret;
    }
    $ret = sel_evalue("selecteurs/ebauches.lst", $struct);
    if ($ret !== false) {
      return $ret;
    }
  }

  // on fait simple : sur le règne
  if (isset($ebauches[$struct['regne']])) {
    return [ $ebauches[$struct['regne']] ];
  } else {
    return [];
  }
}

// indique si un rang est connu
function wp_rang_valide($rang) {
  global $rangs;

  if (isset($rangs[$rang])) {
    return true;
  }
  return false;
}

// retourne le "éteint" du rang
function wp_eteint_rang($rang) {
  global $rangs;

  if (!wp_rang_valide($rang)) {
    return "NOTFOUND";
  }
  return $rangs[$rang]['ex'];
}

// uppercase la première lettre d'une string => outils.php ?
function mb_ucfirst($string, $encoding = 'UTF-8') {
  return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, null, $encoding);
}

function wp_article_mot($mot, $genre = "", &$tableau = null) {
  if (!empty($mot)) {
    if (is_null($tableau)) {
      global $rangs;
      $tableau = $rangs;
      if (!wp_rang_valide($mot)) {
        echo "$mot : NOTFOUND:rang_invalide_art1";
        return "NOTFOUND";
      }
    }

    // bas de casse
    $mot = strtolower($mot);

    // Obtenir les articles du mot en fonction du genre
    $articles = [
      'm' => ['le', 'un'],
      'f' => ['la', 'une'],
      'n' => ['', '']
    ];
    if (!empty($genre)) {
      $genre_mot = $genre;
    } else {
      $genre_mot = wp_genre_mot($mot);
    }

    // vérifie la première lettre pour l'élision
    $premiere_lettre = mb_substr($mot, 0, 1);
    $article0 = (in_array($premiere_lettre, ["a", "e", "i", "o", "u", "é"])) ? "l'" : $articles[$genre_mot][0];
    $article1 = (in_array($premiere_lettre, ["a", "e", "i", "o", "u", "é"])) ? "l'" : $articles[$genre_mot][1];
    
    $result['article'] = [$article0, $article1];
    return $result;
    } else {
      error("wp_article_rang() ! Le paramètre 'mot' est obligatoire.");
      return "";
    }
}

// retourne l'article défini ('le', 'la') du rang
function wp_article_défini($mot) {
  if (is_null($tableau)) {
    global $rangs;
    $tableau = $rangs;
  }
  $article = wp_article_mot($mot);
  if (isset($article['article']) && count($article['article']) >= 2) {
    return $article['article'][0];
  } else {
    error("$mot : NOTFOUND:art0");
    return "NOTFOUND";
  }
}

// retourne l'article indéfini ('un', 'une') du rang
function wp_article_indéfini($mot, $tableau = null) {
  if (is_null($tableau)) {
    global $rangs;
    $tableau = $rangs;
  }
  $article = wp_article_mot($mot);
  if (isset($article['article']) && count($article['article']) >= 2) {
    return $article['article'][1];
  } else {
    error("$mot : NOTFOUND:art1");
    return "NOTFOUND";
  }
}

// Génère un lien wiki à partir d'une page et d'un texte optionnel
function wp_wikilien($page, $texte, $maj = false, $plur = false, $article = false, $adjectif = "", &$tableau = null) {
/**
 * Génère un lien wiki à partir d'une page et d'un texte optionnel.
 * @param string $page Le nom de la page ou la clé d'un tableau (obligatoire).
 * @param string $texte Le texte à afficher pour le lien (facultatif).
 * @param bool $maj Mettre en majuscule le libellé et/ou le nom de la page (facultatif, par défaut à false).
 * @param bool $plur Indique si le lien doit être généré au pluriel. Si true, le libellé sera traité comme étant au pluriel.
 * @param bool $article Renseigne l'article défini ou indéfini qui sera placé avant le titre de la page.
 * @param string $adjectif Renseigne un adjectif placé après le wikilien (tente de s'accorder en genre et nombre sur le titre de la page).
 * @param array $tableau Le tableau à vérifier (facultatif, par défaut à null pour utiliser $rangs global).
 *
 * @return string Le lien wiki généré.
 */

  // Utiliser le tableau passé en paramètre s'il est spécifié, sinon utiliser le tableau global $rang
  if (is_null($tableau)) {
    global $rangs;
    $tableau = $rangs;
  }

  // Convertir le nom de la page en minuscules pour s'assurer que cela correspond à la clé du tableau
  $page = strtolower($page);

  $tpage = '';
  $libellé = '';

  if (!empty($page)) {
    // Vérifier si la page existe dans le tableau
    if (isset($tableau[$page])) {
      // Vérifier si une page interne est spécifiée, sinon utiliser la page telle quelle
      if (!empty($tableau[$page]["lien interne"]["page"])) {
        $tpage = $tableau[$page]["lien interne"]["page"];
      } else {
        $tpage = $tableau[$page];
      }
    } else {
      // Si la page n'existe pas dans le tableau, utiliser la page telle quelle
      $tpage = $page;
    }
  } else {
  
   error("wp_wikilien : un mot (nom de page) est obligatoire.");
  return "NOTFOUND:wikilien_nom_page_manquant";
  }

  if (empty($texte)) {
    // Vérifier si un libellé est spécifié, sinon utiliser le texte fourni
    if (isset($tableau[$page]["lien interne"]["texte"])) {
      $libellé = $tableau[$page]["lien interne"]["texte"];
    } else {
      $libellé = $texte;
    }
  }

  if ($plur) {
    // Si le pluriel est renseigné dans le tableau, on l'utilise.
    if (isset($tableau[$page]["lien interne"]["pluriel"])) {
        $libellé = $tableau[$page]["lien interne"]["pluriel"];
    } else { 
        if (!empty($libellé)) {
            // Si le libellé est déjà défini (non vide), ajoute un "s" pour le pluriel.
            $libellé .= "s";
        } else {
            // Si le libellé n'est pas encore défini, génère un libellé au pluriel
            if (!empty($tpage)) {
                $libellé = $tpage . "s";
            } else { 
                $libellé = $page . "s";
            }
        }
    }
  }

  if ($maj && !empty($libellé)) {
    // Si la majuscule est activée et un libellé est spécifié, mettre en majuscule le libellé
    $libellé = mb_ucfirst($libellé);
  } else {
    // Sinon, mettre en majuscule la page
    $tpage = mb_ucfirst($tpage);
  }

  if ($article) {
  $art = wp_article_mot($page);
  $art .= " "; // espace
  } else { $art = ""; }

  // Construire le lien wiki
  $wikilien = $art;
  $wikilien .= "[[$tpage";

  if (empty($libellé)) {
    // Si aucun libellé est spécifié, fermer le lien
    $wikilien .= "]]";
  } else {
    // Sinon, ajouter le libellé et fermer le lien
    $wikilien .= "|$libellé]]";
  }

  if (!empty($adjectif)) {
    if ($plur) {
      $adj = wp_adjectif_mot($page, $adjectif, $plur);
    } else { $adj = wp_adjectif_mot($page, $adjectif); }
    $wikilien .= " "; // espace
    $wikilien .= $adj;
  }

  // Retourner le lien wiki généré
  return $wikilien;
}

function est_genre_valide($genre) {
  $genres_valides = ['m', 'msg', 'mpl', 'f', 'fsg', 'fpl', 'n', 'nsg', 'npl'];
  return in_array($genre, $genres_valides);
}

function wp_genre_mot($mot, $genre = "", $tableau = null) {
  if (!empty($mot)) {
    if (is_null($tableau)) {
      global $rangs;
      $tableau = $rangs;
    }
    
    // Bas de casse
    $mot = strtolower($mot);

    if (empty($genre)) {
      $genre_mot = isset($tableau[$mot]["genre"]) ? $tableau[$mot]["genre"] : "m"; // Masculin par défaut
    } else { 
      $genre_mot = est_genre_valide($genre) ? $genre : "NOTFOUND: genre de $mot invalide (valeurs acceptées : 'm', 'msg', 'mpl', 'f', 'fsg', 'fpl', 'n', 'nsg', 'npl').";
    }
    return $genre_mot;
  }
}

function wp_adjectif_mot($page, $adjectif, $genre, $plur = false, &$tableau = null) {
  // Utiliser le tableau passé en paramètre s'il est spécifié, sinon utiliser le tableau global $adjectifs
  if (is_null($tableau)) {
    global $adjectifs;
    $tableau = $adjectifs;
  }
  if (est_genre_valide($genre)) {
    $genre_mot = $genre;
  } else {
    $genre_mot = wp_genre_mot($page, "", $tableau);
    if ($genre_mot === "NOTFOUND") { // Ajout d'une vérification pour la gestion d'erreur
      echo "Erreur : wp_adjectif_mot ! Le genre du mot '$page' n'a pas été trouvé.";
      return $adjectif;
    } else { // Si on trouve, on détermine le nombre
      if ($plur) {
        $genre_mot .= "pl";
      } else {
        $genre_mot .= "sg";
      }
    }
  }

  // On tente l'affectation sinon on retourne l'adjectif sans accord.
  $adj = (isset($tableau[$adjectif][$genre_mot])) ? $tableau[$adjectif][$genre_mot] : $adjectif;
  return $adj;
}

// retourne le "un" du rang
function wp_un_rang($rang) {
  global $rangs;

  if (!wp_rang_valide($rang)) {
    return "NOTFOUND";
  }
  return $rangs[$rang]['un'];
}
// retourne le "le" du rang
function wp_le_rang($rang) {
  global $rangs;

  if (!wp_rang_valide($rang)) {
    return "NOTFOUND";
  }
  return $rangs[$rang]['le'];
}
// retourne si le rang est inférieur au genre
function wp_inf_rang($rang) {
  global $rangs;

  if (!wp_rang_valide($rang)) {
    return "NOTFOUND";
  }
  return $rangs[$rang]['inf'];
}

// supprime le rang indiqué de la liste des rangs
function wp_supprime_rang(&$struct, $rang) {
  foreach($struct['rangs'] as $idx => $r) {
    if ($r['rang'] == $rang) {
      unset($struct['rangs'][$idx]);
    }
  }
}

// retourne TRUE si le rang de ce "règne" doit être en italique
function wp_est_italique($rang, $regne) {
  global $italiques;

  if ($italiques[$regne]) {
    return true;
  }
  return wp_inf_rang($rang);
}

// retourne le nom d'un rang selon les options demandées
// (true ou false)
// avec ou sans wikilien / avec ou sans majuscule sur la première lettre / au pluriel ou au singulier
function wp_nom_rang($rang, $lien, $maj, $plur) {
  global $rangs;

  if (!wp_rang_valide($rang)) {
    return "NOTFOUND";
  }
  if ($lien) {
    if ($maj) {
      if ($plur) {
        return $rangs[$rang]['lien'];
      } else {
        return $rangs[$rang]['lien'];
      }
    } else {
      if ($plur) {
        return $rangs[$rang]['lienm'];
      } else {
        return $rangs[$rang]['lienm'];
      }
    }
  } else {
    if ($maj) {
      if ($plur) {
        return $rangs[$rang]['nomp'];
      } else {
        return $rangs[$rang]['nom'];
      }
    } else {
      if ($plur) {
        return $rangs[$rang]['nommp'];
      } else {
        return $rangs[$rang]['nomm'];
      }
    }
  }
}

// exclusions pour italiques
$exclusions = [
  [ " cl[.]", " ''cl.''" ], [ "convar[.]", "''convar.''" ], [ "f[.]sp[.]", "''f.sp.''" ], [ " f[.]", " ''f.''" ],
  [ " gen[.]", " ''gen.''" ], [ "kl[.]", "''kl.''" ], [ "nothog[.]", "''nothog.''" ],
  [ "nothosp[.]", "''nothosp.''" ], [ "nothovar[.]", "''nothovar.''" ], [ " ord[.]", " ''ord.''" ],
  [ " fam[.]", " ''fam.''" ], [ " sect[.]", " ''sect.''" ], [ " ser[.]", " ''ser.''" ],
  [ " sp[.]", " ''sp.''" ], [ "subg[.]", "''subg.''" ], [ "subsp[.]", "''subsp.''" ],
  [ "Groupe", "''Groupe''" ], [ " tr[.]", " ''tr.''" ], [ " var[.]", " ''var.''" ],
  [ "×", "''×''" ], [ "[(]", "''(''" ], [ "[)]", "'')''" ], [ "pv", "''pv''" ],
  [ "pathovar", "''pathovar''" ], [ "morphovar", "''morphovar''" ], [ "phagovar", "''phagovar''" ],
  [ "serovar", "''serovar''" ], [ "chemovar", "''chemovar''" ], [ "cultivar", "''cultivar''" ],
  [ "chemoform", "''chemoform''" ], [ "chemotype", "''chemotype''" ], [ "morphotype", "''morphotype''" ],
  [ "pathotype", "''pathotype''" ], [ "phagotype", "''phagotype''" ], [ "lysotype", "''lysotype''" ],
  [ "phase", "''phase''" ], [ "serotype", "''serotype''" ], [ "state", "''state''" ],
  [ "forma specialis", "''forma specialis''" ]
];

// génère en wikicode un nom scientifique avec la gestion des italiques
function wp_met_italiques($taxon, $rang, $regne, $lien=false, $souslien=true) {
  global $exclusions;

  if ($taxon === null || !isset($taxon)) {
    echo "wp_met_italiques() nécessite un paramètre 'taxon' non vide et non nul.";
    return "Erreur:wp_met_italiques()"; // Capture d'erreur pour preg_replace().
}

  $ref = $taxon;

  // italique nécessaire ?
  if (!wp_est_italique($rang, $regne)) {
    if ($lien) {
      return "[[" . $taxon . "]]";
    } else {
      return $taxon; // pas de modification
    }
  }
  foreach($exclusions as $e) {
    $taxon = preg_replace("/\b" . $e[0] . "\b/", $e[1], $taxon);
  }

  if ($taxon == $ref) {
    if ($lien) {
      return "''[[" . $taxon . "]]''";
    } else {
      if ($souslien) {
        return "''$taxon''";
      } else {
        return "$taxon";
      }
    }
  } else {
    if ($lien) {
      return "[[" . $ref . "|" . "''$taxon''" . "]]";
    } else {
      if ($souslien) {
        return "''$taxon''";
      } else {
        return "$taxon";
      }
    }
  }
}

// lien pour "auteur"
$lien_auteurs = [
  'algue' => 'Citation d\'auteurs en botanique',
  'animal' => 'Citation d\'auteurs en zoologie',
  'reptile' => 'Citation d\'auteurs en zoologie',
  'amphibien' => 'Citation d\'auteurs en zoologie',
  'archaea' => 'Citation d\'auteurs en bactériologie',
  'bactérie' => 'Citation d\'auteurs en bactériologie',
  'champignon' => 'Citation d\'auteurs en botanique',
  'protiste' => 'Citation d\'auteurs en zoologie',
  'végétal' => 'Citation d\'auteurs en botanique',
  'virus' => 'Auteur#Dans les sciences et techniques',
  'neutre' => 'Auteur#Dans les sciences et techniques',
  'eucaryote' => 'Auteur#Dans les sciences et techniques',
  'procaryote' => 'Citation d\'auteurs en bactériologie',
];
function lien_pour_auteur($regne) {
  global $lien_auteurs;

  if (isset($lien_auteurs[$regne])) {
    return $lien_auteurs[$regne];
  } else {
    return 'Auteur#Dans les sciences et techniques';
  }
}

// lien pour le basionyme
$lien_basio = [
  'algue' => '[[basionyme]]',
  'animal' => '[[protonyme]]',
  'reptile' => '[[protonyme]]',
  'amphibien' => '[[protonyme]]',
  'archaea' => '[[basionyme]]',
  'bactérie' => '[[basonyme]]',
  'champignon' => '[[basionyme]]',
  'protiste' => '[[basonyme]]',
  'végétal' => '[[basionyme]]',
  'virus' => '[[basonyme]]',
  'neutre' => '[[basionyme]]',
  'eucaryote' => '[[basionyme]]',
  'procaryote' => '[[basionyme]]',
];
function lien_pour_basionyme($regne) {
  global $lien_basio;

  if (isset($lien_basio[$regne])) {
    return $lien_basio[$regne];
  } else {
    return '[[basionyme]]';
  }
}

// catégorie générale
$categories = [
  'algue' => 'Algue (nom scientifique)',
  'animal' => 'Animal (nom scientifique)',
  'reptile' => 'Animal (nom scientifique)',
  'amphibien' => 'Animal (nom scientifique)',
  'archaea' => 'Archée (nom scientifique)',
  'bactérie' => 'Bactérie (nom scientifique)',
  'champignon' => 'Champignon (nom scientifique)',
  'protiste' => 'Protiste (nom scientifique)',
  'végétal' => 'Plante (nom scientifique)',
  'virus' => '',
  'neutre' => '',
  'eucaryote' => 'Eucaryote (nom scientifique)',
  'procaryote' => '',
];
function lien_pour_categorie($struct) {
  global $categories;
  $regne = $struct['regne'];

  if (get_config("selecteurs")) {
    $ret = sel_evalue("selecteurs/categories.lst.local", $struct);
    if ($ret !== false) {
      return $ret;
    }
    $ret = sel_evalue("selecteurs/categories.lst", $struct);
    if ($ret !== false) {
      return $ret;
    }
  }

  // cas par défaut (pas de traitement particulier)
  if (isset($categories[$regne])) {
    return [$categories[$regne]];
  } else {
    return false;
  }
}

// retourne FALSE (si rien de particulier) ou un *tableau* de portails à insérer
// $portail contient le portail par défaut déjà trouvé (qui peut être ignoré ou ré-inséré)
function lien_pour_portail($portail, $struct) {
  if (get_config("selecteurs")) {
    $ret = sel_evalue("selecteurs/portails.lst.local", $struct);
    if ($ret !== false) {
      return $ret;
    }
    $ret = sel_evalue("selecteurs/portails.lst", $struct);
    if ($ret !== false) {
      return $ret;
    }
  }
  return false;
}

// article "synonymes" selon le règne
$synonymes = [
  'algue' => 'Synonyme (taxinomie)',
  'animal' => 'Synonyme (zoologie)',
  'reptile' => 'Synonyme (zoologie)',
  'amphibien' => 'Synonyme (zoologie)',
  'archaea' => 'Synonyme (taxinomie)',
  'bactérie' => 'Synonyme (taxinomie)',
  'champignon' => 'Synonyme (botanique)',
  'protiste' => 'Synonyme (taxinomie)',
  'végétal' => 'Synonyme (botanique)',
  'virus' => 'Synonyme (taxinomie)',
  'neutre' => 'Synonyme (taxinomie)',
  'eucaryote' => 'Synonyme (taxinomie)',
  'procaryote' => 'Synonyme (taxinomie)',
];
function lien_pour_synonyme($regne) {
  global $synonymes;

  if (isset($synonymes[$regne])) {
    return $synonymes[$regne];
  } else {
    return 'Synonyme (taxinomie)';
  }
}
