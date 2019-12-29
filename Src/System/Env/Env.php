<?php

namespace App\System\Env;

class Env
{
	static public function isProd()
	{
        return true;
	}

	static public function isDev()
	{
        return true;
	}

	static public function isTest()
	{
        return false;
	}
}