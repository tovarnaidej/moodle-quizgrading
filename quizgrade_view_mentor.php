<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


$solska_leta = get_solska_leta($quiz->id,$USER->id,$cm->id);

//var_dump($quiz->id,$USER->id,$cm->id);
//$solsko_leto = "";

if(!in_array($solsko_leto, $solska_leta))
{
	if(isset($solska_leta[count($solska_leta)-1]))
		$solsko_leto = $solska_leta[count($solska_leta)-1];
}


$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm->id;
	
$gradingConfig = $DB->get_record_sql($query);

//var_dump($gradingConfig);

?>
<style>
	input[type='text'] {width:50px;margin-bottom:0;}
	
	#configKvizaHolder input[type='text'] {width:250px;margin-bottom:0;} 
	#configKvizaHolder select {width:264px;margin-bottom:0;} 	

	div.inputHolder {float:right;width:150px;}
	div.inputHolderMargin {margin-left:20px;}
	.saveBtn {margin-bottom:0;}
	#configSave {margin-bottom:0;}
</style>
<!--<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.js"></script>-->
<script type="text/javascript">
var order='ASC';
var orderby='';
$(document).ready(function() {
	$("#izvozBtn").blur();
	/*
	$(window).bind('beforeunload',function(){

		if(preracunano != 1)
		{
			//alert("POZOR: Podatki niso preračunani!");
			return 'Podatki niso preračunani!';
		}
   
	});*/
	
	var page = 1;
	var preracunano = 1;
	
	refresh();
	
	$(".btn_shrani").click(function() {
		
		var id = $(this).attr('id').split('_');
		
		var key = id[1];

		$.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=insert_update_attribute", 
        	{ 
        		quizid: <?php echo $quiz->id; ?>, 
        		atribut: key,
        		prikazi: $("#prikazi_"+key).val(),
        		pozicija: $("#pozicija_"+key).val()
        	},function() {
        	alert("Shranjeno");
        	refresh();
        	return false;
        } );
        
        return false;
	});
	
	
	$("#quizHolder").on('click','.clickable',function() {
		
		if($("#booking_select").val() == "" || $("#datum_select").val() == "" || $("#datum_select").val() == "vsi")
		return false;
		
		var value = $(this).text();
		var razrez = $(this).attr('id');

		$(this).parent().html("<input type='text' value='"+value+"' class='edit_input' id='"+razrez+"' />");
		
		$("#"+razrez).focus();
		$("#"+razrez).select();
		
		return false;
	});
	
	$("#quizHolder").on('keypress','.edit_input',function(event) {

		if(event.keyCode==13)
		{
			$("#izvozBtn").blur();
			//preracunano = 0;
			var id = $(this).attr('id');
			var value = $(this).val();	
			var _id = id;
			id = id.split("-");
			
			$.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=save_editable", 
	        	{ 
	        		quizgradingid: id[0], 
	        		name: id[1],
	        		value: value,
	        		datum: '<?php echo $datum; ?>'
	        	},function(data) {
	        		
	        		if(data.result == false) alert("Napaka pri shranjevanju!");
	        		
	        	refresh();
	        	refresh();
	        	$(this).parent().html("<a href='' class='clickable' id='"+_id+"'>"+$(this).val()+"</a>");
	        	return false; 
	        } );
		}
	});
	
	$("#quizHolder").on('blur','.edit_input',function() {
		
		//preracunano = 0;
		var id = $(this).attr('id');
			var value = $(this).val();	
			var _id = id;
			id = id.split("-");
			
			$.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=save_editable", 
	        	{ 
	        		quizgradingid: id[0], 
	        		name: id[1],
	        		value: value,
	        		datum: '<?php echo $datum; ?>'
	        	},function(data) {
	        		
	        		if(data.result == false) alert("Napaka pri shranjevanju!");
	        		
	        	refresh();
	        	refresh();
	        	$(this).parent().html("<a href='' class='clickable' id='"+_id+"'>"+$(this).val()+"</a>");
	        	return false; 
	        } );

	});
	
	
	$("#quizHolder").on('click','.page',function() {
		//console.log($(this).text());
		page = $(this).text();
		refresh();
		
		$(window).scrollTop(0);
		return false;
	});
	
	$("#quizHolder").on('click','.order',function() {	
		
		orderby = $(this).attr('id');
		
		if(order == "ASC") order = "DESC"; 
		else order = "ASC";
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+$(this).attr('id')+"&order="+order+"&opravil=<?php echo $opravil; ?>&leto=<?php echo $solsko_leto; ?>&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&action=get_view_mentor&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		return false;
	});
	
	function refresh()
	{
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_categories&quizid="+<?php echo $quiz->id; ?>, function( data ) {

			$("#kategorijeConfig").html(data.result);

		},"json");
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+orderby+"&order="+order+"&opravil=<?php echo $opravil; ?>&leto=<?php echo $solsko_leto; ?>&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&action=get_view_mentor&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		  
		  $.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_bookings_mentor&quizid=+<?php echo $quiz->id; ?>&cmid=<?php echo $cm->id; ?>",function(data) {
		  	$("#booking_select").html('<option value="">Izberite izvedbo</option>');
				for(var i in data.result)
				{
					var selected = "";
					
					if(data.result[i].optionid == "<?php echo $booking; ?>")
					selected = " selected ";
					
					html = '<option '+selected+' value="'+data.result[i].optionid+'">'+data.result[i].naziv_izvedbe+'</option>';
					$("#booking_select").append(html);
				}
		    	
		  });
		  
		  $.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_dates_mentor&quizid=+<?php echo $quiz->id; ?>&cmid=<?php echo $cm->id; ?>&solskoleto=<?php echo $solsko_leto; ?>&booking=<?php echo $booking; ?>&os=<?php echo $os; ?>",function(data) {
		  	$("#datum_select").html('<option value="vsi">Izberite datum opravljanja</option>');
				for(var i in data.result)
				{
					var selected = "";
					
					if(data.result[i].timefinish == "<?php echo $datum; ?>")
					selected = " selected ";
					
					html = '<option '+selected+' value="'+data.result[i].timefinish+'">'+data.result[i].datum+'</option>';
					$("#datum_select").append(html);
				}
		  });
		  
		  $.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_institutions_mentor&quizid=+<?php echo $quiz->id; ?>&cmid=<?php echo $cm->id; ?>&datum=<?php echo $datum; ?>&booking=<?php echo $booking; ?>",function(data) {
		  	$("#os_select").html('<option value="">Izberite OŠ</option>');
				for(var i in data.result)
				{
					var selected = "";
					
					if(data.result[i].institution == "") continue;
					
					if(data.result[i].institution == "<?php echo $os; ?>")
					selected = " selected ";
					
					html = '<option '+selected+' value="'+data.result[i].institution+'">'+data.result[i].institution+'</option>';
					$("#os_select").append(html);
				}
		  });
		  
		},"json");
		
		return false;
	}
	
	$("#osveziBtn").click(function() {
		refresh();
	});
	
	$("#preracunajBtn").click(function() {
		$.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?preracun=1&page="+(page-1)+"&orderby="+orderby+"&order="+order+"&leto=<?php echo $solsko_leto; ?>&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&action=get_view_mentor&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  //$("#quizHolder").html(data.result);
		  //console.log(data.result);
		  refresh();
		  preracunano = 1;
		  alert("Preračun zaključen.");
		},"json");
		
		return false;
		
	});
	
	$("#izvozBtn").click(function() {
		
		{
			window.location.href = "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/izvoz.php?&mentor=1&datum=<?php echo $datum; ?>&leto=<?php echo $solsko_leto; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&quizid=<?php echo $quiz->id; ?>";
			return false;
		}
	});
	
	$("#quizHolder").on('click','button',function() {
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?st_dresa=1&page="+(page-1)+"&orderby="+orderby+"&order="+order+"&leto=<?php echo $solsko_leto; ?>&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&action=generiraj_startne_st_mentor&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		refresh();
		return false;
	});

	
	$('#kategorijeConfig').on('click', 'button', function (){
		
        var split = $(this).attr('id').split('_');
		
        $.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=insert_update_cat_config", 
        	{ 
        		quizid: <?php echo $quiz->id; ?>, 
        		category: split[1],
        		tocke:$("#tocke_"+split[1]).val(),
        		skupnotock:$("#skupnotock_"+split[1]).val(),
        		izpisi:$("#izpis_"+split[1]).val(),
        		izpisikaj:$("#izpisikaj_"+split[1]).val()
        	},function() {
        	alert("Shranjeno");
        	refresh();
        	return false;
        } );
        return false;
    });

});
</script>

