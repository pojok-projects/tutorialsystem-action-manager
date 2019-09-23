<?php

$router->group(['prefix'=>'vam/v1'], function() use($router){
    $router->post('/upload', 'UploadController@uploadVideo');
    $router->post('/storemetadata', 'StoremetadataController@storemetadata');
    $router->get('/savevideo/{id_user}/{id_content_metadata}', 'SavevideoController@savevideo');
    $router->get('/downloadvideo/{id_content_metadata}/{filename}', 'DownloadvideoController@downloadvideo');
});