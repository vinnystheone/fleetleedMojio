<?php

namespace Mojio\Api\Command;

use Mojio\Api\Model\Entity;

class SaveEntity extends GetEntity
{
	protected $jsonContentType = 'application/json';
	
	/**
	 * {@inheritdoc}
	 */
	protected function validate()
	{
		$this->validateEntity();
			
		parent::validate();
	}
	
	protected function build()
	{
		parent::build();
		
		$entity = $this->get('entity');
		if( $entity )
		{
			if( $entity instanceof Entity)
				$entity = $entity->toArray();
			
			$request = $this->getRequest();
			$request->setBody(json_encode($entity));
			
			if ($this->jsonContentType && !$request->hasHeader('Content-Type')) {
                $request->setHeader('Content-Type', $this->jsonContentType);
            }
		}
	}
}