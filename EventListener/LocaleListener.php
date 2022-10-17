<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;


    public function __construct(
        EccubeConfig            $eccubeConfig
    )
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public static function getSubscribedEvents(): array
    {
        // SymfonyのLocaleListenerより優先度を高くする。
        // https://symfony.com/doc/current/translation/locale.html
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $front = strpos($request->getRequestUri(), '/' . env('ECCUBE_ADMIN_ROUTE')) === 0 ? false : true;
        $frontLocale = $this->eccubeConfig['front_locale'] ?? null;

        // FrontのみのLocaleを切り替え。
        // /{_locale}/* へのアクセスの場合は、そのlocaleに再設定される。
        if ($front && $frontLocale !== null) {
            $request->setLocale($frontLocale);
        }
    }
}
