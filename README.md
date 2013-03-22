## 特徴
- NFrameは、URLマッピング、ビューへの変数渡し、SQLラッパーのみを提供するPHPの超軽量フレームワークです。
- Python WSGIや、Ruby Sinatoraに影響を受けていますが、最大の特徴は、フレームワーク自身は **PHPらしくオブジェクト指向でない**ことです。
- PHPベタ書きの良さ（保守性の高さ、学習コストの低さ、高速）を活かしつつ、MVCを取り入れ、少しだけ管理しやすくしてくれます。
- フレームワークのコードは100行程度しかなく、SQLラッパーですら450行しかありません。
- 5台以上のサーバーで月間2000万PV以上のアクセスを安定的に運用している実績があります。

## フォルダ解説

- config apacheなどHTTPサーバーと連携するための設定ファイルを収納します。
- controllers コントローラーをいれます。慣例的にapplication.phpが常に最初に呼ばれ、URLマッピングを定義します。
- lib 主にフレームワークのソースコードが入っています
- public このフォルダは、/public というURLでそのまま公開されます。画像、JS、CSS等の静的ファイルをいれます。
- views HTMlのテンプレートをいれます

## 利用方法方法

- すべてのファイルを任意のフォルダに展開します。
- ApacheのVirtual Hostを有効にし、include {インストールしたパス}/config/app_http_conf.config という行を追加します。
 - 必要に応じて、\<VirtualHost *:80\>に書き換え。また/config/app_http_conf.config内のDOCUMENT_ROOTのパスを書き換えます。
- Apacheを再起動します。
- 指定したURLをブラウザで開きます。
- ローカルで動かす場合は、/etc/hostsにlocal.myapp.comなどのホスト名を追加すると便利です。

## DBの使い方
- /lib/config.php を開き、DBの情報を入れてください。
- その他の使い方は/lib.simple_db.phpをご覧ください。
