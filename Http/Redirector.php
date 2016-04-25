<?php
/*
 * The MIT License
 *
 * Copyright 2016 František Preissler <github@ntisek.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace SecurityTool\Http;

use SecurityTool\Http\HttpsDetector;
use SecurityTool\Http\HostDetector;
use SecurityTool\Http\Header;
use SecurityTool\Exception;
use Zend\Uri\Uri;

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class Redirector
{
    protected $whitelist = [];
    protected $allowProxy = false;

    public function __construct($allowProxy = false)
    {
        $this->allowProxy = (bool) $allowProxy;
    }

    public function getRedirect($urlString, $stayLocal = true, $preserveHttps = true)
    {
        /**
         * Check that the URL has the correct format expected of a valid HTTP
         * or HTTPS URL. If so, normalize the URL.
         */
        $valid = false;
        $url   = new Uri;

        try
        {
            $url->parse($urlString);

            if ($url->isValid() && $url->isAbsolute())
            {
                $url->normalize();
                $valid = true;
            }

        } catch (\Exception $e)
        {
        }

        if (false === $valid)
        {
            throw new Exception\InvalidArgumentException("Given value was not a valid absolute HTTP(S) URL: " . $url);
        }

        /**
         * Make sure we don't redirect from HTTPS to HTTP unless flagged by
         * the user. Using a Strict-Transport-Security header helps too!
         */
        if (true === (bool) $preserveHttps && HttpsDetector::isHttpsRequest())
        {
            if (!$this->isHttps($url))
            {
                throw new Exception\InvalidArgumentException("Given value was not a HTTPS URL as expected: " . $url);
            }
        }

        /**
         * Check if the URL meets the local host restriction unless disabled
         */
        if (true === $stayLocal && !$this->isLocal($url))
        {
            throw new Exception\InvalidArgumentException("Given value was not a local HTTP(S) URL: " . $url);
        }

        /**
         * Check if the URL host exists on a whitelist of allowed hosts
         */
        $whitelist = $this->getWhitelist();

        if (!empty($whitelist) && !$this->isWhitelisted($url))
        {
            throw new Exception\InvalidArgumentException("Given value was not a whitelisted URL as expected: " . $url);
        }

        /**
         * Get URL string after URL encoding checks and return a Location header
         * object.
         */
        $header = new Header\Location(['url' => $url->toString(), 'status_code' => 302]);

        return $header;
    }

    public function redirect($url, $stayLocal = true, $preserveHttps = true, $replace = false)
    {
        $header = $this->getRedirect($url, $stayLocal, $preserveHttps);
        $header->send($replace);
    }

    public function addWhitelist(array $whitelist)
    {
        foreach ($whitelist as $value)
        {
            $this->addWhitelistedHost($value);
        }
    }

    public function getWhitelist()
    {
        return $this->whitelist;
    }

    public function addWhitelistedHost($host)
    {
        $this->whitelist[] = $host;
    }

    protected function isLocal($url)
    {
        $host    = HostDetector::getLocalHost($this->allowProxy);
        $urlHost = $url->getHost();

        if ($url->getPort())
        {
            $urlHost .= ':' . $url->getPort();
        }

        if ($host !== $urlHost)
        {
            return false;
        }

        return true;
    }

    protected function isHttps($url)
    {
        if ($url->getScheme() !== 'https')
        {
            return false;
        }

        return true;
    }

    protected function isWhitelisted($url)
    {
        $whitelist = $this->getWhitelist();

        if (!empty($whitelist))
        {
            $host = $url->getHost();

            foreach ($whitelist as $allowed)
            {
                if ($host === $allowed)
                {
                    return true;
                }
            }
        }

        return false;
    }
}