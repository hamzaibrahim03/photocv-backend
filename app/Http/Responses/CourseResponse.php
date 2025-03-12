<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\GenericJsonResponse;

class CourseResponse extends GenericJsonResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  string  $message
     * @param  mixed   $data
     * @param  int     $statusCode
     * @return JsonResponse
     */
    
	
	public static function success2($template)
    {
		if($template->response_type == 'json'){
			return response()->json([
				'success' => true,
				'message' => $template->message,
				'data' => $template->data
			], $template->code);
		}
		
		else{
			return view($template->view_file, $template->data );	
		}
    
	}
	

    
}

