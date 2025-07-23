#!/bin/sh

# Caminho local do repositório
REPO_DIR="/opnsense-scripts"
# URL do repositório remoto
REPO_URL="https://github.com/habilleandro/opnsense-scripts.git"

# Se o diretório ainda não existe, clona o repositório
if [ ! -d "$REPO_DIR/.git" ]; then
    git clone "$REPO_URL" "$REPO_DIR"
else
    cd "$REPO_DIR" || exit 1
    git fetch origin

    # Verifica se há mudanças
    LOCAL_HASH=$(git rev-parse HEAD)
    REMOTE_HASH=$(git rev-parse origin/main)

    if [ "$LOCAL_HASH" != "$REMOTE_HASH" ]; then
        echo "Atualizações encontradas. Fazendo pull..."
        git pull
    else
        echo "Sem mudanças no repositório."
    fi
fi
