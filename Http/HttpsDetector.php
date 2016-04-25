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

/**
 * @copyright    Copyright (c) 2016
 * @license      http://en.wikipedia.org/wiki/MIT_License Released under the terms of The MIT License
 * @author       František Preissler <github@ntisek.cz>.
 * @created      25.4.2016 12:00
 * @encoding     UTF-8
 */
class HttpsDetector
{
    public static function isHttpsRequest()
    {
        $https = null;

        if (isset($_SERVER['HTTPS']))
        {
            $https = strtolower($_SERVER['HTTPS']);

            if ($https == 'on' || $https == '1')
            {
                return true;
            }
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
        {
            $https = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);

            if ($https == 'https')
            {
                return true;
            }
        }

        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')
        {
            return true;
        }

        return false;
    }
}