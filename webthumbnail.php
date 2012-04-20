<?php

/**
 * =============================================================================
 * 
 * @file        webthumbnail.php
 * @author      Lukasz Cepowski <lukasz[at]cepowski.pl>
 * 
 * @desc        api.webthumbnail.org
 *              The Webthumbnail.org is a free webapi for capturing website 
 *              screenshots in real browsers. Ready to serve thousands of 
 *              thumbnails, fully scalable, cloud based!
 *              Visit http://webthumbnail.org for more information.
 *              
 * @copyright   Copyright (C) 2012 Ognisco Software
 *              All rights reserved.
 *              www.ognisco.com
 *              
 * @license     Redistribution and use in source and binary forms, with or 
 *              without modification, are permitted provided that the following 
 *              conditions are met:
 *              
 *              - Redistributions of source code must retain the above copyright 
 *                notice, this list of conditions and the following disclaimer.
 *              
 *              - Redistributions in binary form must reproduce the above 
 *                copyright notice, this list of conditions and the following 
 *                disclaimer in the documentation and/or other materials 
 *                provided with the distribution.
 *                
 *              THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND 
 *              CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
 *              INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF 
 *              MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
 *              DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR 
 *              CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
 *              SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
 *              NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 *              LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 *              HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 *              CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR 
 *              OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
 *              EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *              
 * =============================================================================
 */

class Webthumbnail
{
    
    const API_URL = 'http://api.webthumbnail.org/';
    
    const BROWSER_CHROME    = 'chrome';
    const BROWSER_FIREFOX   = 'firefox';
    const BROWSER_OPERA     = 'opera';
    
    const FORMAT_PNG = 'png';
    const FORMAT_JPG = 'jpg';
    const FORMAT_GIF = 'gif';
    
    const MIN_WIDTH = 70;
    const MAX_WIDTH = 2048;
    
    const MIN_HEIGHT = 70;
    const MAX_HEIGHT = 4096;
    
    protected $_url;
    protected $_width = self::MIN_WIDTH;
    protected $_height = self::MIN_HEIGHT;
    protected $_browser = self::BROWSER_CHROME;
    protected $_format = self::FORMAT_PNG;
    
    /**
     * Init webthumbnail.
     * @param string $url
     */
    public function __construct($url)
    {
        $this->_url = $url;
    }
    
    /**
     * Get url.
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * Set width.
     * @param int $width
     * @return Webthumbnail
     */
    public function setWidth($width)
    {
        $this->_width = $this->_minmax((int) $width, self::MIN_WIDTH, self::MAX_WIDTH);
        return $this;
    }
    
    /**
     * Get width.
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }
    
    /**
     * Set height.
     * @param int $height
     * @return Webthumbnail
     */
    public function setHeight($height)
    {
        $this->_height = $this->_minmax((int) $height, self::MIN_HEIGHT, self::MAX_HEIGHT);
        return $this;
    }
    
    /**
     * Get height.
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * Set browser type.
     * @param string $browser
     * @throws WebthumbnailException
     * @return Webthumbnail
     */
    public function setBrowser($browser)
    {
        switch (strtolower($browser)) {
            case self::BROWSER_CHROME:
            case self::BROWSER_FIREFOX:
            case self::BROWSER_OPERA:
                $this->_browser = strtolower($browser);
                break;
            default:
                throw new WebthumbnailException("Unsupported browser type '{$browser}'!");
        }
        return $this;
    }
    
    /**
     * Get browser type.
     * @return string
     */
    public function getBrowser()
    {
        return $this->_browser;
    }
    
    /**
     * Set format type.
     * @param string $format
     * @throws WebthumbnailException
     * @return Webthumbnail
     */
    public function setFormat($format)
    {
        switch (strtolower($format)) {
            case self::FORMAT_PNG:
            case self::FORMAT_JPG:
            case self::FORMAT_GIF:
                $this->_format = strtolower($format);
                break;
            default:
                throw new WebthumbnailException("Unsupported format type '{$format}'!");
        }
        return $this;
    }
    
