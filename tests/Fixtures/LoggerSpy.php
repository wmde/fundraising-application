<?php

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\PsrLogTestDoubles\LogCall;

class LoggerSpy extends \WMDE\PsrLogTestDoubles\LoggerSpy {
	public function getFirstLogCall(): LogCall {
		$logCall = $this->getLogCalls()->getFirstCall();
		if ( $logCall === null ) {
			throw new \RuntimeException( "Log call is null" );
		}
		return $logCall;
	}

}
