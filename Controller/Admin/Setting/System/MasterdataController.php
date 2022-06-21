<?php

namespace Plugin\MultiLingual\Controller\Admin\Setting\System;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class MasterdataController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Setting\System\MasterdataController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Setting\System\MasterdataController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * マスタデータ管理のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、MasterdataControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/setting/system/masterdata", name="admin_setting_system_masterdata", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/setting/system/masterdata/{entity}/edit", name="admin_setting_system_masterdata_view", methods={"GET", "POST"})
     * @Template("MultiLingual/Resource/template/admin/Setting/System/masterdata.twig")
     */
    public function index(Request $request, $entity = null)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->index($request, $entity);
    }

    /**
     * @Route("/%eccube_admin_route%/setting/system/masterdata/edit", name="admin_setting_system_masterdata_edit", methods={"GET", "POST"})
     * @Template("MultiLingual/Resource/template/admin/Setting/System/masterdata.twig")
     */
    public function edit(Request $request)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->edit($request);
    }
}

