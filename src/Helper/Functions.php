<?php



function getBearerToken($request) {
	return strpos($request->header('Authorization'), 'Bearer') !== false ? substr($request->header('Authorization'), 7) : $request->header('Authorization');
}

function getNameClass($class) {
	$partes = explode('\\', get_class($class));
	return array_pop($partes);
}

function exception_error($e) {
	$class = explode('\\',get_class($e));

	$payload = [
	 	'error'=>$e->getMessage()
	 	,'file'=>$e->getFile()
	 	,'line'=>$e->getLine()
	 	,'code'=>$e->getCode()
	 	,'trace' => $e->getTraceAsString()
	 ];

	 if (method_exists($e, 'getResponse'))
	 	$payload['http_response'] = $e->getResponse();

	 if (method_exists($e, 'getHttpCode'))
	 	$payload['http_code_response'] = $e->getHttpCode();

	 \Log::error('Error en sistema ('.array_pop($class).'): ',$payload);
}

function arrayKeyLowerToUpper($arr) {
	$newArr = [];
	if (range(0, count($arr)-1) !== $arr) {
		foreach($arr as $key => $val) {
			if (!is_array($val))
				$newArr[strtoupper($key)] = $val;
			else
				$newArr[strtoupper($key)] = arrayKeyLowerToUpper($val);
		}
	}else {
		foreach($arr as $item) {
			if (!is_array($item))
				$newArr[] = $item;
			else
				$newArr[] = arrayKeyLowerToUpper($item);
		}
	}

	return $newArr;
}

function arrayKeyLower($arr) {
	$tmpArray = [];
	foreach($arr as $key => $value) {
		if (!is_array($value))
			$tmpArray[lcfirst($key)] = $value;
		else if (array_keys($value) === range(0, count($value)-1)) {
			$tmpValue = [];
			foreach($value as $subArray) {
				$tmpValue[] = $this->toLower($subArray);
			}
			$tmpArray[lcfirst($key)] = $tmpValue;
		}else {
			$tmpArray[lcfirst($key)] = $this->toLower($value);
		}
	}

	return $tmpArray;
}

function arrayReMap($arr, $map) {
	$newArr = [];
	if (range(0, count($arr)-1) !== $arr) {
		foreach($arr as $key => $val) {
			if (!is_array($val)) {
				if (isset($map[$key]))
					$newArr[$map[$key]] = $val;
				else
					$newArr[$key] = $val;
			}else {
				if (isset($map[$key]))
					$newArr[$map[$key]] = arrayReMap($val,$map);
				else
					$newArr[$key] = arrayReMap($val,$map);
			}
		}
	}else {
		foreach($arr as $val) {
			if (!is_array($val))
				$newArr[] = $val;
			else
				$newArr[] = arrayReMap($val, $map);
		}
	}

	return $newArr;
}