<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Controllers;

use lfkeitel\phptotp\Base32;
use lfkeitel\phptotp\Totp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Admin\AdminUser;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\PermissionsProvider;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\PgsqlUserProvider;
use Romanzaycev\Fundamenta\Components\Auth\AuthHelper;
use Romanzaycev\Fundamenta\Components\Auth\Context;
use Romanzaycev\Fundamenta\Components\Auth\Transport\HeaderTransport;
use Romanzaycev\Fundamenta\Components\Http\GenericApiAnswer;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;
use Romanzaycev\Fundamenta\Components\Rbac\RbacManager;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Exceptions\Domain\AccessDeniedException;
use Slim\Exception\HttpUnauthorizedException;

readonly class Auth
{
    public function __construct(
        private PgsqlUserProvider $pgsqlUserProvider,
        private Configuration     $configuration,
        private RbacManager       $rbacManager,
    ) {}

    /**
     * @throws \Throwable
     */
    public function make(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (AuthHelper::isAuthorized($request)) {
            return HttpHelper::respond(GenericApiAnswer::success(), $response);
        }

        $params = $request->getParsedBody();

        $username = trim($params["login"] ?? "");
        $password = trim($params["password"] ?? "");
        $answer2fa = trim($params["answer2fa"] ?? "");

        if (empty($username) || empty($password)) {
            throw new HttpUnauthorizedException($request, "Empty login or password");
        }

        $user = $this
            ->pgsqlUserProvider
            ->getByLogin($username);

        if (!$user || !$user->isActive()) {
            throw new HttpUnauthorizedException($request, "Incorrect username or password");
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            throw new HttpUnauthorizedException($request, "Incorrect username or password");
        }

        if (!$this->rbacManager->hasPermission($user, PermissionsProvider::ADMIN_LOGIN)) {
            throw new HttpUnauthorizedException($request, "You cannot perform this action");
        }

        if ($this->configuration->get("admin.security.auth.totp_required", false)) {
            if (empty($answer2fa)) {
                return HttpHelper::respond(
                    GenericApiAnswer::success([
                        "is_2fa_needed" => true,
                    ]),
                    response: $response,
                );
            }

            $userTotpSecret = $user->getTotpSecret();

            if (!empty($userTotpSecret)) {
                $secret = Base32::decode($userTotpSecret);
                $key = (new Totp())->GenerateToken($secret);

                if ($answer2fa !== $key) {
                    throw new HttpUnauthorizedException($request, "Incorrect 2FA answer");
                }
            }
        }

        $user->setLastLogin(new \DateTimeImmutable());

        if (password_needs_rehash($user->getPasswordHash(), PASSWORD_DEFAULT)) {
            $user->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        }

        $user->setLastUa($request->getHeaderLine("User-Agent"));
        $user->setLastIp($request->getServerParams()['REMOTE_ADDR'] ?? null);

        $this
            ->pgsqlUserProvider
            ->update(
                $user,
            );

        $authContext = AuthHelper::getContext($request);

        return $this->createAuthAndRespond($authContext, $user, $response);
    }

    /**
     * @throws \Throwable
     */
    public function refresh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = AuthHelper::getToken($request);

        if (!$token) {
            throw new AccessDeniedException();
        }

        $user = $this->pgsqlUserProvider->getUser($token);

        if (!$user) {
            throw new AccessDeniedException();
        }

        if (!$user->isActive()) {
            throw new AccessDeniedException();
        }

        if ($request->getHeaderLine("User-Agent") !== $user->getLastUa()) {
            throw new AccessDeniedException();
        }

        $user->setLastLogin(new \DateTimeImmutable());
        $this->pgsqlUserProvider->update($user);

        $authContext = AuthHelper::getContext($request);
        $authContext->getStorage()->delete($token);

        return $this->createAuthAndRespond($authContext, $user, $response);
    }

    /**
     * @throws \Throwable
     */
    private function createAuthAndRespond(Context $authContext, AdminUser $user, ResponseInterface $response): ResponseInterface
    {
        $token = $authContext
            ->getStorage()
            ->create(
                [
                    "is_adm" => true,
                    "adm_uuid" => $user->getId(),
                ],
                (new \DateTimeImmutable())
                    ->add(
                        new \DateInterval(
                            $this->configuration->get("admin.security.auth.ttl", "PT24H")
                        )
                    ),
            );
        $authContext->start(
            $token,
            HeaderTransport::class,
        );

        return HttpHelper::respond(
            GenericApiAnswer::success([
                "token" => $token->getId(),
                "token_valid_to" => $token
                    ->expiresAt()
                    ->format(DATE_ATOM),
                "is_2fa_needed" => false,
            ]),
            response: $response,
        );
    }
}
