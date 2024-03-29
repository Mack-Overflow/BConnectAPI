<?php

namespace App\Services;

use App\Models\SendToType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SendTextRequest;
use App\Models\Url;
use App\Models\Subscriber;
use App\Models\TextMessage;
use App\Models\SentMessage;
use App\Services\GenerateReviewUrlService;
use Twilio\Rest\Client;

/**
 * This service is designed to be called to send a single message
 */
class SmsService
{
    // public Client $client;
    protected $genReview;
    protected $client;

    public function __construct(GenerateReviewUrlService $gr)
    {
        // $this->client = $client;
    }

    /**
     * Contains params for message contents, one or more sendToTypes
     * @param $campaignId
     * @param $reviewerNo
     * for reviews, it would be generated by SendReviewText job handler,
     * for campaigns, it would be generated by 
     * 
     * @return JsonResponse
     */
    public static function send(int $campaignId, string $reviewerNo=null)
    {
        Log::info("sending");
        $shortUrlLink = "";

        $campaign = TextMessage::find($campaignId);
        $sendToType = $campaign->sendToType;
        $businessId = $campaign->businessId;
        $url = $campaign->url;
        $header = $campaign->header;
        $body = $campaign->body;

        // Get the link url depending on the environment
        if (getenv('APP_ENV') === 'local') {
            $reviewerNo = "+14352224432";
            // Replace with Ngrok
            $shortUrlLink = getenv("NGROK_URL");
        } else if (getenv('APP_ENV') === 'staging') {
            $shortUrlLink = "https://api.bconnect-staging.com/link/";

            // TEMPORARY WHILE ASSIGNING NEW TWILIO URL
            return response()->json(['message' => 'Message Send Endpoint Hit'], 200);
        } else if (getenv('APP_ENV') === 'production') {
            $shortUrlLink = "https://api.bconnect.com/link/";
        }

        try {
            // Get Twilio keys
            $account_id = getenv('TWILIO_SID');
            $auth_tok = getenv('TWILIO_TOKEN');
            
            // Replace below with Business Twilio number
            $twilio_num = getenv('TWILIO_FROM');

            $client = new Client($account_id, $auth_tok);

            // Handle retrieving #'s from sendToType
            
            

            if ($sendToType === 'Review Invite' && $reviewerNo !== null) {
                Log::info("send block");
                $sendToSubscriber = Subscriber::where(['phoneNumber' => $reviewerNo, 'businessId' => $businessId, 'subscribed' => 1])->firstOrFail();

                $shortUrl = !Url::where('fullUrl', $url)->exists() ? self::handleNewUrl($url, $reviewerNo, $businessId) : Url::where('fullUrl', $url)->first()->shortUrl;

                $client->messages->create($reviewerNo, [
                    'from' => $twilio_num,
                    'body' => "$header \n\n $body \n $shortUrl" 
                ]);
                $sendToSubscriber->lastMsgSentType = 'Review Invite';

                $sent = SentMessage::firstOrCreate([
                    'textMessageId' => $campaignId,
                    'businessId' => $businessId,
                    'sendToType' => $sendToType,
                ]);
                
                $sent->timesSent++;
                $sendToSubscriber->save();
                $sent->save();

                return response()->json(['message' => 'Review invite sent!']);
            } else if($sendToType === 'Review Invite' && $reviewerNo === null) {
                return response()->json(['error' => 'No recipient phone number provided for reviewer'], 400);
            }
            // else { // handles all other sendToTypes
                // self::getRecipients($sendToTypes, $businessId);
            // }

            // (getenv('APP_ENV') === 'local') ? $recipientNos = ["+14352224432"] : self::getRecipients($sendToTypes);
            $recipientNos = (getenv('APP_ENV') === 'local') ? ["+14352224432"] : null;
            if ($recipientNos === null) return;
            Log::info($recipientNos);
            
            foreach($recipientNos as $recipientNo)
            {
                // If a short Url already exists, retrieve it using the fullUrl
                // Or else, create a new Url model
                if ($url) {
                    $shortUrl = !Url::where('fullUrl', $url)->exists() ? self::handleNewUrl($url, $recipientNo, $businessId) : Url::where(['fullUrl' => $url, 'businessId' => $businessId])->first()->shortUrl;
                } else {
                    $shortUrl = '';
                    $shortUrlLink = '';
                }

                Log::info("Recipient No: ".$recipientNo);
                $client->messages->create($recipientNo, [
                    'from' => $twilio_num,
                    'body' => "$header \n\n $body \n $shortUrlLink$shortUrl"
                ]);

                $sent = SentMessage::firstOrCreate([
                    'textMessageId' => $campaignId,
                    'businessId' => $businessId,
                    'sendToType' => $sendToType,
                ]);
                
                $sent->timesSent++;
                $sent->save();

                // Increments sentMessageCount of recipient (if successful)
                $updateSubscriber = Subscriber::find($recipientNo);
                $updateSubscriber->sentMessage();
                $updateSubscriber->lastMsgSentType = $sendToType;
            }
            

            return response()->json(['message' => 'Successfully sent message']);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param $sendToType, a key for SendToType table/model
     * 
     * @return array of recipient phone no.s to be iterated in send method
     */
    public static function getRecipients(mixed $sendToType, int $businessId) : array
    {
        $recipients = [];
        if ($sendToType === 'Uncontacted')
        { // Previously uncontacted, uploaded subscribers
            $recipients = Subscriber::where([
                    'businessId' => $businessId,
                    'lastMsgSentType' => $sendToType
                ])
                ->get()
                ->toArray();
        }
        else if ($sendToType === 'Opt-In Invite')
        {
            $recipients = Subscriber::where([
                'businessId' => $businessId,
                'subscribed' => 0
            ])
            ->get()
            ->toArray();
        }
        else if ($sendToType === 'Have Redeemed')

        return $recipients;
    }

    /**
     * 
     */
    public function sendMultiple()
    {

    }



    // public static function create() : Client
    // {
    //     $twilSid = env('TWILIO_SENDER_NO');
    //     $twilToken = env('TWILIO_AUTH_TOKEN');

    //     $client = new Client($twilSid, $twilToken);
    //     return $client;
    // }

    // public static function send(string $recipientNo, mixed $meta) 
    // {
    //     // $recipientNo = env('TWILIO_PHONE_NO');
    //     try 
    //     {
    //         $client->messages->create(
    //             $senderNo,
    //             $meta
    //         );
    //     }
    //     catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage(), 400]);
    //     }
        
    // }

    public function get()
    {
        //
    }

    public function read()
    {
        //
    }

    /**
     * Creates a new Url to be used in message
     * @param string $fullUrl
     * @param string $customerPhoneNo
     * 
     * @return String $shortUrl
     */
    private static function handleNewUrl(string $fullUrl, string $customerPhoneNo, int $businessId) : String
    {
        $shortUrl = GenerateReviewUrlService::generate();
        // $shortUrl = $this->genReview->generate(); // Generate new Short URL
        $subscriber = json_decode(Subscriber::where(['phoneNumber' => $customerPhoneNo, 'businessId' => $businessId])->get())[0];
        // Log::info($subscriber);
        $subscriberId = $subscriber->id;
        Log::info($subscriberId);

        Url::create([
            'businessId' => $businessId,
            'subscriberId' => $subscriber->id,
            'fullUrl' => $fullUrl,
            'shortUrl' => $shortUrl,
        ]);

        return $shortUrl;
    }

    public function sendSingleText(SendTextRequest $request)
    {
        $account_id = getenv('TWILIO_SID');
        $auth_tok = getenv('TWILIO_TOKEN');
        $twilio_num = getenv('TWILIO_FROM'); // Replace with business twilio number

        if (getenv('APP_ENV') === 'local') {
            // $reviewerNo = "+14352224432";
            // Replace with Ngrok
            $shortUrlLink = getenv("NGROK_URL");
        } else if (getenv('APP_ENV') === 'staging') {
            $shortUrlLink = "https://api.bconnect-staging.com/link/";

            // TEMPORARY WHILE ASSIGNING NEW TWILIO URL
            return response()->json(['message' => 'Single text-message send endpoint hit'], 200);
        } else if (getenv('APP_ENV') === 'production') {
            $shortUrlLink = "https://api.bconnect.com/link/";
        }

        try {
            $header = $request->msgHeader;
            $body = $request->body;
            $url = $request->url;
            $businessId = \Auth::user()->businessId;
            $promoCode = $request->promoCode;
            $recipientNo = $request->recipientNo;

            // Handle URL 

            // if ($url) {
            //     $shortUrl = !Url::where('fullUrl', $url)->exists() ? self::handleNewUrl($url, $recipientNo, $businessId) : Url::where(['fullUrl' => $url, 'businessId' => $businessId])->first()->shortUrl;
            // } else {
            //     $shortUrl = '';
            //     $shortUrlLink = '';
            // }

            $client = new Client($account_id, $auth_tok);
            
            // Send message

            // $client->messages->create($textInfo['recipientNo'], [
            //     'from' => $twilio_num,
            //     'body' => "$header \n\n $body \n $shortUrlLink$shortUrl"
            // ]));

            return response()->json(['message' => 'Successfully created message (test endpoint)'], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
    }
}