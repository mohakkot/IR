<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
if ($query)
{
 $query = strtolower($query);
 require_once('solr-php-client/Apache/Solr/Service.php');
 require_once('SpellCorrector.php');
 require('generatesnippet.php');
 $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572/');
 $keywords = explode(" ", $query);

 $spellcorrect = "";
 $check="";
 foreach($keywords as $key => $part){
    $spellcorrect .= SpellCorrector::correct($part).' '; 
    $check .= SpellCorrector::correct($part).'+'; 
 }
$check = rtrim($check, "+");
$type = $_REQUEST['searchtype'];
$link = "http:/"."/localhost/solrquery.php?q=$check+&searchtype=$type"; 

 if (get_magic_quotes_gpc() == 1){
 	$query = stripslashes($query);
    }

 $param = [
        'q.op' => 'AND',
    ];
 
 try{	
	if($_REQUEST['searchtype']=="external"){
		$param = array('sort' => 'pageRankFile desc');
	}
	$results = $solr->search($query, 0, $limit,$param);

 }
 catch (Exception $e){
 	die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
 }
}
//start reading csv
$num = [];
$CSVfp = fopen("NYDMap.csv", "r");
if($CSVfp !== FALSE) {
 while(($data = fgetcsv($CSVfp, 1000, ",")) !== FALSE){
  $num[$data[0]] = $data[1];
 }
}
fclose($CSVfp);
//end of reading csv file

?>
<html>
 <head>
 <title>PHP Solr Client</title>
 <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
 <script src="http://code.jquery.com/jquery-1.12.4.js"></script>
 <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
 <style>
 body {background-color: lightyellow}
 </style>
 </head>
 <body>
 <center><h2 style="align:center;color:red">Solr Query Screen</h2>
 <form accept-charset="utf-8" method="get">
 <label for="q" style="align:center">Search Box:</label>
 <input id="q" name="q" type="text" style="align:center; width:40%" placeholder="Enter Your Query Here" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
 <input type="submit"/></center>
<br>
<center><input type="radio" name="searchtype" value="inbuilt" <?php if (isset($_GET["searchtype"]) && $_GET["searchtype"]=="inbuilt") echo "checked";?>>Lucene Search</input>
<input type="radio" name="searchtype" value="external" <?php if (isset($_GET["searchtype"]) && $_GET["searchtype"]=="external") echo "checked";?>>Pagerank Search</input></center><br>
 </form>

<?php

