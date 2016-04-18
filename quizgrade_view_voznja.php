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
<script type="text/javascript" src="http://code.jquery.com/jquery-2.1.3.js"></script>
<script type="text/javascript">
var order='ASC';
var orderby='';
$(document).ready(function() {
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
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+$(this).attr('id')+"&order="+order+"&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&action=get_view&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
		
		return false;
	});
	
	function refresh()
	{
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_categories&quizid="+<?php echo $quiz->id; ?>, function( data ) {

			$("#kategorijeConfig").html(data.result);

		},"json");
		
		$( "#quizHolder" ).html('');
		
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?page="+(page-1)+"&orderby="+orderby+"&order="+order+"&datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&action=get_view&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
	}
	
	$("#izvozBtn").click(function() {
		
		{
			window.location.href = "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/izvoz.php?datum=<?php echo $datum; ?>&os=<?php echo $os; ?>&quizid=<?php echo $quiz->id; ?>";
			return false;
		}
	});
	
	$("#quizHolder").on('click','button',function() {
		
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
        } );
    });

});
</script>

<?php 
$meseci = Array('1'=>'januar','2'=>'februar','3'=>'marec','4'=>'april','5'=>'maj','6'=>'junij','7'=>'julij','8'=>'avgust','9'=>'september','10'=>'oktober','11'=>'november','12'=>'december' );
?>

<div style="margin-top:20px;">
<div style="<?php if($activetab == "view") echo "display:none;" ?>height:300px;overflow-y: auto;">
	<table cellpadding="5">
		<tr>
			<th>Atribut</th>
			<th>Prikazan</th>
			<th>Pozicija</th>
		</tr>
	<?php 
	
	$user = $DB->get_record('user', array('id'=>$USER->id));

	$attributeConfig = $DB->get_records('quizgrading_att_config', array('quizid'=>$quiz->id));
	
	$attCfgArr = Array();
	
	foreach($attributeConfig as $key=>$object)
	{
		$attCfgArr[$object->atribut] = $object;
	}

	$dovoljeniAtributi = Array('id','username','email','icq','skype','yahoo','aim','msn','phone1','phone2','institution','department','address','city','country','description','middlename');

	foreach($user as $key=>$value)
	{
		if(in_array($key, $dovoljeniAtributi))
		{
			$prikazi = 0;
			$pozicija = 0;
		
			if(key_exists($key, $attCfgArr))
			{
				$prikazi = 	$attCfgArr[$key]->prikazi;
				$pozicija = 	$attCfgArr[$key]->pozicija;
			}
			
			?>
			<tr>
				<td valign="top"><?php echo $key; ?></td>
				<td valign="top"><select id="prikazi_<?php echo $key; ?>"><option <?php if(!$prikazi) echo "selected"; ?> value="0">NE</option><option <?php if($prikazi) echo "selected"; ?> value="1">DA</option></select></td>
				<td valign="top"><input id="pozicija_<?php echo $key; ?>" type="text" value="<?php echo $pozicija; ?>" name="<?php echo $key; ?>" /></td>
				<td valign="top"><button id="shrani_<?php echo $key; ?>" class="btn_shrani">SHRANI</button></td>
			</tr>
			<?php
		}
		
	}
	
	  ?>
	</table>
</div>
<div style="<?php if($activetab == "view") echo "display:none;" ?>" id="kategorijeConfig">
	
</div>
<div style="<?php if($activetab != "view") echo "display:none;" ?>">
<div style="margin-top:50px;">
	<h3 style="float:left;">Rezultati:</h3>
	<button style="float:right;" id="izvozBtn">Izvozi CSV</button>
</div>

<div style="clear: both;">
	<form action="<?php global $PAGE; echo new moodle_url($PAGE->url,array('courses' => $activetab,'id'=>$cm->id)) ?>" method="get">
		<div>
			<input type="hidden" name="id" value="<?php echo $cm->id; ?>">
		    <input type="hidden" name="courses" value="<?php echo $activetab; ?>">
		    <select name="datum">
		    	<option value="">Izberite datum opravljanja</option>
			<?php $dates = get_quiz_dates($quiz->id);
			foreach($dates as $key=>$object): $date = new DateTime($object->timefinish); ?>
				<option <?php if($object->timefinish==$datum) echo "selected='selected'"; ?> value="<?php echo $object->timefinish; ?>"><?php echo $date->format('d.m.Y'); ?></option>
			<?php endforeach; ?>
			</select>
			<select name="os">
				<option value="">Izberite OŠ</option>
				<?php $institutions = get_quiz_institutions($quiz->id); ?>
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