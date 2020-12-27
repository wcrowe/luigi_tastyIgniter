<?php namespace Thoughtco\Printer;

use AdminAuth;
use Admin\Facades\AdminLocation;
use Admin\Widgets\Form;
use App;
use Assets;
use Event;
use System\Classes\BaseExtension;
use Thoughtco\Printer\Models\Settings;
use Thoughtco\Printer\Models\Printer;

/**
 * Printer Extension Information File
 */
class Extension extends BaseExtension
{
    public function boot()
    {
	    // write default settings to database if missing
	    if (Settings::get('output_format') === NULL){

			Settings::set([
				'output_format' => '||# {{ $location_name }}
### {{ $location_address }}
### Phone: {{ $location_telephone }}
-----
## Order for {{ $order_type }}
## Order number: {{ $order_id }}
## {{ $order_date }} {{ $order_time }}
## {{ $order_payment }}
-----
<|{{ $customer_name }}
{{ $telephone }}
{{ $order_address }}
-----
@foreach ($order_menus as $menu)
###{!! str_pad(substr($menu[\'menu_quantity\'].\'x \'.$menu[\'menu_name\'], 0, $charsPerRow - 11), $charsPerRow - 11, \' \', STR_PAD_RIGHT) !!}    {{ str_pad($menu[\'menu_price\'], 7, \' \', STR_PAD_LEFT) }}
@if ($menu[\'menu_options\'])@foreach ($menu[\'menu_options\'] as $option){!! str_pad(substr($option[\'menu_option_linequantity\'].\'x \'.$option[\'menu_option_name\'], 0, $charsPerRow - 11), $charsPerRow - 11, \' \', STR_PAD_RIGHT) !!}    {{ str_pad($option[\'menu_option_linetotal\'], 7, \' \', STR_PAD_LEFT) }}
@endforeach
@endif

@endforeach

-----
@foreach ($order_totals as $total)
### {{ str_pad(substr($total[\'order_total_title\'], 0, $charsPerRow - 11), $charsPerRow - 11, \' \', STR_PAD_RIGHT) }}    {{ str_pad($total[\'order_total_value\'], 7, \' \', STR_PAD_LEFT) }}
@endforeach
-----
### {{ $site_url }}',
				'lines_before' => 0,
				'lines_after' => 0
			]);

	    }

        // add autoprint everywhere
        $this->addAutoprintEverywhere();

		// add print button to form fields
        $this->extendActionFormFields();

		// add print button to list fields
		$this->extendListColumns();

        // extend option values model
		\Admin\Models\Menu_option_values_model::extend(function ($model){
			$model->fillable(array_merge($model->getFillable(), ["print_docket"]));
		});

    }

    public function registerPermissions()
    {
        return [
            'Thoughtco.Printer.Manage' => [
                'description' => 'Create, modify and delete printers',
                'group' => 'module',
            ],
            'Thoughtco.Printer.View' => [
                'description' => 'Print and autoprint dockets',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'tools' => [
                'child' => [
                    'printer' => [
                        'priority' => 10,
                        'class' => 'printer',
                        'href' => admin_url('thoughtco/printer/printers'),
                        'title' => lang('thoughtco.printer::default.text_title'),
                        'permission' => 'Thoughtco.Printer.*',
                    ],
                ],
            ],
        ];
    }

	public function registerSettings()
	{
	    return [
	        'settings' => [
	            'label' => 'Printer Settings',
                'icon' => 'fa fa-print',
	            'description' => 'Manage printer settings.',
                'model' => 'Thoughtco\Printer\Models\Settings',
	            'permissions' => ['Thoughtco.Printer.Manage'],
	        ],
	    ];
	}

    protected function addAutoprintEverywhere()
    {
	    // enable autoprint everywhere in the admin panel
        Event::listen('admin.controller.beforeResponse', function ($controller, $params){

			// only show if logged in
			if (!AdminAuth::isLogged()) return;

	        // not on the autoprint page
	        if (!($controller instanceof \Thoughtco\Printer\Controllers\Autoprint || $controller instanceof \Thoughtco\Printer\Controllers\Printdocket)){

		        // build list of printers the user is allowed to access by location
		        $printerList = [];
		        Printer::where(['is_enabled' => true])
				->each(function($printer) use (&$printerList){
			        if (isset($printer->printer_settings['autoprint_everywhere']) && $printer->printer_settings['autoprint_everywhere']){
						if (AdminLocation::getId() === NULL || AdminLocation::getId() == $printer->location_id){
				        	$printerList[] = $printer->id;
				        }
			        }
		        });

		        if (count($printerList)){

			        // make printer list available
			        Assets::putJsVars(['thoughtco_printer' => $printerList, 'thoughtco_printer_base' => admin_url('thoughtco/printer/autoprint?location=')]);

			        // add autoprint everywhere js
			        $controller->addJs('extensions/thoughtco/printer/assets/js/autoprint-everywhere-1.0.1.js', 'thoughtco-printer');
			        $controller->addCss('extensions/thoughtco/printer/assets/css/autoprint-everywhere.css', 'thoughtco-printer');

		        }

	        }

        });
    }

    protected function extendActionFormFields()
    {

	    // this flag is necessary to stop menu_options_model firing multiple times
	    $isExtended = false;

        Event::listen('admin.form.extendFieldsBefore', function (Form $form) use (&$isExtended) {

	        if ($isExtended) return;

	        // if its an menu form
            if ($form->model instanceof \Admin\Models\Menus_model) {

				$form->tabs['fields']['print_docket'] = [
				 	 'label' => 'Docket text',
			         'type' => 'text',
			         'span' => 'left'
			    ];

			    $isExtended = true;

			}

	        // if its an menu options form
            if ($form->model instanceof \Admin\Models\Menu_options_model) {

				$form->fields['option_values']['form']['fields']['print_docket'] = [
				 	 'label' => 'Docket text',
			         'type' => 'text'
			    ];

			    $isExtended = true;

			}

	        // if its an orders form
            if ($form->model instanceof \Admin\Models\Orders_model) {

	            // add a print docket button beside the order type
	            $form->tabs['fields']['order_type_name']['type'] = 'addon';
				$form->tabs['fields']['order_type_name']['addonRight'] = [
			         'tag' => 'a',
			         'label' => 'Print docket',
			         'attributes' => [
				         'href' => admin_url('thoughtco/printer/printdocket?sale='.$form->model->order_id),
				         'class' => 'btn btn-outline-default',
				         'id' => 'thoughtco_order_print_docket'
			         ]
				];

				// add a print docket button on the items page
				$form->tabs['fields']['order_menus_after'] = [
		            'tab' => 'lang:admin::lang.orders.text_tab_menu',
		            'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/button',
		        ];

            }

        });

    }

	// extend order list to add print button
	protected function extendListColumns(){

		Event::listen('admin.list.extendColumns', function (&$widget) {

			if ($widget->getController() instanceof \Admin\Controllers\Orders){

				$widget->addColumns(['print_me' => [
					'invisible' => false,
					'label' => 'Print',
					'type' => 'text',
					'valueFrom' => 'order_id',
					'defaults' => 1,
					'formatter' => function($something, $column, $value){
						return '<a class="btn btn-primary" href="'.admin_url('thoughtco/printer/printdocket?sale='.$value).'">Print</a>';
					}
				]]);

			}

			if ($widget->getController() instanceof \Thoughtco\Printer\Controllers\Printers){

                if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
                {
                    $widget->removeColumn('edit');
                }

			}

		});

		Event::listen('admin.toolbar.extendButtons', function (&$widget) {

			if ($widget->getController() instanceof \Thoughtco\Printer\Controllers\Printers){

                if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
                {
                    $widget->getController()->widgets['toolbar']->removeButton('create');
                    $widget->getController()->widgets['toolbar']->removeButton('delete');
                }

			}
		});

	}
}
