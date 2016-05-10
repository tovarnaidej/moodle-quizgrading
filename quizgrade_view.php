<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
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
<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.js"></script>
<script type="text/javascript">
var order='ASC';
var orderby='';
jQuery.noConflict();
$(document).ready(function() {
	//alert("la");
	var page = 1;
	
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
		return false;
		var value = $(this).text();
		var razrez = $(this).attr('id');

		$(this).parent().html("<input type='text' value='"+value+"' class='edit_input' id='"+razrez+"' />");
		
		$("#"+razrez).focus();
		$("#"+razrez).select();
		
		return false;
	});
	
	$("#quizHolder").on('keypress','.edit_input',function(event) {
		return false;
		if(event.keyCode==13)
		{
			var id = $(this).attr('id');
			var value = $(this).val();	
			var _id = id;
			id = id.split("-");
			
			$.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=save_editable", 
	        	{ 
	        		quizgradingid: id[0], 
	        		name: id[1],
	        		value: value
	        	},function(data) {
	        		
	        		if(data.result == false) alert("Napaka pri shranjevanju!");
	        		
	        	refresh();
	        	$(this).parent().html("<a href='' class='clickable' id='"+_id+"'>"+$(this).val()+"</a>");
	        	return false; 
	        } );
		}
	});
	
	$("#quizHolder").on('blur','.edit_input',function() {
		return false;
		var id = $(this).attr('id');
		var value = $(this).val();	
		var _id = id;
		id = id.split("-");
		
		$.post( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=save_editable", 
        	{ 
        		quizgradingid: id[0], 
        		name: id[1],
        		value: value
        	},function(data) {
        		if(data.result == false) alert("Napaka pri shranjevanju!");

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
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+$(this).attr('id')+"&order="+order+"&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&leto=<?php echo $solsko_leto; ?>&action=get_view&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		return false;
	});
	
	function refresh()
	{
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_categories&quizid="+<?php echo $quiz->id; ?>, function( data ) {

			$("#kategorijeConfig").html(data.result);

		},"json");
		
		//$( "#quizHolder" ).html('');
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+orderby+"&order="+order+"&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&leto=<?php echo $solsko_leto; ?>&action=get_view&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		$.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_bookings&quizid=+<?php echo $quiz->id; ?>",function(data) {
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
		  
		  $.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_dates&quizid=+<?php echo $quiz->id; ?>&solskoleto=<?php echo $solsko_leto; ?>&booking=<?php echo $booking; ?>&os=<?php echo $os; ?>",function(data) {
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
		   
		  $.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_quiz_institutions&quizid=+<?php echo $quiz->id; ?>&datum=<?php echo $datum; ?>&booking=<?php echo $booking; ?>",function(data) {
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

		return false;
	}
	
	$("#osveziBtn").click(function() {
		refresh();
	});
	
	$("#izvozBtn").click(function() {
		
		{
			window.location.href = "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/izvoz.php?datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&leto=<?php echo $solsko_leto; ?>&quizid=<?php echo $quiz->id; ?>";
			return false;
		}
	});
	
	$("#izvozBtnRez").click(function() {
		
		{
			window.location.href = "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/izvoz_rez.php?datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&leto=<?php echo $solsko_leto; ?>&quizid=<?php echo $quiz->id; ?>";
			return false;
		}
	});
	
	$("#quizHolder").on('click','button',function() {
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?st_dresa=1&page="+(page-1)+"&orderby="+orderby+"&order="+order+"&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&leto=<?php echo $solsko_leto; ?>&action=generiraj_startne_st&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		refresh();
		return false;
	});
	
	$("#preracunajBtn").click(function() {
		$.getJSON( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?preracun=1&page="+(page-1)+"&orderby="+orderby+"&order="+order+"&leto=<?php echo $solsko_leto; ?>&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&booking=<?php echo $booking; ?>&action=get_view&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  //$("#quizHolder").html(data.result);
		  //console.log(data.result);
		  refresh();
		  preracunano = 1;
		  alert("Preračun zaključen.");
		},"json");
		
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
require_once('nastavitve_form_admin.php'); 

$mform = new nastavitve_form_admin($CFG->wwwroot . '/mod/quizgrading/saveNastavitve.php',array ('quizid'=>$quiz->id));
$mform->setQuiz($quiz->id);

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
  
} else {
  $mform->display();
}

$solska_leta = get_solska_leta($quiz->id,null,$cm->id);

$query = "SELECT gr.*,cm.id cmid FROM {course_modules} cm,{quizgrading} gr WHERE cm.instance=gr.id AND cm.id=".$cm->id;
	
$gradingConfig = $DB->get_record_sql($query);

?>
</div>
<!--
<div style="<?php if($activetab == "view") echo "display:none;" ?>" id="kategorijeConfig">
	
</div>-->
<div style="<?php if($activetab != "view") echo "display:none;" ?>">
<div style="margin-top:50px;">
	<h3 style="float:left;">Rezultati:</h3>
	<button style="float:right;" id="izvozBtn">Izvozi CSV</button>
	<?php if($gradingConfig->tip_instance != "1"): ?>
		<button type="button" style="float:right;" id="izvozBtnRez">Izvozi rezultate</button>
	<?php endif; ?>
	<!--<button style="float:right;" id="osveziBtn">Osveži</button>-->
	<button style="float:right;" id="preracunajBtn">Prenos</button>
</div>

<div style="clear: both;">
	<form action="<?php global $PAGE; echo new moodle_url($PAGE->url,array('courses' => $activetab,'id'=>$cm->id)) ?>" method="get">
		<div>
			<input type="hidden" name="id" value="<?php echo $cm->id; ?>">
		    <input type="hidden" name="courses" value="<?php echo $activetab; ?>">
		    <select id="solskoleto_select" name="solsko_leto">
		    	<option value="">Izberite šolsko leto</option>
		    	<option <?php echo (($solsko_leto=="all") ? "selected" : ""); ?> value="all">Vsa leta</option>
		    	<?php foreach($solska_leta as $leto): ?>
		    		<option <?php echo (($leto==$solsko_leto) ? "selected" : ""); ?> value="<?php echo $leto; ?>"><?php echo $leto; ?></option>
		    	<?php endforeach; ?>
		    </select>
		    <select name="booking">
		    	<option value="">Izberite izvedbo</option>
		    	<?php $bookings = get_quiz_bookings($quiz->id,$solsko_leto);
						foreach($bookings as $key=>$object): ?>
						<option <?php echo ($booking==$object->optionid) ? "selected" : ""; ?> value="<?php echo $object->optionid; ?>"><?php echo $object->naziv_izvedbe; ?></option>
						<?php endforeach; ?>
		    </select>
		    <select name="datum">
		    	<option value="">Izberite datum opravljanja</option>
			<?php $dates = get_quiz_dates($quiz->id,$solsko_leto,$booking,$os);
			foreach($dates as $key=>$object): $date = new DateTime($object->timefinish); ?>
				<option <?php if($object->timefinish==$datum) echo "selected='selected'"; ?> value="<?php echo $object->timefinish; ?>"><?php echo $date->format('d.m.Y'); ?></option>
			<?php endforeach; ?>
			</select>
			<select name="os">
				<option value="">Izberite OŠ</option>
				<?php $institutions = get_quiz_institutions($quiz->id,$booking,$datum,$solsko_leto); ?>
				<?php foreach($institutions as $key=>$object): if(trim($object->institution) == "") continue; ?>
					<option <?php if($object->institution == $os) echo " selected "; ?> value="<?php echo $object->institution; ?>"><?php echo $object->institution; ?></option>
				<?php endforeach; ?>
			</select>
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