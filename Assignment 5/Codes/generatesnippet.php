<?php
	ini_set('memory_limit','2048M');
        include("simple_html_dom.php");
	
	function generate_snippet($value, $query){

		$file = file_get_contents($value);
		$html = str_get_html($file);
		$s =  strtolower($html->plaintext);

		$strips = explode(" ",$query);
		$query = array_pop($strips);
		$s = str_replace("\'","",$s);
		$s = str_replace("!","",$s);
		$s = str_replace("?","",$s);
		$s = str_replace(",","",$s);
		$s = str_replace(",","",$s);
		$piece = explode(" ", $s);
		$pieces = array_values(array_filter($piece));

		if(false !== $start = array_search($query, $pieces)){
			$start -=5;
		}
		else{
			$start = 0; 
		}

		$end = $start+60;
		if($end>count($pieces))$end=count($pieces)-1;
		$str = "";

		if($start<0)$start =0;

		if($start < $end){
			for($i = $start ; $i<$end; $i++)$str.=" ".$pieces[$i];
			if($start==0){
			 return $str." ...";
			}
			else{
			 return "...".$str."...";
			}
		}
		else{
			return "0";
		}
	}
	
?>
