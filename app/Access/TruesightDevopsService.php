<?php

namespace BookStack\Access;

use BookStack\Entities\Tools\DevopsUser;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class TruesightDevopsService
{
    /**
     * Devops URL
     *
     * @var string
     */
    protected $devops_host;

    /**
     * Devops domain name
     *
     * @var string
     */
    protected $devops_domain;

    /**
     * Host domain
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * TruesightDevopsService constructor.
     * @param mixed $devops_host
     * @param mixed $devops_domain
     */
    public function __construct()
    {
        $this->devops_host = env('DEVOPS_HOST', '');
        $this->devops_domain = env('DEVOPS_DOMAIN', '');
        $this->httpClient = new Client();
    }

    public function getInfo(string $username, string $password): ?DevopsUser
    {
        if ($this->devops_host == '' || $this->devops_domain == '') {
            return null;
        }
        try {
            $response = $this->httpClient->get($this->devops_host . '/_api/_common/GetUserProfile?__v=5', [
                RequestOptions::AUTH => [
                    $username,
                    $password,
                    'ntlm',
                    'domain' => $this->devops_domain
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Access the desired field in the JSON response
                $devopsUser = new DevopsUser($data['identity']['AccountName'], $data['identity']['DisplayName']);
                return $devopsUser;
            }
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }
}
