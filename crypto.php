<?php

/**************************************************************
* basic
**************************************************************/

function help(){
	$handle = fopen("crypto.php", "r");
	if ($handle) {
		echo "\n";
	    while (($line = fgets($handle)) !== false) {
	        if(strrpos($line, "function", -strlen($line)) !== false){
	        	echo $line;
	        }
	    }
	    fclose($handle);
		echo "\n";
	}
}

function normalizeText($text){
	$text =  strtoupper($text);
	$out = "";
	for ($i=0; $i < strlen($text); $i++) { 
		if($text[$i]>="A" && $text[$i]<="Z")$out.=$text[$i];
	}
	return $out;
}

function subArray($array, $beginIndex, $endIndex){
	$out = array();
	for ($i=$beginIndex; $i <= $endIndex; $i++) { 
		$out[] = $array[$i];
	}
	return $out;
}

function humanize($text, $sIndex=0){
	$indexes = array();
	$len = strlen($text);
	if($sIndex>=$len)return "";

	for ($i=2; ($i < 15) && ($sIndex+$i <= $len); $i++) { 
		if(wordExists(substr($text, $sIndex, $i)))$indexes[] = $i;
	}
	if(empty($indexes))$indexes[] = 1;
	//print_r($indexes);
	$ret = "";
	$length = $indexes[count($indexes)-1];
	//if(count($indexes)>2)$indexes = subArray($indexes, count($indexes)-2, count($indexes)-1)
	//foreach ($indexes as $length) {
		$ret2 = substr($text, $sIndex, $length). " " . humanize($text, $sIndex+$length);
		if($ret=="" || strlen($ret2) < strlen($ret))$ret = $ret2;
	//}
	if($sIndex==0)echo $ret."\n\n";
	return $ret;
}

require "dictionary.php";
function wordExists($text){
	global $englishDictionary;

	$start = 0;
	$end = count($englishDictionary)-1;

	if(($englishDictionary[$start] == $text))return true;
	if(($englishDictionary[$end] == $text))return true;

	while($end-$start>1){
		$mid = round($start + ($end - $start)/2);
		if(($englishDictionary[$mid] == $text))return true;
		if($englishDictionary[$mid] > $text){
			$end = $mid;
		}else{
			$start = $mid;
		}
	}

	return false;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}


/**************************************************************
* Indicators
**************************************************************/

function englishFA(){
	$out = array();
	$out['A'] = 8.167;
	$out['B'] = 1.492;
	$out['C'] = 2.782;
	$out['D'] = 4.253;
	$out['E'] = 12.702;
	$out['F'] = 2.228;
	$out['G'] = 2.015;
	$out['H'] = 6.094;
	$out['I'] = 6.966;
	$out['J'] = 0.153;
	$out['K'] = 0.772;
	$out['L'] = 4.025;
	$out['M'] = 2.406;
	$out['N'] = 6.749;
	$out['O'] = 7.507;
	$out['P'] = 1.929;
	$out['Q'] = 0.095;
	$out['R'] = 5.987;
	$out['S'] = 6.327;
	$out['T'] = 9.056;
	$out['U'] = 2.758;
	$out['V'] = 0.978;
	$out['W'] = 2.361;
	$out['X'] = 0.150;
	$out['Y'] = 1.974;
	$out['Z'] = 0.074;

	return $out;
}

function englishFAGraph(){
	return textFAGraph(englishFA());
}

function letterCount($text){
	$out=array();
	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		$out[chr($i)]=0;
	}
	for($i=0; $i<strlen($text); $i++){
		$ti = $text[$i];
		if(isset($out[$ti]))$out[$ti]++;
	}
	return $out;
}

function textFA($text){
	$sum = strlen($text);
	$out = letterCount($text);

	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		$out[chr($i)]=100 * $out[chr($i)] / $sum;
	}

	return $out;
}

