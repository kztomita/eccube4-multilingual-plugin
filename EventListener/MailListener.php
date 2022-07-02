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
use Swift_MimePart;
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
            EccubeEvents::MAIL_CUSTOMER_CONFIRM => 'onSendMailCustomerConfirm',
            EccubeEvents::MAIL_CUSTOMER_COMPLETE => 'onSendMailCustomerComplete',
            EccubeEvents::MAIL_CUSTOMER_WITHDRAW => 'onSendMailCustomerWithdraw',
            EccubeEvents::MAIL_CONTACT => 'onSendMailContact',
            /*
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
     *                         'template' => 'Mail/en/forgot_mail.twig']
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

    /**
     * イベントハンドラで処理すべき仕事があるかを返す。
     *
     * Return:
     * Localeのテンプレートの情報   呼び出し元は、返されたテンプレート情報に
     *                           合わせてSwift_Messageを書き換える必要がある
     * null                      なにもする必要はない
     *
     * @param $templateId
     * @return array|null  Localeテンプレートの情報
     *         array example: ['subject' => 'xxxx',
     *                         'template' => 'Mail/en/forgot_mail.twig']
     */
    private function isThereJob($templateId)
    {
        $locale = LocaleHelper::getCurrentRequestLocale();

        if (!in_array($locale, $this->eccubeConfig['multi_lingual_locales'])) {
            return null;
        }

        /** @var MailTemplate $MailTemplate */
        $MailTemplate = $this->mailTemplateRepository->find($templateId);
        if (!$MailTemplate) {
            return null;
        }

        $localeTemplate = $this->findLocaleTemplate($MailTemplate->getFileName(), $locale);
        if (!$localeTemplate) {
            return null;
        }

        return $localeTemplate;
    }

    /**
     * HTMLメールがあれば削除
     *
     * @param \Swift_Message $message
     * @return void
     */
    private function stripHtmlMail(\Swift_Message $message): void
    {
        $children = $message->getChildren();
        $children = array_filter($children, function ($var) {
            if ($var instanceof Swift_MimePart) {
                if ($var->getContentType() == 'text/html') {
                    return false;
                }
            }
            return true;
        });
        $message->setChildren($children);
    }

    private function updateMessage(\Swift_Message $message, string $subject, string $body): void
    {
        // TODO from名の変更
        $message
            ->setSubject('['.$this->BaseInfo->getShopName().'] '.$subject)
            ->setFrom([$this->BaseInfo->getEmail03() => $this->BaseInfo->getShopName()])
            ->setBody($body, 'text/plain');

        // HTMLメールは対応しないのでaddPart()されたものがあれば削除
        $this->stripHtmlMail($message);
    }

    public function onSendMailCustomerConfirm(EventArgs $event): void
    {
        $localeTemplate = $this->isThereJob($this->eccubeConfig['eccube_entry_confirm_mail_template_id']);
        if (!$localeTemplate) {
            return;
        }

        /** @var \Swift_Message $message */
        $message = $event->getArgument('message');

        /** @var Customer $Customer */
        $Customer = $event->getArgument('Customer');

        $activateUrl = $event->getArgument('activateUrl');

        // TODO $activateUrl書き換え

        $body = $this->twig->render($localeTemplate['template'], [
            'BaseInfo' => $this->BaseInfo,
            'Customer' => $Customer,
            'activateUrl' => $activateUrl,
        ]);

        $this->updateMessage($message, $localeTemplate['subject'], $body);
    }

    public function onSendMailCustomerComplete(EventArgs $event): void
    {
        $localeTemplate = $this->isThereJob($this->eccubeConfig['eccube_entry_complete_mail_template_id']);
        if (!$localeTemplate) {
            return;
        }

        /** @var \Swift_Message $message */
        $message = $event->getArgument('message');

        /** @var Customer $Customer */
        $Customer = $event->getArgument('Customer');

        $body = $this->twig->render($localeTemplate['template'], [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $this->updateMessage($message, $localeTemplate['subject'], $body);
    }

    public function onSendMailCustomerWithdraw(EventArgs $event): void
    {
        $localeTemplate = $this->isThereJob($this->eccubeConfig['eccube_customer_withdraw_mail_template_id']);
        if (!$localeTemplate) {
            return;
        }

        /** @var \Swift_Message $message */
        $message = $event->getArgument('message');

        /** @var Customer $Customer */
        $Customer = $event->getArgument('Customer');

        $body = $this->twig->render($localeTemplate['template'], [
            'Customer' => $Customer,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $this->updateMessage($message, $localeTemplate['subject'], $body);
    }

    public function onSendMailContact(EventArgs $event): void
    {
        $localeTemplate = $this->isThereJob($this->eccubeConfig['eccube_contact_mail_template_id']);
        if (!$localeTemplate) {
            return;
        }

        /** @var \Swift_Message $message */
        $message = $event->getArgument('message');

        $formData = $event->getArgument('formData');

        $body = $this->twig->render($localeTemplate['template'], [
            'data' => $formData,
            'BaseInfo' => $this->BaseInfo,
        ]);

        $this->updateMessage($message, $localeTemplate['subject'], $body);
    }

    public function onSendMailPasswordReset(EventArgs $event): void
    {
        $localeTemplate = $this->isThereJob($this->eccubeConfig['eccube_forgot_mail_template_id']);
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

        // TODO reset urlの書き換え

        $this->updateMessage($message, $localeTemplate['subject'], $body);
    }

    public function onSendMailPasswordResetComplete(EventArgs $event): void
    {
        return;
    }
}
