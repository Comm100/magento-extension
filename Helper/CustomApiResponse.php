<?php

namespace Comm100\LiveChat\Helper;

/**
 * Class for generating the custom api response.
 */
class CustomApiResponse
{
    /**
     * @var bool  $_success
     */
    public $_success;

    /**
     * @var mixed $_data
     */
    public $_data;

    public function __construct()
    {
        $this->_success = false;
        $this->_data = null;
    }

    /**
     * Method to set the api response data.
     * @param bool defines weather the api sent a true:success or false:failure.
     * @param mixed data that needs to be send back from the api.
     */
    public function setApiResponse(bool $success, $data)
    {
        $this->_success = $success;
        $this->_data = $data;
    }

    /**
     * Method to get the api response data.
     * @return mixed[] an array of success and data.
     */
    public function getApiResponse()
    {
        return array('success' => $this->_success, 'data' => $this->_data);
    }
}
