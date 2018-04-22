<?php header('Content-type: text/xml');
include_once('Sax4PHP.php');

class MySaxHandler extends DefaultHandler {

// Toutes les variables du programme 
  private $Source;
  private $domImpl;
  private $docType;
  private $em;
  private $FinalEM;
  private $racine;
	private $courant;
  private $nomPaysCourant;
  private $populationMax;
  private $superficie;
  private $listesPays;
  private $listesEM;
  private $paysDom;
  private $fleuveDom;
  private $parcourtDom;
  private $espaceMaritimeDom;
  private $cotoieDom;
  private $paysValide;
  private $nomFleuve;
  private $tabFleuves;
  private $parcourt;
  private $FleuveID;
  private $listePaysFleuve;
  private  $listePaysEM;
  private $cotoie;
  private $EmID;
  private $fleuves;
  //private $nomFleuve;
  private $LongeurFleuve;
  private $seJette;
  private $distance;
  private $nom_e;
	private $nomEM;
  private $estFleuve=false;
  private $dansNomFleuve=false;
  private $Fleuve=false;
  private $PaysNom=false;
  private $population=false;
  private $ville=false;
  private $nomCompteur=0;
  private $estLongeurFleuve=false;
	private $estEM=false;
	private $estNomEM=false;
	private $estEspace=false;
 
