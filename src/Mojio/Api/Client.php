<?php
//copy of Client.php from vendor/mojio/mojio/src/Mojio/Api/Client.php
namespace Mojio\Api;

use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Common\Collection;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Common\Event;

class Client extends \Guzzle\Service\Client
{
	const LIVE = "https://api.moj.io/v1";
	const SANDBOX = "https://api.moj.io/v1";
	
	/**
	 * @var string Mojio App ID
	 */
	protected $appId;
	
	/**
	 * @var string Mojio App secret key
	 */
	protected $secretKey;
	
	/**
	 * @var string Service version
	 */
	protected $version;
	
	/**
	 * @var token Token
	 */
	public $token;
	
	/**
	 * Factory method to create a new Mojio client
	 *
	 * @param array|Collection $config Configuration data. Array keys:
	 *    host - Base URL host.  Default: api.moj.io
	 *    base_url - Base URL of web service.  Default: {{scheme}}://{{host}}/{{version}}
	 *    app_id - Mojio App ID
	 *    secret_key - Mojio App Secret Key
	 *    token - Optional Token ID
	 *
	 * @return S3Client
	 */
	public static function factory($config = array() )
	{
		$defaults = array(
		        'scheme' => 'https',
		        'host' => 'api.moj.io',
				'base_url' => '{scheme}://{host}/{version}',
		        'oauth_base_url' => '{scheme}://accounts.moj.io/oauth2',
				'app_id' => null,
				'secret_key' => null,
				'version' => 'v1'
		);
		$required = array('base_url', 'app_id', 'secret_key','version','oauth_base_url');
		$config = Collection::fromConfig($config, $defaults, $required);
		$client = new self($config->get('base_url'), $config);
		
		// Attach a service description to the client
		$description = ServiceDescription::factory(__DIR__ . '/service.json');
		$client->setDescription($description);
		
		$client->getEventDispatcher()->addListener('request.before_send', function( Event $event ) {
			$request = $event['request'];
			$token = $request->getClient()->getTokenId();
			
			if($token) {
			    $request->setHeader('MojioApiToken',$token);
			}
		});
		
		return $client;
	}
	
	private function getOAuthProvider($redirect_uri) {
	     return new \Mojio\OAuth2\Provider\Mojio (array(
                'clientId'  =>  $this->getConfig('app_id'),
                'clientSecret'  =>  $this->getConfig('secret_key'),
                'base_url' => $this->expandTemplate($this->getConfig('oauth_base_url')),
                'redirectUri'   =>  $redirect_uri,
                'scopes' => 'full legacy'
        ));
	}
	
	public function getAuthorizationUrl($redirect_uri) {
	    $provider = $this->getOAuthProvider($redirect_uri);
	    //dd($provider->getAuthorizationUrl());
	    //return 'https://accounts.moj.io/oauth2/authorize?response_type=code&scope=full%20legacy&client_id=7a3d645b-4e88-4d85-a29f-e150568c8f45&redirect_uri='.$redirect_uri;
	    return $provider->getAuthorizationUrl();
	}
	
	public function authorize($redirect_uri, $code) {
	    $provider = $this->getOAuthProvider($redirect_uri);
	    
	    $tokenId = $provider->getAccessToken(new \League\OAuth2\Client\Grant\AuthorizationCode(), array(
	       'code' => $code 
	    ));

	    if($tokenId) {
	        try {
	            $this->_hasInitialized = true;
	            $token = $this->getToken(array('id' => $tokenId) );

				$this->token = $token;
				// added this line -kw
				$this->AccessToken = $tokenId;

			}catch( GuzzleException $e ){
				// added by kw to debug
				var_dump( $e->getMessage() );
				var_dump( $e->getResponse()->getMessage() );
				var_dump($this);
				dd($e);
			}
	    }
	}
	
	// added here to refresh token - kw
	public function refreshToken($redirect_uri,$refreshToken) {
	    $provider = $this->getOAuthProvider($redirect_uri);
	    
	    $tokenId = $provider->getAccessToken(new \League\OAuth2\Client\Grant\RefreshToken(), array(
	       'refresh_token' => $refreshToken 
	    ));

	    // added by kw to debug
	    //var_dump($tokenId);

	    if($tokenId) {
	        try {
	            $this->_hasInitialized = true;
	            $token = $this->getToken(array('id' => $tokenId) );
				$this->token = $token;
				// added this line -kw
				$this->AccessToken = $tokenId;

			}catch( GuzzleException $e ){
				// added by kw to debug
				var_dump( $e->getMessage() );
				var_dump( $e->getResponse()->getMessage() );
				var_dump($this);
				dd($e);
			}
	    }

	}

	private function initializeToken () {
	    if($this->token) {
	        return;
	    }
	    
	    $tokenId = $this->getConfig('token');
	    if( $tokenId ) {
	        try {
	            $token = $this->getToken(array('id' => $tokenId) );
	            	
	            $this->token = $token;
	            return;
	        }catch(GuzzleException $e ){
	            // Token
	        }
	    }
	     
        // Attempt to initalize client using app id and secret
	    try {
	        $token = $this->begin( array(
	            'appId' => $this->getConfig('app_id') ,
	            'secretKey' => $this->getConfig('secret_key')
	        ));
	         
	        $this->token = $token;
	        return;
	    }catch( GuzzleException $e ){
	        throw $e;
	    }
	}
	
	private $_hasInitialized = false;
	public function getTokenId()
	{
	    if(!$this->_hasInitialized) {
	        $this->_hasInitialized = true;
	        $this->initializeToken ();
	    }
		return $this->token ? $this->token->getId() : null;
	}
	
	public function isAuthenticated()
	{
		return $this->getTokenId() && $this->token->UserId;
	}
	
	public function currentUser()
	{
		return $this->isAuthenticated() ? $this->getUser( array('id' => $this->token->UserId ) ) :  null;
	}
}