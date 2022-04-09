



	async function getHrefs(id) {
		console.log(jQuery('#one').val());
		jQuery("#responseHref").html('Загрузка...');
		var datavar = {
			id: id,
			action: 'prok_get_urls',
			whatever: 1234,
			beginCon: getVal('#one'),
			endCon: getVal('#two'),
			url: getVal('#url'),
			process: JSON.stringify(getProcessData())
		};
		return jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: (datavar),
			//dataType: "json", // можно также передать в виде массива или объекта
			success: (data) => {
				//simpleProcessResponse(data,null,'Ошибка на стороне сервера обратитесь к администратору')
				let res = JSON.parse(data);
				if (res.code == 100) {
					console.dir(res);
					let str = "";
					str += "<B>Кол-во ссылок: </B>" + res.data.length + "<br>";
					for (let i = 0; i < res.data.length; i++) {
						str += res.data[i] + "<br>";
					}

				} else {
					if (res.error_message == null) {
						console.dir(res);
						jQuery("#response").html('Ошибка на стороне сервера обратитесь к администратору');
					} else {
						console.dir(res);
						jQuery("#response").html(error_message);
					}

				}

			},
			error: (jqXHR, exception) => processError(jqXHR, exception, 'Ошибка на стороне сервера обратитесь к администратору')
		});


	}

	async function getHr2(id, state) {
		getHrefs(id).then(async function (data) {
			let res = JSON.parse(data);
			arr = res.data;
			console.dir(arr)
			let count = getVal("#countAddPost");
			for (let i = 0; i < count; i++) {
				if (state) {
					console.dir(arr[i])
					await getContent(id);
				} else {
					console.dir(arr[i])
					await getTestContent(id);
				}
			}
		});

	}

	async function getContent(id) {
		//jQuery("#response").html('Загрузка...');
		//jQuery("#responseHref").html('')
		console.log(jQuery('#one').val());
		var datavar = {
			action: 'prok_action',
			whatever: 1234,
			test: 0,
			id: id,
			begin: getVal('#oneContent'),
			end: getVal('#twoContent'),
			title: getVal('#title'),
			ingr_pr: getVal('#prIng'),
			step_pr: getVal('#prStep'),
			cal: getVal('#prCal'),
			serves: getVal('#prServes'),
			time_cook: getVal('#prTimeCook'),
			autopost: getVal('#timeAutoupdate'),
			statust: jQuery("#stt")[0].checked,
			cat: getVal('#cat'),
			process: JSON.stringify(getProcessData())
		};
		// с версии 2.8 'ajaxurl' всегда определен в админке
		return  jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: (datavar),
				//dataType: "json", // можно также передать в виде массива или объекта
				success: (data) => simpleProcessResponse(data, null, 'Ошибка на стороне сервера обратитесь к администратору', false),
				error: (jqXHR, exception) => processError(jqXHR, exception, 'Ошибка на стороне сервера обратитесь к администратору')
			});

	}

	async function getTestContent(id) {
		console.log(jQuery('#one').val());
		//jQuery("#response").html('Загрузка...');
		var datavar = {
			action: 'prok_action',
			whatever: 1234,
			test: 1,
			id: id,
			begin: getVal('#oneContent'),
			end: getVal('#twoContent'),
			title: getVal('#title'),
			ingr_pr: getVal('#prIng'),
			step_pr: getVal('#prStep'),
			cal: getVal('#prCal'),
			serves: getVal('#prServes'),
			time_cook: getVal('#prTimeCook'),
			autopost: getVal('#timeAutoupdate'),
			cat: getVal('#cat'),
			statust: jQuery("#stt")[0].checked,
			process: JSON.stringify(getProcessData())
		};
		console.log(getVal('#cat'));
		return jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: (datavar),
			//dataType: "json", // можно также передать в виде массива или объекта
			success: (data) => {
				simpleProcessResponse(data, null, 'Ошибка на стороне сервера обратитесь к администратору', false);
			},
			error: (jqXHR, exception) => processError(jqXHR, exception, 'Ошибка на стороне сервера обратитесь к администратору')
		});

	}

	function getVal(selector) {
		return jQuery(selector).val().toString()
	}

	function saveOptions(id) {
		jQuery("#responseHref").html('')
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
			ingr_pr: getVal('#prIng'),
			step_pr: getVal('#prStep'),
			cal: getVal('#prCal'),
			serves: getVal('#prServes'),
			time_cook: getVal('#prTimeCook'),
			autopost: getVal('#timeAutoupdate'),
			countAddPost: getVal('#countAddPost'),
			firstNumber: getVal('#firstNumber'),
			cat: getVal('#cat'),
			status: jQuery("#st")[0].checked,
			statust: jQuery("#stt")[0].checked,
			process: JSON.stringify(getProcessData())
		};


		console.dir(datavar);
		// с версии 2.8 'ajaxurl' всегда определен в админке



			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: (datavar),
				//dataType: "json", // можно также передать в виде массива или объекта
				success: (data) => simpleProcessResponse(data, 'Сохранено', 'Ошибка на стороне сервера обратитесь к администратору'),
				error: (jqXHR, exception) => processError(jqXHR, exception, 'Ошибка на стороне сервера обратитесь к администратору')
			});
			// если элемент – ссылка, то не забываем:
			// return false;




	}


	function simpleProcessResponse(data, responseOk, responseEr, clear = true) {
		console.log(data);
		let res = JSON.parse(data);
		if (res.code == 100) {
			console.dir(res);
			let response = responseOk != null ? responseOk : res.data;
			if (clear) {
				jQuery("#response").html(response);
			} else {
				jQuery("#response").append(response);
			}

		} else {
			let response = res.error_message == null ? responseEr : res.error_message;
			if (clear) {
				console.dir(res);
				jQuery("#response").html(response);
			} else {
				console.dir(res);
				jQuery("#response").append(response);
			}
		}
		return res;
	}

	function processError(jqXHR, exception, message) {
		var msg = '';
		if (jqXHR.status === 0) {
			msg = 'Not connect.\n Verify Network.';
		} else if (jqXHR.status == 404) {
			msg = 'Requested page not found. [404]';
		} else if (jqXHR.status == 500) {
			msg = 'Internal Server Error [500].';
		} else if (exception === 'parsererror') {
			msg = 'Requested JSON parse failed.';
		} else if (exception === 'timeout') {
			msg = 'Time out error.';
		} else if (exception === 'abort') {
			msg = 'Ajax request aborted.';
		} else {
			msg = 'Uncaught Error.\n' + jqXHR.responseText;
		}
		console.log('Получено с сервера: ' + msg);
		jQuery("#response").html(message);
	}

	function del() {

		let arr = jQuery("input[name='row']");
		console.log(arr);
		for (let i = 0; i < arr.length; i++) {
			console.log(jQuery(arr[i]).prop('checked'));
			if (jQuery(arr[i]).prop('checked')) {
				let id = jQuery(arr[i]).val();
				console.log(id);
				delData(id, jQuery(arr[i]));
			}
		}
	}

	function delData(id, obj) {
		var data = {
			action: 'prok_del',
			id: id
		};

		// с версии 2.8 'ajaxurl' всегда определен в админке
		jQuery.post(ajaxurl, data, function (response) {
			jQuery("#response").html('Получено с сервера: ' + response);
			//console.log(obj.parent.parent);
			if (response == 'OK') {
				obj.parent().parent().remove();
			}

			//alert( 'Получено с сервера: ' + response );
		}, 'html');
	}

	function getProcessData() {
		let arr = [];
		for (let i = 0; i < 50; i++) {
			arr.push(getDataRow(i));
		}
		console.log(arr);
		return arr;
	}

	function getDataRow(number) {
		return [getValInput(number, 'search'),
			getValInput(number, 'type'),
			getValInput(number, 'name'),
			getValInput(number, 'replace'),];

	}

	function getValInput(number, type) {
		return jQuery(jQuery('[name="params[usrepl][' + number + '][' + type + ']"]')[0]).val();
	}



