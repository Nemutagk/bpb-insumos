<?php
namespace Nemutagk\BpBInsumos\Middleware;

use Log;
use Closure;
use AccountService;
use AuthService;
use Nemutagk\BpBInsumos\Service\HttpException;

class AuthMiddleware
{
	public function handle($request, Closure $next, $app, $permission) {
		$header = $request->header();

		$token = isset($header['authorization']) ? str_replace('Bearer ', '', $header['authorization'][0]) : null;

		if (empty($token)) {
			Log::error('Request no tiene token de autorización');

			return response()->json(['message'=>'Acceso no autorizado'], 401);
		}

		try {
			$response = AccountService::request('post','auth/validate', [
				'token' => $token
				,'aplicacion' => $app
				,'permiso' => $permission
			]);

			if (!$response['data']['success']) {
				Log::error('Error al autenticar: ', $response['data']);
				return response()->json(['message'=>'Acceso no autorizado','message'=>($response['data']['message'] ?? null),'error'=>($response['data']['error'] ?? null)], 401);
			}

			if ($response['data']['data']) {
				AuthService::setAuth($response['data']['data']);
				$request->attributes->add(['auth'=>$response['data']['data']]);
			}

			return $next($request);
		}catch(HttpException $e) {
			exception_error($e);
			return response()->json(['message'=>'Acceso no autorizado'], 401);
		}catch(Exception $e) {
			exception_error($e);
			return response()->json(['message'=>'Acceso no autorizado','error'=>$e->getMessage()], 500);
		}
	}
}