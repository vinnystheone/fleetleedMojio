<?php
// from vendor/mojio/mojio/src/Mojio/Api/Model/Entity.php
namespace Mojio\Api\Model;

use Mojio\Api\Exception\ResponseException;

abstract class Entity implements \ArrayAccess
{
	public $data = array();
	
	public function __construct( $data )
	{
		$this->data = $data;
		
		if( !isset( $this->data['Id'] ) && isset( $this->data['_id'] ))
			$this->data['Id'] = $this->data['_id'];
	}
	
	public function setData( $data )
	{
		$this->data = $data;
		
		return $this;
	}
	
	public function getTypeUrl()
	{
		return $this::$type . "s";
	}
	
	public function getType()
	{
		return $this::$type;
	}
	
	public static function getClass( $type )
	{
		switch( $type ){
			case 'user':
			case 'users':
				return "\\Mojio\\Api\\Model\\UserEntity";
			case 'app':
			case 'apps':
				return "\\Mojio\\Api\\Model\\AppEntity";
			case 'mojio':
			case 'mojios':
			case 'device':
			case 'devices':
				return "\\Mojio\\Api\\Model\\MojioEntity";
			case 'vehicle':
			case 'vehicles':
				return "\\Mojio\\Api\\Model\\VehicleEntity";
			case 'trip':
			case 'trips':
				return "\\Mojio\\Api\\Model\\TripEntity";
			case 'event':
			case 'events':
				return "\\Mojio\\Api\\Model\\EventEntity";
			case 'order':
			case 'orders':
			case 'invoice':
			case 'invoices':
				return "\\Mojio\\Api\\Model\\InvoiceEntity";
			case 'product':
			case 'products':
				return "\\Mojio\\Api\\Model\\ProductEntity";
			case 'token':
			case 'tokens':
			case 'login':
			case 'logins':
				return "\\Mojio\\Api\\Model\\TokenEntity";
			case 'subscription':
			case 'subscriptions':
				return "\\Mojio\\Api\\Model\\SubscriptionEntity";
			case 'vins':
				return "\\Mojio\\Api\\Model\\VinEntity";
			default:
				return null;
		}
	}
	
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}
	
	public function __get($name)
	{
		return isset($this->data[$name] ) ? $this->data[$name] : null;
	}
	
	public function toArray()
	{
		return $this->data;
	}
	
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    
    public function getId()
    {
    	return $this->_id;
    }
}