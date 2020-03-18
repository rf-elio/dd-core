<?php declare(strict_types=1);

/**
 * Copyright (c) 2020, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\FactFinder\Components;

use Elio\FactFinder\Service\FactFinderConfigurationInterface;
use GuzzleHttp\Client;

/**
 *
 * Class ElioFactFinderService
 *
 * @category  Service Component
 * @package   Shopware\Plugins\FactFinder\Components
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class ElioFactFinderService
{
    /**
     * @var FactFinderConfigurationInterface
     */
    private $ffConfig;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $params = [
        'query'=>[]
    ];

    public function __construct(FactFinderConfigurationInterface $ffConfig)
    {
        $this->ffConfig = $ffConfig;
        $this->client = new Client();
        $this->params = $this->getRequestDefaulParams();
    }

    public function getRequestDefaulParams():array
    {
        $this->upsertRequestParam('format', 'json');
        $this->upsertRequestParam('username', $this->ffConfig->getUserName());
        $this->upsertRequestParam('password', $this->getHashedPassword());
        $this->upsertRequestParam('channel', $this->ffConfig->getChannel());
        $this->upsertRequestParam('version', $this->ffConfig->getVersion());

        return $this->params;
    }

    private function getHashedPassword():string
    {
        $hashedPW = '';
        switch($this->ffConfig->getAuthenticationType())
        {
            case FactFinderConfigurationInterface::ADVANCED_AUTHENTICATION:
                $timestamp = time() . '000'; //milliseconds needed;
                $this->upsertRequestParam('timestamp', $timestamp);
                $hashedPW = md5($this->ffConfig->getAuthenticationPrefix()
                    .$timestamp
                    .md5($this->ffConfig->getPassword())
                    .$this->ffConfig->getAuthenticationPostfix()
                );
                break;
            case FactFinderConfigurationInterface::SIMPLE_AUTHENTICATION:
                $hashedPW = md5($this->ffConfig->getPassword());
                break;
        }
        return $hashedPW;
    }

    public function getUri():string
    {
        return $this->ffConfig->getRequestProtocol() . '://'
            .$this->ffConfig->getServerAddress() . ':'
            .$this->ffConfig->getServerPort() . '/'
            .$this->ffConfig->getContext();
    }

    public function getConfig():FactFinderConfigurationInterface
    {
        return $this->ffConfig;
    }

    /**
     * Update request parameter when exist or insert it when it does not exist.
     *
     * @param string $key
     * @param $value
     */
    public function upsertRequestParam(string $key, $value):void
    {
        foreach ($this->params as $param)
        {
            $param[$key] = $value;
        }
        $this->params['query'] = $param;
    }

    public function getRequestParams():array
    {
        return $this->params;
    }

    public  function resetResquestParams():void
    {
        $this->params = [
            'query'=>[]
        ];
    }

    public function getSuggestions(string $query):array
    {
        $this->upsertRequestParam('query', $query);

        $request = $this->client->request('GET', $this->getUri() . '/Suggest.ff', $this->getRequestParams());

        return json_decode($request->getBody()->getContents(),true)['suggestions'];
    }

    public function search(string $query):array
    {
        $this->upsertRequestParam('query', $query);

        $request = $this->client->request('GET', $this->getUri() . '/Search.ff', $this->getRequestParams());

        return json_decode($request->getBody()->getContents(),true)['searchResult'];
    }


}