 // debut de la nouvelle balise
  function startElement($name, $att) {
  	switch($name) {


  		case 'country': ;
	  	  $this->superficie=$att['area'];
	  	  $this->courant=$att['car_code'];
	  	  $this->PaysNom=false;
        $this->ville=false;
        $this->tabFleuves=array();
        $this->nomCompteur=0; 
      break;
      case 'ville':
        $this->ville=true;
      break;
	  	case 'name':  

		    $this->PaysNom=true;
        $this->nomCompteur++;
        if($this->estFleuve==true)
        { 
          $this->dansNomFleuve=true;
        }
  		  else if($this->estEM==true)
        {
  			 $this->estNomEM=true;
  			 $this->estEM=false;
  		  }
      break;

      case 'population': 
			 $this->population=true;
      break;

		  case 'sea': 
			 $this->isSea=true;
       $this->listePaysEM=$att['country'];
       $this->EmID=$att['id'];
		  break;

      case 'length':
        $this->estLongeurFleuve=true;
      break;

      case 'to':
    		 if($att['watertype']=="sea")
    		{
    			$this->estEspace=true;
    		}
        $this->seJette=$att['water'];
      break; 

      case 'Fleuve': $this->estFleuve=true; 
        $this->FleuveID=$att['id'];
        $this->listePaysFleuve=$att['country'];
      break;

      case 'source':
        if($this->estFleuve)
        {
          $this->estFleuve=false;
          $this->Source=$att['country'];
        }

  		default: $this->PaysNom=false; $this->population=false; $this->river=false;$this->dansNomFleuve=false;$this->estLongeurFleuve=false;
  } 		

  }
  // fin de la balise 
  function endElement($name) {

  	switch($name) {
	  		case 'country':
          $this->em[0][$this->courant]=array("id-p"=>$this->courant,"superficie"=>$this->superficie,"nom-p"=>$this->nomPaysCourant,"population"=>$this->populationMax,"fleuves"=>array());
        break;


        case 'Fleuve':

          $this->estFleuve=false;
          $paysSource=explode(" ",$this->Source);
          $paysRiver=explode(" ",$this->listePaysFleuve);

          $this->parcourt=array();

          

          if(sizeof($paysSource)==1 && $this->estEspace)
          {
             foreach($paysRiver as $p)
             {
              $this->parcourt[]=array("id-pays"=>$p, "distance"=>"inconnu");
              $this->paysValide[$p]=1;
             }

            $this->em[0][$paysSource[0]]["fleuves"][$this->FleuveID]['id_f']=$this->FleuveID;
            $this->em[0][$paysSource[0]]["fleuves"][$this->FleuveID]['nomFleuve']=$this->nomFleuve;
            $this->em[0][$paysSource[0]]["fleuves"][$this->FleuveID]['LongeurFleuve']=$this->LongeurFleuve;
            $this->em[0][$paysSource[0]]["fleuves"][$this->FleuveID]['seJette']=$this->seJette; 
            $this->em[0][$paysSource[0]]["fleuves"][$this->FleuveID]['parcourt']=$this->parcourt;

             $this->paysValide[$paysSource[0]]=2;

          }

          $this->estEspace=false;

        break;
       
  		case 'sea':

        $this->cotoie=array();

    	  $lpays=explode(" ",$this->listePaysEM);

        foreach($lpays as $p)
        { 
          $this->cotoie[]=array("id-p"=>$p);
          $this->paysValide[$p]=3;

        }

    		$this->em[1][$this->EmID]=array("nom-e"=>$this->nomEM,"type"=>"inconnu","cotoie"=>$this->cotoie);

  		break;

    }
  } 	
  // Fonction appelé lorsque le parser rencontre des caractères à l'intérieur d'un élément
   function characters($txt) 
  {
    $txt = trim($txt);


    if ($this->PaysNom==true && $this->nomCompteur ==1) 
    {
    	$this->PaysNom = false;
    	$this->nomPaysCourant = $txt;
    }
    else if($this->population &&  $this->ville==false)
    {
        $this->population=false;
        $this->populationMax = $txt;
    }
     else if($this->dansNomFleuve)
    {
        $this->dansNomFleuve=false;     
        $this->nomFleuve = $txt;
    }
    else if($this->estLongeurFleuve)
    {
       $this->estLongeurFleuve=false;
       $this->LongeurFleuve = $txt;
    }
  	else if($this->estNomEM)
  	{

  	 $this->estNomEM=false;
  	 $this->nomEM = $txt;
  	}
  }
  //debut de la lecture du document
  function startDocument() 
  {
      $this->domImpl = new DOMImplementation();
      $this->docType = $this->domImpl->createDocumentType("em", '', 'em.dtd');
      $this->FinalEM = $this->domImpl->createDocument('', 'em',$this->docType);
      $this->FinalEM->encoding = 'UTF-8';
      $this->FinalEM->standalone = false;
      $this->FinalEM->formatOutput = true;
      $this->racine=$this->FinalEM->documentElement;
      $this->listesPays= $this->FinalEM->createElement('liste-pays');
      $this->listesEM= $this->FinalEM->createElement('liste-espace-maritime');
  } 
  // fin de la lecture du document 
  function endDocument() {


   foreach($this->em[0] as $pays)
    {
      if(array_key_exists($pays["id-p"],$this->paysValide))
      {
       $this->paysDom=$this->FinalEM->createElement('pays');
       $this->paysDom->setAttribute("id-p",$pays["id-p"]);
       $this->paysDom->setAttribute("superficie",$pays["superficie"]);
       $this->paysDom->setAttribute("nom-p",$pays["nom-p"]);
       $this->paysDom->setAttribute("population",$pays["population"]);

          if(isset($pays['fleuves']))
          {
            foreach($pays['fleuves'] as $fleuve => $key)
            {
              if(isset($key["id_f"]))
              {
                $this->fleuveDom=$this->FinalEM->createElement('fleuve');
                $this->fleuveDom->setAttribute("id-f",$key["id_f"]);
                $this->fleuveDom->setAttribute("nomFleuve",$key["nomFleuve"]);
                $this->fleuveDom->setAttribute("longueur",$key["LongeurFleuve"]);
                $this->fleuveDom->setAttribute("se-jette",$key["seJette"]);

                foreach($key['parcourt'] as $prct)
                {
                  $this->parcourtDom=$this->FinalEM->createElement('parcourt');
                  $this->parcourtDom->setAttribute("id-pays",$prct['id-pays']);
                  $this->parcourtDom->setAttribute("distance",$prct['distance']);
                  $this->fleuveDom->appendChild($this->parcourtDom);   
                }
                $this->paysDom->appendChild($this->fleuveDom);  

              }       

            }
          }

        $this->listesPays->appendChild($this->paysDom);  
      }
    }
    foreach($this->em[1] as $idE=>$espaceM)
    {
       $this->espaceMaritimeDom=$this->FinalEM->createElement('espace-maritime');
       $this->espaceMaritimeDom->setAttribute("id-e",$idE);
       $this->espaceMaritimeDom->setAttribute("nom_e",$espaceM["nom-e"]);
       $this->espaceMaritimeDom->setAttribute("type",$espaceM["type"]);

      foreach($espaceM["cotoie"] as $k => $v)
      {
        $this->cotoieDom=$this->FinalEM->createElement('cotoie');
        $this->cotoieDom->setAttribute("id-p",$v['id-p']);
        $this->espaceMaritimeDom->appendChild($this->cotoieDom); 
      }
         $this->listesEM->appendChild($this->espaceMaritimeDom);  
    }

   $this->racine->appendChild($this->listesPays);  
   $this->racine->appendChild($this->listesEM);  

   $this->FinalEM->save('emSax.xml');

  }
       
}

$xml = file_get_contents('mondial.xml');
$sax = new SaxParser(new MySaxHandler());
try {
	$sax->parse($xml);
}catch(SAXException $e){  
	echo "\n",$e;
}catch(Exception $e) {
	echo "Default exception >>", $e;
}?>
