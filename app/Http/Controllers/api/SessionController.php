<?php
namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SessionController extends Controller
{
    public function heartbeatasd(Request $request)
    {
        return response()->json(['status' => 'success']);
    }
}
