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

namespace SecurityTool\Common;

use SecurityTool\Exception;

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class Sanitizer extends AbstractOptions
{
    protected $purifier = null;
    protected $config   = null;
    protected $filter   = '';

    public function __construct($cachePath, array $options = null)
    {
        if ((!isset($cachePath) || !is_dir($cachePath) || !is_writable($cachePath)) && false !== $cachePath)
        {
            throw new Exception\RuntimeException(
                'The HTMLPurifier HTML Sanitiser requires a cache location to ' . 'improve performance. Please set a cache path or set the ' . 'first parameter to the constructor of this class to false. ' . 'Ensure the given location, if set, is writable by PHP'
            );
        }

        $this->config = \HTMLPurifier_Config::createDefault();

        if (false === $cachePath)
        {
            $this->getConfig()->set('Cache.DefinitionImpl', null);
        }
        else
        {
            $this->getConfig()->set('Cache.SerializerPath', rtrim($cachePath, '\\/ '));
        }

        parent::__construct($options);
    }

    public function sanitize($html, $filter = null)
    {
        return $this->getHtmlPurifier()->purify($html, $filter);
    }

    public function reset()
    {
        $this->purifier = null;
    }

    public function setFilterDefinition($filter)
    {
        $this->filter = $filter;
        $this->setOption('HTML.Allowed', $this->filter);
    }

    public function getFilterDefinition()
    {
        return $this->filter;
    }

    public function setOption($key, $value)
    {
        $this->reset();
        $this->getConfig()->set($key, $value);
    }

    public function getOption($key)
    {
        return $this->getConfig()->get($key);
    }

    public function getOptions()
    {
        throw new Exception\RuntimeException(
            'Unfortunately, there\'s no way to retrieve all options from ' . 'HTMLPurifier_Config\'s property list object'
        );
    }

    public function setConfig(\HTMLPurifier_Config $config)
    {
        $this->reset();
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setHtmlPurifier(\HtmlPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function getHtmlPurifier()
    {
        if (!isset($this->purifier))
        {
            $this->setHtmlPurifier(new \HTMLPurifier($this->getConfig()));
        }

        return $this->purifier;
    }
}