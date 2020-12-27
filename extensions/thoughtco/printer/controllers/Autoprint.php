<?php

namespace Thoughtco\Printer\Controllers;

use AdminMenu;
use ApplicationException;
use DB;
use Template;
use Thoughtco\Printer\Models\Printer;
use Thoughtco\Printer\Models\Settings;
use Thoughtco\Printer\Classes\Printerfunctions;
use Request;

/**
 * Autoprint Admin Controller
 */
class Autoprint extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'printer');
        Template::setTitle(lang('thoughtco.printer::default.btn_autoprint'));

    }

    public function index()
    {

	    // get sales not yet printed
        if (($printerId = Request::get('location'))){

	        $settings = $this->printerSettings($printerId);

		    // update sale status
		    if (($saleId = Request::get('updateSale'))){

		    	$sale = \Admin\Models\Orders_model::where('order_id', $saleId)->first();

		    	// valid sale
		    	if ($sale !== NULL){

		       		if ($settings !== false){

				    	// if we set status
				    	if ($settings->setstatus != -1){
					    	$sale->updateOrderStatus($settings->setstatus);
					    }

				    }

			    }

				echo 1;
				exit();

		    }

		    // get sales
		    if (Request::get('getSales')){

		        $return = [];

		        if ($settings !== false){

					$sales = \Admin\Models\Orders_model::where(function($query) use ($settings){
						$query = $query
							->where('location_id', '=', $settings->location_id)
							->where('status_id', '=', $settings->getstatus ?? setting('default_order_status', 1));

						if ($settings->autoprint_sameday){
							$query = $query->where('order_date', '=', date('Y-m-d'));
						}

						return $query;
					})
					->orderBy('order_id', 'asc')
					->limit(10)
					->get();

					foreach ($sales as $sale){

				    	// turn sale data into variables
				    	$variables = Printerfunctions::getSaleData($sale, $settings->categories ?? []);

				    	$js = false;

				    	// if we have something to print
				    	if (count($variables['order_menus']) > 0){

							// render the blade or pagic template
							$output = Printerfunctions::renderTemplate($printerId, (object)$settings, $variables);

							$js = $settings->type == 'ethernet' ? Printerfunctions::orderToEthernetJS($output, $settings) : $output;

				    	}

						// turn our output into epson friendly js or esc/p
				    	$return[] = [
							'js' => $js,
							'id' => $sale->order_id
				    	];

					}

				}

				echo json_encode($return);
				exit();

			}

	    }

        // add the js library
		$this->addJs('extensions/thoughtco/printer/assets/js/encoding-indexes.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/encoding.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/epos-2.14.0.js', 'thoughtco-printer');
		$this->addJs('extensions/thoughtco/printer/assets/js/escprint-1.0.4.js', 'thoughtco-printer');

    }

    public function printerSettings($printerId)
    {

    	$printer = Printer::where('id', $printerId)->first();

    	// valid printer
    	if ($printer !== NULL){

	    	$settings = (object)$printer->printer_settings;
	    	$settings->location_id = $printer->location_id;
	    	$settings->label = $printer->label;

	    	// use default format
	    	if ($printer->printer_settings['usedefault'] == 1){

		    	$settings->output_format = Settings::get('output_format', '');
		    	$settings->lines_before = Settings::get('lines_before', 0);
		    	$settings->lines_after = Settings::get('lines_after', 0);
				$settings->encoding = Settings::get('encoding', 'windows-1252');

		    // use custom format
	    	} else {

		    	$settings->output_format = $printer->printer_settings['format'];
		    	$settings->lines_before = $printer->printer_settings['lines_before'];
		    	$settings->lines_after = $printer->printer_settings['lines_after'];
				$settings->encoding = $printer->printer_settings['encoding'];

	    	}

	    	if ($settings->lines_before == '') $settings->lines_before = 0;
	    	if ($settings->lines_after == '') $settings->lines_after = 0;

			// how many copies to print?
			$copies = intval($printer->printer_settings['copies']);
			if ($copies < 1) $copies = 1;
	    	$settings->copies = $copies;

	    	$settings->autocut = $printer->printer_settings['autocut'];

			return $settings;

	    }

	    return false;

	}

    public function renderAutoprint()
    {

        // location
        if (($printerId = Request::get('location'))){

	    	$settings = $this->printerSettings((int)$printerId);

	    	// valid printer
	    	if ($settings !== false){

		    	// JS doesnt need this
		    	unset($settings->output_format);

				return '<script>PRINTER_SETTINGS='.json_encode($settings).';</script>';

		    }

	    }

	    return false;

    }

}
