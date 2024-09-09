<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Validation;
use App\Http\Services\api\ApiService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;


class AccessToken
{
    public $Service,$Model;

    public function __construct(){
        $this->Model = new Validation();
        $this->Service = new ApiService();
    }

    public function handle(Request $request, Closure $next)
    {
        // CHECK WHETHER ACCESS TOKEN IS PRESENT IN THE HEADER OR NOT
        $header_token = config('constant.TOKEN_RFQ_API_SERVICE');
        $validate = $this->Model->ValidateHeaderToken($request,$header_token);
        if($validate['error_code'] != null){
            return $this->Service->SendHTTPErroringResponse(401,config('error.1004'));
        }
        return $next($request);
    }
}
