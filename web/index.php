<?php
// Dépendances
require_once join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'outils.php'));
require_once join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'configuration.php'));
require_once join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'modules.php'));
require_once join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'wikipedia.php'));
require_once join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'rendu.php'));
?>

<!-- En-tête -->
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
        <!-- Multi-select -->
        <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-multiselect.js"></script>
        <link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>

        <title>Taxobot</title>
    </head>
    <header>
    <nav class="menu menu-left">
      <ul>
        <li><a href="../web/index.php" target="_blank">Recherche</a></li>
      </ul>
    </nav>
    <div class="logo">
        <a href="https://commons.wikimedia.org/wiki/File:Logo_Taxobot.png">
            <img src="image/logo.png" alt="Logo de Taxobot">
        </a>
    </div>
    <nav class="menu menu-right">
      <ul>
        <li><a href="https://fr.wikipedia.org/wiki/Projet:Biologie/Taxobot" target="_blank">Wikipédia</a></li>
        <li><a href="https://github.com/Hexasoft/taxobot" target="_blank">GitHub</a></li>
      </ul>
    </nav>
  </header>
    <body>
    <main>
        <div id="processing-message" style="display:none;">
        <p>La requête est en cours de traitement dans un nouvel onglet. Le traitement peut prendre quelques minutes...</p>
        </div>
        <!-- Formulaire -->
        <div class="form">
            <form id="search-form" action="../taxobot.php" method="GET" onsubmit="updateOffField(); showProcessingMessage();" target="_blank">
                <div class="form-input">
                    <!-- Menu : domaine -->
                    <select data-trigger name="domaine">
                        <option value="*" selected>Domaine (*)</option>
                        <option value="animal">Animal</option>
                        <option value="végétal">Végétal</option>
                        <option value="champignon">Champignon</option>
                        <option value="bactérie">Bactérie</option>
                        <option value="algue">Algue</option>
                        <option value="protiste">Protiste</option>
                        <!-- autres chartes -->
                    </select>

                    <!-- Menu : Classification -->
                    <select data-trigger name="classification">
                        <option value="" selected>Classification (*)</option>
                        <option value="gbif" selected>GBIF</option>
                        <option value="itis">ITIS</option>
                        <option value="mycobank">MycoBank</option>
                        <option value="wrms">WoRMSms</option>
                        <option value="algaebase">AlgaeBase</option>
                        <option value="lpsn">LPSN</option>

                        <!-- Autres classifications -->
                    </select>

                    <!-- Champ de texte -->
                    <input type="text" name="taxon" id="taxon" placeholder="Saisir le nom du taxon" required onblur="trimInput(this)">

                    <!-- Bouton "Rechercher" -->
                    <button type="submit" id="search-button">Rechercher</button>

                    <!-- Bouton "Options" -->
                    <button type="button" id="options-button">Options</button>
                </div>
                
                <div id="options" style="display:none;">

                                    <div class="column">
                                        <!-- Checkboxes -->
                                        <input type="checkbox" name="liens-synonymes" id="liens-synonymes" value="true" checked>
                                        <label for="liens-synonymes">Liens sur les synonymes</label>
                                        <br/>
                                        <input type="checkbox" name="suivre-synonymes" id="suivre-synonymes" value="true" checked>
                                        <label for="suivre-synonymes">Si le taxon demandé est un synonyme, suivre et traiter.</label>
                                        <br/>
                                        <input type="checkbox" name="trier-synonymes" id="trier-synonymes" value="true" checked>
                                        <label for="trier-synonymes">Trier les synonymes par ordre alphabétique</label>
                                        <br/>
                                        <input type="checkbox" name="selecteurs" id="selecteurs" value="true" checked>
                                        <label for="selecteurs">Utiliser les fichiers de définition (ébauches, catégories, auteurs, etc.)</label>
                                        <br/>
                                        <input type="checkbox" name="plan" id="plan" value="true">
                                        <label for="plan">Générer un plan-type, même quand il n'y a pas d'information</label>
                                        <br/>
                                        <input type="checkbox" name="liens-inf-sp" id="liens-inf-sp" value="false">
                                        <label for="liens-inf-sp">Liens sur les taxons inférieurs à l'espèce</label>
                                        <br/>
                                        <input type="checkbox" name="juste-ext" id="juste-ext" value="false">
                                        <label for="juste-ext">Déterminer uniquement les liens externes</label>
                                        <br/>
                                        <input type="checkbox" name="inclure-invalides" id="inclure-invalides" value="false">
                                        <label for="inclure-invalides">Inclure les biorefs additionnels</label>
                                        <br/>
                                        <input type="checkbox" name="liste" id="liste" value="false">
                                        <label for="liste">Afficher la liste des modules</label>
                                        <br/>
                                        <input type="checkbox" name="article" id="article" value="false">
                                        <label for="article">Générer uniquement la sortie de l'article et rien d'autre</label>
                                    </div>
                                    <div class="column">
                                        <!-- Sélectif -->
                                        <label for="auteurs">Mode de traitement des auteurs : </label>
                                        <br/>
                                        <select name="auteurs" id="auteurs">
                                        <option value="s">standard</option>
                                        <option value="n">nouveau</option>
                                        <option value="n1">suggestif</option>
                                        </select>
                                        <br/>
                                        <label>Module(s) à désactiver: </label>
                                        <br/>
                                            <select multiple="multiple" id="modules_off" name="off[]">
                                                // Liste de modules 
                                                <option value="gbif">GBIF</option>
                                                <option value="itis">ITIS</option>
                                                <option value="mycobank">MycoBank</option>
                                                <option value="wrms">WoRMS</option>
                                                <option value="algaebase">AlgaeBASE</option>
                                                <option value="lpsn">LPSN</option>
                                            </select>
                                        <br/>
                                        <!-- Saisie -->
                                        <label for="seuil-colonnes">Seuil des colonnes (-2 ; 50) :</label><br/>
                                        <input type="number" name="seuil-colonnes" id="seuil-colonnes" min="-2" max="50" value="25">
                                        <br/>
                                        <label for="limite-listes">Nombre items de liste : </label><br/>
                                        <input type="number" name="limite-listes" id="limite-listes" min="0" value="0">
                                        <br/>
                                        <label for="timeout">Durée d'exécution des modules :</label><br/>
                                        <input type="number" name="timeout" id="timeout" min="0" value="10">
                                        <br/>
                                        <label for="force-regne">Indiquer le règne (charte) : </label><br/>
                                        <input type="text" name="force-regne" id="force-regne" value="" onblur="trimInput(this)">
                                        <br/>
                                        <label for="force-rang">Indiquer le rang : </label><br/>
                                        <input type="text" name="force-rang" id="force-rang" value="" onblur="trimInput(this)">                    
                                    </div>
                </div>
            </form>
        </div>
    </main>
    <!-- Pied de page -->
    <footer id="footer">
        <p><a href="https://fr.wikipedia.org/wiki/Projet:Biologie/Taxobot" target="_blank">Taxobot</a> 2023 - Hébergé sur Toolforge par <a href="https://wikitech.wikimedia.org/wiki/Help:Cloud_Services_introduction" target="_blank">Wikimedia Cloud Services</a> - <a href="https://github.com/Hexasoft/taxobot/tree/main/web/LICENSE.md" target="_blank">Licences</a></p>
    </footer>
                <!-- JS -->
        <script src="js/options.js"></script>
        <script src="js/copy.js"></script>
        <!-- Multi select -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('#modules_off').multiselect();
            });
        </script>
        <!-- Script visant à transformer off[] (array) en off (string: list) -->
        <script>
            function trimInput(inputElement) {
                var inputValue = inputElement.value;
                var trimmedValue = inputValue.trim();
                inputElement.value = trimmedValue;
             }
            function updateOffField() {
                // Récupère off[]
                var offSelect = document.getElementById("modules_off");
                // Convertit off[] en off=a,b,c
                var selectedValues = Array.from(offSelect.selectedOptions).map(option => option.value);
                if (selectedValues.length > 0) {
                    var offInput = document.createElement("input");
                    offInput.type = "hidden";
                    offInput.name = "off";
                    offInput.value = selectedValues.join(",");
                    document.getElementById("search-form").appendChild(offInput);
                }
                // Supprime off[]
                offSelect.value = "";
            }
            function showProcessingMessage() {
                var processingMessage = document.getElementById('processing-message');
                processingMessage.style.display = 'block';
            }
        </script>
    </body>
</html>