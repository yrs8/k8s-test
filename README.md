# k8s-test
## 概要
* 自身の env(development or production)をレスポンスとして返すシンプルな web アプリケーション
* Kubernetes のマニフェストファイルを管理

## マニフェスト設計の考慮点
* Pod のスケーリング: HorizontalPodAutoscaler を使用して、CPU 使用率に基づいて Pod の数を自動的にスケールアップまたはスケールダウン
* Pod の移行: PodDisruptionBudget を使用して、Podの移行中に同時にダウンできる Pod の数を制限
* マニフェストの自動デプロイ: Argo CD を使ったマニフェストの自動デプロイ
* YAML の分割・管理: YAML をモジュール単位し、Kustomize を利用して環境(dev/prod)差分を管理
* ConfigMap の利用: Nginx の設定管理に ConfigMap を利用
* Argo CD の YAML 管理: Argo CD の設定も YAML で管理
* ノードの割当: affinity を利用してどのノードに配置するかを制御

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
1. ブラウザからアクセス
   ```
   http://localhost:8080/
   ```
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

### Kubernetes デプロイ方法
* はじめに
   * マニフェストは Kubernetes v1.28.6 での動作を確認しています。
   * デプロイは、[Argo CD](docs/argocd.md) による持続的デリバリーを利用しています。
   * 手動で適用する場合は、以下手順を参考にしてください。

1. リポジトリをクローン
   ```bash
   $ git clone https://github.com/yrs8/k8s-test.git
   ```
1. 最終的な YAML 設定を生成
   * **[本番環境]**
      ```bash
      $ cd manifests/kustomize/overlays/prod
      $ kustomize build . > all-in-one-prod.yaml
      ```
   * **[開発環境]**
      ```bash
      $ cd manifests/kustomize/overlays/dev
      $ kustomize build . > all-in-one-dev.yaml
      ```
     * [補足] Argo CD から出力する場合
         ```bash
         $ argocd app manifests k8s-test > all-in-one-prod.yaml
         ```
1. マニフェストの適用・デプロイ
   * **[本番クラスター]**
      ```bash
      $ kubectl apply -f all-in-one-prod.yaml
      ```
   * **[開発クラスター]**
      ```bash
      $ kubectl apply -f all-in-one-dev.yaml
      ```
1. デプロイ状態を確認
   ```bash
   $ kubectl get pods -l app=app-nginx
   $ kubectl get pods -l app=app-php
   ```
1. ブラウザから動作確認
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
   $ kubectl delete -f deployment.yaml
   $ kubectl delete deployment app-nginx app-php
   $ kubectl delete service app-nginx-service app-php-service
   ```
* レジストリへプッシュする方法 ※この辺は環境やレジストリに合わせて適宜読み替え
   * **[本番環境]**
      ```bash
      $ docker compose --env-file .env.prod build

      $ docker tag app-nginx-prod:latest <container.registry>/app-nginx-prod:v1.0
      $ docker tag app-php-prod:latest <container.registry>/app-php-prod:v1.0

      $ docker image push <container.registry>/app-nginx-prod:v1.0
      $ docker image push <container.registry>/app-php-prod:v1.0
      ```
   * **[開発環境]**
      ```bash
      $ docker compose --env-file .env.dev build

      $ docker tag app-nginx-dev:latest <container.registry>/app-nginx-dev:v1.0
      $ docker tag app-php-dev:latest <container.registry>/app-php-dev:v1.0

      $ docker image push <container.registry>/app-nginx-dev:v1.0
      $ docker image push <container.registry>/app-php-dev:v1.0
      ```

### 注意事項
* Kubernetes のデプロイは、Kubernetes クラスタが既にセットアップされていることを前提としています。
* ローカルでの開発は、Docker と Docker Compose がインストールされていることを前提としています。