<?php

class RetrieveGoogleReviewService
{
    /**
     * Fetch all Google Business places associated with businesses in Bconnect
     */
    public static function fetchAll()
    {
        $google_key = getenv('GOOGLE_API_KEY');
        $businesses = Business::whereNotNull('google_place_id')->get();
        foreach($businesses as $biz)
        {
            $pid = $biz->google_place_id;
           //$url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=ChIJwWJZjhLwsgIRWC4gaAJyfSM&key=".$google_key;
        }
    }
}