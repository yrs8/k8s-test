# Argo CD
## Argo CD を使用した Kubernetes への継続的デリバリー

イメージ画像
<img src="https://github.com/yrs8/k8s-test/assets/51853487/603e8ccc-d85b-4e9d-9ea9-49135d805208" width= "85%" >

## 導入背景

Argo CD は、Kubernetes クラスタ上でのアプリケーションの持続的デリバリーを実現するツールです。GitOps の原則に基づいており、Kubernetes マニフェストが Git リポジトリに保存され、Argo CD がそれらをクラスタに同期します。これにより、アプリケーションのデプロイメントが自動化され、可視性が向上し、変更の追跡が容易にします。

## 手順
### Argo CD 構築
参考: https://argo-cd.readthedocs.io/en/stable/getting_started/

1. Argo CD インストール
   ```bash
   $ kubectl create namespace argocd
   $ kubectl apply -n argocd -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/install.yaml
   ```
1. argocd-server service type を LoadBalancer へ変更
   ```bash
   $ kubectl patch svc argocd-server -n argocd -p '{"spec": {"type": "LoadBalancer"}}'
   ```
1. 初期パスワード確認
   ```bash
   $ kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d; echo
   ```
1. 初期パスワード変更
   1. 確認
      ```bash
      $ kubectl get svc argocd-server -n argocd ← <EXTERNAL-IP> 確認
      $ kubectl get pod -n argocd ← Pod 名称確認
      ```
   1. argocd-server に入る
      ```bash
      $ kubectl exec -it argocd-server-xxxxxxxxxx-xxxxx -n argocd -- /bin/bash
   1. パスワード変更
      ```bash
      $ argocd@argocd-server:~$ argocd login <EXTERNAL-IP> --username admin --password <初期パスワード>
      $ argocd@argocd-server:~$ argocd account update-password
      *** Enter password of currently logged in user (admin): ← <初期パスワード>
      *** Enter new password for user admin: ← <新規パスワード>
      *** Confirm new password for user admin: ← <新規パスワード>
      Password updated
      Context '<EXTERNAL-IP>' updated
      $ argocd@argocd-server:~$ argocd logout <EXTERNAL-IP>
      $ argocd@argocd-server:~$ exit
      ```
1. 初期パスワード用 secret 削除
   ```bash
   $ kubectl delete secret -n argocd argocd-initial-admin-secret
   ```
1. アクセス
   ```
   https://<EXTERNAL-IP>/
   admin / <新規パスワード> でログイン可能
   ```

### アプリケーションの作成
1. Argo CD CLI をインストール（手順は省略）
   * https://argo-cd.readthedocs.io/en/stable/cli_installation/
1. Argo CD にログイン
   ```bash
   $ argocd login <EXTERNAL-IP> --username admin --password <新規パスワード> --insecure
   ```
1. アプリケーションを作成 （例のため環境に合わせて適宜変更）
   * **[本番クラスター]**
      ```bash
      argocd app create k8s-test \
        --repo https://github.com/yrs8/k8s-test.git \
        --path kubernetes-manifests/prod \
        --dest-namespace default \
        --dest-server https://kubernetes.default.svc \
        --sync-policy automated
      ```
   * **[開発クラスター]**
      ```bash
      argocd app create k8s-test-dev \
        --repo https://github.com/yrs8/k8s-test.git \
        --path kubernetes-manifests/dev \
        --dest-namespace default \
        --dest-server https://kubernetes.default.svc \
        --sync-policy automated
      ```
1. 作成したアプリケーションの一覧を表示
   ```bash
   $ argocd app list
   ```
1. アプリケーションを同期
   ```bash
   $ argocd app sync k8s-test
   ```

### アクセス方法
   ```
   https://<EXTERNAL-IP>/
   admin / <新規パスワード> でログイン可能
   ```

## メモ
### 証明書適用
1. TLS 用の secret 作成
   ```bash
   $ kubectl create secret tls argocd-server-tls --cert=<cert ファイル> --key=<key ファイル> -n argocd
   ```
1. argocd-server-tls を適用
   ```bash
   # volumeMounts.mountPath "/app/config/tls" の name を argocd-server-tls へと変更する
   $ kubectl patch deployment argocd-server -n argocd -p '
   spec:
     template:
       spec:
         containers:
         - name: argocd-server
           volumeMounts:
           - mountPath: /app/config/tls
             name: argocd-server-tls
         volumes:
         - name: argocd-server-tls
           secret:
             secretName: argocd-server-tls
   '
   ```
1. 変更内容確認
   ```bash
   $ kubectl get deployment argocd-server -n argocd -o jsonpath='{.spec.template.spec.containers[0]}' | jq
   ```
1. 再起動して適用
   ```bash
   $ kubectl rollout restart deployment/argocd-server -n argocd
   ```