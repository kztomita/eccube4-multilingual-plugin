<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Page;
use Eccube\Request\Context;
use Plugin\MultiLingual\Common\LocaleHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * TwigInitializeListenerで設定したページタイトルを上書きする
 */
class TwigListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Context
     */
    private $requestContext;

    public function __construct(
        EccubeConfig $config,
        Environment $twig,
        Context $context
    )
    {
        $this->eccubeConfig = $config;
        $this->twig = $twig;
        $this->requestContext = $context;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->requestContext->isAdmin()) {
            return;
        }

        $globals = $this->twig->getGlobals();
        if (!isset($globals['Page'])) {
            return;
        }
        /** @var Page $Page */
        $Page = $globals['Page'];

        // localeに応じたページ名をservices.yamlから取得する

        $locale = LocaleHelper::getCurrentRequestLocale();

        $multiLingualPages = $this->eccubeConfig['multi_lingual_pages'];

        if (!isset($multiLingualPages[$Page->getUrl()]) ||
            !isset($multiLingualPages[$Page->getUrl()][$locale])) {
            return;
        }
        $pageConfig = $multiLingualPages[$Page->getUrl()][$locale];

        $this->twig->addGlobal('title', $pageConfig['title']);
    }
}
