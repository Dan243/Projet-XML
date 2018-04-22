<?php 

include_once('Sax4PHP/Sax4PHP.php');
		
class Exo_sax extends DefaultHandler{
	private $texte;
	
	private $percent_asia = 0;
	private $percent_autre = 0;
	private $capitale	="";
	
	// boolean
	private $is_asie		 = False;
	private $is_capitale	 = False;
	private $is_pays		 = False;
	private $autre_continent = False;
	private $is_percent_asia = False;
	private $is_percent_autre= False;
	private $aff="<pays ";
	
	function characters($txt){
		$txt_reduit = trim($txt);
		if (!(empty($txt_reduit))) $this->texte .= $txt;
	 }
	 
	 function startDocument(){
		echo "<?xml version='1.0' encoding='utf-8'?>\n";
		echo "<liste-pays>\n";
	}
	
	 function endDocument(){
		echo "</liste-pays>";
	 }
	
	function startElement($nom, $att){
		$this->texte ="";
		switch(utf8_decode($nom)){
			case 'name' :
				break;
				
			case 'encompassed':
				if($att['continent'] == 'asia' && $att['percentage'] != 100){
					$this->is_asie 			= True;
					$this->percent_asia 	= $att['percentage'];
				}elseif($this->is_percent_autre){
					$this->percent_autre 	= $att['percentage'];
				}
				break;
					
			case 'city' :
				if (isset($att['is_country_cap']) and $att['is_country_cap'] == 'yes'){
					$this->is_capitale = True;               
				}     
				break;
			
			default:;
		}
	}
	
	function endElement($nom){
		switch(utf8_decode($nom)){
			case 'name' :
				// nom pays
				if ($this->is_pays){
					$this->aff .= " nom='".utf8_decode($this->texte)."'";
					$this->is_pays=False;
				// nom de la ville
				}elseif ($this->is_capitale){
					//on ajoute les attributs
					$this->capitale = utf8_decode($this->texte);
					$this->is_capitale = False;               
				}
				break;

			case 'encompassed':
				if ($this->is_percent_asia){
					$this->is_percent_asia = False;
				}

				if ($this->is_percent_autre){
					$this->is_percent_autre = False;
				}
				break;
				
			case 'country':
				
				if ($this->is_asie){
					$this->aff .= " capitale='".$this->capitale."'";
					$this->aff .= " proportion-asia='".$this->percent_asia."'";
					$percentage=100-$this->percent_asia;
					$this->aff .= " proportion-autres='".$percentage."'";
					$this->aff .= " /> \n";
					echo $this->aff;
				}
				
				// on fait un réinit des boolean pour le prochain pays
				$this->is_pays			= True;
				$this->is_asie 			= False;
				$this->is_percent_asia 	= True;
				$this->is_percent_autre = True;
				$this->is_capitale 		= False;
				$this->percent_asia		= 0;			
				$this->percent_autre	= 0;
				$this->aff 				= "<pays ";
				
			default:;
		}
	}
}        

// lecture du fichier
$xml = file_get_contents('mondial.xml');
$sax = new SaxParser(new Exo_sax());
try {
	$sax->parse($xml);
}catch(SAXException $e){  
	echo "\n",$e;
}catch(Exception $e) {
	echo "Default exception >>", $e;
}


?>