function textFAGraph($textFA){
	$out = array();

	foreach ($textFA as $key => $value) {
		$out[$key] = "";
		for ($i=0; $i<30; $i++) {
			if($value>$i)
				$out[$key] .= "*";
			else
				$out[$key] .= " ";
		}
	}
	return $out;
}

function printTwoGraphs($graph1, $graph2){
	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		echo "[". chr($i) ."] => ";
		echo $graph1[chr($i)];
		echo "[". chr($i) ."] => ";
		echo $graph2[chr($i)];
		echo "\n";
	}
	echo "\n";
}

function compareFA($text1FA, $text2FA){
	$diff = 0;
	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		$diff += abs($text1FA[chr($i)] - $text2FA[chr($i)])*abs($text1FA[chr($i)] - $text2FA[chr($i)]);
	}
	return $diff;
}

function getCharsDifference($a, $b){
	$i = ord($a) - ord("A");
	$j = ord($b) - ord("A");
	$k = $j - $i;
	if($k<0)$k+=26;
	return chr($k+ord("A"));
}

/**************************************************************
* Metrics
**************************************************************/

// should be =0.065
function metricIC($text){
	$out = array();
	for ($i=0 ; $i < strlen($text); $i++) { 
		if(!isset($out[$text[$i]]))$out[$text[$i]]=0;
		$out[$text[$i]]++;
	}

	$sum = 0;
	$count = 0;
	foreach ($out as $i) {
		$count += $i;
		$sum += ($i-1)*$i;
	}
	$count *= ($count-1);
	$ic = $sum/$count;
	return $ic;
}

// should be <=150
function metricFA($text){
	return compareFA(englishFA(), textFA($text));
}

// should be >=9
function metricEnglishScore($text){
	$words = array("the","be","to","of","and","in","that","have","it","for","not","on","with","he","as","you","do","at");
	$score = 0;
	foreach ($words as $word) {
		$word = strtoupper($word);
		$score += substr_count($text, $word);
	}
	return $score*100/strlen($text);
}

function orderByMetricAsc($lines, $metricName){
	echo "YET BUGGY!!\n";
	$out = array();
	$metrics = array();

	foreach ($lines as $key => $line) {
		$metrics[$key] = $metricName($line);
	}

	while(count($metrics)>0){
		$bestIndex = -1;
		$bestScore = -1;
		foreach ($metrics as $key => $value) {
			if($value > $bestScore){
				$bestScore = $value;
				$bestIndex = $key;
			}
		}
		$out[] = $lines[$bestIndex];
		unset($metrics[$bestIndex]);
	}
	return $out;
}

function orderByMetricDesc($lines, $metricName){
	return array_reverse(orderByMetricAsc($lines, $metricName));
}

function printMetrics($text){
	echo "metric IC : ".metricIC($text)."\n";
	echo "metric FA : ".metricFA($text)."\n";
	echo "metric ENG: ".metricEnglishScore($text)."\n\n";
}

/**************************************************************
* Shift
**************************************************************/

function shift($text, $shift){
	$out = $text;
	for ($i=0; $i < strlen($out); $i++) { 
		$n = ord($out[$i]);
		$n += $shift;
		while($n<ord("A"))$n+=(ord("Z")-ord("A")+1);
		while($n>ord("Z"))$n-=(ord("Z")-ord("A")+1);
		$out[$i] = chr($n);
	}
	return $out;
}

function shiftByMap($text, $map=array()){
	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		if(!isset($map[chr($i)]))$map[chr($i)]=chr($i);
	}

	$out = $text;
	for ($i=0; $i<strlen($text); $i++) { 
		$out[$i] = $map[$text[$i]];
	}
	return $out;
}

function shiftMapFromKey($keyArr){
	$keyLen = count($keyArr);
	$alphabet = array();
	for ($i=ord('A'); $i <= ord('Z'); $i++) { 
		$alphabet[chr($i)]=chr($i);
	}
	foreach ($keyArr as $key => $value) {
		unset($alphabet[$value]);
	}
	$map = array();

	$index = ord('A');
	foreach ($keyArr as $key => $value) {
		$map[chr($index++)] = $value;
	}
	foreach ($alphabet as $key => $value) {
		$map[chr($index++)] = $value;
	}

	return $map;
}

