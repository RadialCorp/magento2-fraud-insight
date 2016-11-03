<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Payment\Adapter;

abstract class AbstractType
{
	/**
	 * Initialize the class properties.
	 *
	 * @return self
	 */
	abstract protected function _initialize();
}
