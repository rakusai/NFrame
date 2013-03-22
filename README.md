** 特徴
- NFrameは、URLマッピング、ビューへの変数渡し、SQLラッパーのみを提供する超軽量フレームワークです。
- Python WSGIや、Ruby Sinatoraに影響を受けています。
- PHPベタ書きの良さ（保守性の高さ、学習コストの低さ、高速）を活かしつつ、MVCを取り入れ少しだけ管理しやすくしてくれます。
- コード行数はURLマッピング（40行）、ビューへの変数渡し（50行）、SQLラッパー（450行）しかありません。

** フォルダ解説

- views HTMlのテンプレートをいれます
- public このフォルダは、/public というURLでそのまま公開されます。画像、JS、CSS等の静的ファイルをいれます。
- lib 主にフレームワークのソースコードが入っています
- controllers コントローラーをいれます。慣例的にapplication.phpが常に最初に呼ばれ、URLマッピングを定義します。
- config apacheなどHTTPサーバーと連携するための設定ファイルを収納します。

** 利用方法方法

- すべてのファイルを展開します。
- ApacheのVirtual Hostにinclude {インストールしたパス}/config/app_apache_conf.configをセットします。
-- 必要に応じて、<VirtualHost *:80>に書き換え。また/config/app_apache_conf.configのパスを書き換えます。
- Apacheを再起動します。

** DBの使い方
- /lib/config.php を開き、DBの情報を入れてください。
- 