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
            /*
            EccubeEvents::MAIL_CUSTOMER_CONFIRM => '',
            EccubeEvents::MAIL_CUSTOMER_COMPLETE => '',
            EccubeEvents::MAIL_CUSTOMER_WITHDRAW => '',
            EccubeEvents::MAIL_CONTACT => '',
            EccubeEvents::MAIL_ORDER => '',
            EccubeEvents::MAIL_ADMIN_CUSTOMER_CONFIRM => '',
            EccubeEvents::MAIL_ADMIN_ORDER => '',
            */
            EccubeEvents::MAIL_PASSWORD_RESET => 'onSendMailPasswordReset',
            // 現在パスワード変更完了メールは送信されていない
            //EccubeEvents::MAIL_PASSWORD_RESET_COMPLETE => 'onSendPasswordResetComplete',
            // TODO 出荷完了通知メールのイベントがない
        ];
    }

    /**
     * オリジナルのメールテンプレートに対応するLocaleのメールテンプレート情報を
     * 取得する。
     *
     * @param string $templateFile  オリジナルのメールテンプレートファイル名
     * @param string $locale        Ex. 'en'
     * @return array|null
     *         array example: ['subject' => 'xxxx',
     *                         'template' => 'Mail/forgo_mail_en.twig']
     */
    private function findLocaleTemplate(string $templateFile, string $locale)
    {
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
        // TODO addPart()されたものの削除が必要

        $message
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$localeTemplate['subject'])
            ->setBody($body, 'text/plain');
    }

    public function onSendMailPasswordResetComplete(EventArgs $event): void
    {
        return;
    }
}
