<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\FirewallMapInterface;

/**
 * @author Kajetan Kołtuniak <kajetan@koltuniak.com>
 */
final class SessionNameSubscriber implements EventSubscriberInterface
{
    const DEFAULT_SESSION_PREFIX = 'sylius_';

    private $firewallMap;

    private $session;

    /**
     * SessionNameSubscriber constructor.
     * @param FirewallMapInterface|null $firewallMap
     * @param SessionInterface|null $session
     */
    public function __construct(
        FirewallMapInterface $firewallMap = null,
        SessionInterface $session = null
    )
    {
        $this->firewallMap = $firewallMap;
        $this->session = $session;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 15]
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ( null === $this->firewallMap ) {
            return;
        }

        if ( null === $this->session ) {
            return;
        }

        $request = $event->getRequest();
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);
        $cookies = $event->getRequest()->cookies;

        if ( $cookies->has($this->session->getName()) ) {
            if( $this->session->getId() != $cookies->get($this->session->getName()) ) {
                $this->session->setName(static::DEFAULT_SESSION_PREFIX.$firewallConfig->getName());
            }
        }
    }
}