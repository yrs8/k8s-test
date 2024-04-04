# k8s-test
## 概要
* 自身の env(development or production)をレスポンスとして返すシンプルな web サーバ
* Kubernetes にデプロイするためのマニフェストファイル

## 環境構築手順
### ローカル開発
* 必要な環境やコマンド ※()内は利用したバージョンです。
   ```
   Docker (Docker version 25.0.3, build 4debf41)
   Docker Compose (Docker Compose version v2.24.6-desktop.1)
   Git (git version 2.44.0)
   ```
1. Docker イメージのビルド
   * **[本番環境]**
      ```bash
      $ docker compose --env-file .env.prod build
      ```
   * **[開発環境]**
      ```bash
      $ docker compose --env-file .env.dev build
      ```
1. コンテナの起動
   * **[本番環境]**
      ```bash
      $ docker compose --env-file .env.prod up -d
      ```
   * **[開発環境]**
      ```bash
      $ docker compose --env-file .env.dev up -d
1. コンテナの停止
   * **[本番環境]**
      ```bash
      $ docker compose --env-file .env.prod stop
      or
      $ docker compose --env-file .env.prod down
      ```
   * **[開発環境]**
      ```bash
      $ docker compose --env-file .env.dev stop
      or
      $ docker compose --env-file .env.dev down
      ```
1. ブラウザからアクセス
   ```
   http://localhost:8080/
   ```
1. レジストリへプッシュ ※この辺は環境に合わせて適宜読み替え
   * **[本番環境]**
      ```bash
      $ docker compose --env-file .env.prod build

      $ docker tag app-nginx-prod:latest <container.registry>/app-nginx-prod:v1.0
      $ docker tag app-php-prod:latest <container.registry>/app-php-prod:v1.0

      $ docker image push yrs8/app-nginx-prod:v1.0
      $ docker image push yrs8/app-php-prod:v1.0
      ```
   * **[開発環境]**
      ```bash
      $ docker compose --env-file .env.dev build

      $ docker tag app-nginx-dev:latest <container.registry>/app-nginx-dev:v1.0
      $ docker tag app-php-dev:latest <container.registry>/app-php-dev:v1.0

      $ docker image push yrs8/app-nginx-dev:v1.0
      $ docker image push yrs8/app-php-dev:v1.0
      ```

### Kubernetes デプロイ方法
はじめに：マニフェストは Kubernets v1.28.6 での動作を確認しています。

1. リポジトリをクローン
   ```bash
   $ git clone https://github.com/yrs8/k8s-test.git
   ```
1. ディレクトリに移動
   ```bash
   $ cd k8s-test/kubernetes-manifests
   ```
2. マニフェストの適用・デプロイ
   * **[本番クラスター]**
      ```bash
      $ kubectl apply -f deployment-prod.yaml
      ```
   * **[開発クラスター]**
      ```bash
      $ kubectl apply -f deployment-dev.yaml
      ```
3. デプロイ状態を確認
   ```bash
   $ kubectl get pods -l app=app-nginx
   $ kubectl get pods -l app=app-php
   ```
4. ブラウザから動作確認
   1. Service の EXTERNAL-IP を確認
      ```bash
      $ kubectl get svc app-nginx-service
      ```
   1. ブラウザからアクセス
      ```
      http://<EXTERNAL-IP>/
      ```

### メモ
* リソースを削除したいときに使いそうなコマンド
   ```bash
   $ kubectl delete -f deployment-dev.yaml
   $ kubectl delete deployment app-nginx app-php
   $ kubectl delete service app-nginx-service app-php-service
   ```

### 注意事項
* Kubernetes のデプロイは、Kubernetes クラスタが既にセットアップされていることを前提としています。
* ローカルでの開発は、Docker と Docker Compose がインストールされていることを前提としています。