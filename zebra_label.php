<!DOCTYPE html>
<!-- (C)JoWi joembedded.de 

This is a simple test form to send manual paramaters to the available forms.
For "Auto Print" Forms see 'badge.html' as sample usecase.
-->
<html>
    <head>
        <meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="jquery-3.3.1.min.js"></script>
		
        <title>ZEBRA Label</title>
		        
		<style>
        .t_in { background-color:whitesmoke; margin:2px}
        .t_out { background-color:white; margin:2px}
		.t_frame { background-color:lightgray; border:1px solid gray; padding: 5px;}
        </style>
		<script>
		"use strict";
		//--- Globals ---
		let ajaxActiveFlag=0; // Reset on Success or Error-Close
        function print() {
			let formname = document.getElementById("id_t_form").value
			if(!formname.length){
				alert("ERROR: No Formname")
				return
			}
			document.getElementById("id_t_res").value = "Wait..."
			let p0 = document.getElementById("id_t_par0").value
			let p1 = document.getElementById("id_t_par1").value
			let p2 = document.getElementById("id_t_par2").value
			let p3 = document.getElementById("id_t_par3").value
			let p4 = document.getElementById("id_t_par4").value
			let p5 = document.getElementById("id_t_par5").value

			ajaxActiveFlag=1
			let jcmd = {f:formname, p0:p0, p1:p1, p2:p2, p3:p3, p4:p4, p5:p5, dbg:0} // opt. dbg>0: No Print
			$.post("./zebra_print.php", jcmd, function( data ) {
				ajaxActiveFlag=0;
				let result = data
				document.getElementById("id_t_res").value = result
			},'text')
			
		}
		
		// ---Setup ---
        function setup() {
			$.ajaxSetup({ 
				type: 'POST',	
				timeout: 60000, 	// 60 secs Time
				error: function(xhr, status , error){ // error string already in xhr
					var errorMessage = xhr.status + ': ' + xhr.statusText+" - AJAX:'"+error+"'"
					if(xhr.responseText!==undefined) errorMessage+="\n"+xhr.responseText
					alert("ERROR:" + errorMessage)
					ajaxActiveFlag=0
					document.getElementById("id_t_res").value = "ERROR:" + errorMessage
				}
			})
			document.getElementById("id_t_send").addEventListener("click", print)
        }
		//--- Page loaded und ready ---
        window.addEventListener("load", setup)
        </script>
    </head>

    <body>
		<div  class="t_frame">
			<h2>ZEBRA Label - Online Printer V0.1</h2>
			Demonstrates the use of 'zebra_printer.php'. Select a form and print it. 
			<br><br>

			<b>Form:</b>
			<div>
				Name: 
				<select  class="t_in" style="width: 30%;" id="id_t_form">
<?PHP
				$dir = scandir("./forms/");	// With sort
				foreach($dir as $entry){
					if($entry[0]==='.') continue;
					if(strcasecmp(substr($entry,-4),".frm")) continue;
					$form = $entry;
					echo "<option value='$form'> $form </option>\n";
				}
?>					
				</select>
				<br>
			</div>
			<br>
			<b>Parameters:</b>
			<div>
				#0: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par0"><br>
				#1: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par1"><br>
				#2: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par2"><br>
				#3: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par3"><br>
				#4: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par4"><br>
				#5: <input class="t_in"; style="width: 80%;" type="text" id="id_t_par5"><br>
			</div>
			<br>
			<b>Result:</b>
			<div>
				Result: <input class="t_out"; style="width: 60%;" type="text" id="id_t_res" disabled><br>
			</div>

			<br>
			<button id="id_t_send"><b><h2>&nbsp; Print &nbsp;</h2></b></button>
		</div>
    </body>
</html>