// display results
if ($results)
{
 $total = (int) $results->response->numFound;
 $start = min(1, $total);
 $end = min($limit, $total);
?>
<?php if($spellcorrect != strtolower($query).' '){
echo "Did You Mean : "?> <a href=<?php echo $link?>><?php echo $spellcorrect?></a></br>
<?php echo "Showing Results for:  ".$query;
}?></br></br>
 <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div></br></br>
<?php
 // iterate result documents
 foreach ($results->response->docs as $doc)
 {
?>

<?php
 // iterate document fields / values

$valueid = "N/A";
$valueurl = "N/A";
$valuedesc = "N/A";
$valuetit = "N/A";
$valauthor = "";
$valdate = "";
$valsize = "";
 foreach ($doc as $field => $value)
 {

	if($field == "id")
	{
		$pos=strripos($value,"/");
		$path = $value;
		$valueid = $value;
		$val=substr($value,$pos+1);
		$valueurl=$num[$val];
		
	}
	if($field == "description")
	{
		$valuedesc = $value;
	}
	if($field == "title")
	{
		$valuetit = $value;
	}
	if($field == "parsely_author"){
		$valauthor = $value;
	}
	if($field == "parsely_pub_date"){
		$date = new DateTime($value);		
		$valdate = $date->format('Y-m-d');
	}
	if($field == "stream_size"){		
		$valsize = $value;
	}
		
 	
 }
?>
 <a style="color:blue; text-decoration:none; font-size:18px" href=<?php echo $valueurl;?> ><?php echo htmlspecialchars($valuetit, ENT_NOQUOTES, 'utf-8'); ?></a><br>
 <a style="color:green; font-size:14px"href=<?php echo $valueurl;?> ><?php echo htmlspecialchars($valueurl, ENT_NOQUOTES, 'utf-8'); ?></a><br>
 <span style="text-align:justify"><?php $snippet = htmlspecialchars(generate_snippet($path, $query), ENT_NOQUOTES, 'utf-8'); echo $snippet;?></span><br>
 <?php if($valauthor != ""){?>
	<span style="color:grey; font-size:11px">Author: <?php echo htmlspecialchars($valauthor, ENT_NOQUOTES, 'utf-8');?>; </span>
<?php }?>
 <?php if($valauthor != ""){?>
	<span style="color:grey; font-size:11px">Published: <?php echo htmlspecialchars($valdate, ENT_NOQUOTES, 'utf-8');?>; </span>
<?php }?>
<?php if($valsize != ""){?>
	<span style="color:grey; font-size:11px">Size: <?php echo htmlspecialchars($valsize, ENT_NOQUOTES, 'utf-8');?>bytes</span> </br></br>
<?php }?>




<?php }

}
?>
 </body>

 <script>
        $(function() {
            var URL_PREFIX = "http://localhost:8983/solr/csci572/suggest?q=";
            var URL_SUFFIX = "&wt=json";
            $("#q").autocomplete({
                source : function(request, response) {
                    var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                    var URL = URL_PREFIX + lastword + URL_SUFFIX;
                    $.ajax({
                        url : URL,
                        success : function(data) {
                            var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                            var suggestions = data.suggest.suggest[lastword].suggestions;
                            suggestions = $.map(suggestions, function (value, index) {
                                var prefix = "";
                                var query = $("#q").val();
                                var queries = query.split(" ");
                                if (queries.length > 1) {
                                    var lastIndex = query.lastIndexOf(" ");
                                    prefix = query.substring(0, lastIndex + 1).toLowerCase();
                                }
                                if (prefix == "" && isStopWord(value.term)) {
                                    return null;
                                }
                                if (!/^[0-9a-zA-Z]+$/.test(value.term)) {
                                    return null;
                                }
                                return prefix + value.term;
                            });
                            response(suggestions.slice(0, 5));
                        },
                        dataType : 'jsonp',
                        jsonp : 'json.wrf'
                    });
                },
                minLength : 1
            });
        });
        function isStopWord(word)
        {
            var regex = new RegExp("\\b"+word+"\\b","i");
            return stopWords.search(regex) < 0 ? false : true;
        }
        var stopWords = "a,able,about,above,abst,accordance,according,accordingly,across,act,actually,added,adj,\
        affected,affecting,affects,after,afterwards,again,against,ah,all,almost,alone,along,already,also,although,\
        always,am,among,amongst,an,and,announce,another,any,anybody,anyhow,anymore,anyone,anything,anyway,anyways,\
        anywhere,apparently,approximately,are,aren,arent,arise,around,as,aside,ask,asking,at,auth,available,away,awfully,\
        b,back,be,became,because,become,becomes,becoming,been,before,beforehand,begin,beginning,beginnings,begins,behind,\
        being,believe,below,beside,besides,between,beyond,biol,both,brief,briefly,but,by,c,ca,came,can,cannot,can't,cause,causes,\
        certain,certainly,co,com,come,comes,contain,containing,contains,could,couldnt,d,date,did,didn't,different,do,does,doesn't,\
        doing,done,don't,down,downwards,due,during,e,each,ed,edu,effect,eg,eight,eighty,either,else,elsewhere,end,ending,enough,\
        especially,et,et-al,etc,even,ever,every,everybody,everyone,everything,everywhere,ex,except,f,far,few,ff,fifth,first,five,fix,\
        followed,following,follows,for,former,formerly,forth,found,four,from,further,furthermore,g,gave,get,gets,getting,give,given,gives,\
        giving,go,goes,gone,got,gotten,h,had,happens,hardly,has,hasn't,have,haven't,having,he,hed,hence,her,here,hereafter,hereby,herein,\
        heres,hereupon,hers,herself,hes,hi,hid,him,himself,his,hither,home,how,howbeit,however,hundred,i,id,ie,if,i'll,im,immediate,\
        immediately,importance,important,in,inc,indeed,index,information,instead,into,invention,inward,is,isn't,it,itd,it'll,its,itself,\
        i've,j,just,k,keep,keeps,kept,kg,km,know,known,knows,l,largely,last,lately,later,latter,latterly,least,less,lest,let,lets,like,\
        liked,likely,line,little,'ll,look,looking,looks,ltd,m,made,mainly,make,makes,many,may,maybe,me,mean,means,meantime,meanwhile,\
        merely,mg,might,million,miss,ml,more,moreover,most,mostly,mr,mrs,much,mug,must,my,myself,n,na,name,namely,nay,nd,near,nearly,\
        necessarily,necessary,need,needs,neither,never,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,nor,\
        normally,nos,not,noted,nothing,now,nowhere,o,obtain,obtained,obviously,of,off,often,oh,ok,okay,old,omitted,on,once,one,ones,\
        only,onto,or,ord,other,others,otherwise,ought,our,ours,ourselves,out,outside,over,overall,owing,own,p,page,pages,part,\
        particular,particularly,past,per,perhaps,placed,please,plus,poorly,possible,possibly,potentially,pp,predominantly,present,\
        previously,primarily,probably,promptly,proud,provides,put,q,que,quickly,quite,qv,r,ran,rather,rd,re,readily,really,recent,\
        recently,ref,refs,regarding,regardless,regards,related,relatively,research,respectively,resulted,resulting,results,right,run,s,\
        said,same,saw,say,saying,says,sec,section,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sent,seven,several,shall,she,shed,\
        she'll,shes,should,shouldn't,show,showed,shown,showns,shows,significant,significantly,similar,similarly,since,six,slightly,so,\
        some,somebody,somehow,someone,somethan,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specifically,specified,specify,\
        specifying,still,stop,strongly,sub,substantially,successfully,such,sufficiently,suggest,sup,sure,t,take,taken,taking,tell,tends,\
        th,than,thank,thanks,thanx,that,that'll,thats,that've,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,\
        thered,therefore,therein,there'll,thereof,therere,theres,thereto,thereupon,there've,these,they,theyd,they'll,theyre,they've,\
        think,this,those,thou,though,thoughh,thousand,throug,through,throughout,thru,thus,til,tip,to,together,too,took,toward,towards,\
        tried,tries,truly,try,trying,ts,twice,two,u,un,under,unfortunately,unless,unlike,unlikely,until,unto,up,upon,ups,us,use,used,\
        useful,usefully,usefulness,uses,using,usually,v,value,various,'ve,very,via,viz,vol,vols,vs,w,want,wants,was,wasn't,way,we,wed,\
        welcome,we'll,went,were,weren't,we've,what,whatever,what'll,whats,when,whence,whenever,where,whereafter,whereas,whereby,wherein,\
        wheres,whereupon,wherever,whether,which,while,whim,whither,who,whod,whoever,whole,who'll,whom,whomever,whos,whose,why,widely,\
        willing,wish,with,within,without,won't,words,world,would,wouldn't,www,x,y,yes,yet,you,youd,you'll,your,youre,yours,yourself,\
        yourselves,you've,z,zero";
    </script>
</body>
</html>
</html>
