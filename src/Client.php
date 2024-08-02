<?php

/**
 * Class Client (PHP YTDL Library)
 *
 * A simple PHP Class for making HTTP requests using cURL
 * 
 * This library relies on Cobalt’s free API.
 * 
 * Sources:
 * - Cobalt Code: https://github.com/imputnet/cobalt
 * - Cobalt Site: https://cobalt.tools
 * - Cobalt API Docs: https://github.com/imputnet/cobalt/blob/current/docs/api.md
 * 
 * @package pira\ytdl
 * @version 1.7.0
 * @license MIT License
 * @link https://github.com/code3-dev/ytdl-php
 * @api https://github.com/imputnet/cobalt
 */

class Client
{
    private $ch;
    private $options = [];

    /**
     * Constructor - initializes a new cURL handle and sets default options
     */
    public function __construct()
    {
        $this->ch = curl_init();
        $this->setDefaults();
    }

    /**
     * Headers - sets the HTTP headers for the request
     *
     * @param array $headers - an array of headers to set
     * @return Client - returns the Client object for chaining
     */
    public function Headers($headers)
    {
        $this->options[CURLOPT_HTTPHEADER] = $headers;
        return $this;
    }

    /**
     * Option - sets a cURL option for the request
     *
     * @param int $option - the option to set
     * @param mixed $value - the value to set the option to
     * @return Client - returns the Client object for chaining
     */
    public function Option($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
        return $this;
    }

    /**
     * Timeout - sets the request timeout in seconds
     *
     * @param int $timeout - the timeout in seconds
     * @return Client - returns the Client object for chaining
     */
    public function Timeout($timeout)
    {
        $this->Option(CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    /**
     * Url - sets the URL for the request
     *
     * @param string $url - the URL to set
     * @return Client - returns the Client object for chaining
     */
    public function Url($url)
    {
        $this->Option(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Method - sets the HTTP method for the request
     *
     * @param string $method - the HTTP method to set (e.g. "GET", "POST", etc.)
     * @return Client - returns the Client object for chaining
     */
    public function Method($method)
    {
        $this->Option(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        return $this;
    }

    /**
     * Body - sets the request body for the request
     *
     * @param mixed $body - the request body to set
     * @return Client - returns the Client object for chaining
     */
    public function Body($body)
    {
        $this->Option(CURLOPT_POSTFIELDS, $body);
        return $this;
    }

    /**
     * Send - sends the HTTP request and returns the response
     *
     * @return string - the response from the server
     * @throws Exception - if cURL encounters an error while executing the request
     */
    public function Send()
    {
        curl_setopt_array($this->ch, $this->options);
        $response = curl_exec($this->ch);
        if ($response === false) {
            throw new Exception(curl_error($this->ch), curl_errno($this->ch));
        }
        return $response;
    }

    /**
     * getHeaders - returns the response headers
     *
     * @return array - the response headers
     */
    public function getHeaders()
    {
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_NOBODY, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers) {
            $trimmedHeader = trim($header);
            if (!empty($trimmedHeader)) {
                $headers[] = $trimmedHeader;
            }
            return strlen($header);
        });
        curl_exec($this->ch);
        return $headers ?? [];
    }

    /**
     * getStatus - returns the HTTP status code of the response
     *
     * @return int - the HTTP status code
     */
    public function getStatus()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * getBody - returns the response body
     *
     * @return string - the response body
     */
    public function getBody()
    {
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($this->ch);
        if ($response === false) {
            throw new Exception(curl_error($this->ch), curl_errno($this->ch));
        }
        return $response;
    }

    /**
     * Encoding - sets the encoding(s) for the request
     *
     * @param string|array $encodings - the encoding(s) to set
     * @return Client - returns the Client object for chaining
     */
    public function Encoding($encodings)
    {
        if (is_array($encodings)) {
            $encodings = implode(',', $encodings);
        }
        $this->Option(CURLOPT_ENCODING, $encodings);
        return $this;
    }

    /**
     * MaxRedirects - sets the maximum number of redirects to follow
     *
     * @param int $maxRedirects - the maximum number of redirects
     * @return Client - returns the Client object for chaining
     */
    public function MaxRedirects($maxRedirects)
    {
        $this->Option(CURLOPT_MAXREDIRS, $maxRedirects);
        return $this;
    }

    /**
     * VerifyPeer - sets whether to verify the peer's SSL certificate
     *
     * @param bool $verify - whether to verify the peer's SSL certificate
     * @return Client - returns the Client object for chaining
     */
    public function VerifyPeer($verify)
    {
        $this->Option(CURLOPT_SSL_VERIFYPEER, $verify);
        return $this;
    }

    /**
     * Proxy - sets the proxy for the request
     *
     * @param string $proxy - the proxy to set
     * @return Client - returns the Client object for chaining
     */
    public function Proxy($proxy)
    {
        $this->Option(CURLOPT_PROXY, $proxy);
        return $this;
    }


    /**
     * setDefaults - sets some default cURL options for the request
     *
     * @return void
     */
    private function setDefaults()
    {
        $defaults = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
        curl_setopt_array($this->ch, $defaults);
    }

    /**
     * Destructor - closes the cURL handle when the object is destroyed
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }
}
