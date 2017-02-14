<?php
// copy of Mojio.php from vendor/mojio/mojio/src/Mojio/OAuth2/Provider/Mojio.php
namespace Mojio\OAuth2\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\User;

class Mojio extends AbstractProvider
{
    public $scopeSeparator = ' ';

    // kw- changed scope so we can still login
    public $scopes = array(
        'full legacy'
    );
    
    public $base_url = 'https://api.moj.io/oauth2';

    public function urlAuthorize()
    {
        return $this->base_url . '/authorize';
    }

    public function urlAccessToken()
    {
        return $this->base_url . '/token';
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->_id;
    }
    
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        throw new \Exception("Unimplemented");
    }
    
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;
        $user = new User;
        $user->uid = $this->userUid($response, $token);

        return $user;
    }
}
