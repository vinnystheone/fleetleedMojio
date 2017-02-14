<?php

namespace Mojio\Api\Command;

use Mojio\Api\Model\Entity;
use Mojio\Api\Exception\ResponseException;

class DeleteEntity extends MojioCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function validate()
	{
		$this->validateEntity();
			
		parent::validate();
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function process()
	{
		if ($this->getResponse()->isSuccessful()) {
			$this->result = true;
		}else{
			throw new ResponseException( $this->getResponse() );
		}
	}
}