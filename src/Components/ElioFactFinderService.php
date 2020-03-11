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
use GuzzleHttp\Exception\GuzzleException;

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
     * @var array
     */
    private $params;


    public function __construct(FactFinderConfigurationInterface $ffConfig)
    {
        $this->ffConfig = $ffConfig;
        $this->params = $this->getRequestDefaulParams();
    }

    public function getRequestDefaulParams():array
    {
        $timestamp = time() . '000'; //milliseconds needed;
        $hashedPW = md5($this->ffConfig->getAuthenticationPrefix()
            .$timestamp
            .md5($this->ffConfig->getPassword())
            .$this->ffConfig->getAuthenticationPostfix()
        );

        return $params = [
            'query' => [
                'format' => 'json',
                'timestamp' => $timestamp,
                'username' => $this->ffConfig->getUserName(),
                'password' => $hashedPW,
                'channel' => $this->ffConfig->getChannel()
            ]
        ];
    }

    public function getUri():string
    {
        return $this->ffConfig->getRequestProtocol()
            .'://'.$this->ffConfig->getServerAddress()
            .'/'.$this->ffConfig->getContext();
    }

    public function getConfig():FactFinderConfigurationInterface
    {
        return $this->ffConfig;
    }

    /**
     * Update request parameter when exist or insert it when it does not exist.
     *
     * @param string $key
     * @param string $value
     */
    public function upsertRequestParam(string $key, string $value):void
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

    public function getSuggestions(string $query):array
    {
        $this->upsertRequestParam('query', $query);

        /*** @var Client $client */
        $client = new Client();
        $request = $client->request('GET', $this->getUri() . '/Suggest.ff', $this->getRequestParams());

        return json_decode($request->getBody()->getContents(),true)['suggestions'];
    }


}
