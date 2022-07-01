<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Entity\MailTemplate;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\MailTemplateRepository;
use Plugin\MultiLingual\Common\LocaleHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var MailTemplateRepository
     */
    protected $mailTemplateRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct(
        MailTemplateRepository $mailTemplateRepository,
        BaseInfoRepository $baseInfoRepository,
        \Twig_Environment $twig,
        EccubeConfig $eccubeConfig
    )
    {
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->eccubeConfig = $eccubeConfig;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EccubeEvents::MAIL_PASSWORD_RESET => 'onSendMailPasswordReset',
        ];
    }

    /**
     * オリジナルのメールテンプレートに対応するLocaleのメールテンプレート情報を
     * 取得する。
     *
     * @param string $templateFile  オリジナルのメールテンプレートファイル名
     * @param string $locale        Ex. 'en'
     * @return array|null
     *         array exmaple: ['subject' => 'xxxx',
     *                         'template' => 'Mail/forgo_mail_en.twig']
     */
    private function findLocaleTemplate(string $templateFile, string $locale)
    {
        $locale = LocaleHelper::getCurrentRequestLocale();

        $templates = $this->eccubeConfig['multi_lingual_mail_templates'];

        if (!isset($templates[$templateFile]) ||
            !isset($templates[$templateFile][$locale])) {
            return null;
        }

        $localeTemplate = $templates[$templateFile][$locale];
        $localeTemplate['template'] = '@MultiLingual/default/' . $localeTemplate['template'];

        return $localeTemplate;
    }

    public function onSendMailPasswordReset(EventArgs $event): void
    {
        $locale = LocaleHelper::getCurrentRequestLocale();

        if (!in_array($locale, $this->eccubeConfig['multi_lingual_locales'])) {
            return;
        }

        /** @var MailTemplate $MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($this->eccubeConfig['eccube_forgot_mail_template_id']);

        $localeTemplate = $this->findLocaleTemplate($MailTemplate->getFileName(), $locale);
        if (!$localeTemplate) {
            return;
        }

        /** @var \Swift_Message $message */
        $message = $event->getArgument('message');

        /** @var Customer $Customer */
        $Customer = $event->getArgument('Customer');

        $reset_url = $event->getArgument('resetUrl');

        $body = $this->twig->render($localeTemplate['template'], [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'expire' => $this->eccubeConfig['eccube_customer_reset_expire'],
            'reset_url' => $reset_url,
        ]);

        // TODO from書き換え
        // TODO reset urlの書き換え

        $message
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$localeTemplate['subject'])
            ->setBody($body, 'text/plain');
    }
}