<?php 
$meseci = Array('1'=>'januar','2'=>'februar','3'=>'marec','4'=>'april','5'=>'maj','6'=>'junij','7'=>'julij','8'=>'avgust','9'=>'september','10'=>'oktober','11'=>'november','12'=>'december' );
?>

<div style="margin-top:20px;">
<div style="<?php if($activetab == "view") echo "display:none;" ?>">
<?php 
/*
require_once('nastavitve_form.php'); 
require_once('nastavitve_tockovanja_form.php'); 

$mform = new nastavitve_form($CFG->wwwroot . '/mod/quizgrading/saveNastavitve.php',array ('quizid'=>$quiz->id));
$mform->setQuiz($quiz->id);

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
  
} else {
  $mform->display();
}
*/



?>
</div>
<!--
<div style="<?php if($activetab == "view") echo "display:none;" ?>" id="kategorijeConfig">
	
</div>-->
<div style="<?php if($activetab != "view") echo "display:none;" ?>">
<div style="margin-top:50px;">
	<h3 style="float:left;">Rezultati:</h3>
	<button type="button" style="float:right;" id="izvozBtn">Izvozi CSV</button>
	<!--<button style="float:right;" id="preracunajBtn">Preračunaj</button>-->
	<!--<button style="float:right;" id="osveziBtn">Osveži</button>-->
	<!--<button style="float:right;" id="preracunajBtn">Prenos</button>-->
