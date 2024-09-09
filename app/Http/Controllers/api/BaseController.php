<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Services\api\ApiService;
use App\Models\Validation;
use App\Http\Processor\api\HttpProcessor;
use App\Http\Request\api\GenerateApiRequestResponseBody;
use App\Http\Services\api\AwsService;

class BaseController extends Controller
{
    public $ApiService,$ValidationModel,$HttpProcessor,$GenerateApiRequestResponseBody,$AwsService;
    public function __construct(){
        $this->ApiService = new ApiService();
        $this->ValidationModel = new Validation();
        $this->HttpProcessor = new HttpProcessor();
        $this->GenerateApiRequestResponseBody = new GenerateApiRequestResponseBody();
        $this->AwsService = new AwsService();
    }
}
