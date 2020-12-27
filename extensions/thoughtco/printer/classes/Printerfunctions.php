<?php

namespace Thoughtco\Printer\Classes;

use Admin\Models\Menus_model;
use Admin\Models\Menu_item_option_values_model;
use Admin\Models\Menu_option_values_model;
use DB;
use Igniter\Flame\Support\PagicHelper;
use Igniter\Flame\Support\StringParser;
use Thoughtco\Printer\Models\Settings;

class Printerfunctions {

	public static function templateDir(){
		return 'extensions/thoughtco/printer/views/temp/';
	}

	// delete all files
	public static function clearTemplates()
	{
		$files = glob(self::templateDir().'*.php');
		foreach ($files as $file){
			if (is_file($file))
		    	unlink($file);
		}
	}

	public static function removeUnprintableCharacters($string)
	{
		return preg_replace('/[[:^print:]]/', '', $string);
	}

    // get data from sale assigned to variables
    public static function getSaleData($model, $limitCategories = [])
    {
        $data = [];

        $data['site_name'] = setting('site_name');
        $data['site_url'] = str_replace(array('https://','http://'), '', site_url(''));
        $data['order_number'] = $model->order_id;
        $data['order_id'] = $model->order_id;
        $data['first_name'] = self::removeUnprintableCharacters($model->first_name);
        $data['last_name'] = self::removeUnprintableCharacters($model->last_name);
        $data['customer_name'] = self::removeUnprintableCharacters($model->customer_name);
        $data['email'] = self::removeUnprintableCharacters($model->email);
        $data['telephone'] = self::removeUnprintableCharacters($model->telephone);
        $data['order_comment'] = self::removeUnprintableCharacters($model->comment);

        $data['order_type'] = $model->order_type;
        $data['order_time'] = $model->order_time;
        $data['order_date'] = $model->order_date->format('d M y');

        $data['order_payment'] = ($model->payment_method) ? $model->payment_method->name : lang('admin::lang.orders.text_no_payment');
		$data['order_payment_code'] = ($model->payment_method) ? $model->payment_method->code : 'none';

        $data['order_menus'] = [];
        $menus = $model->getOrderMenus();

        // order menus by category order
        foreach ($menus as $idx=>$menu){
	        $menu->category_priority = 100;
	        $menuModel = Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();
	        if ($menuModel && count($menuModel->categories) > 0){

		        // if we have a category limitation and this item is not in it, then remove
		        if (count($limitCategories) > 0 && count(array_intersect($limitCategories, $menuModel->categories->pluck('category_id')->toArray())) == 0){
			        unset($menus[$idx]);

			    // otherwise order by priority
			    } else {
		        	$menu->category_priority = $menuModel->categories[0]->priority;
		        }

	        }
        }

        $menus = $menus->toArray();
        uasort($menus, function($a, $b){
	        return $a->category_priority > $b->category_priority ? 1 : -1;
        });

		$orderMenuOptions = $model->getOrderMenuOptions();
		foreach ($menus as $menu)
		{
			$categoryName = '';

			// get menu model item
			$menuModelItem = Menus_model::with(['categories', 'menu_options', 'menu_options.option_values', 'menu_options.menu_option_values'])
			->where('menu_id', $menu->menu_id)
			->first();

			if ($category = $menuModelItem->categories()->first())
			{
				$categoryName = $category->name;
			}

			$optionData = [];
			if ($orderMenuItemOptions = $orderMenuOptions->get($menu->order_menu_id))
			{
				foreach ($orderMenuItemOptions as $orderMenuItemOption) {
					if ($orderMenuItemOption->quantity > 0){

						$optionText = $orderMenuItemOption->order_option_name;

						// loop over menu options in the menu_model
						foreach ($menuModelItem->menu_options as $modelMenuOption)
						{
							// if menu option is the same as the one in the order
							if ($modelMenuOption->menu_option_id = $orderMenuItemOption->order_menu_option_id)
							{
								// loop over menu_item_option_values
								foreach ($modelMenuOption->menu_option_values as $modelMenuOptionItemValue)
								{
									// if item value id is the same as the value in our order
									if ($modelMenuOptionItemValue->menu_option_value_id == $orderMenuItemOption->menu_option_value_id)
									{
										// loop over the actual values
										foreach ($modelMenuOption->option_values as $modelMenuOptionValue)
										{
											if ($modelMenuOptionItemValue->option_value_id == $modelMenuOptionValue->option_value_id)
											{
												if ($modelMenuOptionValue->print_docket != '')
												{
													$optionText = $modelMenuOptionValue->print_docket;
												}
											}
										}
									}
								}
							}
						}

						$optionData[] = [
							'menu_option_quantity' => $orderMenuItemOption->quantity,
							'menu_option_linequantity' => $menu->quantity * $orderMenuItemOption->quantity,
							'menu_option_name' => $optionText,
							'menu_option_price' => currency_format($orderMenuItemOption->order_option_price),
							'menu_option_subtotal' => currency_format($orderMenuItemOption->quantity * $orderMenuItemOption->order_option_price),
							'menu_option_linetotal' => currency_format($menu->quantity * $orderMenuItemOption->quantity * $orderMenuItemOption->order_option_price),
						];

					}
				}
			}

			$data['order_menus'][] = [
				'menu_name' => (isset($menu->print_docket) && $menu->print_docket != '' ? $menu->print_docket : $menu->name),
				'menu_quantity' => $menu->quantity,
				'menu_price' => currency_format($menu->price),
				'menu_subtotal' => currency_format($menu->subtotal),
				'menu_options' => $optionData,
				'menu_comment' => self::removeUnprintableCharacters($menu->comment),
				'menu_category_name' => $categoryName,
			];
		}

        $data['order_totals'] = [];
        $orderTotals = $model->getOrderTotals();
        foreach ($orderTotals as $total) {
            $data['order_totals'][] = [
				'order_total_title' => htmlspecialchars_decode($total->title),
				'order_total_code' => $total->code,
				'order_total_value' => currency_format($total->value),
				'priority' => $total->priority,
            ];
        }

        $data['order_address'] = lang('admin::lang.orders.text_collection_order_type');

        if ($model->address){
	        $address = $model->address->toArray();
	        $address['format'] = '{address_1}, {address_2}, {city}, {postcode}';
            $data['order_address'] = self::removeUnprintableCharacters(str_replace(', , ', ', ', format_address($address, TRUE)));
        }

        if ($model->location) {
            $data['location_name'] = $model->location->location_name;
            $data['location_email'] = $model->location->location_email;
            $data['location_telephone'] = $model->location->location_telephone;

	        $address = $model->location->getAddress();
	        $address['format'] = '{address_1}, {address_2}, {city}, {postcode}';
            $data['location_address'] = str_replace(', , ', ', ', format_address($address, TRUE));
        }

        $status = $model->status()->first();
        $data['status_name'] = $status ? $status->status_name : null;
        $data['status_comment'] = $status ? $status->status_comment : null;

        return $data;
    }

