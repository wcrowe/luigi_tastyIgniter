<?php

namespace Thoughtco\Printer\Controllers;

use AdminMenu;
use ApplicationException;
use DB;
use Request;
use Thoughtco\Printer\Models\Printer;
use Thoughtco\Printer\Models\Settings;
use Thoughtco\Printer\Classes\Printerfunctions;

/**
 * Automation Admin Controller
 */
class Printdocket extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'printer');

    }

    public function index()
    {
		$this->addJs('extensions/thoughtco/printer/assets/js/encoding-indexes.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/encoding.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/epos-2.14.0.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/escprint-1.0.4.js', 'thoughtco-printer');
    }

    public function renderPrintdocket()
    {

	    $previousPage = $_SERVER['HTTP_REFERER'] ?? '/admin/orders';

	    $renderHtml = '<h1>'.lang('thoughtco.printer::default.printing_docket').'</h1>'.PHP_EOL;
	    $renderHtml .= '<p><br /><a class="btn btn-secondary" href="'.$previousPage.'">'.lang('thoughtco.printer::default.btn_back').'</a></p>'.PHP_EOL;
		$renderHtml .= '<script type="text/javascript">window.addEventListener("DOMContentLoaded", function(){ ';

        // get the sale
        if ($saleId = Request::get('sale'))
        {
	    	$sale = \Admin\Models\Orders_model::where('order_id', $saleId)->first();

	    	// valid sale
	    	if ($sale !== NULL){

	    		// loop over all printers for this location
		    	Printer::where(['location_id' =>  $sale->location_id, 'is_enabled' => true])
		    	->each(function($printer) use(&$renderHtml, $sale, $previousPage){

			    	// valid printer
			    	if ($printer !== NULL){

				    	// redirect means success
				    	if (Request::get('redirect'))
                            return;
							
						$encoding = 'windows-1252';

				    	// use default format
				    	if ($printer->printer_settings['usedefault'] == 1){

					    	$output_format = Settings::get('output_format', '');
					    	$linesBefore = Settings::get('lines_before', 0);
					    	$linesAfter = Settings::get('lines_after', 0);
							$encoding = Settings::get('encoding', 'windows-1252');

					    // use custom format
				    	} else {

					    	$output_format = $printer->printer_settings['format'];
					    	$linesBefore = $printer->printer_settings['lines_before'];
					    	$linesAfter = $printer->printer_settings['lines_after'];
							$encoding = $printer->printer_settings['encoding'];

				    	}

				    	if ($linesBefore == '') $linesBefore = 0;
				    	if ($linesAfter == '') $linesAfter = 0;

				    	// turn sale data into variables
				    	$variables = Printerfunctions::getSaleData($sale, $printer->printer_settings['categories'] ?? []);

				    	// if we have something to print
				    	if (count($variables['order_menus']) > 0){

							// render the blade or pagic template
							$output_format = Printerfunctions::renderTemplate($printer->id, (object)$printer->printer_settings, $variables);

							// how many copies to print?
							$copies = intval($printer->printer_settings['copies']);
							if ($copies < 1) $copies = 1;

							$settings = [
								'lines_before' => $linesBefore,
								'lines_after' => $linesAfter,
								'copies' => $copies,
								'autocut' => $printer->printer_settings['autocut'],
								'characters_per_line' => $printer->printer_settings['characters_per_line'] ?? 48,
								'codepage' => $printer->printer_settings['codepage'] ?? 16,
								'encoding' => $encoding,
							];

							$renderHtml .= 'try { ';

							// usb
							if ($printer->printer_settings['type'] == 'usb'){

								$renderHtml .= '
								(async function(){

									try {

										var devices = await ESCPrint.getDevices();

										if (devices.length){

											await ESCPrint.sendText(devices[0], '.json_encode($settings).', `'.$output_format.'`);
											//location.href="'.$previousPage.'";
											return;

										} else {

											alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
											//location.href="'.$previousPage.'";
											return;
										}

									} catch (e){
										alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
									}

								}());
								';

							// ip
							} else if ($printer->printer_settings['type'] == 'ip'){

								$socketUrl = $printer->printer_settings['ssl'] == 1 ? 'wss://' : 'ws://';
								$socketUrl .= $printer->printer_settings['ip_address'];
								$socketUrl .= ':'.$printer->printer_settings['port'];

								$renderHtml .= '
								(async function(){

									// Create WebSocket connection.
									let socket = new WebSocket("'.$socketUrl.'");

									socket.onclose = function (event) {
										//alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
										//location.href="'.$previousPage.'";
										return;
									};

									socket.onerror = function (event) {
										alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
										//location.href="'.$previousPage.'";
										return;
									};

									// Connection opened
									socket.onopen = async function (event) {
										await ESCPrint.sendText(socket, '.json_encode($settings).', `'.$output_format.'`);
										//location.href="'.$previousPage.'";
										return;
									};

								}());
								';

							// ethernet
							} else {

								// turn our output into epson friendly js
								$output_format = Printerfunctions::orderToEthernetJs($output_format, $printer->printer_settings);

								// build a single print
								$printData = '';
								$printData .= ($linesBefore > 0 ? 'deviceObj.addFeedLine('.$linesBefore.');' : '');
								$printData .= implode("\r\n", $output_format);
								$printData .= ($linesAfter > 0 ? 'deviceObj.addFeedLine('.$linesAfter.');' : '');
								if ($printer->printer_settings['autocut']) $printData .= 'deviceObj.addCut();';

								// copies
								$finalPrintData = '';
								for ($i=0; $i<$copies; $i++){
									$finalPrintData .= $printData;
								}

						    	// html to output
						    	$renderHtml .= '
								var ePosDev = new epson.ePOSDevice();
								ePosDev.connect(
									"'.$printer->printer_settings['ip_address'].'",
									'.$printer->printer_settings['port'].',
									(connectionResult) => {

										var deviceId = \''.$printer->printer_settings['device_name'].'\';
										var options = {\'crypto\': true, \'buffer\': false};

										if ((connectionResult == \'OK\') || (connectionResult == \'SSL_CONNECT_OK\')){

											//Retrieves the Printer object
											ePosDev.createDevice(
												deviceId,
												ePosDev.DEVICE_TYPE_PRINTER,
												options,
												(deviceObj, errorCode) => {

													// no device
													if (deviceObj === null){
														alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
														//location.href="'.$previousPage.'";
														return;
													}

													if (errorCode == \'OK\'){

														// called on print success/fail
														deviceObj.onreceive = (response) => {

															//if (response.success){
															//	location.href = location.href + "&redirect='.urlencode($previousPage).'";
															//	return;
															//}

															//location.href="'.$previousPage.'";

														};

														'.$finalPrintData.'
														deviceObj.send();
														ePosDev.disconnect();

													}

												}
											);

										} else {

											alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");

										}

									}
								);
						    	;';

					    	}

					    	$renderHtml .= ' } catch (e){ } ';

				    	}

			    	} else {

		        		$renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_printer').'");';

			    	}

		    	});

	    	} else {

	        	$renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_sale').'");';

	    	}

        } else {

	        $renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_sale').'");';

        }

        // where do we go from here?
        $redirectTo = Request::get('redirect') ?? $_SERVER['REQUEST_URI'].'&redirect='.urlencode($previousPage);

        // redirect
        if (Request::get('redirect')){
	        header('Location: '.$redirectTo);
	        exit();
        }

		$renderHtml .= '});

		// after 6 seconds redirect to confirm page
		window.setTimeout(function(){
			location.href="'.$redirectTo.'";
		}, 6000);

		</script>';

        return $renderHtml;

    }

}
