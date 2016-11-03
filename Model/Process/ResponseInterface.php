<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Radial\FraudInsight\Model\Process;

interface ResponseInterface
{
	/**
	 * Process the response payload.
	 *
	 * @return self
	 */
	public function process();
}
