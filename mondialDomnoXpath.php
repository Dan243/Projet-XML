<?php
  header('Content-type: text/xml');

  

  
  $mondial = new DOMDocument();
  $mondial->load("Mondial2015/XML/mondial.xml");


  $resultat = new DOMDocument();
  $resultat->preserveWhiteSpace = false;
  $resultat->formatOutput = true;
  $base = '<?xml version="1.0" encoding="UTF-8"?>
  <em><liste-pays></liste-pays><liste-espace-maritime></liste-espace-maritime></em>';
  $resultat->loadxml($base);

  
  $listeMondial = $mondial->documentElement;

  
  $lstPays = array();
  $lstFleuve = array();
  $lstEM = array();

  
  foreach($listeMondial->childNodes as $node) {
    if($node->tagName == "country"){
      $pays = $res->createElement("pays");
      $pays->setAttribute("id-p", $node->getAttribute("car_code"));
      $pays->setAttribute("superficie", $node->getAttribute("area"));
      foreach($node->childNodes as $pays_child){
        if($pays_child->tagName == "name"){
          $pays->setAttribute("nom-p", $pays_child->firstChild->wholeText);
        }
        else if($pays_child->tagName == "population"){
          $pays->setAttribute("nbhab", $pays_child->firstChild->wholeText);
        }
      }
      $lstPays[$node->getAttribute("car_code")] = [$pays, false, false, false];
    }

   
    else if($node->tagName == "river"){
      $fleuve = $resultat->createElement("fleuve");
      $fleuve->setAttribute("id-f",$node->getAttribute("id"));
      foreach($node->childNodes as $river_child){
        if($river_child->tagName == "name"){
          $fleuve->setAttribute("nom-f",$river_child->firstChild->wholeText);
        }
        else if($river_child->tagName == "length"){
          $fleuve->setAttribute("longueur",$river_child->firstChild->wholeText);
        }
        else if($river_child->tagName == "to"){
          $fleuve->setAttribute("se-jette",$river_child->getAttribute("water"));
        }
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
      foreach($node->childNodes as $river_child){
        if($river_child->tagName == "source"){
            $pays_id = explode(" ", $river_child->getAttribute("country"));
            if(sizeof($pays_id)==1 && substr( $fleuve->getAttribute("se-jette"), 0, 4 ) === "sea-"){
              $lstPays[$pays_id[0]][0]->appendChild($fleuve);
              $lstPays[$pays_id[0]][2] = true;
            }
        }
      }
    }

    
    else if($node->tagName == "sea"){
      $espace_maritime = $resultat->createElement("espace-maritime");
      $espace_maritime->setAttribute("id-e",$node->getAttribute("id"));
      $espace_maritime->setAttribute("type","inconnu");
      foreach(explode(" ", $node->getAttribute("country")) as $pays_id){
        $cotoie = $resultat->createElement("cotoie");
        $cotoie->setAttribute("id-p",$pays_id);
        $lstPays[$pays_id][3] = true;
        $espace_maritime->appendChild($cotoie);
      }
      foreach($node->childNodes as $sea_child){
        if($sea_child->tagName == "name"){
          $espace_maritime->setAttribute("nom-e",$sea_child->firstChild->wholeText);
        }
      }
      $lstEM[$node->getAttribute("id")] = $espace_maritime;
    }
  }

  $lstPays_element = $resultat->firstChild->firstChild;

  foreach($lstPays as $el){
    if ($el[3] || $el[2] || $el[1]){
      $lstPays_element->appendChild($el[0]);
    }
  }

  $lstEM_element = $resultat->firstChild->firstChild->nextSibling;
  foreach($lstEM as $el){
    $lstEM_element->appendChild($el);
  }
  $resultat->save("mondialNo_xpath.xml");
?>
