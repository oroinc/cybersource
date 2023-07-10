<?php

namespace Oro\Bundle\CyberSourceBundle\EventListener\HttpKernel;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CyberSourceBundle\Integration\CyberSourceChannelType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionUtils;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 *
 * Listener updates cookies with "SameSite=None".
 * On order review step, before redirecting to CyberSource website, the response session cookie
 * should have SameSite=None, in order to not lose session on return from CyberSource back to application.
 * Required for \Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings::HOSTED_CHECKOUT method.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
 */
class AllowCrossOriginCookiesListener
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $registry
    ) {
    }

    /**
     * Method should be executed after \Symfony\Component\HttpKernel\EventListener\SessionListener::onKernelResponse
     * where session cookie is created. But mentioned method also closes session.
     * In order to be able to access session data current method additionally starts and saves session, which is
     * fine, according to note in
     * \Symfony\Component\HttpKernel\EventListener\AbstractSessionListener::onKernelResponse :
     * "Listeners after closing the session can still work with the session as usual because
     * Symfonys session implementation starts the session on demand. So writing to it after
     * it is saved will just restart it."
     *
     * @param ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $session = $this->requestStack->getSession();
        $session->start();
        if (!$session->isStarted() || !$this->isApplicable($event)) {
            $session->save();
            return;
        }

        $response = $event->getResponse();
        $cookies = $this->getCookies($response);

        $this->updateCookies($response, $cookies, $session);

        $session->save();
    }

    protected function isApplicable(ResponseEvent $event): bool
    {
        if (!$event->isMainRequest()) {
            // Do not apply changes for non-main request
            return false;
        }

        if (!$event->getRequest()->isSecure()) {
            // Do not apply changes if request is not HTTPS
            return false;
        }

        return $this->isCyberSourceCheckout($event->getRequest());
    }

    protected function isCyberSourceCheckout(Request $request): bool
    {
        $route = $request->attributes->get('_route');
        if ($route !== 'oro_checkout_frontend_checkout') {
            return false;
        }
        
        $checkoutId = $request->attributes->get('id');
        $checkout = $checkoutId ? $this->registry->getRepository(Checkout::class)->find($checkoutId) : null;
        $paymentMethod = $checkout ? $checkout->getPaymentMethod() : null;

        return $paymentMethod && str_contains($paymentMethod, CyberSourceChannelType::TYPE);
    }

    private function getCookies(Response $response): array
    {
        $result = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $result[$cookie->getName()] = $cookie;
        }

        return $result;
    }

    /**
     * @param Response $response
     * @param Cookie[] $cookies
     * @param SessionInterface $session
     */
    private function updateCookies(Response $response, array $cookies, SessionInterface $session): void
    {
        $csrfCookie = $cookies['_csrf'] ?? null;
        if ($csrfCookie) {
            $this->replaceCookieUsingSameSiteOption($response, $csrfCookie);
        }

        $httpsCsrfCookie = $cookies['https-_csrf'] ?? null;
        if ($httpsCsrfCookie) {
            $this->replaceCookieUsingSameSiteOption($response, $httpsCsrfCookie);
        }

        $sessionId = $session->getId();
        $sessionName = $session->getName();
        $sessionCookie = $cookies[$sessionName] ?? null;

        if (!$sessionCookie) { // cookie is already set in header
            SessionUtils::popSessionCookie($sessionName, $sessionId); // remove from header
            $sessionCookie = $this->createCookieUsingSameSiteOption($sessionName, $sessionId);
        }
            
        $this->replaceCookieUsingSameSiteOption($response, $sessionCookie, $sessionId);
    }

    private function replaceCookieUsingSameSiteOption(
        Response $response,
        Cookie $oldCookie,
        ?string $replaceValue = null
    ): void {
        $newCookie = $this->copyCookieUsingSameSiteOption($oldCookie, $replaceValue);
        $response->headers->setCookie($newCookie);
    }

    private function copyCookieUsingSameSiteOption(Cookie $source, ?string $replaceValue = null): Cookie
    {
        return Cookie::create(
            $source->getName(),
            $replaceValue ?? $source->getValue(),
            $source->getExpiresTime(),
            $source->getPath(),
            $source->getDomain(),
            true,
            $source->isHttpOnly(),
            $source->isRaw(),
            Cookie::SAMESITE_NONE
        );
    }

    private function createCookieUsingSameSiteOption(string $name, string $value): Cookie
    {
        return Cookie::create(
            $name,
            $value,
            0,
            '/',
            null,
            true,
            false,
            false,
            Cookie::SAMESITE_NONE
        );
    }
}
