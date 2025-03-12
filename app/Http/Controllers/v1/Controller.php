<?php

namespace App\Http\Controllers\v1;

abstract class Controller
{
	public $defaultPageSize = 10;

    public function getResponseTemplate() {


		$objTemplate = new \stdClass();
		$objTemplate->message = "Operatrion completed...";
		$objTemplate->data = [];
		$objTemplate->code = 200;
		$objTemplate->view_file = "welcome";

		$objTemplate->response_type = 'html';
		if(\request()->header('Accept')== 'application/json' || \request()->header('accept')== 'application/json' ){
			$objTemplate->response_type = 'json';
		}

		return $objTemplate;
	}
}
