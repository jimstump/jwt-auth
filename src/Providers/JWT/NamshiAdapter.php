<?php

namespace Tymon\JWTAuth\Providers\JWT;

use Exception;
use Namshi\JOSE\JWS;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class NamshiAdapter extends JWTProvider implements JWTInterface
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    protected $jws;

    public function __construct($secret, $algo, $driver = null)
    {
        parent::__construct($secret, $algo);

        $this->jws = $driver ?: new JWS(['typ' => 'JWT', 'alg' => $algo]);
    }

    /**
     * Create a JSON Web Token
     *
     * @return string
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->secret);

            return (string) $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new JWTException('Could not create token: ' . $e->getMessage());
        }
    }

    /**
     * Decode a JSON Web Token
     *
     * @param  string  $token
     * @return array
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function decode($token)
    {
        try {
            // let's never allow unsecure tokens
            $jws = JWS::load($token, false);
        } catch (Exception $e) {
            throw new TokenInvalidException('Could not decode token: ' . $e->getMessage());
        }

        if (! $jws->verify($this->secret, $this->algo)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        return (array) $jws->getPayload();
    }
}
