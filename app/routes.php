<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Aws\S3\S3Client;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

require 'config.php';


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*') 
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $html = file_get_contents('../public/file_upload.html');
        $response = $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($html);
        return $response;
    });

    $app->get('/hello', function(Request $request, Response $response){
        $response->getBody()->write('Hello...');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->post('/upload', function (Request $request, Response $response, $args) {

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $accessKeyId = ACCESS_KEY;
        $secretAccessKey = SECRET_KEY;
        $endpoint = ENDPOINT;
        $region = REGION;
        $bucket = BUCKET;

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $stream = fopen($uploadedFile->getFilePath(), 'rb');

            // Configure AWS credentials and S3 client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $region,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);

            // Upload the image to S3
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => 'images/' . $uploadedFile->getClientFilename(),
                'Body' => $stream
            ]);

            fclose($stream);

            $response->getBody()->write('File uploaded successfully');
        } else {
            $response->getBody()->write('Error uploading file');
        }

        return $response;
    });
};