function shiftAffineMap($a, $b){
	$map = array();
	for ($i=0; $i < 26; $i++) { 
		$o = ($a*$i+$b)%26;
		echo "[$i:$o]\n";
		$map[chr($i+ord('A'))] = chr($o+ord('A'));
	}
	return $map;
}


function getAllShifts($text){
	$out = array();
	for ($i=0; $i < 26; $i++) { 
		$out[] = shift($text, $i);
	}
	return $out;
}

function bruteForceShift($text){
	$text1 = bruteForceShiftFA($text);
	$text2 = bruteForceShiftEnglishScore($text);
	if($text1==$text2)return $text1;
	else return $text1."\n".$text2;
}

function bruteForceShiftFA($text){
	$englishFA = englishFA();
	$leastDifferent = 999999;
	$bestIndex = -1;
	for ($i=0; $i < 26; $i++) {
		$textFA = textFA(shift($text, $i));
		$difference = compareFA($englishFA, $textFA);
		if($difference < $leastDifferent){
			$leastDifferent = $difference;
			$bestIndex = $i;
		}
	}
	return shift($text, $bestIndex);
}

function bruteForceShiftEnglishScore($text){
	$bestScore = -1;
	$bestIndex = -1;
	for ($i=0; $i < 26; $i++) {
		$score = metricEnglishScore(shift($text, $i));
		if($score > $bestScore){
			$bestScore = $score;
			$bestIndex = $i;
		}
	}
	return shift($text, $bestIndex);
}

function shiftRemoveKeyFA($text, $removeChars=array()){
	$keyLen = count($removeChars);
	$textFA = textFA($text);
	$englFA = englishFA();

	for ($i=0; $i < count($removeChars); $i++) { 
		$char = $removeChars[$i];
		$charOrd = ord($char);

		for ($j=$charOrd; $j > ord('A') ; $j--) { 
			$textFA[chr($j)] = $textFA[chr($j-1)];
		}
	}
	for ($i=0; $i < $keyLen; $i++) { 
		unset($englFA[chr(ord('A')+$i)]);
		unset($textFA[chr(ord('A')+$i)]);
	}

	return compareFA($textFA, $englFA);
}

function shiftRemoveKeyLengthCombinations($text, $keyLen){
	for ($i=0; $i < 26; $i++) {
		echo "\n$i/26 ".date("H:i:s")."\n\n\n";
		ob_flush();flush(); 
		_shiftRemoveKeyLengthCombinations($text, $keyLen, array(), $i);
	}
}

function _shiftRemoveKeyLengthCombinations($text, $keyLenLeft, $indexes, $addIndex){
	$indexes[] = chr($addIndex+ord('A'));
	if($keyLenLeft==1){
		$fa = shiftRemoveKeyFA($text, $indexes);
		if($fa<200){
			//print_r($indexes);
			//echo $fa;
			shiftTryKeyPermutation($text, $indexes);
		}
		return;
	}else{
		for ($i=$addIndex+1; $i < 26; $i++) { 
			_shiftRemoveKeyLengthCombinations($text, $keyLenLeft-1, $indexes, $i);
		}
	}
}


function shiftTryKeyPermutation($text, $keyArray){
	$map = shiftMapFromKey($keyArray);
	_shiftTryKeyPermutation($text, $keyArray, $map);
}

function _shiftTryKeyPermutation($text, $keyArray, $map){
	if(count($keyArray)==0){
		$shifted = shiftByMap($text, $map);
		$metric = metricFA($shifted);
		if($metric<200){
			foreach ($map as $key => $value) {
				echo $value;
			}
			echo "\n".$metric."\n$shifted\n\n\n";
		}
	}else{
		foreach ($keyArray as $key => $value) {
			$map2 = $map;
			$map2[chr(ord('A')+count($keyArray)-1)] = $value;
			$key2 = $keyArray;
			unset($key2[$key]);
			_shiftTryKeyPermutation($text, $key2, $map2);
		}
	}
}


