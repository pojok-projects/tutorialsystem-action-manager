<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class StoremetadataController extends Controller
{

    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    public function storemetadata(Request $request)
    {
        $rules = [
            'user_id' => 'required|uuid',
            'video_title' => 'required|max:256',
            'video_description' => 'required|max:2000',
            'video_genre' => 'required|max:256',
            'category_id' => 'required|uuid',
            'privacy' => 'required|in:public,private',
 

            'file_name' => 'required',
            'duration' => 'required|integer',
            'file_path' => 'required',
            'size' => 'required|integer',
            'format' => 'required',
            'resolution' => 'required'
            
        ];
        $message = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $message);

       
        $metavideos = array(
            [
                'file_name'         => $request->file_name,
                'duration'          => (int) $request->duration,
                'file_path'         => $request->file_path,
                'size'              => (int) $request->size,
                'format'            => $request->format,
                'resolution'        => $request->resolution,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]
        );

        $result = $this->client->request('POST', $this->endpoint . 'content/metadata/store', [
            'json' => [
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'video_title' => $request->video_title,
                'video_description' => $request->video_description,
                'video_genre' => $request->video_genre,
                'privacy' => $request->privacy,
                'metavideos' => $metavideos
            ]
        ]);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        return response()->json(json_decode($result->getBody(), true));
        
    }
}
