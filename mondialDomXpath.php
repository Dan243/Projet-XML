<?php
  header('Content-type: text/xml');

  
  
  
  $mondial = new DOMDocument();
  $mondial->load("Mondial2015/XML/mondial.xml");
  $mondial_xpath = new DOMXpath($mondial);

  
  $base = '<?xml version="1.0" encoding="UTF-8"?>
  <em><liste-pays></liste-pays><liste-espace-maritime></liste-espace-maritime></em>';
  $resultat = new DOMDocument();
  $resultat->preserveWhiteSpace = false;
  $resultat->formatOutput = true;
  $resultat->loadxml($base);
  $resultat_xpath = new DOMXpath($resultat);
  $lstPays_element = $res_xpath->query("/em/liste-pays")->item(0);
  $lstEM_element = $res_xpath->query("/em/liste-espace-maritime")->item(0);

 
  $lstPays = array();
  $lstFleuve = array();
  $lstEM= array();

  
  foreach($mondial_xpath->query("/mondial/country") as $node) {
    $pays = $resultat->createElement("pays");
    $identifiant = $node->getAttribute("car_code");
    $pays->setAttribute("id-p", $identifiant);
    $pays->setAttribute("superficie", $node->getAttribute("area"));
    $pays->setAttribute("nom-p", $mondial_xpath->query("/mondial/country[./@car_code = '".$identifiant."']/name/text()")->item(0)->wholeText);
    $pays->setAttribute("nbhab", $mondial_xpath->query("/mondial/country[./@car_code = '".$identifiant."']/population/text()")->item(0)->wholeText);
    $lstPays[$node->getAttribute("car_code")] = [$pays, false, false, false];
  }

  
  foreach($mondial_xpath->query("/mondial/river") as $node) {
    $fleuve = $resultat->createElement("fleuve");
    $identifiant = $node->getAttribute("id");
    $fleuve->setAttribute("id-f",$identifiant);
    $fleuve->setAttribute("nom-f", $mondial_xpath->query("/mondial/river[./@id = '".$identifiant."']/name/text()")->item(0)->wholeText);
    $fleuve->setAttribute("longueur", $mondial_xpath->query("/mondial/river[./@id = '".$identifiant."']/length/text()")->item(0)->wholeText);

    if($to = $mondial_xpath->query("/mondial/river[./@id = '".$identifiant."']/to")->item(0)){
      $fleuve->setAttribute("se-jette", $to->getAttribute("water"));
    }

    $pays_id_list = explode (" ", $node->getAttribute("country"));
    foreach($pays_id_list as $pays_id){
      $parcours = $resultat->createElement("parcours");
      $parcours->setAttribute("id",$pays_id);
      $parcours->setAttribute("distance",sizeof($pays_id_list)==1?$fleuve->getAttribute("longueur"):"inconnue");
      $fleuve->appendChild($parcours);

      if(substr( $fleuve->getAttribute("se-jette"), 0, 4 ) === "sea-"){
        $lstPays[$pays_id][1] = true;
      }
    }

    $pays_id = explode(" ", $mondial_xpath->query("/mondial/river[./@id = '".$identifiant."']/source")->item(0)->getAttribute("country"));
    if(sizeof($pays_id)==1 && substr( $fleuve->getAttribute("se-jette"), 0, 4 ) === "sea-"){
      $lstPays[$pays_id[0]][0]->appendChild($fleuve);
      $lstPays[$pays_id[0]][2] = true;
    }
  }


  foreach($mondial_xpath->query("/mondial/sea") as $node) {
    $espace_maritime = $resultat->createElement("espace-maritime");
    $identifiant = $node->getAttribute("id");
    $espace_maritime->setAttribute("id-e",$identifiant);
    $espace_maritime->setAttribute("type","inconnu");
    $espace_maritime->setAttribute("nom-e",$mondial_xpath->query("/mondial/sea[./@id-e = '".$identifiant."']/name/text()")->item(0)->wholeText);

    foreach(explode(" ", $node->getAttribute("country")) as $pays_id){
      $cotoie = $resultat->createElement("cotoie");
      $cotoie->setAttribute("id-p",$pays_id);
      $lstPays[$pays_id][3] = true;
      $espace_maritime->appendChild($cotoie);
    }

    $lstEM[$identifiant] = $espace_maritime;
  }

  foreach($lstPays as $el){
    if ($el[3] || $el[2] || $el[1]){
      $lstPays_element->appendChild($el[0]);
    }
  }

  foreach($lstEM as $el){
    $lstEM_element->appendChild($el);
  }
  $resultat->save("mondial_xpath.xml");
?>
