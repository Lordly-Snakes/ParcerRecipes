(function( $ ) {
	'use strict';
	

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	


})( jQuery );
function getHrefs(){
	
}
function getContent(){
	jQuery("#response").html('Загрузка...');
	console.log(jQuery('#one').val());
	var data = {
		action: 'prok_action',
		whatever: 1234,
		beginCon: getVal('#one'),
		endCon: getVal('#two'),
		end: '12',
		test: 0,
		url: getVal('#url'),
		begin: getVal('#oneContent'),
		end: getVal('#twoContent'),
		title: getVal('#title'),
		process: JSON.stringify( getProcessData())
	};

	// с версии 2.8 'ajaxurl' всегда определен в админке
	jQuery.post( ajaxurl, data, function( response ){
		jQuery("#response").html('Получено с сервера: ' + response);
		console.log('Получено с сервера: ' + response);
		//alert( 'Получено с сервера: ' + response );
	});
	
// 	jQuery(document).ready( function( $ ){
		
// 	} );

}

function getTestContent(){
	console.log(jQuery('#one').val());
	jQuery("#response").html('Загрузка...');
	var data = {
		action: 'prok_action',
		whatever: 1234,
		beginCon: getVal('#one'),
		endCon: getVal('#two'),
		end: '12',
		test: 1,
		url: getVal('#url'),
		begin: getVal('#oneContent'),
		end: getVal('#twoContent'),
		title: getVal('#title'),
		process: JSON.stringify( getProcessData())
	};

	// с версии 2.8 'ajaxurl' всегда определен в админке
	jQuery.post( ajaxurl, data, function( response ){
		jQuery("#response").html('Получено с сервера: ' + response);
		console.log('Получено с сервера: ' + response);
		//alert( 'Получено с сервера: ' + response );
	});
	
// 	jQuery(document).ready( function( $ ){
		
// 	} );

}
function getVal(selector){
	return jQuery(selector).val().toString()
}

function saveOptions(id){
	jQuery("#response").html('Сохранение...');
	console.log(jQuery('#one').val());
	getProcessData();
	var datavar = {
			action: 'prok_save',
			id: id,
			name: getVal('#name'),
			url: getVal('#url'),
			beginCon: getVal('#one'),
			endCon: getVal('#two'),
			begin: getVal('#oneContent'),
			end: getVal('#twoContent'),
			title: getVal('#title'),
			process: JSON.stringify( getProcessData())
	};



	console.log(datavar);
	// с версии 2.8 'ajaxurl' всегда определен в админке
	
	jQuery(function($){
	
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: (datavar), // можно также передать в виде массива или объекта
			success: function( data ) {
				//jQuery("#response").html('Получено с сервера: ' + data);
				console.log('Получено с сервера: ' + data);
				jQuery("#response").html('Сохранено');
			}
		});
		// если элемент – ссылка, то не забываем:
		// return false;
	
 	});


}

function del(){
	
	let arr = jQuery("input[name='row']");
	console.log( arr);
	for(let i =0;i< arr.length;i++){
		console.log( jQuery(arr[i]).prop('checked'));
		if(jQuery(arr[i]).prop('checked')){
		   let id = jQuery(arr[i]).val();
			console.log(id);
			delData(id,jQuery(arr[i]));
		}
	}
}

function delData(id,obj){
	var data = {
		action: 'prok_del',
		id: id
	};

	// с версии 2.8 'ajaxurl' всегда определен в админке
	jQuery.post( ajaxurl, data, function( response ){
		jQuery("#response").html('Получено с сервера: ' + response);
		//console.log(obj.parent.parent);
		if(response == 'OK'){
			obj.parent().parent().remove();   
		}
		
		//alert( 'Получено с сервера: ' + response );
	},'html');
}

function getProcessData(){
	let arr = [];
	for(let i=0;i<50;i++){
		arr.push(getDataRow(i));
	}
	console.log(arr);
	return arr;
}

function getDataRow(number){
	return [getValInput(number,'search'),
	getValInput(number,'type'),
	getValInput(number,'name'),
	getValInput(number,'replace'),];
	
}

function getValInput(number,type){
	return jQuery(jQuery('[name="params[usrepl]['+ number +']['+ type +']"]')[0]).val();	
}