    /**
     * Get format type.
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }
    
    /**
     * Get capture url.
     * @return string
     */
    public function getCaptureUrl()
    {
        return self::API_URL . 
            '?width='   . $this->_width .
            '&height='  . $this->_height .
            '&format='  . $this->_format .
            '&browser=' . $this->_browser .
            '&url=' . $this->_url;
    }
    
    /**
     * Execute the capture call.
     * @return WebthumbnailHttpCall
     */
    public function callCapture()
    {
        return new WebthumbnailHttpCall($this->getCaptureUrl());
    }
    
    /**
     * Execute the get-status call.
     * @return WebthumbnailHttpCall
     */
    public function callGetStatus()
    {
        return new WebthumbnailHttpCall($this->getCaptureUrl().'&action=get-status');
    }
    
    /**
     * Check if thumbnail is captured.
     * @return boolean
     */
    public function isCaptured()
    {
        $call = $this->callGetStatus();
        return ($call->getResponse() == 'finished' ? true : false);
    }
    
    /**
     * Capture a thumbnail.
     * This method send a capture call and waits untill the thumbnail is ready.
     * @return WebthumbnailHttpCall
     */
    public function capture()
    {
        $this->callCapture();
        while (!$this->isCaptured()) {
            sleep(2);
        }
        return $this->callCapture();
    }
    
    /**
     * Capture a thumbnail and send it to the browser.
     * @see examples/capture_to_browser.php
     * @return WebthumbnailHttpCall
     */
    public function captureToBrowser()
    {
        $call = $this->capture();
        header("Content-Type: ".$call->getContentType());
        header("Content-Length: ".$call->getContentLength());
        echo $call->getResponse();
        return $call;
    }
    
    /**
     * Capture a thumbnail and send it to the file.
     * @see examples/capture_to_file.php
     * @param string $filename
     * @throws WebthumbnailException
     * @return WebthumbnailHttpCall
     */
    public function captureToFile($filename)
    {
        $call = $this->capture();
        if (!@file_put_contents($filename, $call->getResponse(), LOCK_EX)) {
            throw new WebthumbnailException("Cannot write thumbnail to file '{$filename}'!");
        }
        return $call;
    }
    
    protected function _minmax($x, $min, $max)
    {
        if ($x < $min) {
            return $min;
        } else if ($x > $max) {
            return $max;
        }
        return $x;
    }
    
}

class WebthumbnailHttpCall
{
    
    public $_reponse;
    public $_statusCode;
    public $_contentType;
    public $_contentLength;
    
    /**
     * Create and execute a http call.
     * @param string $url
     * @throws WebthumbnailException
     */
    public function __construct($url)
    {
        if (!function_exists('curl_init')) {
            throw new WebthumbnailException("Curl is not supported by your version of PHP!");
        }
        
        $ch = curl_init($url);
        if (!$ch) {
            throw new WebthumbnailException("curl_init failed!");
        }
        
        $referer = '-';
        if (isset($_SERVER) && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
        
        $userAgent = 'Webthumbnail.org Client PHP/'.phpversion();
        if (isset($_SERVER) && isset($_SERVER['SERVER_SOFTWARE'])) {
            $userAgent .= ' ' . $_SERVER['SERVER_SOFTWARE'];
        }
        
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $this->_response = curl_exec($ch);
        if (!$this->_response) {
            throw new WebthumbnailException("curl_exec failed!");
        }
        
        $this->_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->_contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $this->_contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        curl_close($ch);
    }
    
    /**
     * Get response body.
     * @return string
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Get http status code.
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
    
    /**
     * Get content type.
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }
    
    /**
     * Get content length.
     * @return int
     */
    public function getContentLength()
    {
        return $this->_contentLength;
    }
    
}

class WebthumbnailException extends Exception {}

