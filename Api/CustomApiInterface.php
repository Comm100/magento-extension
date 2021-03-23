<?php

namespace Comm100\LiveChat\Api;

interface CustomApiInterface
{
    /** POST API for activate.
     * @param mixed $params
     *
     * @return mixed[]
     * */
    public function activate($params);

    /** POST API for post connect.
     * @param mixed $params
     *
     * @return mixed[]
     * */
    public function connect($params);

    /** GET API for getting the list of stores.
     * @return mixed[]
     * */
    public function getWebsiteStores();

    /** POST API for changing the campaign.
     * @param mixed $params
     *
     * @return mixed[]
     * */
    public function setStoresAndCampaigns($params);

    /** GET API for health check.
     * @return mixed[]
     * */
    public function health();

    /** GET API for diconnecting the user.
     * @param mixed $params
     *
     * @return mixed[]
     * */
    public function disconnect();
}
