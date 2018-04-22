<?php header('Content-type: text/xml');
include_once('Sax4PHP.php');

class MySaxHandler extends DefaultHandler {

	private $id_pays_courant;
  private $nom_pays_courant;
  private $populationMax;
  private $superficie;
  private $nom_f;
  private $tabFleuves;
  private $parcourt;
  private $id_river,$liste_pays_river,$liste_pays_sea,$cotoie,$idSea;
  private $longueur_f=0;
  private $fleuves;
  private $isRiver=false;
  private $InNameRiver=false;
  private $river=false;
	private $nom_pays=false;
  private $population=false;
  private $city=false;
  private $compteur_name=0;
  private $NameRiver;
  private $isLength_f=false;
  private $length_f;
  private $seJette;
  private $distance;
  private $nom_e;
	private $NameSea;
	private $isSea=false;
	private $isNameSea=false;
	private $isEspace=false;
  private $Source;
  private $domImpl;
  private $docType;
  private $em;
  private $emFinal;
  private $racine;
  private $liste_p;
  private $liste_e;
  private $paysDom;
  private $fleuveDom;
  private $parcourtDom;
  private $espaceMaritimeDom;
  private $cotoieDom;
  private $paysValide;

  function startElement($name, $att) {
  	switch($name) {


  		case 'country': ;
	  	  $this->superficie=$att['area'];
	  	  $this->id_pays_courant=$att['car_code'];
	  	  $this->nom_pays=false;
        $this->city=false;
        $this->tabFleuves=array();
        $this->compteur_name=0; 
      break;
      case 'city':
        $this->city=true;
      break;
	  	case 'name':  

		    $this->nom_pays=true;
        $this->compteur_name++;
        if($this->isRiver==true)
        { 
          $this->InNameRiver=true;
        }
        /*Nom de la sea*/
  		  else if($this->isSea==true)
        {
  			 $this->isNameSea=true;
  			 $this->isSea=false;
  		  }
      break;

      case 'population': 
			 $this->population=true;
      break;

		  case 'sea': 
			 $this->isSea=true;
       $this->liste_pays_sea=$att['country'];
       $this->idSea=$att['id'];
		  break;

      case 'length':
        $this->isLength_f=true;
      break;

      case 'to':
    		 if($att['watertype']=="sea")
    		{
    			$this->isEspace=true;
    		}
        $this->seJette=$att['water'];
      break; 

      case 'river': $this->isRiver=true; 
        $this->id_river=$att['id'];
        $this->liste_pays_river=$att['country'];
      break;

      case 'source':
        if($this->isRiver)
        {
          $this->isRiver=false;
          $this->Source=$att['country'];
        }

  		default: $this->nom_pays=false; $this->population=false; $this->river=false;$this->InNameRiver=false;$this->isLength_f=false;
  } 		

  }
  function endElement($name) {

  	switch($name) {
	  		case 'country':
          $this->em[0][$this->id_pays_courant]=array("id-p"=>$this->id_pays_courant,"superficie"=>$this->superficie,"nom-p"=>$this->nom_pays_courant,"population"=>$this->populationMax,"fleuves"=>array());
        break;


        case 'river':

          $this->isRiver=false;
          $paysSource=explode(" ",$this->Source);
          $paysRiver=explode(" ",$this->liste_pays_river);

          $this->parcourt=array();

          

          if(sizeof($paysSource)==1 && $this->isEspace)
          {
             foreach($paysRiver as $p)
             {
              $this->parcourt[]=array("id-pays"=>$p, "distance"=>"inconnu");
              $this->paysValide[$p]=1;
             }

            $this->em[0][$paysSource[0]]["fleuves"][$this->id_river]['id_f']=$this->id_river;
            $this->em[0][$paysSource[0]]["fleuves"][$this->id_river]['nom_f']=$this->NameRiver;
            $this->em[0][$paysSource[0]]["fleuves"][$this->id_river]['length_f']=$this->length_f;
            $this->em[0][$paysSource[0]]["fleuves"][$this->id_river]['seJette']=$this->seJette; 
            $this->em[0][$paysSource[0]]["fleuves"][$this->id_river]['parcourt']=$this->parcourt;

             $this->paysValide[$paysSource[0]]=2;

          }

          $this->isEspace=false;

        break;
       
  		case 'sea':

        $this->cotoie=array();

    	  $lpays=explode(" ",$this->liste_pays_sea);

        foreach($lpays as $p)
        { 
          $this->cotoie[]=array("id-p"=>$p);
          $this->paysValide[$p]=3;

        }

    		$this->em[1][$this->idSea]=array("nom-e"=>$this->NameSea,"type"=>"inconnu","cotoie"=>$this->cotoie);

  		break;

    }
  } 	

