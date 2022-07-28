# このプラグインは

このプラグインは、EC-CUBE4に越境ECとして機能を追加する"実験的な"プラグインです。<br />
EC-CUBE4で追加された多言語機能を利用して以下の機能を追加します。

- 商品ページやマイページ各ページを多言語表示できるようになる
- 管理画面から商品名、商品説明、カテゴリ名を多言語で入力できる
- 対応localeはservices.yamlで指定可。

英語についてはデフォルトで翻訳テキストが存在するためそのまま英語表示できますが、
その他の言語の表示も行いたい場合はsrc/Eccube/Resource/locale/にあるメッセージ用のファイルを用意する必要があります。

実際に越境ECとして使うには不足している機能はある。

# ライセンス

一部、EC-CUBE本体のファイルを修正する必要があるためGPLとなります。<br />
https://www.ec-cube.net/license/business.php 参照。

# 動作環境

v4.0.6で確認。
v4.1系では動作しない。

# インストール手順

テスト環境を構築しそこで行ってください。

(1) 本リポジトリをcloneしてMultiLingualという名称でEC-CUBEのapp/Plugin/に設置する。

    git clone xxxxx.git MultiLingual
    mv MultiLingual <EC-CUBEの設置先>/app/Plugin/

security.yamlの修正
パッチの適用

    bin/console eccube:plugin:install --code MultiLingual
    bin/console eccube:plugin:enable --code MultiLingual

# 現状後回しにしていること

一部日本語表示が残っています。
- トップページの新着情報は多言語対応していない
- アカウント登録時の職業選択プルダウン

