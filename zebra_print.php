<?PHP
	/* Zebra_print.php
	* (C)2020JoWi joembedded.de
	* Form renderer and sender 
	*
	* Sample FORM file with 2 Parameters and 2 Images
	^XA
	^LRY
	^FO100,50
	^GB195,203,195^FS
	^FO180,110^CFG
	^FD$0^FS					  <- $0 to $5 is replaced by p0..p5
	^FO130,170
	^FD$1^FS
	^FD$#bt32x32.png^FS            <- $#FNAME is replcaed by ZPL data of FNAME (only for PNG)
	^FD$#qr195.png^FS
	^XZ
	*/
	error_reporting(E_ALL);
	header('Content-Type: text/plain; charset=utf-8');
	require_once("./zebralib.php");

	//----- Local settings for THIS printer -------
	define ("PRINTER_IP","192.168.178.241");
	define ("PRINTER_PORT","6101"); // 6101 Default RAW Port ZD420

	$dbg=@$_REQUEST["dbg"];
	//$dbg = -1; // Man.
	

	$p0=@$_REQUEST["p0"];
	$p1=@$_REQUEST["p1"];
	$p2=@$_REQUEST["p2"];
	$p3=@$_REQUEST["p3"];
	$p4=@$_REQUEST["p4"];
	$p5=@$_REQUEST["p5"];
	
	$formname = @$_REQUEST["f"];

	if($dbg){ // set $dbg to <0 to catch the params
		$fd=fopen('dbg.log','w');
		fwrite($fd,"dbg: $dbg\n");
		fwrite($fd,"p0: '$p0'\n");
		fwrite($fd,"p1: '$p1'\n");
		fwrite($fd,"p2: '$p2'\n");
		fwrite($fd,"p3: '$p3'\n");
		fwrite($fd,"p4: '$p4'\n");
		fwrite($fd,"p5: '$p5'\n");
		fwrite($fd,"formname: '$formname'\n");
		fclose($fd);
	}

	if(!strlen($formname)) die("ERROR: Which Form?");
	$form=@file_get_contents("./forms/".$formname);
	if(!$form) die("ERROR: Form '$formname' not found");
	
	if($dbg>1) {
		echo "Form(raw):\n".$form."\n\n";
	}
	
	$form = str_replace('$0', $p0, $form);
	$form = str_replace('$1', $p1, $form);
	$form = str_replace('$2', $p2, $form);
	$form = str_replace('$3', $p3, $form);
	$form = str_replace('$4', $p4, $form);
	$form = str_replace('$5', $p5, $form);
	
	if($dbg>1) echo "Form(Params):\n".$form."\n\n";

	for(;;){
		$pos = strpos($form,'$#');
		if($pos===false) break;
		$pngfname='';
		$pos+=2;
		for(;;){
			$c=substr($form,$pos,1);
			if($c<=' ' || $c>'z' || $c=='^') break;
			$pngfname.=$c;
			$pos++;
		}
		if($dbg>1) echo "\n(Found Image '$pngfname')";

		$srcimage = @imagecreatefrompng("./forms/$pngfname"); 
		if($srcimage === false ) die("ERROR: File '$pngfname' not Found or not PNG Format");
		$zimage = new ZImage($srcimage); 
		$imgdata = $zimage->toAscii();

		if($dbg>1) echo "(Image '$pngfname': ".strlen($imgdata)." Bytes)\n";

		$form = str_replace('$#'.$pngfname, $imgdata, $form);
	}

	if($dbg>0) {
		if($dbg>1) echo "Form(Images):\n".$form."\n";
		$res = "(dbg=$dbg)";
	}else{
		$res = zpl_sendto(PRINTER_IP,PRINTER_PORT,$form); 
	}

	if($dbg){
		$fd=fopen('dbg.log','a');
		fwrite($fd,"res: '$res'\n\n");
		fwrite($fd,"Form(rendered):\n".$form."\n");
		fclose($fd);
	}

	if($res) die("ERROR: Send failed ('$res')");
	echo "OK";
?>