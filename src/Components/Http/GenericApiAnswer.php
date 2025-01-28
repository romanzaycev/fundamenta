<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http;

class GenericApiAnswer implements ApiAnswer
{
    public function __construct(
        protected bool $isSuccess,
        protected ?string $message = null,
        protected ?array $data = null,
        protected ?int $statusCode = null,
    ) {}

    public static function success(?array $data = null, ?string $message = "Successful action", int $statusCode = 200): self
    {
        return new self(
            true,
            $message,
            $data,
            $statusCode,
        );
    }

    public static function fail(?string $message = "Failed action", ?array $data = null, int $statusCode = 500): self
    {
        return new self(
            false,
            $message,
            $data,
            $statusCode,
        );
    }

    public function getStatusCode(): int
    {
        if ($this->statusCode !== null) {
            return $this->statusCode;
        }

        if ($this->isSuccess) {
            return 200;
        }

        return 500;
    }

    public function jsonSerialize(): array
    {
        $answer = [
            "is_success" => $this->isSuccess,
        ];

        if ($this->data !== null) {
            $answer["data"] = $this->data;
        }

        if ($this->message !== null) {
            $answer["message"]  = $this->message;
        }

        return $answer;
    }
}