   function characters($txt) 
  {
    $txt = trim($txt);


    if ($this->nom_pays==true && $this->compteur_name ==1) 
    {
    	$this->nom_pays = false;
    	$this->nom_pays_courant = $txt;
    }
    else if($this->population &&  $this->city==false)
    {
        $this->population=false;
        $this->populationMax = $txt;
    }
     else if($this->InNameRiver)
    {
        $this->InNameRiver=false;     
        $this->NameRiver = $txt;
    }
    else if($this->isLength_f)
    {
       $this->isLength_f=false;
       $this->length_f = $txt;
    }
  	else if($this->isNameSea)
  	{

  	 $this->isNameSea=false;
  	 $this->NameSea = $txt;
  	}
  }

  function startDocument() 
  {
      $this->domImpl = new DOMImplementation();
      $this->docType = $this->domImpl->createDocumentType("em", '', 'em.dtd');
      $this->emFinal = $this->domImpl->createDocument('', 'em',$this->docType);
      $this->emFinal->encoding = 'UTF-8';
      $this->emFinal->standalone = false;
      $this->emFinal->formatOutput = true;
      $this->racine=$this->emFinal->documentElement;
      $this->liste_p= $this->emFinal->createElement('liste-pays');
      $this->liste_e= $this->emFinal->createElement('liste-espace-maritime');
  } 
  function endDocument() {


   foreach($this->em[0] as $pays)
    {
      if(array_key_exists($pays["id-p"],$this->paysValide))
      {
       $this->paysDom=$this->emFinal->createElement('pays');
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
                $this->fleuveDom=$this->emFinal->createElement('fleuve');
                $this->fleuveDom->setAttribute("id-f",$key["id_f"]);
                $this->fleuveDom->setAttribute("nom_f",$key["nom_f"]);
                $this->fleuveDom->setAttribute("longueur",$key["length_f"]);
                $this->fleuveDom->setAttribute("se-jette",$key["seJette"]);

                foreach($key['parcourt'] as $prct)
                {
                  $this->parcourtDom=$this->emFinal->createElement('parcourt');
                  $this->parcourtDom->setAttribute("id-pays",$prct['id-pays']);
                  $this->parcourtDom->setAttribute("distance",$prct['distance']);
                  $this->fleuveDom->appendChild($this->parcourtDom);   
                }
                $this->paysDom->appendChild($this->fleuveDom);  

              }       

            }
          }

        $this->liste_p->appendChild($this->paysDom);  
      }
    }
    foreach($this->em[1] as $idE=>$espaceM)
    {
       $this->espaceMaritimeDom=$this->emFinal->createElement('espace-maritime');
       $this->espaceMaritimeDom->setAttribute("id-e",$idE);
       $this->espaceMaritimeDom->setAttribute("nom_e",$espaceM["nom-e"]);
       $this->espaceMaritimeDom->setAttribute("type",$espaceM["type"]);

      foreach($espaceM["cotoie"] as $k => $v)
      {
        $this->cotoieDom=$this->emFinal->createElement('cotoie');
        $this->cotoieDom->setAttribute("id-p",$v['id-p']);
        $this->espaceMaritimeDom->appendChild($this->cotoieDom); 
      }
         $this->liste_e->appendChild($this->espaceMaritimeDom);  
    }

   $this->racine->appendChild($this->liste_p);  
   $this->racine->appendChild($this->liste_e);  

   $this->emFinal->save('emSax.xml');

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
