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

use SecurityTool\Http\Header;
use SecurityTool\Common;
use SecurityTool\Exception;

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class Headers implements \Countable
{
    protected $options = [];
    protected $headers = [];

    public function __construct(array $options = null)
    {
        if (!is_null($options))
        {
            $this->setOptions($options);
        }
    }

    public function send($replace = false)
    {
        ksort($this->headers, \SORT_STRING);

        foreach ($this->headers as $value)
        {
            $value->send($replace);
        }
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function toArray()
    {
        $headers = [];

        foreach ($this->headers as $value)
        {
            $headers = $value->getHeader();
        }

        asort($headers, \SORT_STRING);

        return $headers;
    }

    public function toString()
    {
        $string  = '';
        $headers = $this->toArray();

        foreach ($headers as $header)
        {
            $string .= sprintf('%s\r\n', $header);
        }

        return $string;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function count()
    {
        return count($this->headers);
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }

    public function setOption($key, $options)
    {
        switch ($key)
        {
            case 'strict_transport_security':
            case 'sts':
                try
                {
                    if (!isset($this->headers['strict_transport_security']))
                    {
                        $this->headers['strict_transport_security'] = new Header\StrictTransportSecurity($options);
                    }
                    else
                    {
                        foreach ($options as $key => $value)
                        {
                            $this->headers['strict_transport_security']->setOption($key, $value);
                        }
                    }
                } catch (\Exception $e)
                {
                    throw $e;
                }
                break;

            case 'csrf_token':
            case 'csrf':
                try
                {
                    if (!isset($this->headers['csrf_token']))
                    {
                        $this->headers['csrf_token'] = new Header\CsrfToken($options);
                    }
                    else
                    {
                        foreach ($options as $key => $value)
                        {
                            $this->headers['csrf_token']->setOption($key, $value);
                        }
                    }
                } catch (\Exception $e)
                {
                    throw $e;
                }
                break;

            default:
                throw new Exception\InvalidArgumentException('Header type not recognised in options: ' . $key);
                break;
        }
    }

    public function getOption($key)
    {
        switch ($key)
        {
            case 'strict_transport_security':
            case 'sts':
                return $this->headers['strict_transport_security']->getOptions();
                break;

            case 'csrf_token':
            case 'csrf':
                return $this->headers['csrf_token']->getOptions();
                break;

            default:
                return null;
                break;
        }
    }

    public function getOptions()
    {
        $return = [];

        ksort($this->headers, \SORT_STRING);

        foreach ($this->headers as $key => $value)
        {
            $return[$key] = $value->getOptions();
        }

        return $return;
    }

    public function addHeader(Header\HeaderInterface $header)
    {
        $class                = get_class($header);
        $parts                = explode('\\', $class);
        $name                 = strtolower(array_shift($parts));
        $this->headers[$name] = $header;

        return $this;
    }
}