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

namespace SecurityTool\Http\Header;

use SecurityTool\Exception;

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class StrictTransportSecurity extends AbstractHeader implements HeaderInterface
{
    protected $options = [
        'max_age'            => 1209600,
        'include_subdomains' => false
    ];

    public function getHeader()
    {
        $header = 'Strict-Transport-Security: ';
        $header .= 'max-age=' . $this->getOption('max_age');

        if (true === $this->getOption('include_subdomains'))
        {
            $header .= '; includeSubDomains';
        }

        return $header;
    }

    public function send($replace = false)
    {
        if ($this->isHttpsRequest())
        {
            header($this->getHeader(), $replace);
        }
        else
        {
            $location = 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header($location, true, 301);
            exit(0);
        }
    }

    public function setOption($key, $value)
    {
        switch ($key)
        {
            case 'max_age': $this->options['max_age'] = (int) $value; break;
            case 'include_subdomains': $this->options['include_subdomains'] = (bool) $value; break;
            default: throw new Exception\InvalidArgumentException('Attempted to set invalid option: ' . $key); break;
        }
    }

    public function __toString()
    {
        return $this->getHeader();
    }
}