/**************************************************************
* Affine
**************************************************************/



/**************************************************************
* Vigenere
**************************************************************/

function getEachNLetter($text, $n, $shift=0){
	$out = "";
	for ($i=0; $i+$shift < strlen($text); $i+=$n) { 
		$out .= $text[$i+$shift];
	}
	return $out;
}

function printVigAnalysisBrief($text, $maxPassLength=10){
	echo "\n== VIG ANALYSIS BRIEF ==\n\n";
	for ($i=1; $i<=$maxPassLength; $i++) { 
		$tableBF = "";
		$totalScore = 0;
		for ($j=0; $j < $i; $j++) { 
			$line = getEachNLetter($text, $i, $j);
			$score = metricIC($line);
			$totalScore += $score;
		}
		echo "key len $i score: ".($totalScore/$i)."\n";
	}
}

function printVigAnalysisForKeyLength($text, $keyLength, $useLines = array()){
	echo "\n== VIG ANALYSIS FOR KEY LENGTH $keyLength ==\n\n";

	$tableBF = "";
	$totalScore = 0;
	$width = -1;

	for ($j=0; $j < $keyLength; $j++) { 
		$line = getEachNLetter($text, $keyLength, $j);
		if($width==-1)$width=strlen($line);
		$score = metricIC($line);
		$totalScore += $score;
		$shifts = orderByMetricDesc(getAllShifts($line),"metricFA");
		echo "\nLine $j: $line\n";
		for ($i=0; $i < 3 && $i<count($shifts); $i++) { 
			echo "  $i(".getCharsDifference($shifts[$i][0],$line[0])."): {$shifts[$i]}   ".metricFA($shifts[$i])."\n";
		}
		if(isset($useLines[$j]))
			$tableBF .= $shifts[$useLines[$j]];
		else
			$tableBF .= $shifts[0];

		// $lineBF = bruteForceShiftFA($line);
		// $tableBF .= $lineBF;
		// echo " BF: $lineBF   FA:".metricFA($lineBF)."\n";
	}

	$bestLine = (tableToText($tableBF, $width));

	echo "\n";
	echo "AVG IC: ".($totalScore/$keyLength)."\n\n";
	echo "BEST LINE : ".$bestLine."\n";
	echo "LINE SCORE: ".(metricEnglishScore($bestLine))."\n\n";

	return $bestLine;
}



/**************************************************************
* Table
**************************************************************/

function getTableSizes($text){
	$out = array();
	$size = strlen($text);
	for ($i = 1; $i <= $size; $i++) { 
		if(($size%$i)==0){
			$out[] = $i;
		}
	}
	return $out;
}

function printTableSizes($text){
	$out = getTableSizes($text);
	echo "\n== TABLE SIZES ==\n\n";

	$size = strlen($text);
	foreach ($out as $i) {
		echo "  ".$i."x".($size/$i)."\n";
	}
	echo "\n";
	return $out;
}

function getTable($text, $width){
	$out = array();
	$i=0;
	while($i<strlen($text)){
		$out[] = substr($text, $i, $width);
		$i+=$width;
	}
	return $out;
}

//TODO
global $BFT_score;
global $BFT_text;
function bruteForceTable($text, $width){
	$height = strlen($text) / $width;
	$stack = array();
	for ($i=0; $i < $height; $i++){
		$stack[$i] = $i;
	}

	global $BFT_score;
	global $BFT_text;
	$BFT_score=0;
	bruteForceTablePerm($text, $width, array(), $stack);
	echo "\n\nBEST RESULT: $BFT_text \n\n";
	return $BFT_text;
}