</div>

<div style="clear: both;">
	<form action="<?php global $PAGE; echo new moodle_url($PAGE->url,array('courses' => $activetab,'id'=>$cm->id)) ?>" method="get">
		<?php 
		//var_dump($solska_leta);
		?>
		<div>
			<input type="hidden" name="id" value="<?php echo $cm->id; ?>">
		    <input type="hidden" name="courses" value="<?php echo $activetab; ?>">
		    <select id="solskoleto_select" name="solsko_leto">
		    	<option value="">Izberite šolsko leto</option>
		    	<?php foreach($solska_leta as $leto): ?>
		    		<option <?php echo (($leto==$solsko_leto) ? "selected" : ""); ?> value="<?php echo $leto; ?>"><?php echo $leto; ?></option>
		    	<?php endforeach; ?>
		    </select>
		    <select id="booking_select" name="booking">
		    	<option value="">Izberite izvedbo</option>
		    	<?php $bookings = get_quiz_bookings_mentor($quiz->id,$USER->id,$cm->id);
						foreach($bookings as $key=>$object): ?>
						<option <?php echo ($booking==$object->optionid) ? "selected" : ""; ?> value="<?php echo $object->optionid; ?>"><?php echo $object->naziv_izvedbe; ?></option>
						<?php endforeach; ?>
		    </select>
		    <select id="datum_select" name="datum">
		    	<option value="vsi">Izberite datum opravljanja</option>
			<?php 
			foreach($dates as $key=>$object): $date = new DateTime($object->timefinish); ?>
				<option <?php if($object->timefinish==$datum) echo "selected='selected'"; ?> value="<?php echo $object->timefinish; ?>"><?php echo $date->format('d.m.Y'); ?></option>
			<?php endforeach; ?>
			</select>
			
			<select id="os_select" name="os">
				<option value="">Izberite OŠ</option>
				<?php $institutions = get_quiz_institutions_mentor($quiz->id,$cm->id,$USER->id,$booking,$datum); ?>
				<?php foreach($institutions as $key=>$object): if(trim($object->institution) == "") continue; ?>
					<option <?php if($object->institution == $os) echo " selected "; ?> value="<?php echo $object->institution; ?>"><?php echo $object->institution; ?></option>
				<?php endforeach; ?>
			</select>
			<?php if($gradingConfig->tip_instance == "1"): ?>
			<select name="opravil" id="opravil_select">
				<option value="">Izberite uspešnost</option>
				<option <?php if($opravil == "1") echo "selected"; ?> value="1">Opravil</option>
				<option <?php if($opravil == "0") echo "selected"; ?> value="0">Ni opravil</option>
			</select>
			<?php endif; ?>
			<input type="submit" value="Filter report">
		    <input id="buttonclear" type="button" value="Reset">
	    </div>
</form> 

</div>

<div style="clear:both;overflow: auto;" id="quizHolder">
</div>
</div>
</div>

<script type="text/javascript">
    YUI().use('node-event-simulate', function (Y) {

        Y.one('#buttonclear').on('click', function () {
           window.location.href = '<?php global $PAGE; echo new moodle_url($PAGE->url,array('id'=>$cm->id)) ?>';
        });
    });
</script>