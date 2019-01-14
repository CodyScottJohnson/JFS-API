<?php
$app->get('/General/{path:.*}', function ($request, $response) {
    $path = $request->getAttribute('path');
   // header('Content-Type: image/jpeg'); // Or whatever your content type might be
   // echo readfile('/srv/jfsapp/Media/Images/'.$path);
   // return $path;
    $image = @file_get_contents("/srv/jfsapp/Media/Secure/Images/$path");
    if($image === FALSE) {
        $handler = $this->notFoundHandler;
        return $handler($request, $response);    
    }
    
    $response->write($image);
    return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
});
$app->post('/Upload', function ($request, $response) {
  $data = $request->getParsedBody();
  //var_dump($data);
  $img = $data['picture'];
  $saveas= $data['saveas'];
  $img = str_replace('data:image/png;base64,', '', $img);
  $img = str_replace(' ', '+', $img);
  $fileData = base64_decode($img);
  //saving
  $fileName = $saveas;
  echo $fileName;

  file_put_contents($fileName, $fileData);
});
$app->post('/v2/Upload', function ($request, $response) {
  $data = $request->getParsedBody();
  //var_dump($data);
  $img = $data['picture'];
  $saveas= $data['saveas'];
  //saving
  $fileName = $saveas;
  echo $fileName;
  copy( $img, $fileName);
});
?>