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

namespace SecurityTool\Csrf;

use SecurityTool\Common,
    SecurityTool\Exception,
    SecurityTool\Common\FixedTimeComparison;

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class Provider extends Common\AbstractOptions
{
    protected $generator = null;

    protected $token = '';

    protected $options = [
        'token_name_prefix' => 'csrf-token',
        'name'              => '',
        'timeout'           => 3600
    ];

    public function __construct(array $options = null)
    {
        parent::__construct($options);

        $this->generator = new Token;
    }

    public function getToken($refresh = false)
    {
        if (!empty($this->token) && false === $refresh)
        {
            return $this->token;
        }

        $this->token = $this->generator->generate();

        $this->storeTokenToSession();

        return $this->token;
    }

    public function getTokenName()
    {
        return implode(':', [$this->getOption('token_name_prefix'), $this->getOption('name')]);
    }

    public function getName()
    {
        return $this->getOption('name');
    }

    public function getTimeout()
    {
        return $this->getOption('timeout');
    }

    public function isValid($token, $tokenName = null)
    {
        if (is_null($tokenName))
        {
            $tokenName = $this->getTokenName();
        }

        try
        {
            $array = $this->retrieveTokenFromSession($tokenName);
        } catch (Exception\RuntimeException $e)
        {
            return false; //TODO: Set lastException for debug recall
        }

        if (empty($array) || !is_array($array) || !isset($array['token']) || !isset($array['expire']))
        {
            return false;
        }

        $time = time();

        if ((int) $array['expire'] >= $time)
        {
            return false;
        }

        $result = FixedTimeComparison::compare($token, $array['token']);

        return $result;
    }

    protected function storeTokenToSession()
    {
        if (!session_id())
        {
            session_start();
        }

        $expire = 0;

        if ($this->getTimeout() > 0)
        {
            $expire = time() + $this->getTimeout();
        }

        $_SESSION[$this->getTokenName()] = [
            'token'  => $this->getToken(),
            'expire' => $expire
        ];
    }

    protected function retrieveTokenFromSession($tokenName)
    {
        if (!session_id())
        {
            throw new Exception\RuntimeException('A PHP Session has not been started so session storage is unavailable');
        }

        if (!isset($_SESSION[$tokenName]))
        {
            throw new Exception\RuntimeException('Session data does not include a token for the current token ' . 'name: ' . $tokenName);
        }

        return $_SESSION[$tokenName];
    }

    public function setOption($key, $value)
    {
        switch ($key)
        {
            case 'timeout': $this->options['timeout'] = (int) $value; break;
            case 'name': $this->options['name'] = (string) $value; break;
            case 'token_name_prefix': $this->options['token_name_prefix'] = (string) $value;  break;
            default: throw new Exception\InvalidArgumentException('Attempted to set invalid option: ' . $key); break;
        }
    }
}