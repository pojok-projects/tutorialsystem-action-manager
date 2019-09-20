<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

use FFMpeg;

class UploadController extends Controller
{

    private $client;
    private $ffprobe;
    private $endpoint_upload;

    function __construct()
    {
        $this->client = new Client();
        $this->ffprobe = FFMpeg\FFProbe::create();
        $this->endpoint_upload = env('ENDPOINT_UPLOAD');
    }

    public function uploadVideo(Request $request)
    {
        //Rule request
        $rules = [
            'video' => 'mimes:mp4'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $uuid_video = (string) Str::uuid();
        $path_local = $request->file('video')->path();
        $file_name  = $uuid_video.'.'.$request->file('video')->getClientOriginalExtension();
        $format     = $request->file('video')->getClientOriginalExtension();
        $size       = $request->file('video')->getSize();

        $result = $this->client->request('POST', $this->endpoint_upload.'upload', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => file_get_contents($path_local),
                    'filename' => $file_name
                ]
            ]
        ]);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                    'succeeded' => false
                ]
            ], $result->getStatusCode());
        }

        $meta_video = $this->ffprobe->streams( $path_local );
        $video_dimensions = $meta_video->first()->getDimensions();
        $resolution = $video_dimensions->getWidth().'x'.$video_dimensions->getHeight();
        $duration = $this->ffprobe->format( $path_local )->get('duration');

        $results_s3 = json_decode($result->getBody(), true);
        $parse_location = parse_url($results_s3['response']['Location']);

        return response()->json([
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'Success upload',
                'succeeded' => true
            ],
            'result' => [
                'file_name'  => $file_name,
                'duration'   => (int) $duration,
                'file_path'  => $parse_location['path'],
                'size'       => (int) $size,
                'file_name'  => $file_name,
                'format'     => $format,
                'resolution' => $resolution              
            ]
        ], $result->getStatusCode());
    }
}
