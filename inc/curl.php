<?php

/**
 * A basic CURL wrapper
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
**/
class Curl {
    
    /**
     * The file to read and write cookies to for requests
     *
     * @var string
    **/
    public $cookie_file;
    
    /**
     * Determines whether or not requests should follow redirects
     *
     * @var boolean
    **/
    public $follow_redirects = true;
    
    /**
     * An associative array of headers to send along with requests
     *
     * @var array
    **/
    public $headers = array();
    
    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @var array
    **/
    public $options = array();
    
    /**
     * The referer header to send along with requests
     *
     * @var string
    **/
    public $referer;
    
    /**
     * The user agent to send along with requests
     *
     * @var string
    **/
    public $user_agent;
    
    public $filename=false;
    public $code=0;
    public $ar_info=array();
    /**
     * Stores an error string for the last request if one occurred
     *
     * @var string
     * @access protected
    **/
    protected $error = '';
    
    /**
     * Stores resource handle for the current CURL request
     *
     * @var resource
     * @access protected
    **/
    protected $request;
    
    
    /**
     * Initializes a Curl object
     *
     * Sets the $cookie_file to "curl_cookie.txt" in the current directory
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
    **/
    function __construct() {
        $this->cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
        $this->user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0';
    }
    
    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse object
    **/
    function delete($url, $vars = array()) {
        return $this->request('DELETE', $url, $vars);
    }
    
    /**
     * Returns the error string of the current request if one occurred
     *
     * @return string
    **/
    function error() {
        return $this->error;
    }
    
    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse
    **/
    function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url);
    }
    
    
    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse
    **/
    function head($url, $vars = array()) {
        return $this->request('HEAD', $url, $vars);
    }
    
    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function post($url, $vars = array()) {
        return $this->request('POST', $url, $vars);
    }
    
    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $url
     * @param array|string $vars 
     * @return CurlResponse|boolean
    **/
    function put($url, $vars = array()) {
        return $this->request('PUT', $url, $vars);
    }
    
    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @param string $method
     * @param string $url
     * @param array|string $vars
     * @return CurlResponse|boolean
    **/
    function request($method, $url, $vars = array()) {
        $this->error = '';
        $this->request = curl_init();
        //if (is_array($vars)) $vars = http_build_query($vars, '', '&');
        
        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers();
        
        if ($this->filename!=false) 
          {
            $fh=fopen($this->filename,'wb');
            if ($fh==false) {echo 'Error open file '.$this->filename."<br>\r\n";$this->filename=false;}
            else 
            {
              curl_setopt ($this->request, CURLOPT_FILE, $fh);
            }
          }
        $response = curl_exec($this->request);
        if ($this->filename!=false) 
          {
            if ($response==false) {echo "Error get file <br>\r\n";} 
            else {echo "Save file ".$this->filename."<br>\r\n";}
          }
        else
        {
          if ($response) 
            {
              $response = new CurlResponse($response);
            } else {
            $this->error = curl_errno($this->request).' - '.curl_error($this->request);
            }
        }
        $this->code=curl_getinfo($this->request,CURLINFO_HTTP_CODE);
        $this->ar_info=curl_getinfo($this->request);
        curl_close($this->request);
        
        if ($this->filename!=false)
        {
          fclose($fh);
        }
        
        return $response;
    }
    
    /**
     * Formats and adds custom headers to the current request
     *
     * @return void
     * @access protected
    **/
    protected function set_request_headers() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }
    
    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     * @access protected
    **/
    protected function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
    
    /**
     * Sets the CURLOPT options for the current request
     *
     * @param string $url
     * @param string $vars
     * @return void
     * @access protected
    **/
    protected function set_request_options($url, $vars) {
        curl_setopt($this->request, CURLOPT_URL, $url);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        
        # Set some default CURL options
        if ($this->filename==false) curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        if ($this->follow_redirects) curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        if ($this->referer) curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        
        
        # Set any custom CURL options
        foreach ($this->options as $option => $value) {
            if (strtoupper($option)=='PROXY') curl_setopt($this->request, CURLOPT_PROXYTYPE,7);
            curl_setopt($this->request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }

}
