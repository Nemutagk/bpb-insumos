<?php
namespace Nemutagk\BpBInsumos\Middleware;

use Log;
use Closure;
use Nemutagk\BpBInsumos\Logger;
use Nemutagk\BpBInsumos\Models\Access;

class RequestMiddleware
{
	public function handle($request, Closure $next) {
		$headersJson = json_encode($request->header());
		$headers = $request->header();

		Logger::MakeRequestHash();

		if (strpos($headersJson, 'ELB-HealthChecker') === false) {
			$requestAll = $request->all();

			if (isset($requestAll['password']))
				$requestAll['password'] = '*******';

			if (isset($requestAll['password_confirmation']))
				$requestAll['password_confirmation'] = '*******';

			// if (isset($headers['authorization']))
			// 	$headers['authorization'][0] = substr($headers['authorization'][0], 0, 20).'...';

			$accessRequest = [
	            'path' => $request->fullUrl()
	            ,'method' => $request->method()
	            ,'headers' => $headers
	            ,'payload' => $requestAll
	            ,'client' => $request->ip()
	        ];

	        Log::info('RequestAccessInfo: ', $accessRequest);
	        $access = new Access();

	        foreach($accessRequest as $key => $value) {
	        	$access->$key = $value;
	        }

	        $access->save();

	        if (strpos($request->fullUrl(), 'bienparabien') === false && strpos($request->fullUrl(), 'bpb') === false)
				return response()->json(['message'=>'Acceso no autorizado'], 401);
		}

		return $next($request);
	}
}