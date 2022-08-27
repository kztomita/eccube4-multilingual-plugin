<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\MultiLingual\Controller\Admin\Content;

use Eccube\Controller\AbstractController;
use Eccube\Entity\News;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\NewsType;
use Eccube\Repository\NewsRepository;
use Eccube\Util\CacheUtil;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    /**
     * @var NewsRepository
     */
    protected $newsRepository;

    /**
     * NewsController constructor.
     *
     * @param NewsRepository $newsRepository
     */
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    private function testLocale(?string $locale)
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        if ($locale === null || !in_array($locale, $locales)) {
            throw $this->createNotFoundException('Unsupported locale.');
        }
    }

    private function _index(Request $request, $page_no = 1, Paginator $paginator, string $locale)
    {
        $qb = $this->newsRepository->getQueryBuilderAll();

        $qb->andWhere('n.locale = :locale')
            ->setParameter('locale', $locale);

        $event = new EventArgs(
            [
                'qb' => $qb,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_CONTENT_NEWS_INDEX_INITIALIZE, $event);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $this->eccubeConfig->get('eccube_default_page_count')
        );

        return [
            'pagination' => $pagination,
            'defaultLocale' => env('ECCUBE_LOCALE', 'ja'),
            'targetLocale' => $locale,
        ];
    }

    /**
     * 新着情報一覧を表示する。
     *
     * Route annotationはroute名も含めて、NewsControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/content/news", name="admin_content_news")
     * @Route("/%eccube_admin_route%/content/news/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_content_news_page")
     * @Template("@MultiLingual/admin/Content/news.twig")
     *
     * @param Request $request
     * @param int $page_no
     * @param Paginator $paginator
     *
     * @return array
     */
    public function index(Request $request, $page_no = 1, Paginator $paginator)
    {
        return $this->_index($request, $page_no, $paginator, env('ECCUBE_LOCALE', 'ja'));
    }

    /**
     * Localeの新着情報一覧を表示する。
     *
     * @Route("/%eccube_admin_route%/content/localenews/{locale}", name="admin_content_news_locale")
     * @Route("/%eccube_admin_route%/content/localenews/{locale}/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_content_news_page_locale")
     * @Template("@MultiLingual/admin/Content/news.twig")
     *
     * URLによって_localeを切り替えたいわけではないので、URLに埋め込むパラメータ名は_localeではなくlocale。
     */
    public function localeIndex(Request $request, $page_no = 1, Paginator $paginator)
    {
        $locale = $request->attributes->get('locale');
        $this->testLocale($locale);

        return $this->_index($request, $page_no, $paginator, $locale);
    }

    private function _edit(Request $request, $id = null, CacheUtil $cacheUtil, string $locale)
    {
        if ($id) {
            $News = $this->newsRepository->find($id);
            if (!$News) {
                throw new NotFoundHttpException();
            }
        } else {
            $News = new \Eccube\Entity\News();
            $News->setPublishDate(new \DateTime());
            $News->setLocale($locale);      // 新規作成時のみlocale設定
        }

        $builder = $this->formFactory
            ->createBuilder(NewsType::class, $News);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'News' => $News,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_CONTENT_NEWS_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$News->getUrl()) {
                $News->setLinkMethod(false);
            }
            $this->newsRepository->save($News);

            $event = new EventArgs(
                [
                    'form' => $form,
                    'News' => $News,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_CONTENT_NEWS_EDIT_COMPLETE, $event);

            $this->addSuccess('admin.common.save_complete', 'admin');

            // キャッシュの削除
            $cacheUtil->clearDoctrineCache();

            $locales = $this->eccubeConfig['multi_lingual_locales'];
            if (!in_array($locale, $locales)) {
                $resp = $this->redirectToRoute('admin_content_news_edit', ['id' => $News->getId()]);
            } else {
                $resp = $this->redirectToRoute(
                    'admin_content_news_edit_locale',
                    [
                        'id' => $News->getId(),
                        'locale' => $locale,
                    ]
                );
            }
            return $resp;
        }

        return [
            'form' => $form->createView(),
            'News' => $News,
            'defaultLocale' => env('ECCUBE_LOCALE', 'ja'),
            'targetLocale' => $locale,
        ];
    }

    /**
     * 新着情報を登録・編集する。
     *
     * Route annotationはroute名も含めて、NewsControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/content/news/new", name="admin_content_news_new")
     * @Route("/%eccube_admin_route%/content/news/{id}/edit", requirements={"id" = "\d+"}, name="admin_content_news_edit")
     * @Template("@MultiLingual/admin/Content/news_edit.twig")
     *
     * @param Request $request
     * @param null $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function edit(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        return $this->_edit($request, $id, $cacheUtil, env('ECCUBE_LOCALE', 'ja'));
    }

    /**
     * Localeの新着情報を登録・編集する。
     *
     * Route annotationはroute名も含めて、NewsControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/content/localenews/{locale}/new", name="admin_content_news_new_locale")
     * @Route("/%eccube_admin_route%/content/localenews/{locale}/{id}/edit", requirements={"id" = "\d+"}, name="admin_content_news_edit_locale")
     * @Template("@MultiLingual/admin/Content/news_edit.twig")
     *
     * @param Request $request
     * @param null $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function localeEdit(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        $locale = $request->attributes->get('locale');
        $this->testLocale($locale);

        return $this->_edit($request, $id, $cacheUtil, $locale);
    }

    /**
     * 指定した新着情報を削除する。
     *
     * Route annotationはroute名も含めて、NewsControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/content/news/{id}/delete", requirements={"id" = "\d+"}, name="admin_content_news_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param News $News
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, News $News, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        log_info('新着情報削除開始', [$News->getId()]);

        $locale = $News->getLocale();

        try {
            $this->newsRepository->delete($News);

            $event = new EventArgs(['News' => $News], $request);
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_CONTENT_NEWS_DELETE_COMPLETE, $event);

            $this->addSuccess('admin.common.delete_complete', 'admin');

            log_info('新着情報削除完了', [$News->getId()]);

            // キャッシュの削除
            $cacheUtil->clearDoctrineCache();
        } catch (\Exception $e) {
            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $News->getTitle()]);
            $this->addError($message, 'admin');

            log_error('新着情報削除エラー', [$News->getId(), $e]);
        }

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        if (!in_array($locale, $locales)) {
            $resp = $this->redirectToRoute('admin_content_news');
        } else {
            $resp = $this->redirectToRoute(
                'admin_content_news_locale',
                ['locale' => $locale]
            );
        }
        return $resp;
    }
}
