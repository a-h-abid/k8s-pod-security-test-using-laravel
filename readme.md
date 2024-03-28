# Kubernetes Pod Security Admission Test (using Laravel)

This is just a draft project to test the Pod Security Admission.

## Usage

1. `cd docker && docker compose build && cd ..`
1. `kind create cluster --config=kubernetes/kind-cluster.yaml`
1. `kind load docker-image kind-apps--lv:latest`
1. `kubectl label --overwrite ns default pod-security.kubernetes.io/warn=baseline`
1. `kubectl label --dry-run=server --overwrite ns --all pod-security.kubernetes.io/enforce=baseline`
1. `kubectl apply -f ./kubernetes/deploy/`
