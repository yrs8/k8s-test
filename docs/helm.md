# Helm
## Helm の導入
Kubernetes のパッケージマネージャーとして、Helm を利用します。


## Helm のインストール
参考: https://helm.sh/docs/intro/install/

1. Helm の公開鍵を取得し、システムのキーリングに追加
   ```bash
   $ curl https://baltocdn.com/helm/signing.asc | gpg --dearmor | sudo tee /usr/share/keyrings/helm.gpg > /dev/null
   ```
1. HTTPS 通信を可能にするためのパッケージをインストール
   ```bash
   $ sudo apt-get install apt-transport-https --yes
   ```
1. Helm のリポジトリをシステムのソースリストに追加
   ```bash
   $ echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/helm.gpg] https://baltocdn.com/helm/stable/debian/ all main" | sudo tee /etc/apt/sources.list.d/helm-stable-debian.list
   ```
1. システムのパッケージリストを更新
   ```bash
   $ sudo apt-get update
   ```
1. Helm をインストールし
   ```bash
   $ sudo apt-get install helm
   ```