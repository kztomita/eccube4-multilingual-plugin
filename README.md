# このプラグインは

このプラグインは、EC-CUBE4の多言語機能を拡張する"実験的な"プラグインです。

EC-CUBE4では多言語機能が追加されました。.envのECCUBE_LOCALEを設定することでサイトの表示を英語等の別言語での表示に切り替えることができます。
ただし、この機能は管理画面を含めた全ページでの切り替えとなります。ECCUBE_LOCALE=enとした場合、フロントだけでなく管理画面も英語表示となります。
実際の使い方としては、管理画面は日本語のままでフロントだけ英語表示にしたり、フロントは日本語と英語どちらでも表示できるようにしたいケースもあるのではないでしょうか？

本プラグインでは、もともとの管理画面およびフロントの日本語ページはそのままに、他の言語でのフロントの表示も可能にします。

どの言語を追加するかはプラグインのservices.yamlで指定できます(複数指定可)。

英語(en)と中国語(cn)を追加した例

    /          - フロント(日本語)
    /admin/    - 管理画面(日本語)
    /en/home   - フロント(英語)
    /cn/home   - フロント(中国語)(*1)

上記の例のように/en/のようなlocale名が挿入されたページが追加されます。
商品表示ページだけでなく会員登録ページやマイページも他言語での表示が可能になります(日本語のフロントの全ページが他言語ページにも存在します)。

また、管理画面から商品情報等も多言語で入力できるようになります。

- 商品名、商品説明
- 規格名
- カテゴリ名
- タグ名
- 一部マスターデータ(注文状況、性別、商品一覧の表示件数、表示順)(*2)

(*1) 英語以外はsrc/Eccube/Resource/locale/に翻訳ファイルを別途用意する必要があります。翻訳ファイルがない場合は日本語で表示されます。

(*2) 一覧はMultiLingual/Entity/Master/で確認してください。


# ライセンス

一部、EC-CUBE本体のファイルを修正する必要があるためGPLとなります。<br />
https://www.ec-cube.net/license/business.php 参照。

# 動作環境

v4.0.6で確認。
v4.1系では動作しません。

# インストール手順

テスト環境を構築しそこで作業してください。<br />
運用中の環境にインストールするような無茶はしないように。

(1) 本リポジトリをcloneしてMultiLingualという名称でEC-CUBEのapp/Plugin/に設置

    git clone https://github.com/kztomita/eccube4-multilingual-plugin.git MultiLingual
    mv MultiLingual <EC-CUBEの設置先>/app/Plugin/


(2) services.yamlの修正

MultiLingual/Resource/config/services.yamlを修正します。

multi_lingual_localesに対応したいlocaleを指定してください。

    multi_lingual_locales: ['en']
    とか
    multi_lingual_locales: ['en', 'cn']

'en'はデフォルトで存在するmessages.en.yamlを参照するので、英語で表示されます。
その他のlocaleについては翻訳テキストを別途用意する必要があります。
翻訳テキストがない場合は、日本語表示となります。

multi_lingual_textには各localeでの店舗名等を指定してください。
現状管理画面からの更新はできません。

プラグインインストール後にmulti_lingual_localesを変更した場合、正常に動作しません。
multi_lingual_localesを変更する場合は、プラグインをアンインストール後、再インストールしてください。

(3) security.yamlの修正

app/config/eccube/packages/security.yamlを修正します。<br />
各Localeページごとにログインページが存在するため、そのための設定を行います。

services.yamlのmulti_lingual_localesに指定した各localeについて、customer_"locale名":という名前でfirewall設定を追加してください。
定義場所はcustomer:より前に定義してください。

修正例
<pre>
             logout:
                 path: admin_logout
                 target: admin_login
+        customer_en:
+            pattern: ^/en/
+            context: customer
+            anonymous: true
+            provider: customer_provider
+            remember_me:
+                secret: '%kernel.secret%'
+                lifetime: 3600
+                name: eccube_remember_me
+                remember_me_parameter: 'login_memory'
+            form_login:
+                check_path: /en/mypage/login
+                login_path: /en/mypage/login
+                csrf_token_generator: security.csrf.token_manager
+                default_target_path: /en/home
+                username_parameter: 'login_email'
+                password_parameter: 'login_pass'
+                use_forward: false
+                success_handler: eccube.security.success_handler
+                failure_handler: eccube.security.failure_handler
+            logout:
+                path: /en/logout
+                target: /en/home
         customer:
             pattern: ^/
             anonymous: true
</pre>

security.yamlのサンプルはMultiLingual/patches/4.0.6/security.yamlにあります。<br />
enのみの追加ならそのままコピーしても構いません。<br />
既存の修正があった場合は、上書きしないように注意してください。


(3) パッチの適用

EC-CUBE本体のファイルをいくつか修正します。

(a) src/Eccube/Doctrine/ORM/Mapping/Driver/AnnotationDriver.php

