<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SavevideoController extends Controller
{

    private $client;
    private $endpoint_user;
    private $endpoint_content;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint_user = env('ENDPOINT_USER_MANAGER');
        $this->endpoint_content = env('ENDPOINT_CONTENT_MANAGER');
    }

    public function savevideo($id_user, $id_content_metadata)
    {
        
        $result_content = $this->client->request('GET', $this->endpoint_content.'save/'.$id_content_metadata);

        if ($result_content->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result_content->getStatusCode(),
                    'message' => 'Bad Gateway Content',
                ]
            ], $result_content->getStatusCode());
        }

        $result_user = $this->client->request('POST', $this->endpoint_user.'user/savedvideo/store/'.$id_user, [
            'form_params' => [
                'video_id' => $id_content_metadata
            ]
        ]);

        if ($result_user->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result_user->getStatusCode(),
                    'message' => 'Bad Gateway User',
                ]
            ], $result_user->getStatusCode());
        }

        return response()->json(array(
            'status' => [
                'code' => 200,
                'message' => 'data has been saved',
            ],
            'result' => [
                'id_user' => $id_user,
                'id_content_metadata' => $id_content_metadata
            ]
        ), 200); 
    }
}
