<?php

/**
 * @author     Fernando Wobeto <fernandowobeto@gmail.com>
 * @copyright  Copyright (c) 2014
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Wobeto\Twitter\Facade;

use Illuminate\Support\Facades\Facade;

class Twitter extends Facade{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(){
		return 'Twitter';
	}

}
