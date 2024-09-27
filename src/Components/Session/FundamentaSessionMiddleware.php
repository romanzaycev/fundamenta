<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Session;

use BadMethodCallException;
use DateInterval;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use DI\Container;
use InvalidArgumentException;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PSR7Sessions\Storageless\Http\ClientFingerprint\SameOriginRequest;
use PSR7Sessions\Storageless\Http\Configuration;
use PSR7Sessions\Storageless\Session\DefaultSessionData;
use PSR7Sessions\Storageless\Session\LazySession;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use stdClass;

use function sprintf;

final class FundamentaSessionMiddleware implements MiddlewareInterface
{
    public const SESSION_CLAIM = 'session-data';
    public const SESSION_ATTRIBUTE = 'session';

    public function __construct(
        private readonly Configuration $config,
        private readonly HookManager $hookManager,
        private readonly Container $container,
    ) {}

    /**
     * {@inheritDoc}
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $sameOriginRequest = new SameOriginRequest($this->config->getClientFingerprintConfiguration(), $request);
        $token = $this->parseToken($request, $sameOriginRequest);
        $sessionContainer = LazySession::fromContainerBuildingCallback(function () use ($token): SessionInterface {
            return $this->extractSessionContainer($token);
        });
        $request = $request->withAttribute($this->config->getSessionAttribute(), $sessionContainer);
        $this->hookManager->call($this->container, HookManager::ON_SESSION_STARTED, $request);

        return $this->appendToken(
            $sessionContainer,
            $handler->handle($request),
            $token,
            $sameOriginRequest,
        );
    }

    /**
     * Extract the token from the given request object
     */
    private function parseToken(Request $request, SameOriginRequest $sameOriginRequest): UnencryptedToken|null
    {
        /** @var array<string, string> $cookies */
        $cookies = $request->getCookieParams();
        $cookieName = $this->config->getCookie()->getName();

        if (!isset($cookies[$cookieName])) {
            return null;
        }

        $cookie = $cookies[$cookieName];
        if ($cookie === '') {
            return null;
        }

        $jwtConfiguration = $this->config->getJwtConfiguration();
        try {
            $token = $jwtConfiguration->parser()->parse($cookie);
        } catch (InvalidArgumentException) {
            return null;
        }

        if (!$token instanceof UnencryptedToken) {
            return null;
        }

        $constraints = [
            new StrictValidAt($this->config->getClock()),
            new SignedWith($jwtConfiguration->signer(), $jwtConfiguration->verificationKey()),
            $sameOriginRequest,
        ];

        if (!$jwtConfiguration->validator()->validate($token, ...$constraints)) {
            return null;
        }

        return $token;
    }

    /** @throws OutOfBoundsException */
    private function extractSessionContainer(UnencryptedToken|null $token): SessionInterface
    {
        if (!$token) {
            return DefaultSessionData::newEmptySession();
        }

        try {
            return DefaultSessionData::fromDecodedTokenData(
                (object)$token->claims()->get(self::SESSION_CLAIM, new stdClass()),
            );
        } catch (BadMethodCallException) {
            return DefaultSessionData::newEmptySession();
        }
    }

    /**
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    private function appendToken(
        SessionInterface  $sessionContainer,
        Response          $response,
        Token|null        $token,
        SameOriginRequest $sameOriginRequest,
    ): Response
    {
        $sessionContainerChanged = $sessionContainer->hasChanged();

        if ($sessionContainerChanged && $sessionContainer->isEmpty()) {
            return FigResponseCookies::set($response, $this->getExpirationCookie());
        }

        if ($sessionContainerChanged || $this->shouldTokenBeRefreshed($token)) {
            return FigResponseCookies::set($response, $this->getTokenCookie($sessionContainer, $sameOriginRequest));
        }

        return $response;
    }

    private function shouldTokenBeRefreshed(Token|null $token): bool
    {
        if ($token === null) {
            return false;
        }

        return $token->hasBeenIssuedBefore(
            $this->config->getClock()
                ->now()
                ->sub(new DateInterval(sprintf('PT%sS', $this->config->getRefreshTime()))),
        );
    }

    /** @throws BadMethodCallException */
    private function getTokenCookie(SessionInterface $sessionContainer, SameOriginRequest $sameOriginRequest): SetCookie
    {
        $now = $this->config->getClock()->now();
        $expiresAt = $now->add(new DateInterval(sprintf('PT%sS', $this->config->getIdleTimeout())));

        $jwtConfiguration = $this->config->getJwtConfiguration();

        $builder = $jwtConfiguration->builder(ChainedFormatter::withUnixTimestampDates())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim(self::SESSION_CLAIM, $sessionContainer);

        $builder = $sameOriginRequest->configure($builder);

        return $this
            ->config->getCookie()
            ->withValue(
                $builder
                    ->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey())
                    ->toString(),
            )
            ->withExpires($expiresAt);
    }

    private function getExpirationCookie(): SetCookie
    {
        return $this
            ->config->getCookie()
            ->withValue(null)
            ->withExpires(
                $this->config->getClock()
                    ->now()
                    ->modify('-30 days'),
            );
    }
}
