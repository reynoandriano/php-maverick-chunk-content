<?php

use Google\CloudFunctions\FunctionsFramework;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

// Register an HTTP function with the Functions Framework
FunctionsFramework::http('maverickChunkContent', 'maverickChunkContentHandler');

// Define your HTTP handler
function maverickChunkContentHandler(ServerRequestInterface $request): ResponseInterface
{
    if ($request->getMethod() == "POST") {
        $data = $request->getParsedBody();

        $result = [
            'clean' => strip_tags($data['content']),
            'source' => $data['content']
        ];

        return new Response(
            200,
            ['content-type' => 'application/json'],
            json_encode(["data" => $result])
        );

        
    }

    // Return an HTTP response
    return new Response(
        400,
        ['content-type' => 'application/json'],
        json_encode(["message" => "Bad Request"])
    );
}
