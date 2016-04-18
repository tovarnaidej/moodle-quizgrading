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
$(document).ready(function() {
	refresh();
	
	function refresh()
	{
		$( "#quizHolder" ).html('');
		$.get( "<?php echo $CFG->wwwroot; ?>/mod/quizgrading/ajax.php?action=get_view_student&gradingid=<?php echo $cm->id; ?>&quizid="+<?php echo $quiz->id; ?>, function( data ) {
		  $( "#quizHolder" ).html(data.result);
		},"json");
	}
	
});
</script>
<div style="margin-top:20px;">
<div>
	<h3 style="float:left;">Rešeni kvizi:</h3>
</div>
<div style="clear:both;overflow: auto;" id="quizHolder">
</div>
</div>