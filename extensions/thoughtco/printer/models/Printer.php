<?php

namespace Thoughtco\Printer\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use Admin\Models\Statuses_model;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;
use Thoughtco\Printer\Classes\Printerfunctions;

class Printer extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_printer';

    public $timestamps = TRUE;

    public $casts = [
        'location_id' => 'integer',
        'printer_settings' => 'serialize',
    ];

    public $relation = [
        'belongsTo' => [
            'location' => 'Admin\Models\Locations_model',
        ]
    ];

    public $rules = [
        'location_id' => 'sometimes|required|int'
    ];

    // on save
    public function save(?array $options = NULL, $sessionKey = NULL)
    {
	    Printerfunctions::clearTemplates();
	    return parent::save($options, $sessionKey);
    }

    // location options
    public static function getLocationIdOptions()
    {
	    $locations = [];
	    foreach (Locations_model::all() as $location){
	    	$locations[$location->location_id] = $location->location_name;
	    };
	    return $locations;
    }

    // category options
    public static function getCategoryOptions()
    {
	    $categories = [];
	    foreach (Categories_model::all() as $category){
	    	$categories[$category->category_id] = $category->name;
	    };
	    return $categories;
    }

    // status options
    public static function getStatusOptions($allowUnchanged = true)
    {
	    $statuses = $allowUnchanged ? [
		    '-1' => lang('thoughtco.printer::default.leave_unchanged'),
	    ] : [];

	    Statuses_model::listStatuses()->each(function($status) use (&$statuses) {
	    	if ($status->status_for == 'order'){
		    	$statuses[$status->status_id] = $status->status_name;
		    }
	    });

	    return $statuses;
    }

    // category options
    public static function getEncodingOptions()
    {
        return [
            "windows-1250" => "windows-1250",
            "windows-1251" => "windows-1251",
            "windows-1252" => "windows-1252",
            "windows-1253" => "windows-1253",
            "windows-1254" => "windows-1254",
            "windows-1255" => "windows-1255",
            "windows-1256" => "windows-1256",
            "windows-1257" => "windows-1257",
            "windows-1258" => "windows-1258",
            "iso-8859-2" => "iso-8859-2",
            "iso-8859-3" => "iso-8859-3",
            "iso-8859-4" => "iso-8859-4",
            "iso-8859-5" => "iso-8859-5",
            "iso-8859-6" => "iso-8859-6",
            "iso-8859-7" => "iso-8859-7",
            "iso-8859-8" => "iso-8859-8",
            "iso-8859-10" => "iso-8859-10",
            "iso-8859-13" => "iso-8859-13",
            "iso-8859-14" => "iso-8859-14",
            "iso-8859-15" => "iso-8859-15",
            "iso-8859-16" => "iso-8859-16",
        ];

    }

	// get variables
    public function getVariablesAttribute($value)
    {
        return [
            'General' => [
                ['var' => '{{ $site_name }}', 'name' => 'Site name'],
                ['var' => '{{ $site_url }}', 'name' => 'Site URL'],

                ['var' => '{{ $location_name }}', 'name' => 'Location name'],
                ['var' => '{{ $location_telephone }}', 'name' => 'Location telephone'],
                ['var' => '{{ $location_email }}', 'name' => 'Location email'],
                ['var' => '{{ $location_address }}', 'name' => 'Location address'],
            ],
            'Customer' => [
                ['var' => '{{ $first_name }}', 'name' => 'Customer first name'],
                ['var' => '{{ $last_name }}', 'name' => 'Customer last name'],
                ['var' => '{{ $email }}', 'name' => 'Customer email address'],
                ['var' => '{{ $telephone }}', 'name' => 'Customer telephone address'],
            ],
            'Order' => [
                ['var' => '{{ $customer_name }}', 'name' => 'Customer full name'],
                ['var' => '{{ $order_number }}', 'name' => 'Order number'],
                ['var' => '{{ $order_view_url }}', 'name' => 'Order view URL'],
                ['var' => '{{ $order_type }}', 'name' => 'Order type ex. delivery/pick-up'],
                ['var' => '{{ $order_time }}', 'name' => 'Order delivery/pick-up time'],
                ['var' => '{{ $order_date }}', 'name' => 'Order delivery/pick-up date'],
                ['var' => '{{ $order_address }}', 'name' => 'Customer address for delivery order'],
                ['var' => '{{ $order_payment }}', 'name' => 'Order payment method'],
                ['var' => '{{ $order_payment_code }}', 'name' => 'Order payment method code'],
                ['var' => '{{ $order_menus }}', 'name' => 'Order menus (array)'],
                ['var' => '{{ $order_totals }}', 'name' => 'Order total pairs (array)'],
                ['var' => '{{ $order_comment }}', 'name' => 'Order comment'],
            ],
            'Order menus' => [
                ['var' => '{{ $menu_name }}', 'name' => 'Order menu name'],
                ['var' => '{{ $menu_category_name }}', 'name' => 'Order menu category name'],
                ['var' => '{{ $menu_quantity }}', 'name' => 'Order menu quantity'],
                ['var' => '{{ $menu_price }}', 'name' => 'Order menu price'],
                ['var' => '{{ $menu_subtotal }}', 'name' => 'Order menu subtotal'],
                ['var' => '{{ $menu_options }}', 'name' => 'Order menu items (array)'],
                ['var' => '{{ $menu_comment }}', 'name' => 'Order menu comment'],
            ],
            'Order menu options' => [
                ['var' => '{{ $menu_option_name }}', 'name' => 'Order menu option name'],
                ['var' => '{{ $menu_option_quantity }}', 'name' => 'Order menu option quantity'],
                ['var' => '{{ $menu_option_linequantity }}', 'name' => 'Order menu option line quantity'],
                ['var' => '{{ $menu_option_price }}', 'name' => 'Order menu option price'],
                ['var' => '{{ $menu_option_subtotal }}', 'name' => 'Order menu option subtotal'],
                ['var' => '{{ $menu_option_linetotal }}', 'name' => 'Order menu option linetotal'],
            ],
            'Order totals' => [
                ['var' => '{{ $order_total_title }}', 'name' => 'Order total title'],
                ['var' => '{{ $order_total_code }}', 'name' => 'Order total code'],
                ['var' => '{{ $order_total_value }}', 'name' => 'Order total value'],
            ],
            'Status' => [
                ['var' => '{{ $status_name }}', 'name' => 'Status name'],
                ['var' => '{{ $status_comment }}', 'name' => 'Status comment'],
            ],
        ];
    }

}
