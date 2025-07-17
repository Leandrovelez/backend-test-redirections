<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class RedirectController extends Controller
{
    public function index(Redirect $redirect, Request $request){
        $originalQuery = [];
        $originalQueryString = parse_url($redirect->url, PHP_URL_QUERY);

        if ($originalQueryString) {
            parse_str($originalQueryString, $originalQuery);
        }

        $requestQuery = array_filter($request->query(), function($value) {
            return !is_null($value);
        });
        
        $mergedQuery = array_merge($originalQuery, $requestQuery);
        $baseUrl = strtok($redirect->url, '?');
        $finalUrl = $baseUrl;

        if (!empty($mergedQuery)) {
            $finalUrl .= '?' . http_build_query($mergedQuery);
        }
        
        RedirectLog::create([
            'redirect_id' => $redirect->id,
            'query_params' => json_encode($requestQuery),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'header_referer' => $request->header('referer'),
            'user_agent' => $request->header('user-agent')
        ]);

        return redirect()->away($finalUrl);
    }
}
