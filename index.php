<?php

use Google\CloudFunctions\FunctionsFramework;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;
use League\HTMLToMarkdown\HtmlConverter;

// Register an HTTP function with the Functions Framework
FunctionsFramework::http('maverickChunkContent', 'maverickChunkContentHandler');

// Define your HTTP handler
function maverickChunkContentHandler(ServerRequestInterface $request): ResponseInterface
{
    if ($request->getMethod() == "POST") {
        $data = $request->getParsedBody();

        $htmlToMarkdown = new HtmlConverter();
        $markdown = $htmlToMarkdown->convert($data['content']);

        $paragraphs = explode("\n", $markdown);
        $describedParagraph = [];
        foreach ($paragraphs as $paragraph) {
            if(trim($paragraph) != '') {
                $describedParagraph[] = describeParagraph(trim($paragraph));
            }
        }


        $result = [
            'paragraphs' => [
                'count' => count($describedParagraph),
                'item' => $describedParagraph
            ],
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

function describeParagraph($paragraph)
{

    $markdownToHtml = new \cebe\markdown\Markdown();

    $type = 'text';

    if ($paragraph[0] == '#') {
        $type = 'subtitle';
    }

    if ($paragraph[0] == '"') {
        $type = 'quote';
    }

    if ($paragraph[0] == '!') {
        $type = 'image';

        $imgHtml = $markdownToHtml->parse($paragraph);

        $dom = new DOMDocument();
        $dom->loadHTML($imgHtml);
        $img = $dom->getElementsByTagName('img')->item(0);
        
        return [
            'type' => $type,
            'src' => $img->getAttribute('src'),
            'alt' => $img->getAttribute('alt'),
            'html' => $imgHtml,
            'markdown' => $paragraph
        ];
    }

    $describedSentence = [];
    if ($type == 'text') {
        $sentences = explode('.', $paragraph);
        foreach ($sentences as $sentence) {
            if(trim($sentence) != '') {
                $describedSentence[] = describeSentence(trim($sentence) . '.');
            }
        }
    }

    return [
        'type' => $type,
        'length' => strlen($paragraph),
        'html' => $markdownToHtml->parse($paragraph),
        'markdown' => $paragraph,
        'sentences' => [
            'count' => count($describedSentence),
            'item' => $describedSentence
        ]
    ];
}

function describeSentence($sentence)
{
    $markdownToHtml = new \cebe\markdown\Markdown();

    return [
        'type' => 'text',
        'length' => strlen($sentence),
        'html' => $markdownToHtml->parse($sentence),
        'markdown' => $sentence,
    ];
}