    // render function
    public static function renderTemplate($printerId, $settings, $variables){

	    // what template do we use?
	    $template = $settings->usedefault ? Settings::get('output_format') : $settings->format;

		// characters per row
		$variables['charsPerRow'] = $settings->characters_per_line ?? 48;

    	// assume blade
    	if (stripos($template, '{{') !== false){

	    	// what template do we use?
	    	$printerTemplate = $settings->usedefault ? 'default' : 'printer'.$printerId;

	    	// create full path
	    	$fullPath = self::templateDir().$printerTemplate.'.blade.php';

	    	// if file doesn't exist then make it
	    	if (!file_exists($fullPath)){
		    	file_put_contents($fullPath, $template);
	    	}

	    	$render = view('thoughtco.printer::temp.'.$printerTemplate, $variables)->render();

    	// not blade
    	} else {

	    	// render our string with variables
			$render = PagicHelper::parse($template, $variables);
			$render = (new StringParser)->parse($template, $variables);

		}

		//var_dump($render); exit();

		return $render;

    }

    // output to epos ethernet format
    public static function orderToEthernetJs($output, $settings){

        $cmd = [];

        $settings = (array)$settings;

        $output = explode("\r\n", $output);

        $foundAlignment = '';

		foreach ($output as $o){

			$o = trim($o);

			// alignments
			if (stripos($o, '|>') === 0 || stripos($o, '<|') === 0 || stripos($o, '||') === 0){

				if (stripos($o, '|>') === 0){
					$foundAlignment = 'right';
				} else if (stripos($o, '||') === 0){
					$foundAlignment = 'center';
				} else {
					$foundAlignment = 'left';
				}

				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_'.strtoupper($foundAlignment).');';
				$o = substr($o, 2);

			}

            $o = str_replace('"', '', $o);

			// h3/4/5/6
			if (stripos($o, '#### ') === 0 || stripos($o, '##### ') === 0 || stripos($o, '###### ') === 0){

				// get string after #
				$o = str_replace(['### ', '#### ', '##### ', '###### '], '', $o);

				// centre align but standard size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
				$cmd[] = 'deviceObj.addTextSize(1, 1);';
				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h3
			} else if (stripos($o, '### ') === 0){

				// get string after #
				$o = str_replace(['### '], '', $o);

				// centre align but +1 size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
				$cmd[] = 'deviceObj.addTextSize(1, 1);';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, true, undefined);';
				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, false, undefined);';
				$cmd[] = 'deviceObj.addTextSize(1, 1);';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h2
			} else if (stripos($o, '## ') === 0){

				// get string after #
				$o = str_replace(['## '], '', $o);

				// centre align but +1 size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
				$cmd[] = 'deviceObj.addTextSize(1, 2);';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, true, undefined);';
				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, false, undefined);';
				$cmd[] = 'deviceObj.addTextSize(1, 1);';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h1
			} else if (stripos($o, '# ') === 0){

				// get string after #
				$o = str_replace('# ', '', $o);

				// centre align but +2 size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
				$cmd[] = 'deviceObj.addTextSize(1, 3);';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, true, undefined);';
				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, false, undefined);';
				$cmd[] = 'deviceObj.addTextSize(1, 1);';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// hr
			} else if (stripos($o, '*****') === 0 || stripos($o, '-----') === 0){

				// centre align but standard size
				$cmd[] = 'deviceObj.addFeedLine(1);';
				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
				$cmd[] = 'deviceObj.addText("-".repeat('.$settings['characters_per_line'].') + "\r\n");';
				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				$cmd[] = 'deviceObj.addFeedLine(1);';

			// cut line
			} else if (stripos($o, '>>>>>') === 0){

				// cut
				$cmd[] = 'deviceObj.addCut(deviceObj.CUT_FEED);';

			// image
			} else if (stripos($o, '[img') === 0){

				$o = str_replace('[img', '', $o);
				$o = str_replace(']', '', $o);
				$o = trim($o);
				$o = explode(',', $o);

				if (count($o) == 2){
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
					$cmd[] = 'deviceObj.addLogo('.trim($o[0]).','.trim($o[1]).');';
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				}

			// qrcode
			} else if (stripos($o, '[qrcode') === 0){

				$o = str_replace('[qrcode', '', $o);
				$o = str_replace(']', '', $o);
				$o = trim($o);
				$o = explode(',', $o);

				if (count($o) == 2){
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
					$cmd[] = 'deviceObj.addSymbol("'.trim($o[1]).'", deviceObj.SYMBOL_QRCODE_MODEL_1, deviceObj.LEVEL_L, '.$o[0].', 1, 1);';
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				}

			// new line
			} else if (trim($o) == ''){

				// centre align but standard size
				$cmd[] = 'deviceObj.addFeedLine(1);';

			// standard text
			} else {

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';

			}

		}

		return $cmd;

    }

}

?>
