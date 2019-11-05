<?php
    include_once('inc/config.php');

    session_start();
    
//    include 'test.php';

    header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--        <meta charset="UTF-8">-->
        <title></title>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <link href="http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css" />

<!--	<script type="text/javascript" src="inc/jquery.fileDownload.js"></script>-->

<!--	<script type="text/javascript" src="inc/jquery.form.min.js"></script>-->
	<script type="text/javascript" src="inc/main.js"></script>
        <link href="inc/main.css" rel="stylesheet" type="text/css" />

	<style type="text/css">

	</style>
    </head>
    <body>
<!--    <script type="text/javascript" charset="utf-8">
    </script>-->
	<div class="main" style="width: 100%; hight: 100%;">
	</div>
	<div class="footer">
	</div>
	<div id="preparing-modal" title="" style="display: none;">

		<!--Throw what you'd like for a progress indicator below
		<div class="ui-progressbar-value ui-corner-left ui-corner-right" style="width: 100%; height:22px; margin-top: 20px;"></div>
		-->
		<div class="plainlog" style="width: 100%;height: 310px;padding-top: 2px;">
		    <div class="input_outer" style="margin-bottom: 5px;">
			<label>Текущая операция:</label>
			<input type="text" id="action" readonly="true" style="width: 100%;">
		    </div>
		    <div class="input_outer" style="margin-bottom: 10px;">
			<label>Прошло времени:</label>
			<input type="text" class="d_input" id="elapsedtime" readonly="true">
		    </div>
		    <div class="input_outer" id="ac" style="margin-bottom: 10px;">
			<label>Обработано акций:</label>
			<input type="text" class="d_input" id="aprocessed" readonly="true">
		    </div>
		    <div class="input_outer" id="bc" style="margin-bottom: 10px;">
			<label>Обработано книг:</label>
			<input type="text" class="d_input" id="bprocessed" readonly="true">
		    </div>
		    <textarea id="log">
		    </textarea>
		    <div class='statusbar'></div>
		</div>

	</div>
    </body>
</html>
