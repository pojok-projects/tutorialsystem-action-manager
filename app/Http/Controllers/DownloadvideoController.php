<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Response;

class DownloadvideoController extends Controller
{

    private $client;
    private $endpoint_s3;
    private $endpoint_content;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint_s3 = env('ENDPOINT_S3');
        $this->endpoint_content = env('ENDPOINT_CONTENT_MANAGER');
    }

    public function downloadvideo($id_content_metadata, $filename)
    {
        $url_video = $this->endpoint_s3.$filename.'.mp4';
        $name_video = $filename.'.mp4';
        $tempImage = tempnam(sys_get_temp_dir(), $filename);
        copy($url_video, $tempImage);

        $headers = array('Content-Type: video/mp4');
        
        $result_download = $this->client->request('GET', $this->endpoint_content.'download/'.$id_content_metadata);

        if ($result_download->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result_download->getStatusCode(),
                    'message' => 'Bad Gateway Content',
                ]
            ], $result_download->getStatusCode());
        }

        return response()->download($tempImage, $name_video, $headers);

    }
}
