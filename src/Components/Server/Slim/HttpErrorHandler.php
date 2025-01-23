<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\Slim;

use Psr\Http\Message\ResponseInterface;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;
use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;
use Romanzaycev\Fundamenta\Exceptions\Domain\InvalidParamsException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;

class HttpErrorHandler extends ErrorHandler
{
    private ?string $fullPath = null;

    protected function respond(): ResponseInterface
    {
        $statusCode = 500;
        $message = "Application error";
        $exception = $this->tryConvertDomainException($this->exception);

        $this->logger->error(
            "[HttpErrorHandler] Unhandled error: " . $exception->getMessage(),
            [
                "exception" => $exception,
                "code" => $exception->getCode(),
                "uri" => ($exception instanceof HttpException)
                    ? (string)$exception->getRequest()->getUri()
                    : null,
            ],
        );

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        }

        if (!$exception instanceof HttpException && $this->displayErrorDetails) {
            $message = $exception->getMessage();
        }

        if (!$exception instanceof HttpException || $this->displayErrorDetails) {
            $this->logger->error(
                "[HttpErrorHandler] Unhandled error: " . $exception->getMessage(),
                [
                    "exception" => $exception,
                ],
            );
        }

        $answer = [
            "is_success" => false,
            "message" => $message,
        ];

        if ($this->displayErrorDetails) {
            $answer["trace"] = $exception->getTraceAsString();
        }

        $contentType = $this->determineContentType($this->request);

        if ($contentType === null || $contentType === "text/html") {
            $statusCode = (int)$statusCode;
            $body = $this->createHtmlError($answer, $statusCode);
            $contentType = "text/html";
        } else {
            $body = \json_encode((object)$answer, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $response = $this->responseFactory->createResponse((int)$statusCode);
        $response->getBody()->write($body);

        return $response->withHeader("Content-Type", $contentType);
    }

    private function tryConvertDomainException(\Throwable $exception): \Throwable
    {
        if ($exception instanceof EntityNotFoundException) {
            return new HttpNotFoundException(
                $this->request,
                $exception->getMessage(),
                $exception,
            );
        }

        if ($exception instanceof InvalidParamsException) {
            return new HttpBadRequestException(
                $this->request,
                $exception->getMessage(),
                $exception,
            );
        }

        return $exception;
    }

    /**
     * @param array{message: string, trace: ?string} $answer
     */
    private function createHtmlError(array $answer, int $statusCode): string
    {
        ob_start();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error <?=$statusCode?>: <?=HttpHelper::getReasonPhrase($statusCode)?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        }
    </style>
</head>
<body>
    <h1>Error <?=$statusCode?>: <?=HttpHelper::getReasonPhrase($statusCode)?></h1>

    <?php if ($answer["message"]): ?>
        <p><?=htmlspecialchars($answer["message"])?></p>
    <?php endif; ?>

    <?php if (isset($answer["trace"])): ?>
        <h3>Trace:</h3>

        <code style="font-size: 80%; line-height: 80%">
            <?php foreach (explode("\n", $answer["trace"]) as $line): ?>
                <p><?=htmlspecialchars($this->hidePath($line))?></p>
            <?php endforeach; ?>
        </code>
    <?php endif; ?>
</body>
</html><?php

        return ob_get_clean();
    }

    private function hidePath(string $traceLine): string
    {
        $basePath = $this->getFullPath($traceLine);

        return str_replace($basePath, "", $traceLine);
    }

    private function getFullPath(string $traceLine): string
    {
        if ($this->fullPath !== null) {
            return $this->fullPath;
        }

        $tmp = explode(" ", $traceLine);

        if (count($tmp) > 1) {
            $l = $tmp[1];
            $endPos = strpos($l, DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR);

            if ($endPos === false) {
                $endPos = strpos($l, DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR);
            }

            if ($endPos !== false) {
                $this->fullPath = substr($l, 0, $endPos);

                return $this->fullPath;
            }
        }

        return '';
    }
}
