/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function() {
//    $('#selectAll').click(function(e){
    $( "#selectAll" ).on( "click", function(e) {
	var table= $(e.target).closest('table');
	$('td.ch_outer input:checkbox',table).prop('checked',this.checked);
    });

    $( "#preparing-modal" ).on( "dialogclose", function( event, ui ) {
	$('#action').val("");
	$('#elapsedtime').val("");
	$('#aprocessed').val("");
	$('#bprocessed').val("");
	$('#log').val("");
    });


    var settings = {
	//
	// the HTTP method to use. Defaults to "GET".
	//
	httpMethod: "GET",

	//
	// if specified will perform a "httpMethod" request to the specified 'fileUrl' using the specified data.
	// data must be an object (which will be $.param serialized) or already a key=value param string
	//
	data: null,

	//
	//a period in milliseconds to poll to determine if a successful file download has occured or not
	//
	checkInterval: 500,
    };
    var preparing = true;
    var cur_act = '';

    function checkPreparingStatus() {

	$.ajax({
	    type: "POST",
	    url: 'test.php',
	    dataType: "json",
	    data: "check",
	    async: true,
	    processData: false,
	    success: function(data, textStatus) {
//			alert('ajax success ['+data+"] ["+textStatus+"]");
//			$('#log').val($('#log').val()+"\n"+data+"] ["+textStatus+"]");
		if (data.hasOwnProperty('error')) {
			$('#action').val(data.error);
			$('#log').val("");
			preparing = false;
		} else {
		    console.log(data);
		    if (preparing) {
			if (data.hasOwnProperty('action')) {
			    $('#action').val(data.action);
			    if (cur_act != data.action) {
//				    cur_act = data.action;
				$('#log').val($('#log').val()+"\n"+data.action+"...");
			    }
			}
			if (data.hasOwnProperty('elapsedtime')) {
			    $('#elapsedtime').val(data.elapsedtime+" сек.");
			}
			if (data.hasOwnProperty('aprocessed')) {
			    $('#aprocessed').val(data.aprocessed);
			}
			if (data.hasOwnProperty('bprocessed')) {
			    $('#bprocessed').val(data.bprocessed);
			}
			preparing = data.preparing;
		    }
		}

	    },
	    error: function(data, textStatus) {
		$('#action').val("Ошибка ajax-запроса...");
//			alert('ajax error ['+data+"] ["+textStatus+"]");
		console.log(data);
		preparing = false;
	    },
	    complete: function(data) {
		if (preparing) {
		    timer = setTimeout(checkPreparingStatus, settings.checkInterval);

		} else {
		    setTimeout(function(){
			$( "#preparing-modal" ).dialog('close');
		    }, 1000);
		}
	    },
	});
    }


    $( "#preparing-modal div#bc" ).hide();
    $( "#preparing-modal div#ac" ).show();
    dialog = $( "#preparing-modal" ).dialog({
	title: "Подготовка списка акций...",
	height: 400,
	width: 450,
	modal: true
    });//.position({my:"center", at:"center", of:window});

// Формирование списка акций
    $.ajax({
	type: "POST",
	url: 'test.php',
	dataType: "json",
	data: "prepare",
	processData: false,
	async: true,
	success: function(data, textStatus) {
	    if (data.hasOwnProperty('error')) {
		    $('#action').val(data.error);
		    $('#log').val("");
	    } else {
		if (data.hasOwnProperty('action'))
		    $('#action').val(data.action);
		console.log(data);

		setTimeout(function(){
//		    $( "#preparing-modal" ).dialog('close');

		    $.ajax({
			url: "test.php?showprepared",
			cache: false,
			success: function(content) {
			    $("div.main").html(content);
			}
		    });
		}, 1000);
	    }
	    preparing = false;
	},
	error: function(data, textStatus) {
	    $('#action').val("Ошибка ajax-запроса...");
//			alert('ajax error ['+data+"] ["+textStatus+"]");
	    preparing = false;
	},
    });


//    $('form').on( "submit", function(e) {
    $(document).on( "submit", 'form', function(e) {
	//отмена действия по умолчанию для кнопки submit
	e.preventDefault();
    });


// Формирование файла
    $(document).on( "click", "button", function(e) {
	preparing = true;
	$( "#preparing-modal div#ac" ).hide();
	$( "#preparing-modal div#bc" ).show();
	dialog = $( "#preparing-modal" ).dialog({
	    title: "Формирование файла...",
	    height: 400,
	    width: 450,
	    modal: true
	});//.position({my:"center", at:"center", of:window});

	var form = $("#action_form");
	var data = form.serialize();
	if (data.length != 0) data = data + '&';
	data = data + 'submit=' + $(this).val();
	var url = form.attr('action')+'?'+data;
	console.log(url);

	$iframe = $("<iframe style='display: none' src='"+url+"'></iframe>").appendTo("body");

	//отмена действия по умолчанию для кнопки submit
	e.preventDefault();

	timer = setTimeout(checkPreparingStatus, settings.checkInterval);
    });


    timer = setTimeout(checkPreparingStatus, settings.checkInterval);
});