修正内容
<pre>
@@ -76,9 +76,17 @@ class AnnotationDriver extends \Doctrine
                     $sourceFile = str_replace('\\', '/', $sourceFile);
                     $projectDir = str_replace('\\', '/', $projectDir);
                 }
-                // Replace /path/to/ec-cube to proxies path
-                $proxyFile = str_replace($projectDir, $this->trait_proxies_directory, $path).'/'.basename($sourceFile);
-                if (file_exists($proxyFile)) {
+
+                $entityPath = $projectDir . '/src/Eccube/Entity/';
+                $proxyFile = null;
+                if (strpos($sourceFile, $entityPath) === 0) {
+                    // Entity class
+                    $entityClass = substr($sourceFile, strlen($entityPath));
+                    // Replace /path/to/ec-cube to proxies path
+                    $proxyFile = str_replace($projectDir, $this->trait_proxies_directory, $path).'/'.$entityClass;
+                }
+
+                if ($proxyFile !== null && file_exists($proxyFile)) {
                     require_once $proxyFile;
 
                     $sourceFile = $proxyFile;
</pre>

4.0.6での修正後のファイルはMultiLingual/patches/4.0.6/AnnotationDriver.phpにあるのでコピーしても構いません。<br />
管理画面のマスタデータ管理から多言語入力できなくてよければ、本ファイルは修正しなくても構いません。その場合、テーブル選択プルダウンに該当テーブルが表示されません。

(b) src/Eccube/Controller/Admin/Product/ClassNameController.php

規格名更新時にイベントを発行するようにパッチを当てます。

修正内容
<pre>
@@ -115,6 +115,19 @@ class ClassNameController extends Abstra
                 if ($editForm->isSubmitted() && $editForm->isValid()) {
                     $this->classNameRepository->save($editForm->getData());
 
+                    // @@@ 追加
+                    $event = new EventArgs(
+                        [
+                            'form' => $form,
+                            'editForm' => $editForm,
+                            'TargetClassName' => $editForm->getData(),
+                        ],
+                        $request
+                    );
+
+                    $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_CLASS_NAME_INDEX_COMPLETE, $event);
+                    // @@@
+
                     $this->addSuccess('admin.common.save_complete', 'admin');
 
                     return $this->redirectToRoute('admin_product_class_name');
</pre>

4.0.6での修正後のファイルはMultiLingual/patches/4.0.6/ClassNameController.phpにあるのでコピーしても構いません。<br />
多言語での規格名の更新ができなくてよければ、本ファイルは修正しなくても構いません。


(c) src/Eccube/Controller/Admin/Product/ClassCategoryController.php

規格カテゴリ更新時にイベントを発行するようにパッチを当てます。

<pre>
@@ -133,6 +133,20 @@ class ClassCategoryController extends Ab
                 $editForm->handleRequest($request);
                 if ($editForm->isSubmitted() && $editForm->isValid()) {
                     $this->classCategoryRepository->save($editForm->getData());
+
+                    // @@@ Added
+                    $event = new EventArgs(
+                        [
+                            'form' => $form,
+                            'editForm' => $editForm,
+                            'ClassName' => $ClassName,
+                            'TargetClassCategory' => $TargetClassCategory,
+                        ],
+                        $request
+                    );
+                    $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_CLASS_CATEGORY_INDEX_COMPLETE, $event);
+                    // @@@
+
                     $this->addSuccess('admin.common.save_complete', 'admin');
 
                     return $this->redirectToRoute('admin_product_class_category', ['class_name_id' => $ClassName->getId()]);
</pre>

4.0.6での修正後のファイルはMultiLingual/patches/4.0.6/ClassCategoryController.phpにあるのでコピーしても構いません。<br />
多言語での規格カテゴリ名の更新ができなくてよければ、本ファイルは修正しなくても構いません。


(4) プラグインのインストールと有効化

    cd <EC-CUBEの設置先>
    bin/console eccube:plugin:install --code MultiLingual
    bin/console eccube:plugin:enable --code MultiLingual

これでインストールは完了です。

https://インストール先/en/home

のようにアクセスすれば各localeでページ表示されるのが確認できます。ページ右上のプルダウンからlocaleを切り替えることもできます。


# アンインストール手順

    bin/console eccube:plugin:uninstall --code MultiLingual

enable時に以下にテンプレートファイルをコピーしているので、必要に応じて削除してください。

    app/templates/default/Block, Form


EC-CUBE本体に当てたパッチはそのままでも動作に影響はありません。


# 現状後回しにしていること

一部日本語表示が残っています。

- トップページの新着情報は多言語対応していない
- アカウント登録時の職業選択プルダウン
- 都道府県選択プルダウン

他にもあるかも。

現状、プラグインインストール後にservices.yamlでmulti_lingual_localesを変更しても正常に動作しません。変更用のメンテナンスコマンドの追加が必要かも。

その他いろいろ。

当面は、バージョンは0.0.1のままで更新していきます。