//TODO
function bruteForceTablePerm($text, $width, $perm, $stack){
	if(count($stack)==0){
		global $BFT_score;
		global $BFT_text;
		$line = permutateTableLines($text, $width, $perm);
		$newScore = metricEnglishScore($line);
		if($newScore>$BFT_score){
			$BFT_score = $newScore;
			$BFT_text = $line;
		}
	}else{
		foreach ($stack as $key => $value) {
			$perm2 = $perm;
			$perm2[] = $value;
			$stack2 = $stack;
			unset($stack2[$key]);
			bruteForceTablePerm($text, $width, $perm2, $stack2);
		}
	}
}

//TODO
function permutateTableLines($text, $width, $perm=array()){
	$out = $text;
	$height = strlen($text)/$width;

	print_r($height);

	for ($i=0; $i < $height; $i++) { 
		if(!isset($perm[$i]))$perm[$i] = $i;
	}

	//print_r($perm);

	for ($x=0; $x < $width; $x++) { 
		for ($y=0; $y < $height; $y++) { 
			$out[$height*$x+$perm[$y]] = $text[$width*$y+$x];
		}
	}
	return $out;
}

function getVowelsCount($text){
	$count = 0;
	for ($i=0; $i < strlen($text); $i++) { 
		if($text[$i]=='A' || $text[$i]=='E' || $text[$i]=='I' || $text[$i]=='O' || $text[$i]=='U') $count++;
	}
	return $count;
}

function printTable($text, $width){
	$out = getTable($text, $width);
	foreach ($out as $line) {
		for ($i=0; $i < $width; $i++) { 
			echo $line[$i]." ";
		}
		echo "| ". getVowelsCount($line)."\n";
	}
	for ($i=0; $i <= $width; $i++) { 
		echo "--";
	}echo "\n";
	for ($i=0; $i < $width; $i++) { 
		$count = 0;
		for ($j=0; $j < count($out); $j++) { 
			$count += getVowelsCount($out[$j][$i]);
		}
		echo $count. " ";
	}echo "|\n\n";
	return $out;
}

function tableToText($text, $width){
	$out = "";
	for ($x=0; $x < $width; $x++) { 
		for ($y=0; $y*$width+$x < strlen($text); $y++) { 
			$out .= $text[$y*$width+$x];
		}
	}
	return $out;
}

function printTransposedTable($text, $width){
	$i=0;
	while($i<$width){
		$j=$i;
		while($j<strlen($text)){
			echo $text[$j];
			$j+=$width;
		}
		echo "\n";
		$i++;
	}
}

/*function bruteForceTable($text){
	$length = strlen($text);
	
	$bestScore = -1;
	$bestIndex = -1;
	//check sizes
	for ($i=2; $i < sqrt($length); $i++) { 
		if($length%$i==0){
			$x=$i;
			$y=$length/$i;

			$clear = tableToText($text, $x);
			// $score = metricEnglishScore($clear);
			// if($score > $bestScore){
			// 	$bestScore = $score;
			// 	$bestIndex = $x;
			// }
			echo $clear.": ";
			echo metricEnglishScore($clear);
 			echo "\n";
			$clear = tableToText($text, $y);
			// $score = metricEnglishScore($clear);
			// if($score > $bestScore){
			// 	$bestScore = $score;
			// 	$bestIndex = $y;
			// }
			echo $clear.": ";
			echo metricEnglishScore($clear);
			echo "\n";
		}
	}
	// return tableToText($text, $bestIndex);
}*/

/**************************************************************
*************** FOLOWING CODE IS UNREWISED ********************
**************************************************************/

function createChunks($inp, &$out, $size=2, $sep=""){
	$out = array();
	$chunk = "";

	for($i=0; $i<count($inp); $i++){
		$chunk .= $inp[$i];
		if($i%$size == $size-1){
			$out[] = $chunk;
			$chunk = "";
		}
	}
	
	if($chunk!="")$out[] = $chunk;
	printArray($out, "chunk");
}

function multipleBruteForce($arr){
	foreach ($arr as $key => $text) {
		echo "\n\n\ntask ".$key;
		echo "\nShift:\n";
		echo bruteForceShift($text);
		echo "\nTable:\n";
		echo bruteForceTable($text);
	}
}

?>
