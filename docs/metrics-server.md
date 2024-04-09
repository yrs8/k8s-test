# metrics-server
## metrics-server の導入
クラスター内のリソース使用状況データを取得するのに必要となります。具体的には HorizontalPodAutoscaler で CPU Utilization をターゲットとしたスケールアウトで必要となります。

### metrics-server インストール
1. [Helm](helm.md) をインストール
1. Helm リポジトリに metrics-server を追加
   ```bash
   $ helm repo add metrics-server https://kubernetes-sigs.github.io/metrics-server/
   ```
1. Helm を使用して metrics-server をインストール
   ```bash
   $ helm upgrade --install metrics-server metrics-server/metrics-server
   ```
1. metrics-server のログを確認
   ```bash
   $ kubectl logs deployment/metrics-server -n kube-system
   ```
1. metrics-server のデプロイ状況を確認
   ```bash
   $ kubectl get deployments --all-namespaces | grep metrics-server
   ```
1. --kubelet-insecure-tls オプションを追加
   ```bash
   $ kubectl edit deploy metrics-server -n kube-system

      - args:
      - --kubelet-insecure-tls
   ```
1. 編集が正しく反映されたことを確認
   ```bash
   $ kubectl get deploy metrics-server -n kube-system -o yaml
   ```