<div class="row-fluid">
    <div class="page-x-spacer">

	    <h1><?= lang('thoughtco.printer::default.autoprint'); ?></h1>
	    <p><?= lang('thoughtco.printer::default.instructions'); ?></p>

	    <p><br /><strong><?= lang('thoughtco.printer::default.last_order_printed'); ?></strong> <span data-lastid></span></p>

	    <?= $this->renderAutoprint(); ?>

	    <script type="text/javascript">
		    window.addEventListener("DOMContentLoaded", function(){
			    function pollForSales(deviceObj){

				    // usb or ip
				    if (PRINTER_SETTINGS.type == 'usb' || PRINTER_SETTINGS.type == 'ip'){

					    fetch(
					    	location.href + '&getSales=1',
					    	{
						    	method: 'GET'
							}
						)
						.then(function(response){
							return response.json();
						})
						.then(async function(response){

							for (responsei=0; responsei<response.length; responsei++){

								var order = response[responsei];

								try {

                                    if (order.js !== false){

    									await ESCPrint.sendText(
    										deviceObj,
    										PRINTER_SETTINGS,
    										order.js
    									);

    									if (window.parent != window){
    										window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.last_order_printed'); ?>" + order.id, 'printed');
    									} else {
    										document.querySelector('[data-lastid]').innerHTML = order.id;
    									}

                                    }

									updateSale(order.id);

								} catch (e){
									if (window.parent != window){
										window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect'); ?>", 'error');
									} else {
										alert(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect'); ?>");
									}
								}

							};

							// wait 30s and poll again
							window.setTimeout(pollForSales.bind(this, deviceObj), (30000));

						})
						.catch(function(error){

							if (window.parent != window){
								window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_retrieve'); ?>", 'printed');
							} else {

								if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_retrieve'); ?>")){
									pollForSales(deviceObj);
								}

							}

						});

					// ethernet
					} else {

					    fetch(
					    	location.href + '&getSales=1',
					    	{
						    	method: 'GET'
							}
						)
						.then(function(response){
							return response.json();
						})
						.then(function(response){

							for (i=0; i<response.length; i++){

								var order = response[i];

                                if (order.js !== false){

    								for (j=0; j<PRINTER_SETTINGS.copies; j++){

    									deviceObj.addFeedLine(PRINTER_SETTINGS.lines_before);
    									order.js.forEach(function(js){
    										eval(js); // nasty but we control it
    									});
    									deviceObj.addFeedLine(PRINTER_SETTINGS.lines_after);

    									if (PRINTER_SETTINGS.autocut){
    										deviceObj.addCut();
    									}

    									deviceObj.send();

    								}


    								if (window.parent != window){
    									window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.last_order_printed'); ?>" + order.id, 'printed');
    								} else {
    									document.querySelector('[data-lastid]').innerHTML = order.id;
    								}

                                }

								updateSale(order.id);

							};

							// wait 30s and poll again
							window.setTimeout(pollForSales.bind(this, deviceObj), (30000));

						})
						.catch(function(error){

							if (window.parent != window){
								window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_retrieve'); ?>", 'error');
							} else {
								if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_retrieve'); ?>")){
									pollForSales(deviceObj);
								}
							}

						});

					}

			    }

			    // update sale so its no longer pending
			    function updateSale(id){

				    fetch(
				    	location.href + '&updateSale=' + id,
				    	{
					    	method: 'GET'
						}
					)
					.then(function(response){ });

			    }

			    // usb
			    if (PRINTER_SETTINGS.type == 'usb'){

					(async function(){

						try {

							var devices = await ESCPrint.getDevices();
							if (devices.length){
								pollForSales(devices[0]);
							} else {

								if (window.parent != window){
									window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect_retry'); ?>", 'connect');
								} else {
									if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect_retry'); ?>")){
										location.reload();
									}
								}
							}

						} catch (e){

							if (window.parent != window){
								window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect_retry'); ?>", 'connect');
							} else {
								if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect_retry'); ?>")){
									location.reload();
								}
							}
						}

					}());

				// ip
				} else if (PRINTER_SETTINGS.type == 'ip'){

					// Create WebSocket connection.
					let socket = new WebSocket((PRINTER_SETTINGS.ssl == 1 ? 'wss://' : 'ws://') + PRINTER_SETTINGS.ip_address + ':' + PRINTER_SETTINGS.port);

					socket.onclose = function (event) {
						//alert("<?= lang('thoughtco.printer::default.fail_connect'); ?>");
						return;
					};

					socket.onerror = function (event) {
						if (window.parent != window){
							window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect'); ?>", 'error');
						} else {
							alert(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect'); ?>");
						}
						return;
					};

					// Connection opened
					socket.onopen = function (event) {
						pollForSales(socket);
					};

				// epson
			    } else {

					var ePosDev = new epson.ePOSDevice();
					ePosDev.connect(
						PRINTER_SETTINGS.ip_address,
						PRINTER_SETTINGS.port,
						function(connectionResult){

							var deviceId = PRINTER_SETTINGS.device_name;
							var options = {'crypto': true, 'buffer': false};

							if ((connectionResult == 'OK') || (connectionResult == 'SSL_CONNECT_OK')){

								//Retrieves the Printer object
								ePosDev.createDevice(
									deviceId,
									ePosDev.DEVICE_TYPE_PRINTER,
									options,
									function(deviceObj, errorCode){

										// no device
										if (deviceObj === null){
											if (window.parent != window){
												window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect'); ?>", 'connect');
											} else {
												if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect'); ?>")){
													location.reload();
												}
											}
										}

										if (errorCode == 'OK'){

											deviceObj.onerror = function(error){
												deviceObj = null;

												if (window.parent != window){
													window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_try'); ?>", 'error');
												} else {
													if (confirm(PRINTER_SETTINGS.label + ": " + error.message + ". <?= lang('thoughtco.printer::default.fail_try'); ?>")){
														location.reload();
													}
												}
											}

											pollForSales(deviceObj);

											window.addEventListener('beforeUnload', function(){
												ePosDev.disconnect();
											});

									    }

									}
								);

							} else {
								if (window.parent != window){
									window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: <?= lang('thoughtco.printer::default.fail_connect'); ?>", 'connect');
								} else {
									if (confirm(PRINTER_SETTINGS.label + ": <?= lang('thoughtco.printer::default.fail_connect'); ?>")){
										location.reload();
									}
								}
							}

						}
					);

				}

			});
		</script>

    </div>
</div>
